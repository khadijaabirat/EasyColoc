<?php

namespace App\Http\Controllers;
use App\Models\invitation;
use App\Models\Colocations;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\InvitationMail;
class InvitationController extends Controller
{
    public function store(Request $request, Colocations $colocation)
    {
   $isOwner = $colocation->members()->wherePivot('user_id', auth()->id())
                                    ->wherePivot('role', 'owner')
                                    ->exists();
        if (!$isOwner) {
            abort(403, "Seul l'owner qui on l'acceé de envoyeé les invitaions");
        }
        $request->validate(['email' => 'required|email']);
         $token = Str::random(32);
          Invitation::create([
            'email' => $request->email,
            'token' => $token,
            'colocation_id' => $colocation->id,
            'status' => 'pending'
        ]);
Mail::to($request->email)->send(new InvitationMail($colocation, $token));
        return back()->with('success', 'Invitation envoyée avec succès !')->with('generated_token', $token);
    }


    public function accept($token)
    {
        $invitation = Invitation::where('token', $token)->where('status', 'pending')->firstOrFail();

        // If a user is logged in, but their email does not match the invitation email
        if (Auth::check() && Auth::user()->email !== $invitation->email) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        // If no one is logged in (either initially, or after logging out the wrong user)
        if (!Auth::check()) {
            session(['url.intended' => route('invitations.accept', $token)]);

            $userExists = \App\Models\User::where('email', $invitation->email)->exists();
            if ($userExists) {
                return redirect()->route('login')->with('info', "Veuillez vous connecter avec l'email pour accepter l'invitation.");
            } else {
                return redirect()->route('register')->with('info', "Veuillez créer un compte avec l'email pour accepter l'invitation.");
            }
        }

        // At this point, the user is logged in and their email matches
        $user = Auth::user();

        if ($user->colocations()->wherePivotNull('left_at')->exists()) {
            return redirect()->route('dashboard')->with('error', 'Vous avez déjà une colocation active.');
        }

        $invitation->colocation->members()->attach($user->id, [
            'role' => 'member',
            'joined_at' => now(),
            'left_at' => null,
        ]);

        $invitation->update(['status' => 'accepted']);

        return redirect()->route('colocations.show', $invitation->colocation_id)
                         ->with('success', 'Bienvenue dans la colocation !');
    }

    public function joinManual(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        $token = $request->token;
        
        $invitation = Invitation::where('token', $token)->where('status', 'pending')->first();
        if (!$invitation) {
            return back()->with('error', 'Token invalide ou invitation expirée.');
        }

        $user = auth()->user();

        if ($user->colocations()->wherePivotNull('left_at')->exists()) {
            return back()->with('error', 'Vous avez déjà une colocation active.');
        }

        $invitation->colocation->members()->attach($user->id, [
            'role' => 'member',
            'joined_at' => now(),
            'left_at' => null,
        ]);

        $invitation->update(['status' => 'accepted']);

        return redirect()->route('colocations.show', $invitation->colocation_id)
                         ->with('success', 'Bienvenue dans la colocation !');
    }
}
