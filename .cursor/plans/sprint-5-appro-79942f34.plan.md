<!-- 79942f34-50a6-4602-904d-b83ac654db72 4fc06120-d07d-4f3a-890f-afc31e726399 -->
# Sprint 5: Approval Workflow System

## Implementation Overview

Build a single-level approval workflow where users with the "Requester" role must submit transactions for approval. Accountants and Admins can approve/reject these requests with in-app notifications.

## Database & Models

### 1. Create Approvals Migration

- Add `approvals` table with columns:
- `id`, `transaction_id` (foreign key)
- `submitted_by` (foreign key to users)
- `reviewed_by` (nullable, foreign key to users)
- `status` (enum: 'pending', 'approved', 'rejected')
- `notes` (text, nullable - requester's submission notes)
- `rejection_reason` (text, nullable)
- `submitted_at`, `reviewed_at` (nullable)
- `timestamps`
- Add indexes on `transaction_id`, `status`, `submitted_by`, `reviewed_by`

### 2. Create Approval Model

- Define relationships: `transaction()`, `submittedBy()`, `reviewedBy()`
- Add scopes: `scopePending()`, `scopeApproved()`, `scopeRejected()`
- Add helper methods: `approve()`, `reject()`, `isPending()`, `canBeReviewedBy()`

### 3. Create Notification Model

- Add `notifications` table with columns:
- `id`, `user_id` (foreign key)
- `type` (string - e.g., 'approval_request', 'approval_decision')
- `title`, `message`, `action_url` (nullable)
- `read_at` (nullable timestamp)
- `data` (json - additional context)
- `timestamps`
- Add relationships and scopes for unread/read notifications

## Backend Services & Logic

### 4. Create ApprovalService

- `submitForApproval(Transaction $transaction, ?string $notes)` - Create approval record
- `approve(Approval $approval, User $reviewer)` - Approve and update transaction
- `reject(Approval $approval, User $reviewer, string $reason)` - Reject with reason
- `getPendingApprovalsForReviewer(User $user)` - Query pending approvals
- `canUserApprove(User $user)` - Check if user has `approve-transactions` permission

### 5. Create NotificationService

- `sendApprovalRequest(Approval $approval)` - Notify approvers
- `sendApprovalDecision(Approval $approval)` - Notify requester of decision
- `markAsRead(Notification $notification)`
- `getUnreadNotifications(User $user)`

### 6. Update Transaction Logic

- Modify `TransactionController@store`: If user is Requester role, set status to 'pending' and call `ApprovalService::submitForApproval()`
- Add authorization check: Requesters can only edit their own pending transactions
- Update transaction status enum if needed (pending/approved/rejected)

## Controllers & Routes

### 7. Create ApprovalController

- `index()` - Show pending approvals (Accountant/Admin only)
- `show($id)` - Show approval details with transaction
- `approve($id)` - Approve transaction (POST)
- `reject($id)` - Reject with reason (POST, requires `rejection_reason`)

### 8. Create NotificationController

- `index()` - List user's notifications
- `markAsRead($id)` - Mark notification as read
- `markAllAsRead()` - Mark all as read

### 9. Add Routes

- `Route::prefix('approvals')->middleware('can:approve-transactions')`
- `GET /approvals` → `index`
- `GET /approvals/{id}` → `show`
- `POST /approvals/{id}/approve` → `approve`
- `POST /approvals/{id}/reject` → `reject`
- `Route::prefix('notifications')`
- `GET /notifications` → `index`
- `POST /notifications/{id}/read` → `markAsRead`
- `POST /notifications/read-all` → `markAllAsRead`

## Frontend Components

### 10. Create Approval Pages

- `resources/js/pages/Approvals/Index.vue` - List pending approvals with filters
- `resources/js/pages/Approvals/Show.vue` - Detail view with approve/reject actions
- Include transaction details, receipt preview, requester info, submission notes

### 11. Create Notification Components

- `resources/js/components/NotificationBell.vue` - Bell icon with unread count badge
- `resources/js/components/NotificationDropdown.vue` - Dropdown list of recent notifications
- Add to `AppLayout.vue` navigation bar

### 12. Update Transaction Forms

- Add "Submit for Approval" button in `Transactions/Create.vue` for Requesters
- Add optional `notes` textarea field for submission notes
- Show approval status badge in `Transactions/Index.vue` and `Transactions/Show.vue`
- Disable edit/delete for pending approval transactions

### 13. Update Dashboard

- Add "Pending Approvals" widget for Accountants/Admins showing count
- Add "My Pending Requests" widget for Requesters showing their submitted transactions
- Link to `/approvals` page

### 14. Update Sidebar Navigation

- Add "Approvals" menu item (visible only to users with `approve-transactions` permission)
- Show badge with pending approval count

## Form Requests & Validation

### 15. Create Form Requests

- `ApproveTransactionRequest` - Validate approval action
- `RejectTransactionRequest` - Validate rejection (require `rejection_reason`, min 10 chars)

## Testing

### 16. Write Feature Tests

- `ApprovalWorkflowTest.php`:
- Requester can submit transaction for approval
- Accountant can see pending approvals
- Accountant can approve transaction
- Accountant can reject transaction with reason
- Approved transaction updates status
- Rejected transaction cannot be re-approved without re-submission
- Only authorized users can approve
- Requester cannot approve their own transaction

### 17. Write Unit Tests

- `ApprovalServiceTest.php` - Test service methods
- `NotificationServiceTest.php` - Test notification creation and retrieval
- `ApprovalModelTest.php` - Test model relationships and scopes

### 18. Run Tests

- Run `php artisan test --filter=Approval` to verify all approval tests pass
- Run full test suite to ensure no regressions

## Permissions & Authorization

### 19. Update Policies

- Create `ApprovalPolicy` with methods: `viewAny`, `view`, `approve`, `reject`
- Update `TransactionPolicy` to check approval status before allowing edits

## Additional Features

### 20. Approval History

- Show approval timeline in `Transactions/Show.vue`
- Display: submitted by, reviewed by, dates, reason (if rejected)

### 21. Quick Actions

- Add approve/reject buttons directly in approval index table for quick processing
- Show confirmation modals before actions

## Documentation

### 22. Update Sprint Documentation

- Create `docs/07-development/sprint-planning/sprint-05.md`
- Document approval workflow logic, permissions, and usage

### To-dos

- [ ] Create approvals and notifications migrations with proper relationships
- [ ] Build Approval, Notification models and ApprovalService, NotificationService
- [ ] Create ApprovalController, NotificationController and add routes
- [ ] Build Approvals Index/Show pages and notification components
- [ ] Update transaction forms and flows to handle approval workflow
- [ ] Add approval widgets to Dashboard and sidebar navigation
- [ ] Create ApprovalPolicy and update TransactionPolicy authorization
- [ ] Write comprehensive feature and unit tests for approval workflow
- [ ] Document sprint outcomes and approval workflow usage