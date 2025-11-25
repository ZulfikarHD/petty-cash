<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\BalanceService;
use App\Services\BudgetService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected BalanceService $balanceService,
        protected ApprovalService $approvalService,
        protected BudgetService $budgetService
    ) {}

    public function index(): Response
    {
        $user = auth()->user();
        $stats = $this->getStats($user);
        $approvalStats = $this->getApprovalStats($user);
        $budgetAlerts = $this->getBudgetAlerts($user);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'budgetAlerts' => $budgetAlerts,
            'approvalStats' => $approvalStats,
        ]);
    }

    protected function getStats($user): array
    {
        $stats = [];

        if ($user->can('view-users')) {
            $stats['totalUsers'] = User::count();
            $stats['verifiedUsers'] = User::whereNotNull('email_verified_at')->count();
            $stats['recentUsers'] = User::where('created_at', '>=', now()->subDays(30))->count();
        }

        if ($user->can('view-transactions')) {
            $stats['totalTransactions'] = Transaction::count();
            $stats['pendingTransactions'] = Transaction::where('status', 'pending')->count();
            $stats['todayCashIn'] = Transaction::where('type', 'in')
                ->whereDate('transaction_date', today())
                ->sum('amount');
            $stats['todayCashOut'] = Transaction::where('type', 'out')
                ->whereDate('transaction_date', today())
                ->sum('amount');

            // Recent transactions
            $stats['recentTransactions'] = Transaction::with('user')
                ->latest('transaction_date')
                ->latest('created_at')
                ->take(5)
                ->get()
                ->map(fn ($transaction) => [
                    'id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'transaction_date' => $transaction->transaction_date,
                    'status' => $transaction->status,
                    'user' => [
                        'name' => $transaction->user->name,
                    ],
                ]);

            // Cash balance stats
            $currentBalance = $this->balanceService->getCurrentBalance();
            $stats['currentBalance'] = $currentBalance;
            $stats['lowBalanceAlert'] = $this->balanceService->needsLowBalanceAlert($currentBalance);
            $stats['lowBalanceThreshold'] = $this->balanceService->getLowBalanceThreshold();
        }

        return $stats;
    }

    protected function getApprovalStats($user): array
    {
        $approvalStats = [];

        if ($user->can('approve-transactions')) {
            $approvalStats['pendingApprovals'] = $this->approvalService->getPendingApprovalsCount($user);
        }

        if ($user->isRequester()) {
            $approvalStats['myPendingSubmissions'] = $this->approvalService->getPendingSubmissionsCount($user);
        }

        return $approvalStats;
    }

    protected function getBudgetAlerts($user)
    {
        if ($user->can('view-budgets')) {
            return $this->budgetService->getBudgetAlerts()->take(5);
        }

        return [];
    }
}
