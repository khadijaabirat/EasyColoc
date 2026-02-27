<?php

namespace App\Http\Controllers;

use App\Models\Colocations;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ColocationsController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $activeColoc = $user->colocations()->wherePivotNull('left_at')->first();
        if ($activeColoc) {
            return view('dashboard', compact('activeColoc'));
        }
        return view('dashboard');
    }

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user->colocations()->wherePivotNull('left_at')->exists()) {
            return redirect()->route('colocations.index');
        }
        return view('colocations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|min:3',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $hasActiveColoc = $user->colocations()->wherePivotNull('left_at')->exists();
        if ($hasActiveColoc) {
            return redirect()->back()->with('error', 'Vous avez déjà une colocation active.');
        }

        DB::transaction(function () use ($request, $user) {
            $colocation = Colocations::create([
                'name'     => $request->name,
                'status'   => 'active',
                'owner_id' => $user->id,
            ]);
            $colocation->members()->attach($user->id, [
                'role'      => 'owner',
                'joined_at' => now(),
                'left_at'   => null,
            ]);
        });

        return redirect()->route('colocations.index')->with('success', 'Colocation créée avec succès !');
    }

    private function authorizeMember(Colocations $colocation): void
    {
        if (!$colocation->members->contains(Auth::id())) {
            abort(403, "Vous n'êtes pas membre de cette colocation.");
        }
    }

    public function show(Colocations $colocation)
    {
        $this->authorizeMember($colocation);

        $colocation->load([
            'members',
            'expenses.category',
            'expenses.payer',
            'categories',
            'settlements.debtor',
            'settlements.creditor',
        ]);

        // Calculate balances
        $activeMembers = $colocation->members()->wherePivotNull('left_at')->get();
        $totalAmount   = $colocation->expenses()->sum('amount');
        $share         = $activeMembers->count() > 0 ? $totalAmount / $activeMembers->count() : 0;

        $balances = [];
        foreach ($activeMembers as $member) {
            $paid = $colocation->expenses()->where('payer_id', $member->id)->sum('amount');
            $balances[$member->id] = round($paid - $share, 2);
        }

        return view('colocations.show', compact('colocation', 'balances', 'activeMembers'));
    }

    public function leave(Colocations $colocation)
    {
        $user       = auth()->user();
        $membership = $colocation->members()->where('user_id', $user->id)->first();

        if ($membership && $membership->pivot->role !== 'owner') {

            // Reputation check before leaving
            $memberCount  = $colocation->members()->wherePivot('left_at', null)->count();
            $totalAmount  = $colocation->expenses()->sum('amount');
            $share        = $memberCount > 0 ? $totalAmount / $memberCount : 0;
            $memberPaid   = $colocation->expenses()->where('payer_id', $user->id)->sum('amount');
            $memberBalance = round($memberPaid - $share, 2);

            $colocation->members()->updateExistingPivot($user->id, [
                'left_at' => now(),
            ]);

            // Reputation
            if ($memberBalance < 0) {
                $user->decrement('reputation_score');
            } else {
                $user->increment('reputation_score');
            }

            // Recalculate
            SettlementsController::recalculate($colocation);

            return redirect()->route('dashboard')
                ->with('success', 'Vous avez quitté la colocation.');
        }

        // Owner trying to leave — only allowed if alone
        $memberCount = $colocation->members()->wherePivot('left_at', null)->count();
        if ($memberCount === 1) {
            $colocation->update(['status' => 'cancelled']);
            $colocation->members()->updateExistingPivot($user->id, [
                'left_at' => now(),
            ]);
            $user->increment('reputation_score');

            return redirect()->route('dashboard')
                ->with('success', 'Colocation annulée, vous étiez le seul membre.');
        }

        return back()->with('error', "L'owner ne peut pas quitter sans annuler la colocation.");
    }

    public function cancel(Colocations $colocation)
    {
        $isOwner = $colocation->members()
            ->wherePivot('user_id', auth()->id())
            ->wherePivot('role', 'owner')
            ->exists();

        if (!$isOwner) {
            abort(403, "Action non autorisée.");
        }

        DB::transaction(function () use ($colocation) {
            $colocation->update(['status' => 'cancelled']);

            $activeMembers = $colocation->members()->wherePivotNull('left_at')->get();
            $memberCount   = $activeMembers->count();
            $totalAmount   = $colocation->expenses()->sum('amount');
            $share         = $memberCount > 0 ? $totalAmount / $memberCount : 0;

            foreach ($activeMembers as $member) {
                $memberPaid    = $colocation->expenses()->where('payer_id', $member->id)->sum('amount');
                $memberBalance = round($memberPaid - $share, 2);

                // Reputation on cancellation
                if ($memberBalance < 0) {
                    $member->decrement('reputation_score');
                } else {
                    $member->increment('reputation_score');
                }

                $colocation->members()->updateExistingPivot($member->id, [
                    'left_at' => now(),
                ]);
            }
        });

        return redirect()->route('dashboard')->with('success', 'La colocation a été annulée.');
    }
}
