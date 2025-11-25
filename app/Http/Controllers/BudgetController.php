<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Budget;
use App\Models\Category;
use App\Services\BudgetService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    use AuthorizesRequests;

    public function __construct(public BudgetService $budgetService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('view-budgets');

        $query = Budget::query()->with('category');

        // Filter by status
        if ($request->input('status') === 'active') {
            $query->active();
        } elseif ($request->input('status') === 'upcoming') {
            $query->upcoming();
        } elseif ($request->input('status') === 'expired') {
            $query->expired();
        }

        // Search by category name
        if ($request->input('search')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->input('search')}%");
            });
        }

        $budgets = $query->latest('start_date')
            ->paginate(15)
            ->withQueryString()
            ->through(function ($budget) {
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

        return Inertia::render('Budgets/Index', [
            'budgets' => $budgets,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Display budget overview with all active budgets.
     */
    public function overview(): Response
    {
        $this->authorize('view-budgets');

        $activeBudgets = $this->budgetService->getActiveBudgets();
        $alerts = $this->budgetService->getBudgetAlerts();

        return Inertia::render('Budgets/Overview', [
            'activeBudgets' => $activeBudgets,
            'alerts' => $alerts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create-budgets');

        $categories = Category::active()->orderBy('name')->get(['id', 'name', 'color']);

        return Inertia::render('Budgets/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        Budget::create([
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'alert_threshold' => $request->alert_threshold ?? 80.00,
        ]);

        return redirect()->route('budgets.index')
            ->with('success', 'Budget created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Budget $budget): Response
    {
        $this->authorize('view-budgets');

        $budget->load('category');

        // Get transactions for this budget period
        $transactions = $budget->category->transactions()
            ->where('type', 'out')
            ->where('status', 'approved')
            ->whereBetween('transaction_date', [$budget->start_date, $budget->end_date])
            ->with('user')
            ->latest('transaction_date')
            ->get();

        return Inertia::render('Budgets/Show', [
            'budget' => [
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
            ],
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Budget $budget): Response
    {
        $this->authorize('edit-budgets');

        $budget->load('category');
        $categories = Category::active()->orderBy('name')->get(['id', 'name', 'color']);

        return Inertia::render('Budgets/Edit', [
            'budget' => $budget,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBudgetRequest $request, Budget $budget): RedirectResponse
    {
        $budget->update([
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'alert_threshold' => $request->alert_threshold ?? $budget->alert_threshold,
        ]);

        return redirect()->route('budgets.index')
            ->with('success', 'Budget updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Budget $budget): RedirectResponse
    {
        $this->authorize('delete-budgets');

        $budget->delete();

        return redirect()->route('budgets.index')
            ->with('success', 'Budget deleted successfully.');
    }
}
