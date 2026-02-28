<?php

namespace App\Http\Controllers;

use App\Models\Colocations;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MembershipsController extends Controller
{
    /**
     * Owner kicks a member out of the colocation.
     * Per spec: if the member has a debt, that debt is transferred to the owner.
     */
    public function removeMember(Colocations $colocation, User $user)
    {
        // Only the owner of this colocation may remove members
        $isOwner = $colocation->members()
            ->where('user_id', Auth::id())
            ->wherePivot('role', 'owner')
            ->exists();

        if (!$isOwner) {
            abort(403, 'Seul l\'owner peut retirer des membres.');
        }

        // Cannot remove the owner themselves
        $targetIsOwner = $colocation->members()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'owner')
            ->exists();

        if ($targetIsOwner) {
            return back()->with('error', 'Vous ne pouvez pas retirer l\'owner de la colocation.');
        }

        // Check if the target member currently has a debt
        // Check if the target member currently has a debt
        $balances = \App\Http\Controllers\SettlementsController::getBalances($colocation);
        $memberBalance = $balances[$user->id] ?? 0;

        DB::transaction(function () use ($colocation, $user, $memberBalance) {
            // Mark membership as left
            $colocation->members()->updateExistingPivot($user->id, [
                'left_at' => now(),
            ]);

            // Reputation logic
            if ($memberBalance < 0) {
                // Member had a debt: reputation -1 for the member
                $user->decrement('reputation_score');

                // Transfer debt to owner (per spec)
                // We add a synthetic expense attributed to the owner for the debt amount
                // so the balance is absorbed by the owner
                $owner = $colocation->members()
                    ->wherePivot('role', 'owner')
                    ->first();

                if ($owner) {
                    // Create a corrective expense (owner pays member's debt)
                    $colocation->expenses()->create([
                        'title'       => 'Reprise de dette – ' . $user->name,
                        'amount'      => abs($memberBalance),
                        'date'        => now()->toDateString(),
                        'payer_id'    => $owner->id,
                        'category_id' => null,
                    ]);
                }
            } else {
                // No debt on departure: reputation +1
                $user->increment('reputation_score');
            }
        });

        // Recalculate settlements now the member has left
        SettlementsController::recalculate($colocation);

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', $user->name . ' a été retiré(e) de la colocation.');
    }
}
