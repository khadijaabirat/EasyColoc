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
            // Check if member owed money
            if ($memberBalance < -0.01) {
                // Member had a debt: reputation -1
                $user->decrement('reputation_score');

                // Mark membership as kicked, so recalculate() transfers their debts to the owner
                $colocation->members()->updateExistingPivot($user->id, [
                    'left_at' => now(),
                    'active'  => false,
                ]);
            } else {
                // No debt: reputation +1
                $user->increment('reputation_score');

                // Mark as normally left
                $colocation->members()->updateExistingPivot($user->id, [
                    'left_at' => now(),
                ]);
            }
        });

        // Recalculate settlements (now handles 'kicked' transfers automatically)
        SettlementsController::recalculate($colocation);

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', $user->name . ' a été retiré(e) de la colocation.');
    }

    /**
     * Owner transfers ownership to another active member.
     */
    public function transferOwnership(Colocations $colocation, User $user)
    {
        $currentUserId = Auth::id();

        // 1. Check if the current user is actually the owner
        $isOwner = $colocation->members()
            ->where('user_id', $currentUserId)
            ->wherePivot('role', 'owner')
            ->exists();

        if (!$isOwner) {
            abort(403, 'Seul le propriétaire peut transférer ses droits.');
        }

        // 2. Cannot transfer to oneself
        if ($currentUserId === $user->id) {
            return back()->with('error', 'Vous êtes déjà le propriétaire.');
        }

        // 3. Ensure the target user is an ACTIVE member of this colocation
        $targetIsActiveMember = $colocation->members()
            ->where('user_id', $user->id)
            ->wherePivotNull('left_at')
            ->exists();

        if (!$targetIsActiveMember) {
            return back()->with('error', 'Le membre sélectionné n\'est pas actif dans cette colocation.');
        }

        DB::transaction(function () use ($colocation, $user, $currentUserId) {
            // Downgrade current owner to member
            $colocation->members()->updateExistingPivot($currentUserId, [
                'role' => 'member',
            ]);

            // Upgrade target member to owner
            $colocation->members()->updateExistingPivot($user->id, [
                'role' => 'owner',
            ]);

            // Update the colocation's owner_id
            $colocation->update([
                'owner_id' => $user->id,
            ]);
        });

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', 'Les droits de propriétaire ont été transférés à ' . $user->name . '.');
    }
}
