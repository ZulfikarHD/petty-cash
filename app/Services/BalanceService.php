<?php

namespace App\Services;

use App\Models\CashBalance;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BalanceService
{
    /**
     * Get the current cash balance as of a specific date.
     */
    public function getCurrentBalance(?Carbon $asOfDate = null): float
    {
        $date = $asOfDate ?? Carbon::now();

        $openingBalance = $this->getOpeningBalance($date);
        $transactionBalance = $this->getTransactionBalance(null, $date);

        return $openingBalance + $transactionBalance;
    }

    /**
     * Get the opening balance for a period containing the given date.
     */
    public function getOpeningBalance(Carbon $date): float
    {
        $cashBalance = CashBalance::forDate($date)->first();

        if ($cashBalance) {
            return (float) $cashBalance->opening_balance;
        }

        // If no cash balance record exists, check for the most recent closed/reconciled period
        $previousBalance = CashBalance::where('period_end', '<', $date)
            ->whereIn('status', ['reconciled', 'closed'])
            ->orderBy('period_end', 'desc')
            ->first();

        if ($previousBalance) {
            return (float) ($previousBalance->closing_balance ?? $previousBalance->opening_balance);
        }

        return 0.0;
    }

    /**
     * Get the net transaction balance for a period.
     * Returns sum of cash-in minus sum of cash-out for approved transactions.
     */
    public function getTransactionBalance(?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        $query = Transaction::query()->approved();

        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        $cashIn = (clone $query)->cashIn()->sum('amount');
        $cashOut = (clone $query)->cashOut()->sum('amount');

        return (float) $cashIn - (float) $cashOut;
    }

    /**
     * Get detailed balance breakdown for a period.
     */
    public function getPeriodBalance(Carbon $start, Carbon $end): array
    {
        $openingBalance = $this->getOpeningBalance($start);

        $cashIn = Transaction::query()
            ->approved()
            ->cashIn()
            ->byDateRange($start, $end)
            ->sum('amount');

        $cashOut = Transaction::query()
            ->approved()
            ->cashOut()
            ->byDateRange($start, $end)
            ->sum('amount');

        $netFlow = (float) $cashIn - (float) $cashOut;
        $closingBalance = $openingBalance + $netFlow;

        return [
            'opening_balance' => $openingBalance,
            'cash_in' => (float) $cashIn,
            'cash_out' => (float) $cashOut,
            'net_flow' => $netFlow,
            'closing_balance' => $closingBalance,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
        ];
    }

    /**
     * Get balance history with daily snapshots.
     */
    public function getBalanceHistory(Carbon $start, Carbon $end): Collection
    {
        $history = collect();
        $currentDate = $start->copy();
        $runningBalance = $this->getOpeningBalance($start);

        while ($currentDate->lte($end)) {
            $dayTransactions = Transaction::query()
                ->approved()
                ->whereDate('transaction_date', $currentDate)
                ->get();

            $cashIn = $dayTransactions->where('type', 'in')->sum('amount');
            $cashOut = $dayTransactions->where('type', 'out')->sum('amount');
            $netFlow = (float) $cashIn - (float) $cashOut;

            $runningBalance += $netFlow;

            $history->push([
                'date' => $currentDate->toDateString(),
                'cash_in' => (float) $cashIn,
                'cash_out' => (float) $cashOut,
                'net_flow' => $netFlow,
                'balance' => $runningBalance,
                'transaction_count' => $dayTransactions->count(),
            ]);

            $currentDate->addDay();
        }

        return $history;
    }

    /**
     * Get balance summary for multiple periods.
     */
    public function getBalanceSummary(int $months = 6): Collection
    {
        $summary = collect();
        $currentDate = Carbon::now();

        for ($i = 0; $i < $months; $i++) {
            $periodStart = $currentDate->copy()->subMonths($i)->startOfMonth();
            $periodEnd = $currentDate->copy()->subMonths($i)->endOfMonth();

            $balance = $this->getPeriodBalance($periodStart, $periodEnd);

            $cashBalance = CashBalance::forDate($periodStart)->first();

            $summary->push([
                ...$balance,
                'month' => $periodStart->format('F Y'),
                'status' => $cashBalance?->status ?? 'no_record',
                'is_reconciled' => $cashBalance?->isReconciled() ?? false,
                'cash_balance_id' => $cashBalance?->id,
            ]);
        }

        return $summary->reverse()->values();
    }

    /**
     * Reconcile a cash balance period.
     */
    public function reconcileBalance(
        CashBalance $cashBalance,
        float $actualAmount,
        User $user,
        ?string $notes = null
    ): CashBalance {
        $periodBalance = $this->getPeriodBalance(
            $cashBalance->period_start,
            $cashBalance->period_end
        );

        $systemBalance = $periodBalance['closing_balance'];

        $cashBalance->reconcile($user, $actualAmount, $systemBalance, $notes);

        return $cashBalance->fresh();
    }

    /**
     * Check if the current balance is below the low balance threshold.
     */
    public function needsLowBalanceAlert(float $currentBalance): bool
    {
        $threshold = config('cash.low_balance_threshold', 1000.00);

        return $currentBalance < $threshold;
    }

    /**
     * Get the low balance threshold.
     */
    public function getLowBalanceThreshold(): float
    {
        return (float) config('cash.low_balance_threshold', 1000.00);
    }

    /**
     * Check if a period has overlapping balance records.
     */
    public function hasOverlappingPeriod(
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeId = null
    ): bool {
        $query = CashBalance::query()
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('period_start', [$startDate, $endDate])
                    ->orWhereBetween('period_end', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('period_start', '<=', $startDate)
                            ->where('period_end', '>=', $endDate);
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get transactions for a cash balance period.
     */
    public function getTransactionsForPeriod(CashBalance $cashBalance): Collection
    {
        return Transaction::query()
            ->with(['user', 'category'])
            ->approved()
            ->byDateRange($cashBalance->period_start, $cashBalance->period_end)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the active cash balance period.
     */
    public function getActivePeriod(): ?CashBalance
    {
        return CashBalance::active()
            ->orderBy('period_start', 'desc')
            ->first();
    }

    /**
     * Get today's transaction summary.
     */
    public function getTodaySummary(): array
    {
        $today = Carbon::today();

        $cashIn = Transaction::query()
            ->approved()
            ->cashIn()
            ->whereDate('transaction_date', $today)
            ->sum('amount');

        $cashOut = Transaction::query()
            ->approved()
            ->cashOut()
            ->whereDate('transaction_date', $today)
            ->sum('amount');

        return [
            'date' => $today->toDateString(),
            'cash_in' => (float) $cashIn,
            'cash_out' => (float) $cashOut,
            'net_flow' => (float) $cashIn - (float) $cashOut,
        ];
    }
}
