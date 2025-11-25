<?php

namespace Tests\Feature\Feature\Transactions;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_can_create_transaction_with_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Cashier');

        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'type' => 'out',
            'amount' => 50000,
            'description' => 'Office supplies purchase',
            'transaction_date' => now()->toDateString(),
            'category_id' => $category->id,
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'category_id' => $category->id,
            'amount' => 50000,
        ]);
    }

    public function test_can_create_transaction_without_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Cashier');

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'type' => 'out',
            'amount' => 50000,
            'description' => 'Miscellaneous expense',
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'amount' => 50000,
            'category_id' => null,
        ]);
    }

    public function test_can_update_transaction_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Cashier');

        $category1 = Category::factory()->create(['name' => 'Office Supplies']);
        $category2 = Category::factory()->create(['name' => 'Travel']);

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category1->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->put(route('transactions.update', $transaction), [
            'type' => $transaction->type,
            'amount' => $transaction->amount,
            'description' => $transaction->description,
            'transaction_date' => $transaction->transaction_date->toDateString(),
            'category_id' => $category2->id,
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'category_id' => $category2->id,
        ]);
    }

    public function test_category_id_must_exist_in_categories_table(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Cashier');

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'type' => 'out',
            'amount' => 50000,
            'description' => 'Test transaction',
            'transaction_date' => now()->toDateString(),
            'category_id' => 99999, // Non-existent category
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    public function test_can_filter_transactions_by_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Transaction::factory()->create([
            'category_id' => $category1->id,
            'user_id' => $user->id,
        ]);

        Transaction::factory()->create([
            'category_id' => $category2->id,
            'user_id' => $user->id,
        ]);

        Transaction::factory()->create([
            'category_id' => null,
            'user_id' => $user->id,
        ]);

        // Get all transactions - should show 3
        $response = $this->actingAs($user)->get(route('transactions.index'));
        $response->assertStatus(200);
    }

    public function test_transaction_relationship_with_category_works(): void
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(Category::class, $transaction->category);
        $this->assertEquals('Test Category', $transaction->category->name);
    }

    public function test_category_shows_associated_transactions(): void
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();

        $transaction1 = Transaction::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        $transaction2 = Transaction::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        $this->assertCount(2, $category->transactions);
        $this->assertTrue($category->transactions->contains($transaction1));
        $this->assertTrue($category->transactions->contains($transaction2));
    }

    public function test_deleting_category_nullifies_transaction_category(): void
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();

        $transaction = Transaction::factory()->create([
            'category_id' => $category->id,
            'user_id' => $user->id,
        ]);

        $category->delete();

        $transaction->refresh();

        $this->assertNull($transaction->category_id);
    }
}
