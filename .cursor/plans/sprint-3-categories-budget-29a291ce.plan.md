<!-- 29a291ce-b9b5-4295-9ac0-6c5631b158de 3c36c330-30fa-4bd9-9c33-19c2555e08be -->
# Sprint 3: Categories and Budget Management

## Database Schema

### Categories Table

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('color', 7)->default('#6366f1');  // Hex color for UI
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});
```

### Budgets Table

```php
Schema::create('budgets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->decimal('amount', 15, 2);
    $table->date('start_date');
    $table->date('end_date');
    $table->decimal('alert_threshold', 5, 2)->default(80.00);  // Percentage
    $table->timestamps();
    
    $table->unique(['category_id', 'start_date', 'end_date']);
});
```

### Update Transactions Table

Add foreign key constraint to existing `category_id` column:

```php
$table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
```

---

## Backend Implementation

### Models

- `Category` model with relationships: `hasMany(Transaction)`, `hasMany(Budget)`
- `Budget` model with: `belongsTo(Category)`, scopes for active periods, spent amount accessor
- Update `Transaction` model: add `belongsTo(Category)` relationship

### Services

- `BudgetService` - Calculate spent amounts, check budget limits, generate alerts

### Controllers

- `CategoryController` - Full CRUD (Admin only)
- `BudgetController` - CRUD for budget allocations (Admin only)
- Update `TransactionController` - Pass categories to create/edit pages

### Form Requests

- `StoreCategoryRequest`, `UpdateCategoryRequest`
- `StoreBudgetRequest`, `UpdateBudgetRequest` (with date overlap validation)
- Update transaction requests to validate category_id

### Permissions (update seeder)

- `manage-categories`, `view-categories`
- `manage-budgets`, `view-budgets`

---

## Frontend Implementation

### Pages Structure

```
resources/js/pages/
├── Categories/
│   ├── Index.vue      # List with search, status filter
│   ├── Create.vue     # Name, description, color picker
│   ├── Edit.vue
│   └── Show.vue       # Category details with transaction history
├── Budgets/
│   ├── Index.vue      # Budget list with progress bars
│   ├── Create.vue     # Category select, amount, date range
│   ├── Edit.vue
│   └── Overview.vue   # Budget vs actual comparison view
```

### Key UI Components

- Category color picker (simple hex input or preset colors)
- Budget progress bar component (shows spent/total with color states)
- Budget alert banner (warning/danger states)

### Transaction Form Updates

- Add category select dropdown to `Create.vue` and `Edit.vue`
- Show category in `Index.vue` and `Show.vue`

### Dashboard Integration

- Add budget alerts section showing categories approaching/exceeding limits
- Category-wise spending summary widget

---

## Routes

```php
// Categories (Admin only)
Route::resource('categories', CategoryController::class);

// Budgets (Admin only)  
Route::resource('budgets', BudgetController::class);
Route::get('budgets/overview', [BudgetController::class, 'overview'])->name('budgets.overview');
```

---

## Testing Requirements

- `CategoryCRUDTest` - Create, read, update, delete, soft delete
- `CategoryAuthorizationTest` - Permission checks
- `BudgetCRUDTest` - CRUD operations, date validation
- `BudgetCalculationTest` - Spent amount calculations, threshold alerts
- `TransactionCategoryTest` - Category assignment, filtering by category

---

## Key Files to Modify

| File | Changes |

|------|---------|

| `database/migrations/*_create_categories_table.php` | New migration |

| `database/migrations/*_create_budgets_table.php` | New migration |

| `database/migrations/*_add_category_fk_to_transactions.php` | Add FK constraint |

| `app/Models/Category.php` | New model |

| `app/Models/Budget.php` | New model |

| `app/Models/Transaction.php` | Add category relationship |

| `app/Services/BudgetService.php` | Budget calculations |

| `database/seeders/RolesAndPermissionsSeeder.php` | Add category/budget permissions |

| `resources/js/pages/Transactions/Create.vue` | Add category dropdown |

| `resources/js/pages/Dashboard.vue` | Add budget alerts widget |

### To-dos

- [ ] Create categories & budgets migrations + add FK to transactions
- [ ] Create Category & Budget models with relationships and scopes
- [ ] Create BudgetService for calculations and alert logic
- [ ] Update RolesAndPermissionsSeeder with category/budget permissions
- [ ] Create CategoryController, FormRequests, and routes
- [ ] Create BudgetController, FormRequests, and routes
- [ ] Build Category CRUD pages (Index, Create, Edit, Show)
- [ ] Build Budget CRUD pages + Overview with progress bars
- [ ] Add category dropdown to transaction forms
- [ ] Add budget alerts widget to Dashboard
- [ ] Write PHPUnit tests for categories, budgets, and calculations