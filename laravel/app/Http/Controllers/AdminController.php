<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Colocations;
use App\Models\expenses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (!Auth::user()->isGlobalAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }
    }

    /**
     * Main admin dashboard with global statistics.
     */
    public function index()
    {
        $this->authorizeAdmin();

        $stats = [
            'total_users'       => User::count(),
            'banned_users'      => User::where('is_banned', true)->count(),
            'total_colocations' => Colocations::count(),
            'active_colocations'=> Colocations::where('status', 'active')->count(),
            'total_expenses'    => expenses::count(),
            'total_amount'      => expenses::sum('amount'),
        ];

        $users        = User::orderBy('created_at', 'desc')->paginate(15);
        $colocations  = Colocations::with('members')->orderBy('created_at', 'desc')->get();

        return view('admin.index', compact('stats', 'users', 'colocations'));
    }

    /**
     * Ban a user (cannot ban yourself or another admin).
     */
    public function ban(User $user)
    {
        $this->authorizeAdmin();

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas vous bannir vous-même.');
        }

        if ($user->isGlobalAdmin()) {
            return back()->with('error', 'Vous ne pouvez pas bannir un autre administrateur.');
        }

        $user->update(['is_banned' => true]);

        return back()->with('success', $user->name . ' a été banni(e).');
    }

    /**
     * Unban a previously banned user.
     */
    public function unban(User $user)
    {
        $this->authorizeAdmin();

        $user->update(['is_banned' => false]);

        return back()->with('success', $user->name . ' a été débanni(e).');
    }
}
