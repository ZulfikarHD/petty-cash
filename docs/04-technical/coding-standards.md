# Coding Standards

This document outlines the coding standards and best practices for the Petty Cash Book application.

## PHP Code Style

We follow **PSR-12** coding standard with Laravel-specific conventions. All PHP code must pass **Laravel Pint** formatting.

### Running Laravel Pint

```bash
# Fix all files
vendor/bin/pint

# Fix specific directory
vendor/bin/pint app/Http/Controllers

# Check without fixing (dry run)
vendor/bin/pint --test

# Fix only modified files
vendor/bin/pint --dirty
```

## Naming Conventions

### PHP Classes

**Classes**: PascalCase
```php
class TransactionController extends Controller
class StoreTransactionRequest extends FormRequest
```

**Methods**: camelCase
```php
public function createTransaction()
public function getBalance()
```

**Variables**: camelCase
```php
$transactionAmount = 100;
$userId = auth()->id();
$isApproved = true;
```

**Constants**: UPPER_SNAKE_CASE
```php
const MAX_UPLOAD_SIZE = 5242880;
const TRANSACTION_STATUS_PENDING = 'pending';
```

### Database

**Tables**: plural, snake_case
```
transactions
user_roles
cash_balances
```

**Columns**: snake_case
```
transaction_date
created_at
user_id
approved_by
```

**Pivot Tables**: singular, alphabetical order
```
model_has_roles
role_has_permissions
```

**Foreign Keys**: `{singular_table}_id`
```
user_id
category_id
approved_by (if referencing another table's id)
```

### JavaScript/Vue

**Files**: PascalCase for components, camelCase for utilities
```
TransactionList.vue
CreateTransaction.vue
formatCurrency.ts
```

**Component Names**: PascalCase
```vue
<TransactionCard />
<UserMenu />
```

**Props/Variables**: camelCase
```javascript
const transactionId = 123;
const props = defineProps({ userId: Number });
```

**Events**: kebab-case
```vue
@transaction-created="handleCreate"
@user-updated="handleUpdate"
```

## Laravel Best Practices

### Controllers

Keep controllers thin - delegate business logic to services or actions.

```php
// ✅ Good
public function store(StoreTransactionRequest $request)
{
    $transaction = Transaction::create($request->validated());
    
    if ($request->hasFile('receipts')) {
        $this->attachReceipts($transaction, $request->file('receipts'));
    }
    
    return redirect()->route('transactions.index')
        ->with('success', 'Transaction created successfully');
}

// ❌ Bad - too much logic in controller
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'amount' => 'required|numeric',
        // ... more validation
    ]);
    
    if ($validator->fails()) {
        return back()->withErrors($validator);
    }
    
    // ... complex business logic here
    // ... file handling
    // ... calculations
}
```

### Use Form Requests

Always use Form Request classes for validation and authorization.

```php
// ✅ Good - Form Request
class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-transactions');
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string', 'max:1000'],
            'transaction_date' => ['required', 'date'],
            'type' => ['required', 'in:in,out'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Please enter the transaction amount.',
            'amount.min' => 'Amount must be greater than zero.',
        ];
    }
}
```

### Models

Define relationships, casts, and accessors properly.

```php
class Transaction extends Model
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    // Always define fillable or guarded
    protected $fillable = [
        'transaction_number',
        'type',
        'amount',
        'description',
    ];

    // Define casts for proper type handling
    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Explicit return types on relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Use scopes for common queries
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    public function scopeCashIn(Builder $query): void
    {
        $query->where('type', 'in');
    }
}
```

### Eager Loading (Prevent N+1)

Always use eager loading to prevent N+1 query problems.

```php
// ✅ Good - Eager loading
$transactions = Transaction::with(['user', 'approver'])->get();

// ❌ Bad - N+1 problem
$transactions = Transaction::all();
foreach ($transactions as $transaction) {
    echo $transaction->user->name; // Triggers query for each transaction
}
```

### Query Builder Best Practices

```php
// ✅ Good - Use when() for conditional queries
$transactions = Transaction::query()
    ->when($request->type, fn($q, $type) => $q->where('type', $type))
    ->when($request->status, fn($q, $status) => $q->where('status', $status))
    ->paginate();

// ❌ Bad - Multiple if statements
$query = Transaction::query();
if ($request->type) {
    $query->where('type', $request->type);
}
if ($request->status) {
    $query->where('status', $request->status);
}
$transactions = $query->paginate();
```

### Use Services for Complex Logic

For complex business logic, create service classes.

```php
// app/Services/TransactionService.php
class TransactionService
{
    public function create(array $data): Transaction
    {
        DB::beginTransaction();
        
        try {
            $transaction = Transaction::create($data);
            $this->updateBalance($transaction);
            
            DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function updateBalance(Transaction $transaction): void
    {
        // Balance calculation logic
    }
}
```

