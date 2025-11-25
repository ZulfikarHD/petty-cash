<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    /** @use HasFactory<\Database\Factories\BudgetFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'amount',
        'start_date',
        'end_date',
        'alert_threshold',
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
            'alert_threshold' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get the category that owns the budget.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the spent amount for this budget.
     */
    public function getSpentAmountAttribute(): float
    {
        return (float) Transaction::where('category_id', $this->category_id)
            ->where('type', 'out')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date])
            ->sum('amount');
    }

    /**
     * Get the remaining amount for this budget.
     */
    public function getRemainingAmountAttribute(): float
    {
        return (float) ($this->amount - $this->spent_amount);
    }

    /**
     * Get the percentage spent.
     */
    public function getPercentageSpentAttribute(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }

        return round(($this->spent_amount / $this->amount) * 100, 2);
    }

    /**
     * Check if budget is exceeded.
     */
    public function isExceeded(): bool
    {
        return $this->spent_amount > $this->amount;
    }

    /**
     * Check if budget alert threshold is reached.
     */
    public function isAlertThresholdReached(): bool
    {
        return $this->percentage_spent >= $this->alert_threshold;
    }

    /**
     * Scope a query to only include active budgets.
     */
    public function scopeActive($query)
    {
        $today = now()->toDateString();

        return $query->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    /**
     * Scope a query to only include budgets for a specific date.
     */
    public function scopeForDate($query, string $date)
    {
        return $query->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date);
    }

    /**
     * Scope a query to only include upcoming budgets.
     */
    public function scopeUpcoming($query)
    {
        $today = now()->toDateString();

        return $query->where('start_date', '>', $today);
    }

    /**
     * Scope a query to only include expired budgets.
     */
    public function scopeExpired($query)
    {
        $today = now()->toDateString();

        return $query->where('end_date', '<', $today);
    }
}
