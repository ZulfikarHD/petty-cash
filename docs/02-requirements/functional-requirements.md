# Functional Requirements

## Document Information

| Attribute | Value |
|-----------|-------|
| **Document Version** | 1.2 |
| **Last Updated** | November 24, 2024 |
| **Status** | Active |
| **Owner** | Product Owner |

## Introduction

This document defines the functional requirements for the Petty Cash Book application. Each requirement is categorized by feature area and includes priority, status, and acceptance criteria.

### Requirement Priority

- **P0**: Critical (Must Have) - Core functionality
- **P1**: High (Should Have) - Important features
- **P2**: Medium (Nice to Have) - Enhancement features
- **P3**: Low (Won't Have in v1) - Future consideration

### Requirement Status

- ✅ **Implemented** - Feature is complete and tested
- 🔄 **In Progress** - Currently being developed
- 📋 **Planned** - Scheduled for upcoming sprint
- 💭 **Proposed** - Under consideration

---

## 1. Authentication & Authorization

### FR-AUTH-001: User Registration ✅
**Priority**: P0  
**Status**: Implemented (Sprint 1)

Users must be able to register for an account.

**Acceptance Criteria:**
- User provides name, email, and password
- Email must be unique
- Password must be at least 8 characters
- Email verification is required
- User cannot access system until email is verified

### FR-AUTH-002: User Login ✅
**Priority**: P0  
**Status**: Implemented (Sprint 1)

Users must be able to log in with email and password.

**Acceptance Criteria:**
- Login with email and password
- "Remember me" option available
- Failed login attempts are logged
- Redirect to intended page after login
- Show appropriate error messages

### FR-AUTH-003: Password Reset ✅
**Priority**: P0  
**Status**: Implemented (Sprint 1)

Users must be able to reset forgotten passwords.

**Acceptance Criteria:**
- User enters email address
- Reset link sent via email
- Link expires after 60 minutes
- User can set new password
- Old password is invalidated

### FR-AUTH-004: Email Verification ✅
**Priority**: P0  
**Status**: Implemented (Sprint 1)

New users must verify their email addresses.

**Acceptance Criteria:**
- Verification email sent on registration
- Link to verify email
- User can resend verification email
- Verified status stored in database
- Unverified users have restricted access

### FR-AUTH-005: Two-Factor Authentication 💭
**Priority**: P2  
**Status**: Proposed (Sprint 12)

Users should be able to enable 2FA for additional security.

**Acceptance Criteria:**
- QR code generation for 2FA apps
- Backup codes provided
- Can disable 2FA with password
- Required for admin users (configurable)

---

## 2. User Management

### FR-USER-001: Create User ✅
**Priority**: P0  
**Status**: Implemented (Sprint 1)

Admins must be able to create new user accounts.

**Acceptance Criteria:**
- Admin can create users without email verification
- Assign role during creation
- Temporary password generated
- User notified via email
- Only admins can create users

### FR-USER-002: Edit User ✅
**Priority**: P0  
**Status**: Implemented (Sprint 1)

Admins must be able to edit user information.

**Acceptance Criteria:**
- Edit name, email, role
- Cannot edit own role
- Changes are logged
- User notified of changes
- Validation prevents duplicate emails

### FR-USER-003: Delete User ✅
**Priority**: P1  
**Status**: Implemented (Sprint 1)

Admins must be able to deactivate users.

**Acceptance Criteria:**
- Soft delete (keep records)
- Cannot delete own account
- Deleted users cannot login
- Can restore deleted users
- Associated transactions remain

### FR-USER-004: Assign Roles ✅
**Priority**: P0  
**Status**: Implemented (Sprint 1)

Admins must be able to assign roles to users.

**Acceptance Criteria:**
- Roles: Admin, Accountant, Cashier, Requester
- User can have only one role
- Role change takes effect immediately
- Cannot remove own admin role
- Role changes are logged

### FR-USER-005: Profile Management ✅
**Priority**: P1  
**Status**: Implemented (Sprint 1)

Users must be able to manage their own profile.

**Acceptance Criteria:**
- Edit name and email
- Change password
- View assigned role
- Upload profile picture (future)
- Cannot change own role

---

## 3. Transaction Management

### FR-TRANS-001: Create Transaction ✅
**Priority**: P0  
**Status**: Implemented (Sprint 2)

Users must be able to create cash in/out transactions.

**Acceptance Criteria:**
- Required fields: type, amount, description, date
- Type: Cash In or Cash Out
- Amount must be positive number
- Transaction number auto-generated
- Date cannot be in future
- Initial status is "Pending"

### FR-TRANS-002: View Transaction List ✅
**Priority**: P0  
**Status**: Implemented (Sprint 2)

Users must be able to view list of transactions.

**Acceptance Criteria:**
- Paginated list (15 per page)
- Shows: number, date, type, amount, status
- Sorted by date (newest first)
- Permission-based filtering
- Click to view details

### FR-TRANS-003: View Transaction Details ✅
**Priority**: P0  
**Status**: Implemented (Sprint 2)

Users must be able to view full transaction details.

**Acceptance Criteria:**
- All transaction fields visible
- Creator information shown
- Approver information (if approved)
- Rejection reason (if rejected)
- Attached receipts displayed
- Edit/Delete buttons (if allowed)

### FR-TRANS-004: Edit Transaction ✅
**Priority**: P0  
**Status**: Implemented (Sprint 2)

Users must be able to edit pending transactions.

**Acceptance Criteria:**
- Only pending transactions can be edited
- Only creator or admin can edit
- All fields except transaction number editable
- Changes are logged (future)
- Form pre-filled with current data

### FR-TRANS-005: Delete Transaction ✅
**Priority**: P1  
**Status**: Implemented (Sprint 2)

Users must be able to delete pending transactions.

**Acceptance Criteria:**
- Only pending transactions can be deleted
- Only creator or admin can delete
- Soft delete (keep in database)
- Confirmation required
- Cannot be undone by regular users
- Admin can restore

### FR-TRANS-006: Upload Receipt ✅
**Priority**: P1  
**Status**: Implemented (Sprint 2)

Users must be able to attach receipt images.

**Acceptance Criteria:**
- Accept: JPEG, PNG, GIF, PDF
- Max size: 5MB per file
- Multiple files support (future)
- Image preview before upload
- Can delete uploaded receipts
- Receipt stored securely

### FR-TRANS-007: Search Transactions ✅
**Priority**: P1  
**Status**: Implemented (Sprint 2)

Users must be able to search for transactions.

**Acceptance Criteria:**
- Search by transaction number
- Search by description
- Search results paginated
- Highlights search terms
- Shows number of results

### FR-TRANS-008: Filter Transactions ✅
**Priority**: P1  
**Status**: Implemented (Sprint 2)

Users must be able to filter transaction list.

**Acceptance Criteria:**
- Filter by type (in/out/all)
- Filter by status (pending/approved/rejected/all)
- Filter by date range
- Filters can be combined
- Filter state persists in URL
- Clear filters option

### FR-TRANS-009: Transaction Summary ✅
**Priority**: P1  
**Status**: Implemented (Sprint 2)

System must show transaction summaries.

**Acceptance Criteria:**
- Total cash in (approved only)
- Total cash out (approved only)
- Net balance calculation
- Summary respects applied filters
- Real-time updates

### FR-TRANS-010: Auto-Generate Transaction Number ✅
**Priority**: P0  
**Status**: Implemented (Sprint 2)

System must auto-generate unique transaction numbers.

**Acceptance Criteria:**
- Format: TXN-YYYY-NNNNN
- YYYY is current year
- NNNNN is zero-padded sequence
- Guaranteed unique
- Cannot be edited by users
- Resets each year

---

## 4. Approval Workflow

### FR-APPR-001: Submit for Approval 📋
**Priority**: P0  
**Status**: Planned (Sprint 5)

Users must be able to submit transactions for approval.

**Acceptance Criteria:**
- Submit button for pending transactions
- Transaction status changes to "Pending Approval"
- Approvers notified via email
- Submitter cannot edit after submission
- Can cancel submission (return to pending)

### FR-APPR-002: Approve Transaction 📋
**Priority**: P0  
**Status**: Planned (Sprint 5)

Approvers must be able to approve transactions.

**Acceptance Criteria:**
- Only users with approve permission
- Review all transaction details
- Approve with one click
- Status changes to "Approved"
- Approver and timestamp recorded
- Creator notified via email

### FR-APPR-003: Reject Transaction 📋
**Priority**: P0  
**Status**: Planned (Sprint 5)

Approvers must be able to reject transactions.

**Acceptance Criteria:**
- Rejection reason required
- Status changes to "Rejected"
- Approver and timestamp recorded
- Creator notified with reason
- Transaction can be edited and resubmitted

### FR-APPR-004: Approval History 📋
**Priority**: P1  
**Status**: Planned (Sprint 5)

System must maintain approval history.

**Acceptance Criteria:**
- Track all approval actions
- Show who, when, what action
- Display in transaction details
- Include rejection reasons
- Searchable history (future)

### FR-APPR-005: Multi-Level Approval 💭
**Priority**: P2  
**Status**: Proposed (Sprint 5)

System should support multi-level approvals.

**Acceptance Criteria:**
- Configure approval levels per amount
- Sequential approval flow
- Skip levels if approver unavailable
- All levels recorded
- Configurable by admin

---

## 5. Categories & Budgets

### FR-CAT-001: Create Category 📋
**Priority**: P0  
**Status**: Planned (Sprint 3)

Admins must be able to create expense categories.

**Acceptance Criteria:**
- Name and description required
- Category code (optional)
- Active/inactive status
- Parent category (for subcategories)
- Only admins can create

### FR-CAT-002: Assign Category to Transaction 📋
**Priority**: P0  
**Status**: Planned (Sprint 3)

Users must be able to assign categories to transactions.

**Acceptance Criteria:**
- Dropdown list of active categories
- Category is optional initially
- Can change category later
- Category shown in transaction list
- Filter transactions by category

### FR-CAT-003: Set Budget Limit 📋
**Priority**: P1  
**Status**: Planned (Sprint 3)

Admins must be able to set budget limits per category.

**Acceptance Criteria:**
- Budget amount per period (monthly/quarterly/annual)
- Start and end dates
- Alert threshold (e.g., 80%)
- Budget can be adjusted
- Historical budgets maintained

### FR-CAT-004: Track Budget Usage 📋
**Priority**: P1  
**Status**: Planned (Sprint 3)

System must track actual spending vs budget.

**Acceptance Criteria:**
- Real-time budget calculation
- Show remaining budget
- Visual progress bar
- Alert when threshold reached
- Prevent overspending (optional)

### FR-CAT-005: Budget Reports 📋
**Priority**: P1  
**Status**: Planned (Sprint 3)

System must generate budget reports.

**Acceptance Criteria:**
- Budget vs actual per category
- Variance analysis
- Trend charts
- Export to PDF/Excel
- Period comparison

---

## 6. Cash Balance Management

### FR-BAL-001: Set Opening Balance 📋
**Priority**: P0  
**Status**: Planned (Sprint 4)

Cashiers must be able to set opening balance.

**Acceptance Criteria:**
- Enter opening balance for period
- Date and time recorded
- Who entered recorded
- Cannot change after transactions
- Only cashiers can set

### FR-BAL-002: Real-Time Balance 📋
**Priority**: P0  
**Status**: Planned (Sprint 4)

System must show real-time cash balance.

**Acceptance Criteria:**
- Opening balance + cash in - cash out
- Only approved transactions counted
- Displayed on dashboard
- Updates automatically
- Historical balance tracking

### FR-BAL-003: Reconcile Cash 📋
**Priority**: P1  
**Status**: Planned (Sprint 4)

Cashiers must be able to reconcile cash on hand.

**Acceptance Criteria:**
- Enter physical cash count
- Compare with system balance
- Show discrepancy if any
- Record discrepancy reason
- Adjustment transaction created

### FR-BAL-004: Balance History 📋
**Priority**: P1  
**Status**: Planned (Sprint 4)

Users must be able to view balance history.

**Acceptance Criteria:**
- Daily balance snapshots
- Opening, closing, transactions
- Chart visualization
- Export to Excel
- Date range selection

### FR-BAL-005: Low Balance Alert 📋
**Priority**: P2  
**Status**: Planned (Sprint 4)

System should alert when balance is low.

**Acceptance Criteria:**
- Configurable threshold amount
- Alert on dashboard
- Email notification
- Can acknowledge alert
- Alert history maintained

---

## 7. Reporting

### FR-REP-001: Transaction Report 📋
**Priority**: P1  
**Status**: Planned (Sprint 6)

Users must be able to generate transaction reports.

**Acceptance Criteria:**
- Filter by date range, type, status, category
- Grouped by day/week/month
- Summary totals
- Export to PDF/Excel
- Schedule automated reports (future)

### FR-REP-002: Category Report 📋
**Priority**: P1  
**Status**: Planned (Sprint 6)

Users must be able to view spending by category.

**Acceptance Criteria:**
- Pie chart of category breakdown
- Table with amounts and percentages
- Compare periods
- Drill down to transactions
- Export to PDF/Excel

### FR-REP-003: Vendor Report 📋
**Priority**: P2  
**Status**: Planned (Sprint 8)

Users must be able to view spending by vendor.

**Acceptance Criteria:**
- List vendors with total spent
- Filter by date range
- Show transaction count
- Sort by amount
- Export to Excel

### FR-REP-004: User Activity Report 📋
**Priority**: P2  
**Status**: Planned (Sprint 9)

Admins must be able to view user activity.

**Acceptance Criteria:**
- Transactions created per user
- Approval actions per user
- Date range filter
- Export to Excel
- Graphical representation

### FR-REP-005: Audit Report 📋
**Priority**: P1  
**Status**: Planned (Sprint 9)

Admins must be able to generate audit reports.

**Acceptance Criteria:**
- All changes to transactions
- Who, what, when
- Filter by user, date, action
- Export to PDF for archival
- Immutable records

---

## 8. Dashboard

### FR-DASH-001: Summary Widgets ✅
**Priority**: P0  
**Status**: Implemented (Sprint 2)

Dashboard must show key metrics.

**Acceptance Criteria:**
- Total cash in (current period)
- Total cash out (current period)
- Current balance
- Pending approval count
- Recent transactions

### FR-DASH-002: Quick Actions 🔄
**Priority**: P1  
**Status**: In Progress

Dashboard must provide quick action buttons.

**Acceptance Criteria:**
- New transaction button
- Quick approve (for approvers)
- View all transactions
- Generate report
- Customizable shortcuts

### FR-DASH-003: Charts & Graphs 📋
**Priority**: P1  
**Status**: Planned (Sprint 6)

Dashboard must show visual analytics.

**Acceptance Criteria:**
- Cash flow trend (line chart)
- Category breakdown (pie chart)
- Monthly comparison (bar chart)
- Interactive charts
- Period selection

---

## 9. Vendor Management

### FR-VEND-001: Create Vendor 📋
**Priority**: P1  
**Status**: Planned (Sprint 8)

Users must be able to add vendors.

**Acceptance Criteria:**
- Name, contact, address fields
- Vendor code (auto-generated)
- Active/inactive status
- Contact person details
- Payment terms (optional)

### FR-VEND-002: Link Transaction to Vendor 📋
**Priority**: P1  
**Status**: Planned (Sprint 8)

Users must be able to link transactions to vendors.

**Acceptance Criteria:**
- Vendor dropdown on transaction form
- Vendor is optional
- Search vendors by name
- Recent vendors shown first
- Vendor info in transaction details

---

## 10. Reimbursement

### FR-REIMB-001: Submit Reimbursement Request 📋
**Priority**: P1  
**Status**: Planned (Sprint 7)

Staff must be able to request reimbursements.

**Acceptance Criteria:**
- Multiple line items
- Attach multiple receipts
- Enter reimbursement details
- Submit for approval
- Track status

---

## Non-Functional Requirements Summary

See [Non-Functional Requirements](non-functional-requirements.md) for details on:

- **Performance**: Page load < 2s, 100+ concurrent users
- **Security**: HTTPS, CSRF protection, XSS prevention, role-based access
- **Usability**: Intuitive UI, mobile-responsive, accessibility (WCAG 2.1)
- **Reliability**: 99.5% uptime, automated backups, disaster recovery
- **Scalability**: Support 1000+ users, 100K+ transactions
- **Maintainability**: Clean code, documented, tested (80%+ coverage)

---

## Requirements Traceability Matrix

| Requirement ID | Priority | Sprint | Status | Tests |
|----------------|----------|--------|--------|-------|
| FR-AUTH-001 to 004 | P0 | 1 | ✅ | 27 tests |
| FR-USER-001 to 005 | P0 | 1 | ✅ | Included |
| FR-TRANS-001 to 010 | P0-P1 | 2 | ✅ | 42 tests |
| FR-APPR-001 to 005 | P0-P2 | 5 | 📋 | Pending |
| FR-CAT-001 to 005 | P0-P1 | 3 | 📋 | Pending |
| FR-BAL-001 to 005 | P0-P2 | 4 | 📋 | Pending |
| FR-REP-001 to 005 | P1-P2 | 6-9 | 📋 | Pending |
| FR-DASH-001 to 003 | P0-P1 | 2,6 | 🔄 | Partial |
| FR-VEND-001 to 002 | P1 | 8 | 📋 | Pending |
| FR-REIMB-001 | P1 | 7 | 📋 | Pending |

---

**Document Status**: Active  
**Next Review**: After Sprint 3  
**Total Requirements**: 50+ functional requirements

