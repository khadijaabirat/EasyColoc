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
        return back()->with('success', 'Invitation envoyée avec succès !');
    }


    public function accept($token)
    {
        $invitation = Invitation::where('token', $token)->where('status', 'pending')->firstOrFail();
        $user = auth()->user();

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
}
