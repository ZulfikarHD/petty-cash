<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReconcileBalanceRequest;
use App\Http\Requests\StoreCashBalanceRequest;
use App\Models\CashBalance;
use App\Services\BalanceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CashBalanceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(public BalanceService $balanceService) {}

    /**
     * Display a listing of the cash balance periods.
     */
    public function index(Request $request): Response
    {
        $this->authorize('view-transactions');

        $query = CashBalance::query()->with('reconciledBy', 'createdBy');

        // Filter by status
        if ($request->input('status') === 'active') {
            $query->active();
        } elseif ($request->input('status') === 'reconciled') {
            $query->reconciled();
        } elseif ($request->input('status') === 'closed') {
            $query->closed();
        }

        $cashBalances = $query->latest('period_start')
            ->paginate(15)
            ->withQueryString()
            ->through(function ($balance) {
                $periodBalance = $this->balanceService->getPeriodBalance(
                    $balance->period_start,
                    $balance->period_end
                );

                return [
                    'id' => $balance->id,
                    'period_start' => $balance->period_start,
                    'period_end' => $balance->period_end,
                    'opening_balance' => $balance->opening_balance,
                    'closing_balance' => $balance->closing_balance,
                    'calculated_balance' => $periodBalance['closing_balance'],
                    'cash_in' => $periodBalance['cash_in'],
                    'cash_out' => $periodBalance['cash_out'],
                    'status' => $balance->status,
                    'reconciliation_date' => $balance->reconciliation_date,
                    'reconciled_by' => $balance->reconciledBy,
                    'created_by' => $balance->createdBy,
                    'has_discrepancy' => $balance->hasDiscrepancy(),
                    'discrepancy_amount' => $balance->discrepancy_amount,
                    'notes' => $balance->notes,
                    'created_at' => $balance->created_at,
                ];
            });

        // Get current balance and summary
        $currentBalance = $this->balanceService->getCurrentBalance();
        $lowBalanceAlert = $this->balanceService->needsLowBalanceAlert($currentBalance);
        $todaySummary = $this->balanceService->getTodaySummary();

        return Inertia::render('CashBalance/Index', [
            'cashBalances' => $cashBalances,
            'filters' => $request->only(['status']),
            'currentBalance' => $currentBalance,
            'lowBalanceAlert' => $lowBalanceAlert,
            'lowBalanceThreshold' => $this->balanceService->getLowBalanceThreshold(),
            'todaySummary' => $todaySummary,
        ]);
    }

    /**
     * Show the form for creating a new cash balance period.
     */
    public function create(): Response
    {
        $this->authorize('manage-transactions');

        // Get suggested dates (current month)
        $suggestedStart = now()->startOfMonth()->toDateString();
        $suggestedEnd = now()->endOfMonth()->toDateString();

        // Get the last closing balance as suggested opening balance
        $lastBalance = CashBalance::whereIn('status', ['reconciled', 'closed'])
            ->orderBy('period_end', 'desc')
            ->first();

        $suggestedOpeningBalance = $lastBalance
            ? (float) ($lastBalance->closing_balance ?? $lastBalance->opening_balance)
            : 0.0;

        return Inertia::render('CashBalance/Create', [
            'suggestedStart' => $suggestedStart,
            'suggestedEnd' => $suggestedEnd,
            'suggestedOpeningBalance' => $suggestedOpeningBalance,
        ]);
    }

    /**
     * Store a newly created cash balance period.
     */
    public function store(StoreCashBalanceRequest $request): RedirectResponse
    {
        CashBalance::create([
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'opening_balance' => $request->opening_balance,
            'notes' => $request->notes,
            'status' => 'active',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('cash-balances.index')
            ->with('success', 'Cash balance period created successfully.');
    }

    /**
     * Display the specified cash balance period.
     */
    public function show(CashBalance $cashBalance): Response
    {
        $this->authorize('view-transactions');

        $cashBalance->load('reconciledBy', 'createdBy');

        $periodBalance = $this->balanceService->getPeriodBalance(
            $cashBalance->period_start,
            $cashBalance->period_end
        );

        // Get transactions for this period
        $transactions = $this->balanceService->getTransactionsForPeriod($cashBalance);

        // Get daily balance history for the period
        $balanceHistory = $this->balanceService->getBalanceHistory(
            $cashBalance->period_start,
            $cashBalance->period_end
        );

        return Inertia::render('CashBalance/Show', [
            'cashBalance' => [
                'id' => $cashBalance->id,
                'period_start' => $cashBalance->period_start,
                'period_end' => $cashBalance->period_end,
                'opening_balance' => $cashBalance->opening_balance,
                'closing_balance' => $cashBalance->closing_balance,
                'status' => $cashBalance->status,
                'reconciliation_date' => $cashBalance->reconciliation_date,
                'reconciled_by' => $cashBalance->reconciledBy,
                'created_by' => $cashBalance->createdBy,
                'has_discrepancy' => $cashBalance->hasDiscrepancy(),
                'discrepancy_amount' => $cashBalance->discrepancy_amount,
                'discrepancy_notes' => $cashBalance->discrepancy_notes,
                'notes' => $cashBalance->notes,
                'created_at' => $cashBalance->created_at,
            ],
            'periodBalance' => $periodBalance,
            'transactions' => $transactions->map(fn ($transaction) => [
                'id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'transaction_date' => $transaction->transaction_date,
                'category' => $transaction->category,
                'user' => [
                    'name' => $transaction->user->name,
                ],
            ]),
            'balanceHistory' => $balanceHistory,
        ]);
    }

    /**
     * Show the form for reconciling a cash balance period.
     */
    public function reconcile(CashBalance $cashBalance): Response
    {
        $this->authorize('manage-transactions');

        $cashBalance->load('reconciledBy', 'createdBy');

        $periodBalance = $this->balanceService->getPeriodBalance(
            $cashBalance->period_start,
            $cashBalance->period_end
        );

        return Inertia::render('CashBalance/Reconcile', [
            'cashBalance' => [
                'id' => $cashBalance->id,
                'period_start' => $cashBalance->period_start,
                'period_end' => $cashBalance->period_end,
                'opening_balance' => $cashBalance->opening_balance,
                'status' => $cashBalance->status,
                'notes' => $cashBalance->notes,
            ],
            'periodBalance' => $periodBalance,
        ]);
    }

    /**
     * Process the reconciliation for a cash balance period.
     */
    public function storeReconciliation(ReconcileBalanceRequest $request, CashBalance $cashBalance): RedirectResponse
    {
        $this->balanceService->reconcileBalance(
            $cashBalance,
            $request->actual_balance,
            auth()->user(),
            $request->discrepancy_notes
        );

        return redirect()->route('cash-balances.show', $cashBalance)
            ->with('success', 'Cash balance reconciled successfully.');
    }

    /**
     * Remove the specified cash balance period.
     */
    public function destroy(CashBalance $cashBalance): RedirectResponse
    {
        $this->authorize('manage-transactions');

        if ($cashBalance->isReconciled() || $cashBalance->isClosed()) {
            return redirect()->route('cash-balances.index')
                ->with('error', 'Cannot delete a reconciled or closed balance period.');
        }

        $cashBalance->delete();

        return redirect()->route('cash-balances.index')
            ->with('success', 'Cash balance period deleted successfully.');
    }
}
