<!-- 763427bf-c5ae-4fae-b283-3ad189196b01 553a812c-a07a-467e-a50a-283c705822a2 -->
# Sprint 4: Cash Balance & Reconciliation Implementation

## Overview

Implement real-time cash balance tracking with automated calculations from approved transactions, period-based opening balance management, cash reconciliation features, balance history tracking, and configurable low balance alerts.

## Architecture Approach

### Balance Calculation Strategy

- **Real-time calculation** from approved transactions (not stored balance)
- **Opening balances** stored per period in `cash_balances` table
- **Formula**: Current Balance = Opening Balance + Sum(Cash In) - Sum(Cash Out) [approved only]
- **Service class** pattern for balance calculations (following BudgetService pattern)

### Database Design

**cash_balances table**:

- `id`, `period_start`, `period_end`, `opening_balance`, `closing_balance`
- `notes`, `reconciliation_date`, `reconciled_by`, `discrepancy_amount`, `discrepancy_notes`
- `status` enum: 'active', 'reconciled', 'closed'
- Foreign key to `users` for `reconciled_by`

## Implementation Plan

### 1. Backend Foundation

**Migration**: `database/migrations/2025_11_25_XXXXXX_create_cash_balances_table.php`

- Create table with columns above
- Add indexes on `period_start`, `period_end`, `status`
- Add foreign key constraint for `reconciled_by`

**Model**: `app/Models/CashBalance.php`

- Fillable attributes
- Cast dates, decimals
- `belongsTo` relationship: `reconciledBy` (User)
- Scopes: `active()`, `reconciled()`, `forPeriod($start, $end)`
- Methods: `reconcile()`, `isActive()`, `isReconciled()`

**Service**: `app/Services/BalanceService.php`

- `getCurrentBalance(?Carbon $asOfDate = null): float` - calculate from opening + transactions
- `getOpeningBalance(Carbon $date): float` - get opening balance for period containing date
- `getPeriodBalance(Carbon $start, Carbon $end): array` - detailed breakdown
- `getBalanceHistory(Carbon $start, Carbon $end): Collection` - daily/period snapshots
- `reconcileBalance(CashBalance $cashBalance, float $actualAmount, ?string $notes): CashBalance`
- `needsLowBalanceAlert(float $currentBalance): bool` - check threshold
- Methods should handle only approved transactions

**Form Request**: `app/Http/Requests/StoreCashBalanceRequest.php`

- Validation for opening balance form
- Rules: `period_start` (required, date), `period_end` (required, date, after:period_start)
- `opening_balance` (required, numeric, min:0), `notes` (nullable, string)

**Form Request**: `app/Http/Requests/ReconcileBalanceRequest.php`

- Validation for reconciliation
- Rules: `actual_balance` (required, numeric), `discrepancy_notes` (required_if:has_discrepancy)

### 2. Controllers & Routes

**Controller**: `app/Http/Controllers/CashBalanceController.php`

- `index()` - list all balance periods with history
- `create()` - show opening balance form
- `store(StoreCashBalanceRequest)` - save opening balance
- `show(CashBalance)` - view specific period details
- `reconcile(CashBalance)` - show reconciliation form
- `storeReconciliation(ReconcileBalanceRequest, CashBalance)` - process reconciliation

