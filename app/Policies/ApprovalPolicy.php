<?php

namespace App\Policies;

use App\Models\Approval;
use App\Models\User;

class ApprovalPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('approve-transactions');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Approval $approval): bool
    {
        // User can view if they can approve transactions or if they submitted it
        return $user->can('approve-transactions') || $approval->submitted_by === $user->id;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, Approval $approval): bool
    {
        // Must have permission, cannot approve own submission, and must be pending
        return $user->can('approve-transactions')
            && $approval->submitted_by !== $user->id
            && $approval->isPending();
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, Approval $approval): bool
    {
        // Same rules as approve
        return $this->approve($user, $approval);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Anyone with create-transactions permission can submit for approval
        return $user->can('create-transactions');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Approval $approval): bool
    {
        // Only the submitter can update a pending approval (by editing notes)
        return $approval->submitted_by === $user->id && $approval->isPending();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Approval $approval): bool
    {
        // Only admins can delete approvals
        return $user->hasRole('Admin');
    }
}
