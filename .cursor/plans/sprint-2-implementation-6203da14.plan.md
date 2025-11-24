<!-- 6203da14-e435-47a9-b1f7-4edef0ab2b85 557a6c66-c0d5-4dc5-bbc5-ba5009a2f2f8 -->
# Sprint 2: Core Transaction Management Implementation

## Overview

Build the foundation for recording and managing petty cash transactions. Users (primarily cashiers) will be able to record cash-in and cash-out transactions, attach receipt images, view transaction lists with filtering, and manage their pending transactions.

## Current State

- Sprint 1 completed: Authentication & user management with roles (Admin, Accountant, Cashier, Requester)
- Spatie Permission configured with role-based access control
- User management interface with Vue 3 + Inertia.js
- Existing UI components (reka-ui, Tailwind CSS)

## Implementation Steps

### 1. Create Transactions Database Schema

**Migration file:** `database/migrations/YYYY_MM_DD_HHMMSS_create_transactions_table.php`

**Fields:**

- `id` (bigint, primary key)
- `transaction_number` (string, unique) - Auto-generated (e.g., "TXN-2024-00001")
- `type` (enum: 'in', 'out') - Cash in or cash out
- `amount` (decimal 15,2) - Transaction amount
- `description` (text) - Transaction details/purpose
- `transaction_date` (date) - Date of transaction
- `category_id` (bigint, nullable) - FK to categories (for Sprint 3)
- `vendor_id` (bigint, nullable) - FK to vendors (for Sprint 8)
- `user_id` (bigint) - FK to users (who created the transaction)
- `status` (enum: 'pending', 'approved', 'rejected') - Default 'pending'
- `approved_by` (bigint, nullable) - FK to users (approver)
- `approved_at` (timestamp, nullable)
- `rejection_reason` (text, nullable)
- `notes` (text, nullable) - Additional notes
- `timestamps` (created_at, updated_at)
- `deleted_at` (soft delete)

**Indexes:**

- Unique: transaction_number
- Index: user_id, status, transaction_date, type

**Files to create:**

- `database/migrations/YYYY_MM_DD_HHMMSS_create_transactions_table.php`

### 2. Create Transaction Model with Relationships

**Model file:** `app/Models/Transaction.php`

**Features:**

- Mass assignable fields
- Soft deletes
- Relationships:
  - `belongsTo(User::class, 'user_id')` - creator
  - `belongsTo(User::class, 'approved_by')` - approver
  - `hasMany(TransactionAttachment)` or use Spatie Media Library
- Accessors/Mutators:
  - Format transaction_number automatically
  - Cast amount to decimal
- Scopes:
  - `scopePending()`
  - `scopeApproved()`
  - `scopeRejected()`
  - `scopeCashIn()`
  - `scopeCashOut()`
  - `scopeByDateRange($start, $end)`

**Files to create:**

- `app/Models/Transaction.php`
- `database/factories/TransactionFactory.php`
- `database/seeders/TransactionSeeder.php` (optional)

**Files to modify:**

- `app/Models/User.php` - add `hasMany(Transaction::class)` relationship

### 3. Install and Configure Laravel Media Library

**Package:** `spatie/laravel-medialibrary`

**Tasks:**

- Install via Composer: `composer require spatie/laravel-medialibrary`
- Publish migrations: `php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"`
- Run migrations
- Configure Transaction model to use `HasMedia` trait
- Define media collections (e.g., 'receipts')
- Configure conversions for thumbnail generation

**Files to modify:**

- `composer.json` - add spatie/laravel-medialibrary
- `app/Models/Transaction.php` - implement HasMedia interface, add trait, define collections

**Config:**

- `config/media-library.php` - configure storage paths, allowed mime types

### 4. Create Transaction CRUD Backend

**Controller:** `app/Http/Controllers/TransactionController.php`

**Methods:**

