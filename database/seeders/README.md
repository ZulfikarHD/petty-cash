# Database Seeders Guide

This directory contains seeders for populating your Petty Cash Book application with data.

## Available Seeders

### 1. RolesAndPermissionsSeeder
**Purpose**: Creates roles (Admin, Accountant, Cashier, Requester) and permissions for the application.

**Usage**:
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

**What it creates**:
- 4 roles with their respective permissions
- Permission assignments for each role

---

### 2. UserSeeder
**Purpose**: Creates test users for each role.

**Usage**:
```bash
php artisan db:seed --class=UserSeeder
```

**Default Accounts Created**:
- **Admin**: admin@pettycash.com / password
- **Accountant**: accountant@pettycash.com / password
- **Cashier**: cashier@pettycash.com / password
- **Requester**: requester@pettycash.com / password

---

### 3. CashBalanceSeeder
**Purpose**: Creates realistic cash balance periods with historical and current data.

**Usage**:
```bash
php artisan db:seed --class=CashBalanceSeeder
```

**What it creates**:
- 3 reconciled balance periods (historical - past 3 months)
- 1 active balance period (current month)
- 1 upcoming balance period (next month)
- Realistic transactions for each period

**Requirements**: Run UserSeeder first

---

### 4. DemoDataSeeder ⭐ (Recommended)
**Purpose**: Creates a complete demo dataset with categories, budgets, transactions, and cash balances.

**Usage**:
```bash
php artisan db:seed --class=DemoDataSeeder
```

**What it creates**:
- 8 expense categories (Office Supplies, Utilities, Transportation, etc.)
- Budgets for current and next month for each category
- 3 cash balance periods (past, current, upcoming)
- 30-50 realistic transactions per period
- Mix of approved and pending transactions

**Requirements**: Run RolesAndPermissionsSeeder and UserSeeder first

---

## Quick Start Guide

### Option 1: Fresh Install with Demo Data (Recommended for Development)

```bash
# Reset database and seed everything
php artisan migrate:fresh --seed

# Uncomment DemoDataSeeder in DatabaseSeeder.php first, then:
php artisan db:seed --class=DemoDataSeeder
```

### Option 2: Fresh Install with Basic Data Only

```bash
# This runs RolesAndPermissionsSeeder and UserSeeder only
php artisan migrate:fresh --seed
```

### Option 3: Add Demo Data to Existing Database

```bash
# Run individual seeders in order
php artisan db:seed --class=DemoDataSeeder
```

### Option 4: Add Only Cash Balance Data

```bash
# Requires existing users and categories
php artisan db:seed --class=CashBalanceSeeder
```

---

## Seeding Order (Important!)

Always seed in this order:

1. ✅ RolesAndPermissionsSeeder (required first)
2. ✅ UserSeeder (required second)
3. ✅ DemoDataSeeder OR CashBalanceSeeder (optional)

---

## Default Login Credentials

After running the seeders, you can login with:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@pettycash.com | password |
| Accountant | accountant@pettycash.com | password |
| Cashier | cashier@pettycash.com | password |
| Requester | requester@pettycash.com | password |

---

## Demo Data Details

### Categories Created
1. **Office Supplies** (Blue) - Stationery and general supplies
2. **Utilities** (Yellow) - Bills and services
3. **Transportation** (Purple) - Travel and fuel
4. **Meals & Entertainment** (Orange) - Food and events
5. **Maintenance** (Cyan) - Repairs and cleaning
6. **Training & Development** (Green) - Employee training
7. **IT & Equipment** (Indigo) - Technology expenses
8. **Marketing** (Pink) - Promotional materials

### Cash Balance Periods
- **Previous Month**: Reconciled with closing balance
- **Current Month**: Active, ready for transactions
- **Next Month**: Upcoming period

### Transactions
- **Cash In**: 3-5 per period (reimbursements, top-ups)
- **Cash Out**: 10-20 per period (expenses by category)
- **Mix of**: Approved and pending status (for current month)

---

## Customizing Seeders

### Modify Transaction Amounts

Edit the `rand()` values in the seeder:

```php
// In DemoDataSeeder.php
'amount' => rand(50000, 2000000), // Change these values
```

### Modify Number of Transactions

```php
// In DemoDataSeeder.php
for ($i = 0; $i < rand(10, 20); $i++) { // Change these numbers
```

### Add More Categories

Edit the `$categories` array in `DemoDataSeeder.php`:

```php
$categories = [
    ['name' => 'Your Category', 'description' => '...', 'color' => '#hex'],
    // Add more...
];
```

---

## Troubleshooting

### Error: "User not found"
**Solution**: Run UserSeeder first
```bash
php artisan db:seed --class=UserSeeder
```

### Error: "Category not found"
**Solution**: The seeder creates categories automatically, but ensure you're running DemoDataSeeder

### Error: "Duplicate entry"
**Solution**: The seeders use `firstOrCreate()` to prevent duplicates, but if you get this error:
```bash
php artisan migrate:fresh --seed
```

### Error: "Foreign key constraint"
**Solution**: Seed in the correct order (Roles → Users → Demo Data)

---

## Production Warning ⚠️

**DO NOT** run these seeders in production! They are for development and testing only.

Demo data includes:
- Test user accounts with default passwords
- Fake transaction data
- Sample categories and budgets

In production, use proper user registration and data entry workflows.

---

## Tips for Development

1. **Reset frequently**: Use `php artisan migrate:fresh --seed` during development
2. **Custom data**: Modify the seeders to match your testing scenarios
3. **Performance**: Seeders may take 10-30 seconds for large datasets
4. **Faker data**: All descriptions use realistic but fake data

---

## Related Commands

```bash
# Check current seed status
php artisan migrate:status

# Rollback and reseed
php artisan migrate:rollback
php artisan migrate --seed

# Force seed in production (NOT RECOMMENDED)
php artisan db:seed --force

# Seed specific class
php artisan db:seed --class=YourSeederClass
```

---

**Last Updated**: November 25, 2024  
**Version**: 1.0.0  
**Maintained by**: Development Team

