<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashBalance extends Model
{
    /** @use HasFactory<\Database\Factories\CashBalanceFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'period_start',
        'period_end',
        'opening_balance',
        'closing_balance',
        'notes',
        'reconciliation_date',
        'reconciled_by',
        'discrepancy_amount',
        'discrepancy_notes',
        'status',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'discrepancy_amount' => 'decimal:2',
            'reconciliation_date' => 'datetime',
        ];
    }

    /**
     * Get the user who reconciled this balance.
     */
    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    /**
     * Get the user who created this balance record.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include active balances.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include reconciled balances.
     */
    public function scopeReconciled($query)
    {
        return $query->where('status', 'reconciled');
    }

    /**
     * Scope a query to only include closed balances.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope a query to find balance for a specific period.
     */
    public function scopeForPeriod($query, $start, $end)
    {
        return $query->where('period_start', '<=', $start)
            ->where('period_end', '>=', $end);
    }

    /**
     * Scope a query to find balance containing a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('period_start', '<=', $date)
            ->where('period_end', '>=', $date);
    }

    /**
     * Check if the balance is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the balance is reconciled.
     */
    public function isReconciled(): bool
    {
        return $this->status === 'reconciled';
    }

    /**
     * Check if the balance is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Check if the balance has a discrepancy.
     */
    public function hasDiscrepancy(): bool
    {
        return $this->discrepancy_amount !== null && abs((float) $this->discrepancy_amount) > 0;
    }

    /**
     * Reconcile the balance with actual cash on hand.
     */
    public function reconcile(User $user, float $actualBalance, float $systemBalance, ?string $notes = null): bool
    {
        $discrepancy = $actualBalance - $systemBalance;

        return $this->update([
            'status' => 'reconciled',
            'closing_balance' => $actualBalance,
            'reconciled_by' => $user->id,
            'reconciliation_date' => now(),
            'discrepancy_amount' => $discrepancy != 0 ? $discrepancy : null,
            'discrepancy_notes' => $notes,
        ]);
    }

    /**
     * Close the balance period.
     */
    public function close(): bool
    {
        return $this->update([
            'status' => 'closed',
        ]);
    }
}
