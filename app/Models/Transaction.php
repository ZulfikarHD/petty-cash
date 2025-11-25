<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Transaction extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'transaction_number',
        'type',
        'amount',
        'description',
        'transaction_date',
        'category_id',
        'vendor_id',
        'user_id',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('receipts')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp']);
    }

    /**
     * Get the user who created the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who approved the transaction.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the category that owns the transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the approval record for this transaction.
     */
    public function approval(): HasOne
    {
        return $this->hasOne(Approval::class);
    }

    /**
     * Check if this transaction requires approval.
     */
    public function requiresApproval(): bool
    {
        return $this->user && $this->user->isRequester();
    }

    /**
     * Check if this transaction has a pending approval.
     */
    public function hasPendingApproval(): bool
    {
        return $this->approval && $this->approval->isPending();
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved transactions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected transactions.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include cash-in transactions.
     */
    public function scopeCashIn($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope a query to only include cash-out transactions.
     */
    public function scopeCashOut($query)
    {
        return $query->where('type', 'out');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeByDateRange($query, $start, $end)
    {
        if ($start && $end) {
            return $query->whereBetween('transaction_date', [$start, $end]);
        }

        if ($start) {
            return $query->where('transaction_date', '>=', $start);
        }

        if ($end) {
            return $query->where('transaction_date', '<=', $end);
        }

        return $query;
    }

    /**
     * Approve the transaction.
     */
    public function approve(User $approver): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Reject the transaction.
     */
    public function reject(User $approver, string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Check if the transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the transaction is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the transaction is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