**Routes** in `routes/web.php`:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('cash-balances', CashBalanceController::class)
        ->middleware('permission:view-transactions');
    Route::get('cash-balances/{cashBalance}/reconcile', [CashBalanceController::class, 'reconcile'])
        ->name('cash-balances.reconcile');
    Route::post('cash-balances/{cashBalance}/reconciliation', [CashBalanceController::class, 'storeReconciliation'])
        ->name('cash-balances.store-reconciliation');
});
```

### 3. Frontend Components

**Pages**:

- `resources/js/pages/CashBalance/Index.vue` - list periods, current balance widget, quick actions
- `resources/js/pages/CashBalance/Create.vue` - opening balance form
- `resources/js/pages/CashBalance/Show.vue` - period details, transaction breakdown
- `resources/js/pages/CashBalance/Reconcile.vue` - reconciliation form with calculator

**UI Components** (reuse existing):

- Card, Input, Button, Badge, Table components from `@/components/ui`
- Date picker for period selection

**Dashboard Widget**: Update `resources/js/pages/Dashboard.vue`

- Add "Current Balance" card with TrendingUp/Down icon
- Show low balance alert badge if below threshold
- Quick link to balance management

### 4. Sidebar Navigation

**Update**: `resources/js/components/AppSidebar.vue`

- Add "Cash Balance" menu item with DollarSign icon
- Show only if user has `view-transactions` permission
- Position after Transactions menu item

### 5. Permissions & Authorization

**Update**: `database/seeders/RolesAndPermissionsSeeder.php`

- Reuse existing `view-transactions` and `manage-transactions` permissions
- No new permissions needed (balance management follows transaction permissions)

**Roles Access**:

- **Admin**: Full access to all features
- **Accountant**: View and reconcile balances
- **Cashier**: View balance, set opening balance, reconcile
- **Requester**: View current balance only (read-only)

### 6. Dashboard Integration

**Update**: `routes/web.php` dashboard route

```php
// Add to stats array
$stats['currentBalance'] = app(\App\Services\BalanceService::class)->getCurrentBalance();
$stats['lowBalanceAlert'] = app(\App\Services\BalanceService::class)->needsLowBalanceAlert($stats['currentBalance']);
```

**Update**: `resources/js/pages/Dashboard.vue`

- Add balance card to grid (after transaction stats)
- Show current balance prominently
- Display low balance warning badge if alert is active
- Link to balance management page

### 7. Configuration

**Add to**: `.env.example` and `.env`

```
LOW_BALANCE_THRESHOLD=1000.00
```

**Create**: `config/cash.php` (optional)

```php
return [
    'low_balance_threshold' => env('LOW_BALANCE_THRESHOLD', 1000.00),
];
```

### 8. Testing

**Feature Tests**:

- `tests/Feature/CashBalanceTest.php` - CRUD operations, authorization
- `tests/Feature/BalanceCalculationTest.php` - calculation logic accuracy
- `tests/Feature/BalanceReconciliationTest.php` - reconciliation workflow

**Test Coverage**:

- Opening balance creation and validation
- Balance calculation from transactions (only approved)
- Reconciliation with/without discrepancies
- Period-based balance queries
- Low balance alerts
- Authorization for different roles
- Balance history retrieval

**Factories**: `database/factories/CashBalanceFactory.php`

### 9. Key Implementation Details

**Balance Calculation Logic**:

- Query approved transactions within period
- Sum `amount` where `type = 'in'` (cash inflows)
- Sum `amount` where `type = 'out'` (cash outflows)
- Add opening balance to net flow
- Cache daily balances for performance (optional)

**Reconciliation Flow**:

1. Display system calculated balance
2. User enters actual cash on hand
3. Calculate discrepancy (actual - system)
4. If discrepancy > threshold, require notes
5. Save reconciliation record with timestamp
6. Optionally create adjustment transaction

**Low Balance Alert**:

- Check threshold on dashboard load
- Show warning badge on dashboard
- Consider adding to notification center (future sprint)

## Files to Create/Modify

**New Files** (~10):

- Migration for cash_balances table
- CashBalance model
- BalanceService
- 2 Form Requests
- CashBalanceController
- 4 Vue pages (Index, Create, Show, Reconcile)
- CashBalanceFactory
- 3 test files

**Modified Files** (~5):

- `routes/web.php` (routes + dashboard stats)
- `resources/js/pages/Dashboard.vue` (balance widget)
- `resources/js/components/AppSidebar.vue` (menu item)
- `app/Http/Middleware/HandleInertiaRequests.php` (optional: share balance)
- `.env.example` (config)

## Acceptance Criteria Checklist

- [ ] Balance updates automatically when approved transactions created
- [ ] Opening balance can be set per period
- [ ] Reconciliation form calculates discrepancies correctly
- [ ] Balance history shows accurate period snapshots
- [ ] Low balance alert displays on dashboard when threshold crossed
- [ ] Only approved transactions affect balance calculation
- [ ] Role-based access works correctly
- [ ] All tests pass (target: 15+ new tests)
- [ ] Dashboard shows current balance prominently
- [ ] Wayfinder routes generated for new controllers

## Dependencies & Risks

**Dependencies**:

- Existing Transaction model and data
- BudgetService pattern (for consistency)
- Spatie Permissions (already configured)

**Risks & Mitigations**:

- **Performance**: Large transaction volumes - Use indexes, date range queries, optional caching
- **Timezone issues**: Balance calculations across midnight - Use Carbon with app timezone
- **Concurrent reconciliation**: Multiple users - Add row locking in reconciliation
- **Historical data**: Changing past transactions - Document that only approved transactions count

## Post-Implementation Tasks

1. Run `yarn dev` to generate Wayfinder routes
2. Run `vendor/bin/pint` to format PHP code
3. Run `php artisan test --filter=CashBalance` to verify tests
4. Run full test suite: `php artisan test`
5. Seed sample opening balances for testing
6. Update documentation with balance management guide

### To-dos

- [ ] Create cash_balances migration, model, and factory
- [ ] Create BalanceService with calculation methods
- [ ] Create validation requests for balance and reconciliation
- [ ] Create CashBalanceController and register routes
- [ ] Create Vue pages for Index, Create, Show, Reconcile
- [ ] Add balance widget and stats to dashboard
- [ ] Add Cash Balance menu item to sidebar
- [ ] Write feature tests for balance management and calculations
- [ ] Add low balance threshold configuration
- [ ] Run tests, Pint formatter, and verify Wayfinder routes