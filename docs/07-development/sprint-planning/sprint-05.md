# Sprint 5: Approval Workflow System

## Sprint Overview

**Sprint Goal**: Build a single-level approval workflow where users with the "Requester" role must submit transactions for approval. Accountants and Admins can approve/reject these requests with in-app notifications.

**Duration**: 2 weeks  
**Start Date**: November 25, 2024  
**End Date**: December 9, 2024  
**Status**: ✅ COMPLETED

---

## User Stories

### APPR-001: Submit Transactions for Approval ✅
**As a requester**, I can submit transactions for approval  
**Priority**: High  
**Story Points**: 5

**Acceptance Criteria:**
- ✅ Requester transactions automatically go to pending status
- ✅ Approval record created with submission notes
- ✅ Approvers notified of new approval request
- ✅ Non-requester transactions are auto-approved
- ✅ Clear UI indication that approval is required

**Tasks:**
- [x] Update TransactionController to handle Requester role
- [x] Create Approval model and migration
- [x] Integrate ApprovalService in transaction creation
- [x] Add approval notes field to transaction form
- [x] Show approval notice for Requesters

---

### APPR-002: View Pending Approvals ✅
**As an approver**, I can view pending approval requests  
**Priority**: High  
**Story Points**: 3

**Acceptance Criteria:**
- ✅ Approvals page shows pending requests
- ✅ Filter by status (pending, approved, rejected)
- ✅ Summary cards show counts per status
- ✅ Transaction details visible in list
- ✅ Only users with approve-transactions permission can access

**Tasks:**
- [x] Create ApprovalController with index method
- [x] Build Approvals/Index.vue page
- [x] Add status filtering
- [x] Add approval stats cards
- [x] Implement authorization

---

### APPR-003: Approve or Reject Transactions ✅
**As an approver**, I can approve or reject transactions  
**Priority**: High  
**Story Points**: 5

**Acceptance Criteria:**
- ✅ Approve button marks transaction as approved
- ✅ Reject requires reason (min 10 characters)
- ✅ Transaction status updates accordingly
- ✅ Requester notified of decision
- ✅ Cannot approve own submissions
- ✅ Cannot approve already processed requests

**Tasks:**
- [x] Create approve/reject endpoints
- [x] Build approval confirmation dialogs
- [x] Implement rejection reason validation
- [x] Create ApprovalService methods
- [x] Send notification on decision

---

### APPR-004: Track Approval Status ✅
**As a user**, I can track approval status of my requests  
**Priority**: Medium  
**Story Points**: 3

**Acceptance Criteria:**
- ✅ Transaction show page displays approval history
- ✅ Timeline shows submitted/reviewed dates
- ✅ Reviewer name displayed
- ✅ Rejection reason shown if rejected
- ✅ Dashboard shows pending submissions count

**Tasks:**
- [x] Add approval relationship to Transaction
- [x] Update Transactions/Show.vue with approval timeline
- [x] Add approval stats to dashboard
- [x] Create approval history component

---

### APPR-005: In-App Notifications ✅
**As a user**, I receive in-app notifications for approvals  
**Priority**: Medium  
**Story Points**: 5

**Acceptance Criteria:**
- ✅ Notification bell in header shows unread count
- ✅ Dropdown shows recent notifications
- ✅ Mark as read functionality
- ✅ Mark all as read functionality
- ✅ Notifications page for full history
- ✅ Approvers notified of new requests
- ✅ Requesters notified of decisions

**Tasks:**
- [x] Create AppNotification model and migration
- [x] Create NotificationService
- [x] Build NotificationBell component
- [x] Build Notifications/Index.vue page
- [x] Integrate notifications with approval workflow

---

## Technical Implementation

### Database Schema

#### Approvals Table
```sql
CREATE TABLE approvals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT UNSIGNED NOT NULL,
    submitted_by BIGINT UNSIGNED NOT NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT NULL,
    rejection_reason TEXT NULL,
    submitted_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX (transaction_id),
    INDEX (status),
    INDEX (submitted_by),
    INDEX (reviewed_by)
);
```

#### App Notifications Table
```sql
CREATE TABLE app_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    action_url VARCHAR(255) NULL,
    read_at TIMESTAMP NULL,
    data JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (type),
    INDEX (read_at)
);
```

---

### Backend Components Created

