<?php

namespace Tests\Feature;

use App\Models\Colocations;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationMail;
use Tests\TestCase;

class ColocationAndInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_colocation_and_becomes_owner()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/colocations', [
            'name' => 'Ma Super Coloc',
        ]);

        $response->assertRedirect(route('colocations.index'));
        $this->assertDatabaseHas('colocations', ['name' => 'Ma Super Coloc']);
        
        $colocation = Colocations::first();
        $this->assertTrue($colocation->members()->where('user_id', $user->id)->wherePivot('role', 'owner')->exists());
    }

    public function test_user_cannot_create_colocation_if_already_in_one()
    {
        $user = User::factory()->create();
        
        // First colocation
        $this->actingAs($user)->post('/colocations', ['name' => 'First Coloc']);
        
        // Attempt second
        $response = $this->actingAs($user)->post('/colocations', ['name' => 'Second Coloc']);
        
        $response->assertSessionHas('error', 'Vous avez déjà une colocation active.');
        $this->assertEquals(1, Colocations::count());
    }

    public function test_owner_can_send_invitation()
    {
        Mail::fake();

        $owner = User::factory()->create();
        $this->actingAs($owner)->post('/colocations', ['name' => 'Coloc 1']);
        $colocation = Colocations::first();

        $response = $this->post("/colocations/{$colocation->id}/invite", [
            'email' => 'newmember@example.com'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Invitation envoyée avec succès !');

        $this->assertDatabaseHas('invitations', [
            'colocation_id' => $colocation->id,
            'email' => 'newmember@example.com'
        ]);

        Mail::assertSent(InvitationMail::class, function ($mail) {
            return $mail->hasTo('newmember@example.com');
        });
    }

    public function test_user_can_accept_invitation()
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post('/colocations', ['name' => 'Coloc Invitation']);
        $colocation = Colocations::first();

        $invitation = Invitation::create([
            'colocation_id' => $colocation->id,
            'email' => 'invited@example.com',
            'token' => 'random_token_123',
            'expires_at' => now()->addDays(7)
        ]);

        $invitedUser = User::factory()->create(['email' => 'invited@example.com']);

        $response = $this->actingAs($invitedUser)->get('/invitations/accept/random_token_123');

        $response->assertRedirect(route('colocations.show', $colocation->id));
        $response->assertSessionHas('success', 'Bienvenue dans la colocation !');

        $this->assertTrue($colocation->members()->where('user_id', $invitedUser->id)->wherePivot('role', 'member')->exists());
        $invitation->refresh();
        $this->assertEquals('accepted', $invitation->status);
    }

    public function test_user_cannot_accept_invitation_if_already_in_colocation()
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post('/colocations', ['name' => 'Coloc Invitation']);
        $colocation = Colocations::first();

        $invitation = Invitation::create([
            'colocation_id' => $colocation->id,
            'email' => 'busy@example.com',
            'token' => 'token_xyz',
            'expires_at' => now()->addDays(7)
        ]);

        $busyUser = User::factory()->create(['email' => 'busy@example.com']);
        // Put busy user in another colocation
        $this->actingAs($busyUser)->post('/colocations', ['name' => 'Another Coloc']);

        $response = $this->actingAs($busyUser)->get('/invitations/accept/token_xyz');
        $response->assertSessionHas('error', 'Vous avez déjà une colocation active.');
        
        $this->assertFalse($colocation->members()->where('user_id', $busyUser->id)->exists());
    }

    public function test_member_can_leave_colocation()
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post('/colocations', ['name' => 'Coloc Leave']);
        $colocation = Colocations::first();

        $member = User::factory()->create();
        $colocation->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        $response = $this->actingAs($member)->post("/colocations/{$colocation->id}/leave");

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success', 'Vous avez quitté la colocation.');

        $leftMember = $colocation->members()->where('user_id', $member->id)->first();
        $this->assertNotNull($leftMember->pivot->left_at);
    }

    public function test_owner_can_remove_member_and_inherit_debt()
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post('/colocations', ['name' => 'Coloc Removing']);
        $colocation = Colocations::first();

        $member = User::factory()->create();
        $colocation->members()->attach($member->id, ['role' => 'member', 'joined_at' => now(), 'left_at' => null]);

        // Create an expense paid by OWNER so the member owes them money.
        // Total expense 100, 2 members -> member owes 50 = debt.
        $colocation->expenses()->create([
            'title' => 'Loyer',
            'amount' => 100,
            'date' => now()->toDateString(),
            'payer_id' => $owner->id
        ]);

        $memberReputationBefore = $member->reputation_score;

        $response = $this->actingAs($owner)->delete("/colocations/{$colocation->id}/members/{$member->id}");

        $response->assertRedirect(route('colocations.show', $colocation));
        $response->assertSessionHas('success');

        $leftMember = $colocation->members()->where('user_id', $member->id)->first();
        $this->assertNotNull($leftMember->pivot->left_at);

        // Verify member was penalized (reputation_score - 1)
        $member->refresh();
        $this->assertEquals($memberReputationBefore - 1, $member->reputation_score);

        // Verify owner inherited the debt via calculation rather than synthetic expense
        $balances = \App\Http\Controllers\SettlementsController::getBalances($colocation);
        $this->assertEquals(0, $balances[$member->id] ?? 0);
        $this->assertEquals(0, $balances[$owner->id] ?? 0);
    }
}