- `index()` - List transactions with filters (search, date range, type, status)
- `create()` - Show create form
- `store()` - Create new transaction with receipt upload
- `show($id)` - View transaction details
- `edit($id)` - Show edit form (only pending transactions)
- `update($id)` - Update transaction
- `destroy($id)` - Soft delete transaction (only pending, own transactions)

**Form Requests:**

- `app/Http/Requests/StoreTransactionRequest.php`
  - Validate: type, amount (> 0), description, transaction_date, receipts (optional)
  - Authorization: check user has permission to create transactions

- `app/Http/Requests/UpdateTransactionRequest.php`
  - Same validation as store
  - Authorization: check user owns transaction and status is pending

**Routes:** `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('transactions', TransactionController::class);
});
```

**Permissions to check:**

- `create-transactions` - for store()
- `view-transactions` - for index(), show()
- `edit-transactions` - for edit(), update()
- `delete-transactions` - for destroy()

**Files to create:**

- `app/Http/Controllers/TransactionController.php`
- `app/Http/Requests/StoreTransactionRequest.php`
- `app/Http/Requests/UpdateTransactionRequest.php`

**Files to modify:**

- `routes/web.php` - add transaction routes
- `database/seeders/RolesAndPermissionsSeeder.php` - add transaction permissions

### 5. Build Transaction Frontend Interface

**Pages to create:**

**5.1 Transaction List (`resources/js/pages/Transactions/Index.vue`)**

- Display paginated transaction table
- Columns: Transaction #, Date, Type (badge), Amount, Description, Status (badge), Actions
- Search bar (by transaction number, description)
- Filters:
  - Date range picker (start date, end date)
  - Type dropdown (All, Cash In, Cash Out)
  - Status dropdown (All, Pending, Approved, Rejected)
- Action buttons: View, Edit (pending only), Delete (pending only)
- "New Transaction" button (top right)
- Summary cards: Total In, Total Out, Net Balance (for current filter)

**5.2 Transaction Create (`resources/js/pages/Transactions/Create.vue`)**

- Form fields:
  - Type (radio buttons: Cash In / Cash Out)
  - Amount (number input with currency symbol)
  - Transaction Date (date picker)
  - Description (textarea)
  - Notes (textarea, optional)
  - Receipt Upload (multiple files, image preview)
- Validation: client-side + server-side
- Submit button with loading state
- Cancel button (back to list)

**5.3 Transaction Edit (`resources/js/pages/Transactions/Edit.vue`)**

- Same form as Create
- Pre-filled with existing data
- Show existing receipts with delete option
- Only accessible if status is 'pending' and user owns it

**5.4 Transaction Show/Detail (`resources/js/pages/Transactions/Show.vue`)**

- Display all transaction details (read-only)
- Show receipt images in gallery/lightbox
- Show status badge with color coding
- Show approval info (if approved/rejected)
- Action buttons: Edit, Delete (if pending and owned), Back

**Reusable Components:**

**5.5 Transaction Form Component (`resources/js/components/Transactions/TransactionForm.vue`)**

- Reusable form for create/edit
- Props: transaction (optional), isEditing (boolean)
- Emits: submit event

**5.6 Transaction Table Component (`resources/js/components/Transactions/TransactionTable.vue`)**

- Reusable table component
- Props: transactions (paginated data), canEdit, canDelete
- Emits: edit, delete, view events

**5.7 Date Range Picker Component (`resources/js/components/ui/DateRangePicker.vue`)**

- Custom date range selector
- Use existing Vue component library or build with native inputs

**5.8 Receipt Gallery Component (`resources/js/components/Transactions/ReceiptGallery.vue`)**

- Display receipt images
- Lightbox/modal for full-size view
- Delete option for edit mode

**Files to create:**