#### Models
- `app/Models/Approval.php`
  - HasFactory trait
  - Relationships: belongsTo(Transaction), belongsTo(User) for submittedBy, reviewedBy
  - Scopes: pending(), approved(), rejected()
  - Methods: approve(), reject(), isPending(), isApproved(), isRejected(), canBeReviewedBy()
  - Casts: submitted_at (datetime), reviewed_at (datetime)

- `app/Models/AppNotification.php`
  - HasFactory trait
  - Relationships: belongsTo(User)
  - Scopes: unread(), read()
  - Methods: markAsRead(), isUnread(), isRead()
  - Casts: read_at (datetime), data (array)

#### Services
- `app/Services/ApprovalService.php`
  - `submitForApproval(Transaction, ?string $notes)` - Create approval record
  - `approve(Approval, User)` - Approve and update transaction
  - `reject(Approval, User, string $reason)` - Reject with reason
  - `getPendingApprovalsForReviewer(User)` - Paginated pending approvals
  - `getPendingApprovalsCount(User)` - Count for dashboard
  - `getSubmittedApprovals(User)` - User's submissions
  - `getPendingSubmissionsCount(User)` - Count for requester dashboard
  - `canUserApprove(User)` - Permission check

- `app/Services/NotificationService.php`
  - `sendApprovalRequest(Approval)` - Notify all approvers
  - `sendApprovalDecision(Approval)` - Notify requester
  - `createNotification(User, type, title, message, actionUrl, data)`
  - `markAsRead(AppNotification)`
  - `markAllAsRead(User)`
  - `getUnreadNotifications(User, limit)`
  - `getNotifications(User)` - Paginated
  - `getUnreadCount(User)`

#### Controllers
- `app/Http/Controllers/ApprovalController.php`
  - `index()` - List approvals with filters
  - `show(Approval)` - View approval details
  - `approve(ApproveTransactionRequest, Approval)` - Approve transaction
  - `reject(RejectTransactionRequest, Approval)` - Reject with reason

- `app/Http/Controllers/NotificationController.php`
  - `index()` - List all notifications
  - `recent()` - Get recent for dropdown (JSON)
  - `markAsRead(AppNotification)` - Mark single as read
  - `markAllAsRead()` - Mark all as read

#### Form Requests
- `app/Http/Requests/ApproveTransactionRequest.php`
  - Authorization: approve-transactions permission

- `app/Http/Requests/RejectTransactionRequest.php`
  - Validation: rejection_reason (required, min:10, max:1000)
  - Authorization: approve-transactions permission

#### Policies
- `app/Policies/ApprovalPolicy.php`
  - viewAny, view, approve, reject, create, update, delete methods
  - Cannot approve own submissions
  - Must be pending to approve/reject

- `app/Policies/TransactionPolicy.php`
  - viewAny, view, create, update, delete, restore, forceDelete, approve
  - Cannot edit transactions with pending approval
  - Only own pending transactions can be edited/deleted

#### Factories
- `database/factories/ApprovalFactory.php`
  - States: pending(), approved(), rejected()

- `database/factories/AppNotificationFactory.php`
  - States: unread(), read(), approvalRequest(), approvalDecision()

---

### Frontend Components Created

#### Pages

**Approvals:**
- `resources/js/pages/Approvals/Index.vue`
  - Summary cards: Pending, Approved, Rejected counts
  - Status filter dropdown
  - Approvals table with transaction details
  - Quick approve/reject buttons
  - Confirmation dialogs
  - Rejection reason textarea

- `resources/js/pages/Approvals/Show.vue`
  - Transaction details card
  - Approval information card
  - Receipt images gallery
  - Approve/Reject action buttons
  - Image preview modal

**Notifications:**
- `resources/js/pages/Notifications/Index.vue`
  - Full notification history
  - Mark as read/mark all buttons
  - Relative time display
  - Action links

**Components:**
- `resources/js/components/NotificationBell.vue`
  - Bell icon with unread badge
  - Dropdown with recent notifications
  - Mark as read actions
  - Link to full notifications page
  - Auto-refresh every 30 seconds

**Updated Pages:**
- `resources/js/pages/Transactions/Create.vue`
  - Approval notice alert for Requesters
  - Approval notes field
  - Dynamic submit button text

- `resources/js/pages/Transactions/Show.vue`
  - Approval history timeline
  - Submitted/reviewed dates
  - Reviewer info
  - Rejection reason display

