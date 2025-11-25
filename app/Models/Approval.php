<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    /** @use HasFactory<\Database\Factories\ApprovalFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'transaction_id',
        'submitted_by',
        'reviewed_by',
        'status',
        'notes',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Get the transaction being approved.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the user who submitted the approval request.
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the user who reviewed the approval.
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope a query to only include pending approvals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved approvals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected approvals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Approve this approval request.
     */
    public function approve(User $reviewer): bool
    {
        $result = $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        if ($result) {
            $this->transaction->approve($reviewer);
        }

        return $result;
    }

    /**
     * Reject this approval request.
     */
    public function reject(User $reviewer, string $reason): bool
    {
        $result = $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        if ($result) {
            $this->transaction->reject($reviewer, $reason);
        }

        return $result;
    }

    /**
     * Check if the approval is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the approval is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the approval is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if a user can review this approval.
     */
    public function canBeReviewedBy(User $user): bool
    {
        // Cannot review own submissions
        if ($this->submitted_by === $user->id) {
            return false;
        }

        // Must have approve-transactions permission
        if (! $user->can('approve-transactions')) {
            return false;
        }

        // Must be pending
        return $this->isPending();
    }
}
