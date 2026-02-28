<?php

namespace App\Http\Controllers;

use App\Models\Colocations;
use App\Models\expenses;
use App\Models\categories;
use App\Http\Controllers\SettlementsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpensesController extends Controller
{
    /** Guard: authenticated user must be an active member */
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
     * Store a new expense for a colocation.
     */
    public function store(Request $request, Colocations $colocation)
    {
        $this->authorizeMember($colocation);

        if ($colocation->status !== 'active') {
            return back()->with('error', 'Cette colocation n\'est plus active.');
        }

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date',
            'payer_id'    => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // Ensure payer_id is an active member of this colocation
        $isActiveMember = $colocation->members()
            ->where('user_id', $data['payer_id'])
            ->wherePivotNull('left_at')
            ->exists();

        if (!$isActiveMember) {
            return back()->with('error', 'Le payeur sélectionné n\'est pas un membre actif.');
        }

        $colocation->expenses()->create($data);

        // Recalculate who owes whom
        SettlementsController::recalculate($colocation);

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', 'Dépense ajoutée avec succès !');
    }

    /**
     * Update an existing expense.
     * Only the payer or the owner can update it.
     */
    public function update(Request $request, Colocations $colocation, expenses $expense)
    {
        $this->authorizeMember($colocation);

        if ($colocation->status !== 'active') {
            return back()->with('error', 'Cette colocation n\'est plus active.');
        }

        $isOwner = $colocation->members()
            ->where('user_id', Auth::id())
            ->wherePivot('role', 'owner')
            ->exists();

        if ($expense->payer_id !== Auth::id() && !$isOwner) {
            abort(403, 'Seul le payeur ou l\'owner peut modifier cette dépense.');
        }

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date',
            'payer_id'    => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $isActiveMember = $colocation->members()
            ->where('user_id', $data['payer_id'])
            ->wherePivotNull('left_at')
            ->exists();

        if (!$isActiveMember) {
            return back()->with('error', 'Le nouveau payeur sélectionné n\'est pas un membre actif.');
        }

        $expense->update($data);

        // Recalculate settlements
        SettlementsController::recalculate($colocation);

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', 'Dépense modifiée avec succès.');
    }

    /**
     * Delete an expense (owner only or payer).
     */
    public function destroy(Colocations $colocation, expenses $expense)
    {
        $this->authorizeMember($colocation);

        // Only the payer or the owner may delete
        $isOwner = $colocation->members()
            ->where('user_id', Auth::id())
            ->wherePivot('role', 'owner')
            ->exists();

        if ($expense->payer_id !== Auth::id() && !$isOwner) {
            abort(403, 'Seul le payeur ou l\'owner peut supprimer cette dépense.');
        }

        $expense->delete();

        // Recalculate after deletion
        SettlementsController::recalculate($colocation);

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', 'Dépense supprimée.');
    }
}
