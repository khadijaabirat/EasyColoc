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

        if ($settlement->debtor_id !== Auth::id()) {
            abort(403, 'Seul le débiteur peut marquer ce paiement comme effectué.');
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

    /**
     * Recalculate and regenerate all settlements for a colocation.
     * Called automatically after expense changes.
     */
    public static function recalculate(Colocations $colocation): void
    {
        $members     = $colocation->members()->wherePivotNull('left_at')->get();
        $memberCount = $members->count();

        if ($memberCount < 2) {
            return;
        }

        $totalAmount = $colocation->expenses()->sum('amount');
        $share       = round($totalAmount / $memberCount, 2);

        // Build paid map
        $paid = [];
        foreach ($members as $m) {
            $paid[$m->id] = round(
                $colocation->expenses()->where('payer_id', $m->id)->sum('amount'),
                2
            );
        }

        // Net balance: positive = creditor, negative = debtor
        $balances = [];
        foreach ($members as $m) {
            $bal = round($paid[$m->id] - $share, 2);
            if (abs($bal) > 0.01) {
                $balances[$m->id] = $bal;
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
