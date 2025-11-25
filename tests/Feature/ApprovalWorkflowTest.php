<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Approval;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_requester_transaction_creates_approval_request(): void
    {
        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $response = $this->actingAs($requester)->post('/transactions', [
            'type' => 'out',
            'amount' => 50000,
            'description' => 'Office supplies',
            'transaction_date' => today()->format('Y-m-d'),
            'approval_notes' => 'Need this for the meeting tomorrow',
        ]);

        $response->assertRedirect('/transactions');

        // Verify transaction was created with pending status
        $transaction = Transaction::latest()->first();
        $this->assertEquals('pending', $transaction->status);
        $this->assertEquals($requester->id, $transaction->user_id);

        // Verify approval record was created
        $this->assertDatabaseHas('approvals', [
            'transaction_id' => $transaction->id,
            'submitted_by' => $requester->id,
            'status' => 'pending',
            'notes' => 'Need this for the meeting tomorrow',
        ]);
    }

    public function test_cashier_transaction_is_auto_approved(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('Cashier');

        $response = $this->actingAs($cashier)->post('/transactions', [
            'type' => 'out',
            'amount' => 50000,
            'description' => 'Office supplies',
            'transaction_date' => today()->format('Y-m-d'),
        ]);

        $response->assertRedirect('/transactions');

        // Verify transaction was created with approved status
        $transaction = Transaction::latest()->first();
        $this->assertEquals('approved', $transaction->status);
        $this->assertEquals($cashier->id, $transaction->approved_by);

        // Verify no approval record was created
        $this->assertDatabaseMissing('approvals', [
            'transaction_id' => $transaction->id,
        ]);
    }

    public function test_accountant_can_view_pending_approvals(): void
    {
        $this->withoutVite();

        $accountant = User::factory()->create();
        $accountant->assignRole('Accountant');

        // Create a pending approval
        $requester = User::factory()->create();
        $requester->assignRole('Requester');
        $transaction = Transaction::factory()->create([
            'user_id' => $requester->id,
            'status' => 'pending',
        ]);
        Approval::factory()->create([
            'transaction_id' => $transaction->id,
            'submitted_by' => $requester->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($accountant)->get('/approvals');

        $response->assertOk();
    }

    public function test_accountant_can_approve_transaction(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('Accountant');

        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $transaction = Transaction::factory()->create([
            'user_id' => $requester->id,
            'status' => 'pending',
        ]);

        $approval = Approval::factory()->create([
            'transaction_id' => $transaction->id,
            'submitted_by' => $requester->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($accountant)->post("/approvals/{$approval->id}/approve");

        $response->assertRedirect('/approvals');

        // Verify approval was updated
        $approval->refresh();
        $this->assertEquals('approved', $approval->status);
        $this->assertEquals($accountant->id, $approval->reviewed_by);
        $this->assertNotNull($approval->reviewed_at);

        // Verify transaction was updated
        $transaction->refresh();
        $this->assertEquals('approved', $transaction->status);
        $this->assertEquals($accountant->id, $transaction->approved_by);
    }

    public function test_accountant_can_reject_transaction_with_reason(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('Accountant');

        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $transaction = Transaction::factory()->create([
            'user_id' => $requester->id,
            'status' => 'pending',
        ]);

        $approval = Approval::factory()->create([
            'transaction_id' => $transaction->id,
            'submitted_by' => $requester->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($accountant)->post("/approvals/{$approval->id}/reject", [
            'rejection_reason' => 'Insufficient documentation provided for this expense.',
        ]);

        $response->assertRedirect('/approvals');

        // Verify approval was updated
        $approval->refresh();
        $this->assertEquals('rejected', $approval->status);
        $this->assertEquals('Insufficient documentation provided for this expense.', $approval->rejection_reason);
        $this->assertEquals($accountant->id, $approval->reviewed_by);

        // Verify transaction was updated
        $transaction->refresh();
        $this->assertEquals('rejected', $transaction->status);
        $this->assertEquals('Insufficient documentation provided for this expense.', $transaction->rejection_reason);
    }

    public function test_rejection_requires_reason(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('Accountant');

        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $transaction = Transaction::factory()->create([
            'user_id' => $requester->id,
            'status' => 'pending',
        ]);

        $approval = Approval::factory()->create([
            'transaction_id' => $transaction->id,
            'submitted_by' => $requester->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($accountant)->post("/approvals/{$approval->id}/reject", [
            'rejection_reason' => '',
        ]);

        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_rejection_reason_must_be_at_least_10_characters(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('Accountant');

        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $transaction = Transaction::factory()->create([
            'user_id' => $requester->id,
            'status' => 'pending',
        ]);

        $approval = Approval::factory()->create([
            'transaction_id' => $transaction->id,
            'submitted_by' => $requester->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($accountant)->post("/approvals/{$approval->id}/reject", [
            'rejection_reason' => 'Too short',
        ]);

        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_requester_cannot_approve_own_transaction(): void
    {
        $requester = User::factory()->create();
        $requester->assignRole('Requester');
        $requester->givePermissionTo('approve-transactions'); // Even with permission

        $transaction = Transaction::factory()->create([
            'user_id' => $requester->id,
            'status' => 'pending',
        ]);

        $approval = Approval::factory()->create([
            'transaction_id' => $transaction->id,
            'submitted_by' => $requester->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($requester)->post("/approvals/{$approval->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Verify approval was not updated
        $approval->refresh();
        $this->assertEquals('pending', $approval->status);
    }

    public function test_requester_cannot_access_approvals_page(): void
    {
        $this->withoutVite();

        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $response = $this->actingAs($requester)->get('/approvals');

        $response->assertForbidden();
    }

    public function test_approval_creates_notification_for_approvers(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('Accountant');

        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        // Create transaction which should trigger notification
        $this->actingAs($requester)->post('/transactions', [
            'type' => 'out',
            'amount' => 50000,
            'description' => 'Office supplies',
            'transaction_date' => today()->format('Y-m-d'),
        ]);

        // Verify notification was created for accountant
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $accountant->id,
            'type' => 'approval_request',
        ]);
    }

    public function test_approval_decision_creates_notification_for_requester(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('Accountant');

        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $transaction = Transaction::factory()->create([
            'user_id' => $requester->id,
            'status' => 'pending',
        ]);

        $approval = Approval::factory()->create([
            'transaction_id' => $transaction->id,
            'submitted_by' => $requester->id,
            'status' => 'pending',
        ]);

        $this->actingAs($accountant)->post("/approvals/{$approval->id}/approve");

        // Verify notification was created for requester
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $requester->id,
            'type' => 'approval_decision',
        ]);
    }

    public function test_cannot_approve_already_approved_transaction(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('Accountant');

        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $transaction = Transaction::factory()->create([
            'user_id' => $requester->id,
            'status' => 'approved',
        ]);

        $approval = Approval::factory()->approved()->create([
            'transaction_id' => $transaction->id,
            'submitted_by' => $requester->id,
        ]);

        $response = $this->actingAs($accountant)->post("/approvals/{$approval->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_admin_can_view_and_approve_transactions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        $transaction = Transaction::factory()->create([
            'user_id' => $requester->id,
            'status' => 'pending',
        ]);

        $approval = Approval::factory()->create([
            'transaction_id' => $transaction->id,
            'submitted_by' => $requester->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post("/approvals/{$approval->id}/approve");

        $response->assertRedirect('/approvals');

        $approval->refresh();
        $this->assertEquals('approved', $approval->status);
    }

    public function test_approval_service_returns_pending_count(): void
    {
        $accountant = User::factory()->create();
        $accountant->assignRole('Accountant');

        $requester = User::factory()->create();
        $requester->assignRole('Requester');

        // Create 3 pending approvals
        for ($i = 0; $i < 3; $i++) {
            $transaction = Transaction::factory()->create([
                'user_id' => $requester->id,
                'status' => 'pending',
            ]);
            Approval::factory()->create([
                'transaction_id' => $transaction->id,
                'submitted_by' => $requester->id,
                'status' => 'pending',
            ]);
        }

        $approvalService = app(ApprovalService::class);
        $count = $approvalService->getPendingApprovalsCount($accountant);

        $this->assertEquals(3, $count);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Requester');

        $notification = AppNotification::factory()->unread()->create([
            'user_id' => $user->id,
        ]);

        // Use JSON request to get JSON response
        $response = $this->actingAs($user)
            ->postJson("/notifications/{$notification->id}/read");

        $response->assertOk();

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Requester');

        // Create 3 unread notifications
        AppNotification::factory()->unread()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post('/notifications/read-all');

        $response->assertRedirect();

        $unreadCount = $user->appNotifications()->unread()->count();
        $this->assertEquals(0, $unreadCount);
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $user1 = User::factory()->create();
        $user1->assignRole('Requester');

        $user2 = User::factory()->create();
        $user2->assignRole('Requester');

        $notification = AppNotification::factory()->unread()->create([
            'user_id' => $user2->id,
        ]);

        $response = $this->actingAs($user1)->post("/notifications/{$notification->id}/read");

        $response->assertForbidden();
    }
}