- `resources/js/pages/Dashboard.vue`
  - Pending Approvals card (for approvers)
  - My Pending Requests card (for requesters)
  - Links to approval pages

**Navigation:**
- `resources/js/components/AppSidebar.vue`
  - Added "Approvals" menu item (ClipboardCheck icon)
  - Only visible to users with approve-transactions permission

- `resources/js/components/AppSidebarHeader.vue`
  - Added NotificationBell component

---

## Testing

### Test Coverage

#### Feature Tests Created

**Approval Workflow:**
- `tests/Feature/ApprovalWorkflowTest.php` (17 tests)
  - ✅ Requester transaction creates approval request
  - ✅ Cashier transaction is auto approved
  - ✅ Accountant can view pending approvals
  - ✅ Accountant can approve transaction
  - ✅ Accountant can reject transaction with reason
  - ✅ Rejection requires reason
  - ✅ Rejection reason must be at least 10 characters
  - ✅ Requester cannot approve own transaction
  - ✅ Requester cannot access approvals page
  - ✅ Approval creates notification for approvers
  - ✅ Approval decision creates notification for requester
  - ✅ Cannot approve already approved transaction
  - ✅ Admin can view and approve transactions
  - ✅ Approval service returns pending count
  - ✅ User can mark notification as read
  - ✅ User can mark all notifications as read
  - ✅ User cannot mark other users notification as read

### Test Results
- **Total Tests**: 144 → 161 tests
- **New Tests Added**: 17 tests
- **Total Assertions**: 354 → 400+ assertions
- **Status**: ✅ All Approval tests passing (17/17)
- **Code Coverage**: ~85% (estimated)

### Test Command Used
```bash
# Run all Approval tests
php artisan test --filter=ApprovalWorkflowTest

# Run full test suite
php artisan test
```

---

## Routes Added

```php
// Approval Workflow Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('approvals', [ApprovalController::class, 'index'])
        ->name('approvals.index');
    Route::get('approvals/{approval}', [ApprovalController::class, 'show'])
        ->name('approvals.show');
    Route::post('approvals/{approval}/approve', [ApprovalController::class, 'approve'])
        ->name('approvals.approve');
    Route::post('approvals/{approval}/reject', [ApprovalController::class, 'reject'])
        ->name('approvals.reject');
});

// Notification Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::get('notifications/recent', [NotificationController::class, 'recent'])
        ->name('notifications.recent');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.mark-as-read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.mark-all-as-read');
});
```

**Wayfinder Routes Generated:**
- `approvals.index()` - GET /approvals
- `approvals.show(id)` - GET /approvals/{id}
- `approvals.approve(id)` - POST /approvals/{id}/approve
- `approvals.reject(id)` - POST /approvals/{id}/reject
- `notifications.index()` - GET /notifications
- `notifications.recent()` - GET /notifications/recent
- `notifications.markAsRead(id)` - POST /notifications/{id}/read
- `notifications.markAllAsRead()` - POST /notifications/read-all

---

## Approval Workflow Logic

### Transaction Creation Flow
```
1. User creates transaction
2. Check if user has Requester role
   - YES: Set status='pending', create Approval record, notify approvers
   - NO: Set status='approved', auto-approve with user as approver
3. Redirect to transactions list
```

### Approval Flow
```
1. Approver views pending approvals
2. Selects transaction to review
3. Views transaction details and receipts
4. Chooses to Approve or Reject
   - APPROVE: Update approval status, update transaction status, notify requester
   - REJECT: Require reason, update statuses, notify requester with reason
5. Redirect to approvals list
```

### Key Rules
1. **Requesters** must have transactions approved
2. **Cashiers/Accountants/Admins** transactions are auto-approved
3. **Cannot approve own submissions** (even with permission)
4. **Rejection requires reason** (minimum 10 characters)
5. **Only pending approvals** can be processed
6. **Notifications sent** on submission and decision

---

## Permissions Used

### Existing Permissions
- `approve-transactions` - Required to access approvals page and approve/reject

### Role Assignments
- **Admin**: Has approve-transactions (full access)
- **Accountant**: Has approve-transactions (can approve)
- **Cashier**: No approve-transactions (auto-approved)
- **Requester**: No approve-transactions (requires approval)

### Shared Auth Data
Added to `HandleInertiaRequests.php`:
```php
'approveTransactions' => $request->user()->can('approve-transactions'),
```

---

## Sprint Artifacts

