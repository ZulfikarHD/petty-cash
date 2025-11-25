<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Approval;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService
{
    /**
     * Send approval request notification to all approvers.
     */
    public function sendApprovalRequest(Approval $approval): void
    {
        $approval->load('transaction', 'submittedBy');
        $transaction = $approval->transaction;

        // Get all users who can approve transactions
        $approvers = User::permission('approve-transactions')
            ->where('id', '!=', $approval->submitted_by)
            ->get();

        foreach ($approvers as $approver) {
            $this->createNotification(
                user: $approver,
                type: 'approval_request',
                title: 'New Approval Request',
                message: sprintf(
                    '%s submitted transaction %s for approval (Amount: %s)',
                    $approval->submittedBy->name,
                    $transaction->transaction_number,
                    number_format($transaction->amount, 2)
                ),
                actionUrl: '/approvals/'.$approval->id,
                data: [
                    'approval_id' => $approval->id,
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'amount' => $transaction->amount,
                    'submitted_by' => $approval->submittedBy->name,
                ]
            );
        }
    }

    /**
     * Send approval decision notification to the requester.
     */
    public function sendApprovalDecision(Approval $approval): void
    {
        $approval->load('transaction', 'submittedBy', 'reviewedBy');
        $transaction = $approval->transaction;

        $isApproved = $approval->isApproved();
        $title = $isApproved ? 'Transaction Approved' : 'Transaction Rejected';
        $message = sprintf(
            'Your transaction %s has been %s by %s%s',
            $transaction->transaction_number,
            $isApproved ? 'approved' : 'rejected',
            $approval->reviewedBy->name,
            $isApproved ? '' : '. Reason: '.$approval->rejection_reason
        );

        $this->createNotification(
            user: $approval->submittedBy,
            type: 'approval_decision',
            title: $title,
            message: $message,
            actionUrl: '/transactions/'.$transaction->id,
            data: [
                'approval_id' => $approval->id,
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'status' => $approval->status,
                'reviewed_by' => $approval->reviewedBy->name,
                'rejection_reason' => $approval->rejection_reason,
            ]
        );
    }

    /**
     * Create a notification.
     */
    public function createNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?array $data = null
    ): AppNotification {
        return AppNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(AppNotification $notification): bool
    {
        return $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): int
    {
        return $user->appNotifications()
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread notifications for a user.
     */
    public function getUnreadNotifications(User $user, int $limit = 10): Collection
    {
        return $user->appNotifications()
            ->unread()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get all notifications for a user (paginated).
     */
    public function getNotifications(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->appNotifications()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get unread notifications count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotificationsCount();
    }

    /**
     * Delete a notification.
     */
    public function deleteNotification(AppNotification $notification): bool
    {
        return $notification->delete();
    }

    /**
     * Delete all read notifications for a user older than a given number of days.
     */
    public function deleteOldNotifications(User $user, int $daysOld = 30): int
    {
        return $user->appNotifications()
            ->read()
            ->where('created_at', '<', now()->subDays($daysOld))
            ->delete();
    }
}