- `resources/js/pages/Transactions/Index.vue`
- `resources/js/pages/Transactions/Create.vue`
- `resources/js/pages/Transactions/Edit.vue`
- `resources/js/pages/Transactions/Show.vue`
- `resources/js/components/Transactions/TransactionForm.vue`
- `resources/js/components/Transactions/TransactionTable.vue`
- `resources/js/components/Transactions/ReceiptGallery.vue`
- `resources/js/components/ui/DateRangePicker.vue`

**Files to modify:**

- `resources/js/types/index.ts` - add Transaction, TransactionType, TransactionStatus types
- `resources/js/layouts/AppLayout.vue` - add "Transactions" navigation link

### 6. Transaction Number Auto-Generation

**Observer or Model Event:**

Create an observer to auto-generate transaction numbers when creating transactions.

**Format:** `TXN-YYYY-NNNNN` (e.g., TXN-2024-00001)

**Logic:**

- Get last transaction number for current year
- Increment by 1
- Pad with zeros to 5 digits

**Files to create:**

- `app/Observers/TransactionObserver.php`

**Files to modify:**

- `app/Providers/AppServiceProvider.php` - register observer

### 7. Transaction Status Management

**Status Workflow:**

1. **Pending** - Initial state when created
2. **Approved** - Approved by authorized user (Sprint 5 will expand this)
3. **Rejected** - Rejected with reason

**Business Rules:**

- Only pending transactions can be edited
- Only pending transactions can be deleted
- Only users with 'approve-transactions' permission can approve/reject
- Approval records who approved and when

**Implementation:**

- Add status transition methods to Transaction model
- `approve(User $approver)` - set status to approved
- `reject(User $approver, string $reason)` - set status to rejected

### 8. Add Navigation and Dashboard Updates

**Navigation:**

- Add "Transactions" menu item in `resources/js/layouts/AppLayout.vue`
- Visible to users with 'view-transactions' permission

**Dashboard Updates (`resources/js/pages/Dashboard.vue`):**

- Add transaction summary widget
- Show: Total transactions, Pending count, Today's cash in/out
- Recent transactions list (last 5)

**Files to modify:**

- `resources/js/layouts/AppLayout.vue` - add navigation
- `resources/js/pages/Dashboard.vue` - add transaction widgets
- `app/Http/Controllers/DashboardController.php` - pass transaction data

### 9. Validation Rules

**Backend Validation (`StoreTransactionRequest` and `UpdateTransactionRequest`):**

```php
'type' => ['required', 'in:in,out'],
'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999999.99'],
'description' => ['required', 'string', 'max:1000'],
'transaction_date' => ['required', 'date', 'before_or_equal:today'],
'notes' => ['nullable', 'string', 'max:2000'],
'receipts.*' => ['nullable', 'image', 'max:5120'], // 5MB max per image
```

**Frontend Validation:**

- Real-time validation with error messages
- Prevent form submission if invalid
- Match backend validation rules

### 10. Write Comprehensive Tests

**Feature Tests:**

**10.1 `tests/Feature/Transactions/TransactionCRUDTest.php`**

- Test creating cash-in transaction
- Test creating cash-out transaction
- Test updating pending transaction
- Test cannot update approved transaction
- Test deleting pending transaction
- Test cannot delete approved transaction
- Test viewing transaction list
- Test viewing single transaction

**10.2 `tests/Feature/Transactions/TransactionFilterTest.php`**

- Test filtering by date range
- Test filtering by type (in/out)
- Test filtering by status
- Test searching by transaction number
- Test searching by description

**10.3 `tests/Feature/Transactions/TransactionReceiptTest.php`**

- Test uploading receipt with transaction
- Test uploading multiple receipts
- Test viewing receipts
- Test deleting receipt
- Test file size validation
- Test file type validation

**10.4 `tests/Feature/Transactions/TransactionAuthorizationTest.php`**

- Test only authorized users can create transactions
- Test user can only edit own pending transactions
- Test user can only delete own pending transactions
- Test cashier can create transactions
- Test requester permissions

**Unit Tests:**

