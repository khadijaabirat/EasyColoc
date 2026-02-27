<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the first user registered becomes an admin.
     */
    public function test_first_user_becomes_admin(): void
    {
        $response = $this->post('/register', [
            'name' => 'First User',
            'email' => 'first@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        
        $user = User::first();
        $this->assertEquals('admin', $user->role);
        $this->assertTrue($user->isGlobalAdmin());
    }

    /**
     * Test that subsequent users registered become members.
     */
    public function test_subsequent_users_become_members(): void
    {
        // Create first user (admin)
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Register second user
        $response = $this->post('/register', [
            'name' => 'Second User',
            'email' => 'second@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        
        $secondUser = User::where('email', 'second@example.com')->first();
        $this->assertEquals('member', $secondUser->role);
        $this->assertFalse($secondUser->isGlobalAdmin());
    }

    /**
     * Test that a banned user cannot access protected routes and is logged out.
     */
    public function test_banned_user_is_logged_out_and_redirected(): void
    {
        $user = User::factory()->create([
            'is_banned' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        // Should redirect to login
        $response->assertRedirect('/login');
        
        // Session should have error message
        $response->assertSessionHas('error', 'Votre compte a été banni par un administrateur.');
        
        // User should no longer be authenticated
        $this->assertGuest();
    }
}
