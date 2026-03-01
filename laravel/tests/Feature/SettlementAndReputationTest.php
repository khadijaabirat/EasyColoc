<?php

namespace Tests\Feature;

use App\Models\Colocations;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementAndReputationTest extends TestCase
{
    use RefreshDatabase;

    public function test_expenses_generate_correct_settlements()
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post('/colocations', ['name' => 'Coloc 1']);
        $coloc = Colocations::first();

        // Add member
        $member = User::factory()->create();
        $coloc->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        // Add 100 euro expense paid by owner
        $coloc->expenses()->create([
            'title' => 'Internet',
            'amount' => 100,
            'date' => now()->toDateString(),
            'payer_id' => $owner->id,
        ]);

        \App\Http\Controllers\SettlementsController::recalculate($coloc);

        $settlement = $coloc->settlements()->first();
        $this->assertNotNull($settlement, "A settlement should have been generated.");
        $this->assertEquals($member->id, $settlement->debtor_id);
        $this->assertEquals($owner->id, $settlement->creditor_id);
        $this->assertEquals(50, $settlement->amount);
        $this->assertFalse((bool)$settlement->is_paid);
    }

    public function test_mark_payment()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $this->actingAs($owner)->post('/colocations', ['name' => 'Coloc 2']);
        $coloc = Colocations::first();
        $coloc->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        $coloc->expenses()->create([
            'title' => 'Internet',
            'amount' => 100,
            'date' => now()->toDateString(),
            'payer_id' => $owner->id,
        ]);
        \App\Http\Controllers\SettlementsController::recalculate($coloc);

        $settlement = $coloc->settlements()->first();

        $response = $this->actingAs($member)->post("/colocations/{$coloc->id}/settlements/{$settlement->id}/pay");
        
        $response->assertSessionHas('success');
        
        $settlement->refresh();
        $this->assertTrue((bool)$settlement->is_paid);
    }

    public function test_member_leave_with_debt_decreases_reputation()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $this->actingAs($owner)->post('/colocations', ['name' => 'Coloc 3']);
        $coloc = Colocations::first();
        $coloc->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        // Member owes 50
        $coloc->expenses()->create([
            'title' => 'Internet',
            'amount' => 100,
            'date' => now()->toDateString(),
            'payer_id' => $owner->id,
        ]);

        $response = $this->actingAs($member)->post("/colocations/{$coloc->id}/leave");

        $member->refresh();
        $this->assertEquals(-1, $member->reputation_score);
    }

    public function test_member_leave_without_debt_increases_reputation()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $this->actingAs($owner)->post('/colocations', ['name' => 'Coloc 4']);
        $coloc = Colocations::first();
        $coloc->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        // Owner owes member 50
        $coloc->expenses()->create([
            'title' => 'Internet',
            'amount' => 100,
            'date' => now()->toDateString(),
            'payer_id' => $member->id,
        ]);

        $response = $this->actingAs($member)->post("/colocations/{$coloc->id}/leave");

        $member->refresh();
        $this->assertEquals(1, $member->reputation_score);
    }
}
