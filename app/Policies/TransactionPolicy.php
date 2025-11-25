<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-transactions');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        // User can view if they have permission or if they created it
        return $user->can('view-transactions') || $transaction->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-transactions');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        // Must have permission
        if (! $user->can('edit-transactions')) {
            return false;
        }

        // Can only edit pending transactions
        if (! $transaction->isPending()) {
            return false;
        }

        // Users can only edit their own transactions unless they're Admin
        if ($transaction->user_id !== $user->id && ! $user->hasRole('Admin')) {
            return false;
        }

        // Cannot edit if there's a pending approval (for Requesters)
        if ($transaction->hasPendingApproval()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        // Must have permission
        if (! $user->can('delete-transactions')) {
            return false;
        }

        // Can only delete pending transactions
        if (! $transaction->isPending()) {
            return false;
        }

        // Users can only delete their own transactions unless they're Admin
        if ($transaction->user_id !== $user->id && ! $user->hasRole('Admin')) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Transaction $transaction): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, Transaction $transaction): bool
    {
        // Must have approve permission
        if (! $user->can('approve-transactions')) {
            return false;
        }

        // Cannot approve own transactions
        if ($transaction->user_id === $user->id) {
            return false;
        }

        // Must be pending
        return $transaction->isPending();
    }
}
