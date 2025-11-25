<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class BudgetService
{
    /**
     * Get all active budgets with their spent amounts.
     */
    public function getActiveBudgets(): Collection
    {
        return Budget::with('category')
            ->active()
            ->get()
            ->map(function ($budget) {
                return [
                    'id' => $budget->id,
                    'category' => $budget->category,
                    'amount' => $budget->amount,
                    'start_date' => $budget->start_date,
                    'end_date' => $budget->end_date,
                    'alert_threshold' => $budget->alert_threshold,
                    'spent_amount' => $budget->spent_amount,
                    'remaining_amount' => $budget->remaining_amount,
                    'percentage_spent' => $budget->percentage_spent,
                    'is_exceeded' => $budget->isExceeded(),
                    'is_alert_threshold_reached' => $budget->isAlertThresholdReached(),
                ];
            });
    }

    /**
     * Get budget alerts for categories that exceeded or reached threshold.
     */
    public function getBudgetAlerts(): Collection
    {
        return Budget::with('category')
            ->active()
            ->get()
            ->filter(function ($budget) {
                return $budget->isExceeded() || $budget->isAlertThresholdReached();
            })
            ->map(function ($budget) {
                return [
                    'id' => $budget->id,
                    'category' => $budget->category,
                    'amount' => $budget->amount,
                    'spent_amount' => $budget->spent_amount,
                    'remaining_amount' => $budget->remaining_amount,
                    'percentage_spent' => $budget->percentage_spent,
                    'is_exceeded' => $budget->isExceeded(),
                    'severity' => $budget->isExceeded() ? 'danger' : 'warning',
                    'message' => $budget->isExceeded()
                        ? "Budget exceeded for {$budget->category->name}"
                        : "Budget alert: {$budget->percentage_spent}% spent on {$budget->category->name}",
                ];
            })
            ->sortByDesc('percentage_spent')
            ->values();
    }

    /**
     * Get budget for a specific category and date.
     */
    public function getBudgetForCategory(int $categoryId, string $date): ?Budget
    {
        return Budget::where('category_id', $categoryId)
            ->forDate($date)
            ->first();
    }

    /**
     * Check if adding a transaction would exceed budget.
     */
    public function wouldExceedBudget(int $categoryId, float $amount, string $date): array
    {
        $budget = $this->getBudgetForCategory($categoryId, $date);

        if (! $budget) {
            return [
                'has_budget' => false,
                'would_exceed' => false,
                'message' => null,
            ];
        }

        $newTotal = $budget->spent_amount + $amount;
        $wouldExceed = $newTotal > $budget->amount;

        return [
            'has_budget' => true,
            'would_exceed' => $wouldExceed,
            'budget_amount' => $budget->amount,
            'current_spent' => $budget->spent_amount,
            'new_total' => $newTotal,
            'remaining' => $budget->amount - $newTotal,
            'message' => $wouldExceed
                ? 'This transaction would exceed the budget for this category.'
                : null,
        ];
    }

    /**
     * Get category-wise spending summary for a date range.
     */
    public function getCategorySpendingSummary(string $startDate, string $endDate): Collection
    {
        $categories = Category::active()->with([
            'transactions' => function ($query) use ($startDate, $endDate) {
                $query->where('type', 'out')
                    ->where('status', 'approved')
                    ->whereBetween('transaction_date', [$startDate, $endDate]);
            },
            'budgets' => function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q2) use ($startDate, $endDate) {
                            $q2->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                });
            },
        ])->get();

        return $categories->map(function ($category) {
            $totalSpent = $category->transactions->sum('amount');
            $budget = $category->budgets->first();

            return [
                'category' => $category,
                'total_spent' => $totalSpent,
                'budget' => $budget ? $budget->amount : null,
                'percentage_spent' => $budget && $budget->amount > 0
                    ? round(($totalSpent / $budget->amount) * 100, 2)
                    : 0,
            ];
        })->sortByDesc('total_spent')->values();
    }

    /**
     * Check if a budget period overlaps with existing budgets for the category.
     */
    public function hasOverlappingBudget(int $categoryId, string $startDate, string $endDate, ?int $excludeBudgetId = null): bool
    {
        $query = Budget::where('category_id', $categoryId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });

        if ($excludeBudgetId) {
            $query->where('id', '!=', $excludeBudgetId);
        }

        return $query->exists();
    }
}
