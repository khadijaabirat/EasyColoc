<?php

namespace Tests\Feature;

use App\Models\Colocations;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseAndCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_colocation_creation_seeds_default_categories()
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post('/colocations', ['name' => 'Coloc With Cats']);
        $coloc = Colocations::first();
        
        $this->assertEquals(5, $coloc->categories()->count());
        $this->assertDatabaseHas('categories', ['name' => 'Loyer', 'colocation_id' => $coloc->id]);
    }

    public function test_active_member_can_add_expense()
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post('/colocations', ['name' => 'Coloc Expenses']);
        $coloc = Colocations::first();
        $category = $coloc->categories->first();

        $member = User::factory()->create();
        $coloc->members()->attach($member->id, ['role' => 'member', 'joined_at' => now(), 'left_at' => null]);

        $response = $this->actingAs($member)->post("/colocations/{$coloc->id}/expenses", [
            'title' => 'Internet Bill',
            'amount' => 50,
            'date' => now()->toDateString(),
            'payer_id' => $member->id,
            'category_id' => $category->id,
        ]);

        $response->assertRedirect(route('colocations.show', $coloc));
        $this->assertDatabaseHas('expenses', ['title' => 'Internet Bill', 'amount' => 50, 'payer_id' => $member->id]);
    }

    public function test_payer_can_edit_expense()
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post('/colocations', ['name' => 'Coloc Edit']);
        $coloc = Colocations::first();

        $expense = $coloc->expenses()->create([
            'title' => 'Initial',
            'amount' => 10,
            'date' => now()->toDateString(),
            'payer_id' => $owner->id,
        ]);

        $response = $this->actingAs($owner)->put("/colocations/{$coloc->id}/expenses/{$expense->id}", [
            'title' => 'Modified',
            'amount' => 100,
            'date' => now()->toDateString(),
            'payer_id' => $owner->id,
            'category_id' => null,
        ]);

        $response->assertRedirect(route('colocations.show', $coloc));
        $this->assertDatabaseHas('expenses', ['title' => 'Modified', 'amount' => 100]);
    }

    public function test_month_filter_works()
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post('/colocations', ['name' => 'Coloc Filter']);
        $coloc = Colocations::first();

        // Create two expenses in different months
        $coloc->expenses()->create([
            'title' => 'Jan Expense',
            'amount' => 50,
            'date' => '2026-01-15',
            'payer_id' => $owner->id,
        ]);

        $coloc->expenses()->create([
            'title' => 'Feb Expense',
            'amount' => 30,
            'date' => '2026-02-10',
            'payer_id' => $owner->id,
        ]);

        $response = $this->actingAs($owner)->get("/colocations/{$coloc->id}?month=2026-01");
        
        $response->assertOk();
        $response->assertSee('Jan Expense');
        $response->assertDontSee('Feb Expense');
    }
}
