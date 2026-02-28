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
                
                return $joined->lte($expenseDate) && ($left === null || $left->gte($expenseDate));
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
     */
    public static function recalculate(Colocations $colocation): void
    {
        $balances = self::getBalances($colocation);

        // Filter out those extremely close to 0 to avoid float precision dirt
        foreach ($balances as $k => $v) {
            if (abs($v) < 0.01) {
                unset($balances[$k]);
            }
        }

        // Delete un-paid settlements and regenerate
        DB::transaction(function () use ($colocation, $balances) {
            $colocation->settlements()->where('is_paid', false)->delete();

            $debtors   = array_filter($balances, fn($b) => $b < 0);
            $creditors = array_filter($balances, fn($b) => $b > 0);

            asort($debtors);   // most negative first
            arsort($creditors); // most positive first

            $di = array_keys($debtors);
            $ci = array_keys($creditors);
            $dv = array_values($debtors);
            $cv = array_values($creditors);

            $i = $j = 0;
            while ($i < count($dv) && $j < count($cv)) {
                $amt = round(min(abs($dv[$i]), $cv[$j]), 2);
                if ($amt > 0.01) {
                    settlements::create([
                        'colocation_id' => $colocation->id,
                        'debtor_id'     => $di[$i],
                        'creditor_id'   => $ci[$j],
                        'amount'        => $amt,
                        'is_paid'       => false,
                    ]);
                }
                $dv[$i] += $amt;
                $cv[$j] -= $amt;
                if (abs($dv[$i]) < 0.01) $i++;
                if (abs($cv[$j]) < 0.01) $j++;
            }
        });
    }
}
