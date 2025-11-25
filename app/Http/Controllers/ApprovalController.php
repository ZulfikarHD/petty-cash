<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveTransactionRequest;
use App\Http\Requests\RejectTransactionRequest;
use App\Models\Approval;
use App\Services\ApprovalService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ApprovalService $approvalService
    ) {}

    /**
     * Display a listing of pending approvals.
     */
    public function index(Request $request): Response
    {
        $this->authorize('approve-transactions');

        $user = $request->user();
        $status = $request->input('status', 'pending');

        $approvalsQuery = Approval::query()
            ->with(['transaction.user', 'transaction.category', 'submittedBy', 'reviewedBy'])
            ->where('submitted_by', '!=', $user->id);

        if ($status !== 'all') {
            $approvalsQuery->where('status', $status);
        }

        $approvals = $approvalsQuery
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'pending' => Approval::pending()->where('submitted_by', '!=', $user->id)->count(),
            'approved' => Approval::approved()->count(),
            'rejected' => Approval::rejected()->count(),
        ];

        return Inertia::render('Approvals/Index', [
            'approvals' => $approvals,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    /**
     * Display the specified approval.
     */
    public function show(Approval $approval): Response
    {
        $this->authorize('approve-transactions');

        $approval->load([
            'transaction.user',
            'transaction.category',
            'transaction.media',
            'submittedBy',
            'reviewedBy',
        ]);

        return Inertia::render('Approvals/Show', [
            'approval' => $approval,
        ]);
    }

    /**
     * Approve the specified approval request.
     */
    public function approve(ApproveTransactionRequest $request, Approval $approval): RedirectResponse
    {
        $user = $request->user();

        if (! $approval->canBeReviewedBy($user)) {
            return back()->with('error', 'You cannot approve this transaction.');
        }

        $result = $this->approvalService->approve($approval, $user);

        if ($result) {
            return redirect()
                ->route('approvals.index')
                ->with('success', 'Transaction has been approved successfully.');
        }

        return back()->with('error', 'Failed to approve the transaction.');
    }

    /**
     * Reject the specified approval request.
     */
    public function reject(RejectTransactionRequest $request, Approval $approval): RedirectResponse
    {
        $user = $request->user();

        if (! $approval->canBeReviewedBy($user)) {
            return back()->with('error', 'You cannot reject this transaction.');
        }

        $result = $this->approvalService->reject(
            $approval,
            $user,
            $request->validated('rejection_reason')
        );

        if ($result) {
            return redirect()
                ->route('approvals.index')
                ->with('success', 'Transaction has been rejected.');
        }

        return back()->with('error', 'Failed to reject the transaction.');
    }
}
