<?php

namespace App\Http\Controllers;

use App\Models\Colocations;
use App\Models\settlements;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettlementsController extends Controller
{
    private function authorizeMember(Colocations $colocation): void
    {
        $isMember = $colocation->members()
            ->where('user_id', Auth::id())
            ->wherePivotNull('left_at')
            ->exists();

        if (!$isMember) {
            abort(403, "Vous n'êtes pas membre actif de cette colocation.");
        }
    }

    /**
     * Mark a settlement as paid (debtor confirms payment to creditor).
     * Only the debtor can mark their own debt as paid.
     */
    public function markPaid(Colocations $colocation, settlements $settlement)
    {
        $this->authorizeMember($colocation);

        if ($settlement->colocation_id !== $colocation->id) {
            abort(403);
        }

        $isOwner = $colocation->members()
            ->where('user_id', Auth::id())
            ->wherePivot('role', 'owner')
            ->exists();

        if ($settlement->debtor_id !== Auth::id() && !$isOwner) {
            abort(403, 'Seul le débiteur ou le propriétaire peut marquer ce paiement comme effectué.');
        }

        if ($settlement->is_paid) {
            return back()->with('error', 'Ce paiement a déjà été enregistré.');
        }

        $settlement->update(['is_paid' => true]);

        // Update reputation: creditor +1
        $settlement->creditor->increment('reputation_score');
        // Also +1 for honest debtor
        Auth::user()->increment('reputation_score');

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', 'Paiement enregistré ! Réputation mise à jour.');
    }

    public static function getBalances(Colocations $colocation): array
    {
        $allMembers = $colocation->members()->withPivot('joined_at', 'left_at')->get();
        $expenses = $colocation->expenses()->get();
        
        $balances = [];
        foreach ($allMembers as $m) {
            $balances[$m->id] = 0.0;
        }

        foreach ($expenses as $expense) {
            // Find who was active at that date
            $expenseDate = \Carbon\Carbon::parse($expense->date)->startOfDay();
            
            $activeAtDate = $allMembers->filter(function($m) use ($expenseDate) {
                // If created today, date might be today. We just check date.
                $joined = \Carbon\Carbon::parse($m->pivot->joined_at)->startOfDay();
                $left = $m->pivot->left_at ? \Carbon\Carbon::parse($m->pivot->left_at)->startOfDay() : null;
                
                // Active if joined before/on expense date and hasn't left before expense date
                $isActive = $joined->lte($expenseDate) && ($left === null || $left->gte($expenseDate));

                // If they are banned, check if they were banned ON or BEFORE the expense date
                $wasBannedThen = false;
                if ($m->is_banned && $m->banned_at) {
                    $banned = \Carbon\Carbon::parse($m->banned_at)->startOfDay();
                    if ($banned->lte($expenseDate)) {
                        $wasBannedThen = true;
                    }
                }
                
                return $isActive && !$wasBannedThen;
            });

            $count = $activeAtDate->count();
            if ($count > 0) {
                $share = $expense->amount / $count;
                foreach ($activeAtDate as $m) {
                    $balances[$m->id] -= $share;
                }
            }
            
            // Payer gets credited
            if (isset($balances[$expense->payer_id])) {
                $balances[$expense->payer_id] += $expense->amount;
            }
        }

        // Apply already paid settlements
        $paidSettlements = $colocation->settlements()->where('is_paid', true)->get();
        foreach ($paidSettlements as $s) {
            if (isset($balances[$s->debtor_id])) {
                $balances[$s->debtor_id] += $s->amount; // Debtor paid their debt -> balance goes up towards zero
            }
            if (isset($balances[$s->creditor_id])) {
                $balances[$s->creditor_id] -= $s->amount; // Creditor got paid -> balance goes down towards zero
            }
        }
        
        // Round safely to 2 decimals
        $rounded = [];
        foreach ($balances as $id => $val) {
            $rounded[$id] = round($val, 2);
        }
        return $rounded;
    }

    /**
     * Recalculate and regenerate all UNPAID settlements for a colocation.
     * Called automatically after expense changes.
     * Computes raw pairwise debts without applying advanced simplification.
     */
    public static function recalculate(Colocations $colocation): void
    {
        $allMembers = $colocation->members()->withPivot('joined_at', 'left_at')->get();
        $expenses = $colocation->expenses()->get();

        // matrix[debtor_id][creditor_id] = total_raw_amount_owed
        $matrix = [];

        foreach ($expenses as $expense) {
            $expenseDate = \Carbon\Carbon::parse($expense->date)->startOfDay();

            $activeAtDate = $allMembers->filter(function($m) use ($expenseDate) {
                $joined = \Carbon\Carbon::parse($m->pivot->joined_at)->startOfDay();
                $left = $m->pivot->left_at ? \Carbon\Carbon::parse($m->pivot->left_at)->startOfDay() : null;
                
                $isActive = $joined->lte($expenseDate) && ($left === null || $left->gte($expenseDate));

                $wasBannedThen = false;
                if ($m->is_banned && $m->banned_at) {
                    $banned = \Carbon\Carbon::parse($m->banned_at)->startOfDay();
                    if ($banned->lte($expenseDate)) {
                        $wasBannedThen = true;
                    }
                }
                
                return $isActive && !$wasBannedThen;
            });

            $count = $activeAtDate->count();
            if ($count > 0) {
                $share = $expense->amount / $count;
                foreach ($activeAtDate as $m) {
                    // Payer does not owe themselves
                    if ($m->id !== $expense->payer_id) {
                        if (!isset($matrix[$m->id][$expense->payer_id])) {
                            $matrix[$m->id][$expense->payer_id] = 0.0;
                        }
                        $matrix[$m->id][$expense->payer_id] += $share;
                    }
                }
            }
        }

        // Deduct settlements that have already been paid manually
        $paidSettlements = $colocation->settlements()->where('is_paid', true)->get();
        foreach ($paidSettlements as $s) {
            if (isset($matrix[$s->debtor_id][$s->creditor_id])) {
                $matrix[$s->debtor_id][$s->creditor_id] -= $s->amount;
            } else {
                // If they overpaid or paid a debt that no longer exists (due to expense deletion)
                // we cap it at zero below either way, but we can set it so it's tracked
                $matrix[$s->debtor_id][$s->creditor_id] = -$s->amount;
            }
        }

        // Delete un-paid settlements and regenerate based on exact pairs
        DB::transaction(function () use ($colocation, $matrix) {
            $colocation->settlements()->where('is_paid', false)->delete();

            foreach ($matrix as $debtor => $creditors) {
                foreach ($creditors as $creditor => $amount) {
                    $amt = round($amount, 2);
                    // Only generate a settlement if the raw remainder is significant (> 0.01)
                    if ($amt > 0.01) {
                        settlements::create([
                            'colocation_id' => $colocation->id,
                            'debtor_id'     => $debtor,
                            'creditor_id'   => $creditor,
                            'amount'        => $amt,
                            'is_paid'       => false,
                        ]);
                    }
                }
            }
        });
    }
}
