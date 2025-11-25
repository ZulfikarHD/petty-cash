<?php

namespace Tests\Feature\Transactions;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransactionCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_cashier_can_view_transactions_index(): void
    {
        $this->withoutVite();

        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $response = $this->actingAs($cashier)->get('/transactions');

        $response->assertOk();
    }

    public function test_requester_can_view_transactions_index(): void
    {
        $this->withoutVite();

        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $response = $this->actingAs($requester)->get('/transactions');

        $response->assertOk();
    }

    public function test_cashier_can_create_cash_in_transaction(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $response = $this->actingAs($cashier)->post('/transactions', [
            'type' => 'in',
            'amount' => 100000,
            'description' => 'Cash received from customer',
            'transaction_date' => today()->format('Y-m-d'),
            'notes' => 'Payment for invoice #123',
        ]);

        $response->assertRedirect('/transactions');
        // Cashier transactions are auto-approved
        $this->assertDatabaseHas('transactions', [
            'type' => 'in',
            'amount' => 100000,
            'description' => 'Cash received from customer',
            'status' => 'approved',
            'user_id' => $cashier->id,
            'approved_by' => $cashier->id,
        ]);

        // Verify transaction number was generated
        $transaction = Transaction::latest()->first();
        $this->assertStringStartsWith('TXN-'.date('Y'), $transaction->transaction_number);
    }

    public function test_cashier_can_create_cash_out_transaction(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $response = $this->actingAs($cashier)->post('/transactions', [
            'type' => 'out',
            'amount' => 50000,
            'description' => 'Office supplies purchase',
            'transaction_date' => today()->format('Y-m-d'),
            'notes' => 'Paper and pens',
        ]);

        $response->assertRedirect('/transactions');
        // Cashier transactions are auto-approved
        $this->assertDatabaseHas('transactions', [
            'type' => 'out',
            'amount' => 50000,
            'description' => 'Office supplies purchase',
            'status' => 'approved',
        ]);
    }

    public function test_transaction_creation_requires_valid_data(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $response = $this->actingAs($cashier)->post('/transactions', [
            'type' => 'invalid',
            'amount' => -100,
            'description' => '',
            'transaction_date' => '2099-01-01',
        ]);

        $response->assertSessionHasErrors(['type', 'amount', 'description', 'transaction_date']);
    }

    public function test_cashier_can_update_pending_transaction(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $transaction = Transaction::factory()->create([
            'user_id' => $cashier->id,
            'status' => 'pending',
            'amount' => 100000,
        ]);

        $response = $this->actingAs($cashier)->put("/transactions/{$transaction->id}", [
            'type' => $transaction->type,
            'amount' => 150000,
            'description' => 'Updated description',
            'transaction_date' => $transaction->transaction_date,
            'notes' => 'Updated notes',
        ]);

        $response->assertRedirect('/transactions');
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 150000,
            'description' => 'Updated description',
        ]);
    }

    public function test_cashier_cannot_update_approved_transaction(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $transaction = Transaction::factory()->approved()->create([
            'user_id' => $cashier->id,
        ]);

        $response = $this->actingAs($cashier)->put("/transactions/{$transaction->id}", [
            'type' => $transaction->type,
            'amount' => 150000,
            'description' => 'Updated description',
            'transaction_date' => $transaction->transaction_date,
        ]);

        $response->assertStatus(403);
    }

    public function test_cashier_can_delete_pending_transaction(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $transaction = Transaction::factory()->create([
            'user_id' => $cashier->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($cashier)->delete("/transactions/{$transaction->id}");

        $response->assertRedirect('/transactions');
        $this->assertSoftDeleted('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_cashier_cannot_delete_approved_transaction(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $transaction = Transaction::factory()->approved()->create([
            'user_id' => $cashier->id,
        ]);

        $response = $this->actingAs($cashier)->delete("/transactions/{$transaction->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'deleted_at' => null,
        ]);
    }

    public function test_transaction_can_be_viewed(): void
    {
        $this->withoutVite();

        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $transaction = Transaction::factory()->create([
            'user_id' => $cashier->id,
        ]);

        $response = $this->actingAs($cashier)->get("/transactions/{$transaction->id}");

        $response->assertOk();
    }

    public function test_transaction_with_receipts_can_be_created(): void
    {
        Storage::fake('public');

        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $file1 = UploadedFile::fake()->image('receipt1.jpg', 100, 100);
        $file2 = UploadedFile::fake()->image('receipt2.jpg', 100, 100);

        $response = $this->actingAs($cashier)->post('/transactions', [
            'type' => 'out',
            'amount' => 50000,
            'description' => 'Office supplies',
            'transaction_date' => today()->format('Y-m-d'),
            'receipts' => [$file1, $file2],
        ]);

        $response->assertRedirect('/transactions');

        $transaction = Transaction::latest()->first();
        $this->assertCount(2, $transaction->getMedia('receipts'));
    }

    public function test_transactions_can_be_filtered_by_type(): void
    {
        $this->withoutVite();

        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        Transaction::factory()->cashIn()->create(['user_id' => $cashier->id]);
        Transaction::factory()->cashOut()->create(['user_id' => $cashier->id]);

        $response = $this->actingAs($cashier)->get('/transactions?type=in');

        $response->assertOk();
    }

    public function test_transactions_can_be_filtered_by_status(): void
    {
        $this->withoutVite();

        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        Transaction::factory()->create(['user_id' => $cashier->id, 'status' => 'pending']);
        Transaction::factory()->approved()->create(['user_id' => $cashier->id]);

        $response = $this->actingAs($cashier)->get('/transactions?status=pending');

        $response->assertOk();
    }

    public function test_transactions_can_be_searched(): void
    {
        $this->withoutVite();

        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $transaction = Transaction::factory()->create([
            'user_id' => $cashier->id,
            'description' => 'Office supplies purchase',
        ]);

        $response = $this->actingAs($cashier)->get('/transactions?search=Office');

        $response->assertOk();
    }
}
