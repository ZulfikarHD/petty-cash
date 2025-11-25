<?php

namespace Tests\Feature\Feature\Budgets;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_create_budget(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->post(route('budgets.store'), [
            'category_id' => $category->id,
            'amount' => 10000000,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'alert_threshold' => 80,
        ]);

        $response->assertRedirect(route('budgets.index'));
        $this->assertDatabaseHas('budgets', [
            'category_id' => $category->id,
            'amount' => 10000000,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'alert_threshold' => 80,
        ]);
    }

    public function test_admin_can_update_budget(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $category = Category::factory()->create();
        $budget = Budget::factory()->create([
            'category_id' => $category->id,
            'amount' => 5000000,
        ]);

        $response = $this->actingAs($admin)->put(route('budgets.update', $budget), [
            'category_id' => $category->id,
            'amount' => 7500000,
            'start_date' => $budget->start_date->toDateString(),
            'end_date' => $budget->end_date->toDateString(),
            'alert_threshold' => $budget->alert_threshold,
        ]);

        $response->assertRedirect(route('budgets.index'));
        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'amount' => 7500000,
        ]);
    }

    public function test_admin_can_delete_budget(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $budget = Budget::factory()->create();

        $response = $this->actingAs($admin)->delete(route('budgets.destroy', $budget));

        $response->assertRedirect(route('budgets.index'));
        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }

    public function test_end_date_must_be_after_start_date(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->post(route('budgets.store'), [
            'category_id' => $category->id,
            'amount' => 10000000,
            'start_date' => '2025-01-31',
            'end_date' => '2025-01-01',
            'alert_threshold' => 80,
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_cannot_create_overlapping_budget_for_same_category(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $category = Category::factory()->create();

        // Create first budget
        Budget::factory()->create([
            'category_id' => $category->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        // Try to create overlapping budget
        $response = $this->actingAs($admin)->post(route('budgets.store'), [
            'category_id' => $category->id,
            'amount' => 10000000,
            'start_date' => '2025-01-15',
            'end_date' => '2025-02-15',
            'alert_threshold' => 80,
        ]);

        $response->assertSessionHasErrors('start_date');
    }

    public function test_can_create_non_overlapping_budgets_for_same_category(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $category = Category::factory()->create();

        // Create first budget
        Budget::factory()->create([
            'category_id' => $category->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        // Create non-overlapping budget
        $response = $this->actingAs($admin)->post(route('budgets.store'), [
            'category_id' => $category->id,
            'amount' => 10000000,
            'start_date' => '2025-02-01',
            'end_date' => '2025-02-28',
            'alert_threshold' => 80,
        ]);

        $response->assertRedirect(route('budgets.index'));
        $this->assertDatabaseCount('budgets', 2);
    }

    public function test_non_admin_cannot_create_budget(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Requester');

        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post(route('budgets.store'), [
            'category_id' => $category->id,
            'amount' => 10000000,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'alert_threshold' => 80,
        ]);

        $response->assertForbidden();
    }

    public function test_accountant_can_view_but_not_manage_budgets(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('Accountant');

        $budget = Budget::factory()->create();

        $this->actingAs($accountant)->get(route('budgets.index'))->assertStatus(200);
        $this->actingAs($accountant)->get(route('budgets.show', $budget))->assertStatus(200);

        $this->actingAs($accountant)->get(route('budgets.create'))->assertForbidden();
    }
}