## Vue 3 / TypeScript Best Practices

### Component Structure

```vue
<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

// Props
interface Props {
    transaction: Transaction;
    canEdit?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    canEdit: false
});

// Emits
const emit = defineEmits<{
    updated: [transaction: Transaction];
    deleted: [id: number];
}>();

// Refs
const isEditing = ref(false);

// Computed
const formattedAmount = computed(() => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(props.transaction.amount);
});

// Methods
const handleEdit = () => {
    isEditing.value = true;
};
</script>

<template>
    <div class="transaction-card">
        <h3>{{ transaction.description }}</h3>
        <p>{{ formattedAmount }}</p>
        <Button v-if="canEdit" @click="handleEdit">
            Edit
        </Button>
    </div>
</template>
```

### Inertia Forms

```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { store } from '@/actions/App/Http/Controllers/TransactionController';

const form = useForm({
    amount: '',
    description: '',
    type: 'out' as 'in' | 'out',
    transaction_date: new Date().toISOString().split('T')[0],
});

const submit = () => {
    form.post(store().url, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <form @submit.prevent="submit">
        <Input v-model="form.amount" :error="form.errors.amount" />
        <Button type="submit" :disabled="form.processing">
            Submit
        </Button>
    </form>
</template>
```

### Wayfinder Usage

Always use Wayfinder for type-safe routing.

```vue
<script setup lang="ts">
import { index, show, create } from '@/actions/App/Http/Controllers/TransactionController';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <!-- ✅ Good - Wayfinder -->
    <Link :href="show(transaction.id).url">
        View Transaction
    </Link>

    <!-- ❌ Bad - Hardcoded URL -->
    <Link href="/transactions/1">
        View Transaction
    </Link>
</template>
```

## Tailwind CSS Conventions

### Spacing

Use gap utilities instead of margins for spacing items.

```vue
<!-- ✅ Good -->
<div class="flex gap-4">
    <div>Item 1</div>
    <div>Item 2</div>
</div>

<!-- ❌ Bad -->
<div class="flex">
    <div class="mr-4">Item 1</div>
    <div>Item 2</div>
</div>
```

### Component Classes

Group utility classes logically.

```vue
<!-- ✅ Good - Logical grouping -->
<button 
    class="
        px-4 py-2
        bg-blue-600 hover:bg-blue-700
        text-white font-medium
        rounded-lg
        transition-colors
        disabled:opacity-50
    "
>
    Submit
</button>
```

### Dark Mode Support

Use `dark:` variants for dark mode.

```vue
<div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
    Content
</div>
```

## Git Commit Messages

Follow the [Conventional Commits](https://www.conventionalcommits.org/) specification.

### Format

```
type(scope): subject

body (optional)

footer (optional)
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks
- `perf`: Performance improvements

### Examples

```
feat(transactions): add receipt upload functionality

Implemented file upload for transaction receipts using Spatie Media Library.
Users can now attach up to 5 receipt images per transaction.

Closes #123

---

fix(auth): resolve login redirect loop

Fixed issue where users were stuck in redirect loop after login
when coming from a protected route.

Fixes #456

---

docs(setup): update installation instructions

Added steps for Yarn installation and Docker setup.

---

refactor(transactions): extract service layer

Moved business logic from controller to TransactionService
for better testability and maintainability.
```

## Code Review Checklist

Before submitting a PR, ensure:

### General
- [ ] Code follows PSR-12 standards
- [ ] Laravel Pint has been run
- [ ] ESLint shows no errors
- [ ] No commented-out code
- [ ] No `dd()`, `dump()`, or `console.log()` left in code

### Security
- [ ] User input is validated
- [ ] Authorization checks are in place
- [ ] No sensitive data in logs
- [ ] SQL injection is prevented (use query builder/Eloquent)
- [ ] XSS is prevented (use `{{ }}` in Blade, proper escaping in Vue)

### Performance
- [ ] N+1 queries are avoided (eager loading)
- [ ] Database queries are optimized
- [ ] Appropriate indexes exist
- [ ] Pagination is used for large datasets

### Testing
- [ ] New features have tests
- [ ] All tests pass
- [ ] Edge cases are covered
- [ ] Test names are descriptive

### Documentation
- [ ] Code is self-documenting
- [ ] Complex logic has comments
- [ ] PHPDoc blocks are added where needed
- [ ] README is updated if needed

## Resources

- [PSR-12 Extended Coding Style](https://www.php-fig.org/psr/psr-12/)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [Vue 3 Style Guide](https://vuejs.org/style-guide/)
- [Tailwind CSS Best Practices](https://tailwindcss.com/docs/reusing-styles)
- [Conventional Commits](https://www.conventionalcommits.org/)

---

**Last Updated**: November 24, 2024  
**Version**: 1.0.0

