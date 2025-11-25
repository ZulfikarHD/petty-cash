<?php

namespace Tests\Feature\Feature\Categories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_view_categories_list(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        Category::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Categories/Index')
            ->has('categories.data', 3));
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('categories.store'), [
            'name' => 'Office Supplies',
            'description' => 'Office supplies and stationery',
            'color' => '#6366f1',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Office Supplies',
            'slug' => 'office-supplies',
            'description' => 'Office supplies and stationery',
            'color' => '#6366f1',
            'is_active' => true,
        ]);
    }

    public function test_slug_is_auto_generated_if_not_provided(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin)->post(route('categories.store'), [
            'name' => 'Travel Expenses',
            'color' => '#6366f1',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Travel Expenses',
            'slug' => 'travel-expenses',
        ]);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $category = Category::factory()->create([
            'name' => 'Old Name',
        ]);

        $response = $this->actingAs($admin)->put(route('categories.update', $category->slug), [
            'name' => 'New Name',
            'description' => 'Updated description',
            'color' => '#ef4444',
            'is_active' => false,
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name',
            'description' => 'Updated description',
            'color' => '#ef4444',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_soft_delete_category_without_transactions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->delete(route('categories.destroy', $category->slug));

        $response->assertRedirect(route('categories.index'));
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_cannot_delete_category_with_transactions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $category = Category::factory()->create();
        \App\Models\Transaction::factory()->create([
            'category_id' => $category->id,
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('categories.destroy', $category->slug));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_name_must_be_unique(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        Category::factory()->create(['name' => 'Utilities']);

        $response = $this->actingAs($admin)->post(route('categories.store'), [
            'name' => 'Utilities',
            'color' => '#6366f1',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_color_must_be_valid_hex_code(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post(route('categories.store'), [
            'name' => 'Test Category',
            'color' => 'invalid-color',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('color');
    }

    public function test_non_admin_cannot_create_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Requester');

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Test Category',
            'color' => '#6366f1',
            'is_active' => true,
        ]);

        $response->assertForbidden();
    }
}