**10.5 `tests/Unit/Models/TransactionTest.php`**

- Test transaction number generation
- Test relationships (user, approver)
- Test scopes (pending, approved, cashIn, cashOut)
- Test status transition methods

**Files to create:**

- All test files listed above

## Key Technical Decisions

1. **Media Storage:** Using Spatie Media Library for robust file handling, image conversions, and storage management
2. **Transaction Numbers:** Auto-generated with observer pattern for consistency
3. **Status Management:** Simple enum-based workflow (pending → approved/rejected)
4. **Filtering:** Server-side filtering with query parameters for performance
5. **Authorization:** Leveraging Spatie Permission policies for fine-grained access control
6. **File Upload:** Support multiple receipt images per transaction
7. **Soft Deletes:** Enable data recovery and audit trails

## Database Relationships

```
User (1) ---> (*) Transactions [user_id]
User (1) ---> (*) Transactions [approved_by]
Transaction (1) ---> (*) Media [via Spatie Media Library]
```

## Permissions Required

Add to `database/seeders/RolesAndPermissionsSeeder.php`:

- `view-transactions` - View transaction list and details
- `create-transactions` - Create new transactions
- `edit-transactions` - Edit pending transactions
- `delete-transactions` - Delete pending transactions
- `approve-transactions` - Approve/reject transactions (for Sprint 5)

**Role Assignments:**

- **Admin:** All transaction permissions
- **Accountant:** All transaction permissions
- **Cashier:** create, view, edit, delete (own transactions)
- **Requester:** create, view (own transactions)

## Acceptance Criteria Validation

- [ ] Users can create cash-in transactions with all required fields
- [ ] Users can create cash-out transactions with all required fields
- [ ] Receipt images can be uploaded (single or multiple)
- [ ] Receipts are displayed in transaction details
- [ ] Transaction list shows with pagination
- [ ] Filtering by date range works correctly
- [ ] Filtering by type (in/out) works correctly
- [ ] Filtering by status works correctly
- [ ] Search functionality works
- [ ] Only authorized users can edit/delete transactions
- [ ] Only pending transactions can be edited
- [ ] Only own transactions can be edited/deleted (unless admin)
- [ ] Data validation prevents invalid submissions
- [ ] All feature tests pass

## Commands to Run

```bash
# Install Spatie Media Library
composer require spatie/laravel-medialibrary

# Publish migrations
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"

# Create transaction migration
php artisan make:migration create_transactions_table

# Create Transaction model with factory
php artisan make:model Transaction -f

# Create controller
php artisan make:controller TransactionController --resource

# Create form requests
php artisan make:request StoreTransactionRequest
php artisan make:request UpdateTransactionRequest

# Create observer
php artisan make:observer TransactionObserver --model=Transaction

# Run migrations
php artisan migrate

# Run tests
php artisan test --filter Transaction

# Build frontend assets
yarn dev
```

### To-dos

- [ ] Create transactions table migration with all required fields
- [ ] Create Transaction model with relationships, scopes, and factory
- [ ] Install and configure Spatie Media Library for receipt uploads
- [ ] Configure Transaction model to use HasMedia trait and define collections
- [ ] Add transaction permissions to RolesAndPermissionsSeeder
- [ ] Create TransactionController with CRUD methods and form requests
- [ ] Create TransactionObserver for auto-generating transaction numbers
- [ ] Add transaction routes to web.php with auth middleware
- [ ] Create Transactions/Index.vue with table, filters, and search
- [ ] Create Create.vue and Edit.vue pages with TransactionForm component
- [ ] Create Show.vue page with receipt gallery
- [ ] Create reusable components: TransactionTable, ReceiptGallery, DateRangePicker
- [ ] Add Transactions link to navigation and update Dashboard with transaction widgets
- [ ] Write feature tests for CRUD, filtering, receipts, and authorization
- [ ] Write unit tests for Transaction model methods and scopes