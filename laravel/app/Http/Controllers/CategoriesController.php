<?php

namespace App\Http\Controllers;

use App\Models\Colocations;
use App\Models\categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriesController extends Controller
{
    private function authorizeOwner(Colocations $colocation): void
    {
        $isOwner = $colocation->members()
            ->where('user_id', Auth::id())
            ->wherePivot('role', 'owner')
            ->exists();

        if (!$isOwner) {
            abort(403, 'Seul l\'owner peut gérer les catégories.');
        }
    }

    /**
     *  new category
     */
    public function store(Request $request, Colocations $colocation)
    {
        $this->authorizeOwner($colocation);

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

         $exists = $colocation->categories()
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Cette catégorie existe déjà.');
        }

        $colocation->categories()->create(['name' => $request->name]);

        return back()->with('success', 'Catégorie créée avec succès !');
    }

    /**
     * Delete a category  
     */
    public function destroy(Colocations $colocation, categories $category)
    {
        $this->authorizeOwner($colocation);

        if ($category->colocation_id !== $colocation->id) {
            abort(403);
        }

        if ($category->expenses()->exists()) {
            return back()->with('error', 'Impossible de supprimer une catégorie utilisée par des dépenses.');
        }

        $category->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }
}
