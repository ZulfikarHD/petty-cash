<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('view-transactions');

        $transactions = Transaction::query()
            ->with(['user:id,name,email', 'approver:id,name'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->input('type') && $request->input('type') !== 'all', function ($query) use ($request) {
                $query->where('type', $request->input('type'));
            })
            ->when($request->input('status') && $request->input('status') !== 'all', function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->input('start_date') && $request->input('end_date'), function ($query) use ($request) {
                $query->byDateRange($request->input('start_date'), $request->input('end_date'));
            })
            ->latest('transaction_date')
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        // Calculate summary for current filters
        $summaryQuery = Transaction::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->input('type') && $request->input('type') !== 'all', function ($query) use ($request) {
                $query->where('type', $request->input('type'));
            })
            ->when($request->input('status') && $request->input('status') !== 'all', function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->input('start_date') && $request->input('end_date'), function ($query) use ($request) {
                $query->byDateRange($request->input('start_date'), $request->input('end_date'));
            });

        $totalIn = (clone $summaryQuery)->where('type', 'in')->sum('amount');
        $totalOut = (clone $summaryQuery)->where('type', 'out')->sum('amount');

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'type', 'status', 'start_date', 'end_date']),
            'summary' => [
                'total_in' => $totalIn,
                'total_out' => $totalOut,
                'net_balance' => $totalIn - $totalOut,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create-transactions');

        $categories = \App\Models\Category::active()->orderBy('name')->get(['id', 'name', 'slug', 'color']);

        return Inertia::render('Transactions/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $transaction = Transaction::create([
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
            'category_id' => $request->category_id,
            'notes' => $request->notes,
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        // Handle receipt uploads
        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $receipt) {
                $transaction->addMedia($receipt)
                    ->toMediaCollection('receipts');
            }
        }

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): Response
    {
        $this->authorize('view-transactions');

        $transaction->load(['user:id,name,email', 'approver:id,name', 'media']);

        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction,
            'receipts' => $transaction->getMedia('receipts')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'size' => $media->size,
                ];
            }),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction): Response
    {
        $this->authorize('edit-transactions');

        // Only allow editing pending transactions
        if (! $transaction->isPending()) {
            abort(403, 'Only pending transactions can be edited.');
        }

        // Users can only edit their own transactions unless they're admin
        if ($transaction->user_id !== request()->user()->id && ! request()->user()->hasRole('Admin')) {
            abort(403, 'You can only edit your own transactions.');
        }

        $transaction->load('media');
        $categories = \App\Models\Category::active()->orderBy('name')->get(['id', 'name', 'slug', 'color']);

        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction,
            'receipts' => $transaction->getMedia('receipts')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'size' => $media->size,
                ];
            }),
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $transaction->update([
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
            'category_id' => $request->category_id,
            'notes' => $request->notes,
        ]);

        // Handle new receipt uploads
        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $receipt) {
                $transaction->addMedia($receipt)
                    ->toMediaCollection('receipts');
            }
        }

        // Handle receipt deletions
        if ($request->has('delete_receipts')) {
            foreach ($request->delete_receipts as $mediaId) {
                $media = $transaction->media()->find($mediaId);
                if ($media) {
                    $media->delete();
                }
            }
        }

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete-transactions');

        // Only allow deleting pending transactions
        if (! $transaction->isPending()) {
            return back()->with('error', 'Only pending transactions can be deleted.');
        }

        // Users can only delete their own transactions unless they're admin
        if ($transaction->user_id !== $request->user()->id && ! $request->user()->hasRole('Admin')) {
            return back()->with('error', 'You can only delete your own transactions.');
        }

        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }
}
