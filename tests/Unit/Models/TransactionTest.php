<?php

namespace Tests\Unit\Models;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_transaction_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $transaction->user);
        $this->assertEquals($user->id, $transaction->user->id);
    }

    public function test_transaction_belongs_to_approver(): void
    {
        $approver = User::factory()->create();
        $transaction = Transaction::factory()->approved()->create([
            'approved_by' => $approver->id,
        ]);

        $this->assertInstanceOf(User::class, $transaction->approver);
        $this->assertEquals($approver->id, $transaction->approver->id);
    }

    public function test_pending_scope_returns_only_pending_transactions(): void
    {
        Transaction::factory()->create(['status' => 'pending']);
        Transaction::factory()->approved()->create();
        Transaction::factory()->rejected()->create();

        $pendingTransactions = Transaction::pending()->get();

        $this->assertEquals(1, $pendingTransactions->count());
        $this->assertEquals('pending', $pendingTransactions->first()->status);
    }

    public function test_approved_scope_returns_only_approved_transactions(): void
    {
        Transaction::factory()->create(['status' => 'pending']);
        Transaction::factory()->approved()->create();
        Transaction::factory()->approved()->create();

        $approvedTransactions = Transaction::approved()->get();

        $this->assertEquals(2, $approvedTransactions->count());
        $this->assertTrue($approvedTransactions->every(fn ($t) => $t->status === 'approved'));
    }

    public function test_rejected_scope_returns_only_rejected_transactions(): void
    {
        Transaction::factory()->create(['status' => 'pending']);
        Transaction::factory()->rejected()->create();

        $rejectedTransactions = Transaction::rejected()->get();

        $this->assertEquals(1, $rejectedTransactions->count());
        $this->assertEquals('rejected', $rejectedTransactions->first()->status);
    }

    public function test_cash_in_scope_returns_only_cash_in_transactions(): void
    {
        Transaction::factory()->cashIn()->create();
        Transaction::factory()->cashOut()->create();

        $cashInTransactions = Transaction::cashIn()->get();

        $this->assertEquals(1, $cashInTransactions->count());
        $this->assertEquals('in', $cashInTransactions->first()->type);
    }

    public function test_cash_out_scope_returns_only_cash_out_transactions(): void
    {
        Transaction::factory()->cashIn()->create();
        Transaction::factory()->cashOut()->create();
        Transaction::factory()->cashOut()->create();

        $cashOutTransactions = Transaction::cashOut()->get();

        $this->assertEquals(2, $cashOutTransactions->count());
        $this->assertTrue($cashOutTransactions->every(fn ($t) => $t->type === 'out'));
    }

    public function test_by_date_range_scope_filters_transactions(): void
    {
        Transaction::factory()->create(['transaction_date' => '2024-01-01']);
        Transaction::factory()->create(['transaction_date' => '2024-01-15']);
        Transaction::factory()->create(['transaction_date' => '2024-02-01']);

        $transactions = Transaction::byDateRange('2024-01-01', '2024-01-31')->get();

        $this->assertEquals(2, $transactions->count());
    }

    public function test_approve_method_approves_transaction(): void
    {
        $approver = User::factory()->create();
        $transaction = Transaction::factory()->create(['status' => 'pending']);

        $result = $transaction->approve($approver);

        $this->assertTrue($result);
        $this->assertEquals('approved', $transaction->fresh()->status);
        $this->assertEquals($approver->id, $transaction->fresh()->approved_by);
        $this->assertNotNull($transaction->fresh()->approved_at);
    }

    public function test_reject_method_rejects_transaction(): void
    {
        $approver = User::factory()->create();
        $transaction = Transaction::factory()->create(['status' => 'pending']);

        $result = $transaction->reject($approver, 'Invalid receipt');

        $this->assertTrue($result);
        $this->assertEquals('rejected', $transaction->fresh()->status);
        $this->assertEquals($approver->id, $transaction->fresh()->approved_by);
        $this->assertEquals('Invalid receipt', $transaction->fresh()->rejection_reason);
        $this->assertNotNull($transaction->fresh()->approved_at);
    }

    public function test_is_pending_returns_true_for_pending_transaction(): void
    {
        $transaction = Transaction::factory()->create(['status' => 'pending']);

        $this->assertTrue($transaction->isPending());
        $this->assertFalse($transaction->isApproved());
        $this->assertFalse($transaction->isRejected());
    }

    public function test_is_approved_returns_true_for_approved_transaction(): void
    {
        $transaction = Transaction::factory()->approved()->create();

        $this->assertFalse($transaction->isPending());
        $this->assertTrue($transaction->isApproved());
        $this->assertFalse($transaction->isRejected());
    }

    public function test_is_rejected_returns_true_for_rejected_transaction(): void
    {
        $transaction = Transaction::factory()->rejected()->create();

        $this->assertFalse($transaction->isPending());
        $this->assertFalse($transaction->isApproved());
        $this->assertTrue($transaction->isRejected());
    }

    public function test_transaction_number_is_auto_generated(): void
    {
        $transaction = Transaction::factory()->create(['transaction_number' => null]);

        $this->assertNotNull($transaction->transaction_number);
        $this->assertStringStartsWith('TXN-'.date('Y'), $transaction->transaction_number);
    }

    public function test_transaction_numbers_are_sequential(): void
    {
        $transaction1 = Transaction::factory()->create(['transaction_number' => null]);
        $transaction2 = Transaction::factory()->create(['transaction_number' => null]);

        $number1 = (int) substr($transaction1->transaction_number, -5);
        $number2 = (int) substr($transaction2->transaction_number, -5);

        $this->assertEquals($number1 + 1, $number2);
    }

    public function test_amount_is_cast_to_decimal(): void
    {
        $transaction = Transaction::factory()->create(['amount' => 100.50]);

        $this->assertIsString($transaction->amount);
        $this->assertEquals('100.50', $transaction->amount);
    }

    public function test_transaction_date_is_cast_to_date(): void
    {
        $transaction = Transaction::factory()->create(['transaction_date' => '2024-01-15']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $transaction->transaction_date);
    }
}
