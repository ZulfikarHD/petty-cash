<?php

namespace Tests\Feature\Feature\Categories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_admin_has_full_access_to_categories(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $category = Category::factory()->create();

        $this->actingAs($admin)->get(route('categories.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('categories.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('categories.show', $category->slug))->assertStatus(200);
        $this->actingAs($admin)->get(route('categories.edit', $category->slug))->assertStatus(200);
    }

    public function test_accountant_can_view_but_not_manage_categories(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('Accountant');

        $category = Category::factory()->create();

        $this->actingAs($accountant)->get(route('categories.index'))->assertStatus(200);
        $this->actingAs($accountant)->get(route('categories.show', $category->slug))->assertStatus(200);

        $this->actingAs($accountant)->get(route('categories.create'))->assertForbidden();
        $this->actingAs($accountant)->post(route('categories.store'), [
            'name' => 'Test',
            'color' => '#6366f1',
            'is_active' => true,
        ])->assertForbidden();
    }

    public function test_cashier_can_view_categories(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $category = Category::factory()->create();

        $this->actingAs($cashier)->get(route('categories.index'))->assertStatus(200);
        $this->actingAs($cashier)->get(route('categories.show', $category->slug))->assertStatus(200);
    }

    public function test_requester_can_view_categories(): void
    {
        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $category = Category::factory()->create();

        $this->actingAs($requester)->get(route('categories.index'))->assertStatus(200);
        $this->actingAs($requester)->get(route('categories.show', $category->slug))->assertStatus(200);
    }

    public function test_requester_cannot_manage_categories(): void
    {
        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $category = Category::factory()->create();

        $this->actingAs($requester)->get(route('categories.create'))->assertForbidden();
        $this->actingAs($requester)->get(route('categories.edit', $category->slug))->assertForbidden();
        $this->actingAs($requester)->delete(route('categories.destroy', $category->slug))->assertForbidden();
    }

    public function test_guest_cannot_access_categories(): void
    {
        $category = Category::factory()->create();

        $this->get(route('categories.index'))->assertRedirect(route('login'));
        $this->get(route('categories.show', $category->slug))->assertRedirect(route('login'));
    }
}
