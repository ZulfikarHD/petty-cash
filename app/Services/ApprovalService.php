<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ApprovalService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Submit a transaction for approval.
     */
    public function submitForApproval(Transaction $transaction, ?string $notes = null): Approval
    {
        $approval = Approval::create([
            'transaction_id' => $transaction->id,
            'submitted_by' => $transaction->user_id,
            'status' => 'pending',
            'notes' => $notes,
            'submitted_at' => now(),
        ]);

        // Notify all users who can approve transactions
        $this->notificationService->sendApprovalRequest($approval);

        return $approval;
    }

    /**
     * Approve an approval request.
     */
    public function approve(Approval $approval, User $reviewer): bool
    {
        if (! $approval->canBeReviewedBy($reviewer)) {
            return false;
        }

        $result = $approval->approve($reviewer);

        if ($result) {
            $this->notificationService->sendApprovalDecision($approval);
        }

        return $result;
    }

    /**
     * Reject an approval request.
     */
    public function reject(Approval $approval, User $reviewer, string $reason): bool
    {
        if (! $approval->canBeReviewedBy($reviewer)) {
            return false;
        }

        $result = $approval->reject($reviewer, $reason);

        if ($result) {
            $this->notificationService->sendApprovalDecision($approval);
        }

        return $result;
    }

    /**
     * Get pending approvals for a reviewer.
     */
    public function getPendingApprovalsForReviewer(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Approval::query()
            ->pending()
            ->with(['transaction.user', 'transaction.category', 'submittedBy'])
            ->where('submitted_by', '!=', $user->id)
            ->latest('submitted_at')
            ->paginate($perPage);
    }

    /**
     * Get all pending approvals.
     */
    public function getAllPendingApprovals(): Collection
    {
        return Approval::query()
            ->pending()
            ->with(['transaction.user', 'transaction.category', 'submittedBy'])
            ->latest('submitted_at')
            ->get();
    }

    /**
     * Get pending approvals count for a reviewer.
     */
    public function getPendingApprovalsCount(User $user): int
    {
        return Approval::query()
            ->pending()
            ->where('submitted_by', '!=', $user->id)
            ->count();
    }

    /**
     * Get approvals submitted by a user.
     */
    public function getSubmittedApprovals(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Approval::query()
            ->with(['transaction.category', 'reviewedBy'])
            ->where('submitted_by', $user->id)
            ->latest('submitted_at')
            ->paginate($perPage);
    }

    /**
     * Get pending submissions count for a user.
     */
    public function getPendingSubmissionsCount(User $user): int
    {
        return Approval::query()
            ->pending()
            ->where('submitted_by', $user->id)
            ->count();
    }

    /**
     * Check if a user can approve transactions.
     */
    public function canUserApprove(User $user): bool
    {
        return $user->can('approve-transactions');
    }

    /**
     * Get approval by ID with relationships.
     */
    public function getApprovalById(int $id): ?Approval
    {
        return Approval::query()
            ->with(['transaction.user', 'transaction.category', 'transaction.media', 'submittedBy', 'reviewedBy'])
            ->find($id);
    }
}