### Created Files
**Backend:**
- `database/migrations/2025_11_25_043105_create_approvals_table.php`
- `database/migrations/2025_11_25_043109_create_notifications_table.php`
- `app/Models/Approval.php`
- `app/Models/AppNotification.php`
- `app/Services/ApprovalService.php`
- `app/Services/NotificationService.php`
- `app/Http/Controllers/ApprovalController.php`
- `app/Http/Controllers/NotificationController.php`
- `app/Http/Requests/ApproveTransactionRequest.php`
- `app/Http/Requests/RejectTransactionRequest.php`
- `app/Policies/ApprovalPolicy.php`
- `app/Policies/TransactionPolicy.php`
- `database/factories/ApprovalFactory.php`
- `database/factories/AppNotificationFactory.php`

**Frontend:**
- `resources/js/pages/Approvals/Index.vue`
- `resources/js/pages/Approvals/Show.vue`
- `resources/js/pages/Notifications/Index.vue`
- `resources/js/components/NotificationBell.vue`

**Tests:**
- `tests/Feature/ApprovalWorkflowTest.php`

**Modified Files:**
- `routes/web.php` - Added approval and notification routes
- `app/Models/User.php` - Added approval/notification relationships
- `app/Models/Transaction.php` - Added approval relationship
- `app/Http/Controllers/TransactionController.php` - Integrated approval workflow
- `app/Http/Middleware/HandleInertiaRequests.php` - Added approveTransactions permission
- `resources/js/pages/Dashboard.vue` - Added approval stats
- `resources/js/pages/Transactions/Create.vue` - Added approval notes
- `resources/js/pages/Transactions/Show.vue` - Added approval history
- `resources/js/components/AppSidebar.vue` - Added Approvals menu
- `resources/js/components/AppSidebarHeader.vue` - Added NotificationBell
- `resources/js/types/index.d.ts` - Added Approval, AppNotification types

---

## Sprint Retrospective

### What Went Well ✅
1. **Clean Service Architecture**: ApprovalService and NotificationService handle all business logic
2. **Comprehensive Testing**: 17 tests covering all workflow scenarios
3. **Role-Based Logic**: Clear separation between Requester and other roles
4. **In-App Notifications**: Real-time notification bell with polling
5. **UI/UX**: Intuitive approval workflow with confirmation dialogs
6. **Wayfinder Integration**: Type-safe routing throughout
7. **Policy Implementation**: Proper authorization at all levels

### What Could Be Improved 🔧
1. **Email Notifications**: Only in-app notifications implemented
2. **Multi-Level Approval**: Single level only (as specified)
3. **Approval Delegation**: No delegation feature
4. **Bulk Actions**: Cannot approve multiple at once
5. **Real-time Updates**: Uses polling instead of WebSockets

### Technical Debt 📝
- Consider adding email notifications (Sprint 11)
- Add bulk approval actions for efficiency
- Implement WebSocket for real-time notifications
- Add approval audit trail/logging
- Consider approval workflow configuration UI

### Metrics 📊
- **Tests Added**: 17 tests
- **Files Created**: 18 new files
- **Code Quality**: All tests passing, Pint formatted
- **Sprint Velocity**: ~21 story points
- **Sprint Duration**: On schedule

---

## Acceptance Criteria Review

### Sprint Goal Achievement
✅ **ACHIEVED** - All user stories completed and tested

### Quality Gates
- ✅ All tests passing (17 new tests, 46 assertions)
- ✅ Code formatted with Pint
- ✅ No linter errors
- ✅ Authorization properly implemented
- ✅ UI/UX consistent with existing design
- ✅ Wayfinder routes generated
- ✅ Database migrations tested

---

## Definition of Done Checklist

Sprint 5 DoD:
- [x] All code written and follows coding standards
- [x] Unit/feature tests written and passing (17 new tests)
- [x] Code peer-reviewed (self-review for this project)
- [x] Documentation updated (this document)
- [x] Database migrations tested
- [x] Authorization checks implemented
- [x] UI tested manually
- [x] No linter errors
- [x] Wayfinder routes generated
- [x] Feature demonstrated

---

**Sprint Completed**: November 25, 2024  
**Status**: ✅ SUCCESS  
**Next Sprint**: Sprint 6 - Dashboard & Basic Reporting  
**Sprint Lead**: [Your Name]  
**Last Updated**: November 25, 2024

