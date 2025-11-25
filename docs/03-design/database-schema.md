# Database Schema

## Overview

The Petty Cash Book application uses **MySQL/MariaDB** as its database engine with the following characteristics:

- **Character Set**: utf8mb4
- **Collation**: utf8mb4_unicode_ci
- **Engine**: InnoDB
- **Foreign Keys**: Enabled with cascading deletes
- **Soft Deletes**: Enabled on key tables

## Entity Relationship Diagram

```
┌──────────────┐         ┌──────────────────┐         ┌──────────────────┐
│    roles     │         │      users       │         │   transactions   │
├──────────────┤         ├──────────────────┤         ├──────────────────┤
│ id           │◄────────┤ id               │◄────────┤ id               │
│ name         │         │ name             │         │ trans_number     │
│ guard_name   │         │ email            │         │ type             │
│ created_at   │         │ password         │         │ amount           │
│ updated_at   │         │ email_verified   │         │ description      │
└──────────────┘         │ remember_token   │         │ trans_date       │
                         │ created_at       │         │ category_id (FK) │──┐
                         │ updated_at       │         │ vendor_id        │  │
                         └──────────────────┘         │ user_id (FK)     │  │
                                  │                   │ status           │  │
                                  │                   │ approved_by      │  │
                                  │                   │ approved_at      │  │
                                  │                   │ rejection_reason │  │
                                  │                   │ notes            │  │
                                  │                   │ created_at       │  │
                                  │                   │ updated_at       │  │
                                  │                   │ deleted_at       │  │
                                  │                   └──────────────────┘  │
                                  │                          │              │
                                  └──────────────────────────┘              │
                                        (approver)                          │
                                                                            │
┌───────────────┐                                                          │
│   categories  │◄─────────────────────────────────────────────────────────┘
├───────────────┤
│ id            │
│ name          │
│ slug          │◄──────┐
│ description   │       │
│ color         │       │
│ is_active     │       │
│ created_at    │       │
│ updated_at    │       │
│ deleted_at    │       │
└───────────────┘       │
       │                │
       │                │
       │                │
       ▼                │
┌───────────────┐       │
│    budgets    │       │
├───────────────┤       │
│ id            │       │
│ category_id   │───────┘ (FK)
│ amount        │
│ start_date    │
│ end_date      │
│ alert_thres   │
│ created_at    │
│ updated_at    │
└───────────────┘


┌───────────────┐
│     media     │
├───────────────┤
│ id            │
│ model_type    │◄─── Polymorphic relationship with transactions
│ model_id      │
│ uuid          │
│ collection    │
│ file_name     │
│ mime_type     │
│ disk          │
│ size          │
│ created_at    │
│ updated_at    │
└───────────────┘
```

## Tables

### users

Stores user account information with authentication details.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint | NO | AUTO | Primary key |
| name | varchar(255) | NO | - | User full name |
| email | varchar(255) | NO | - | Unique email address |
| email_verified_at | timestamp | YES | NULL | Email verification timestamp |
| password | varchar(255) | NO | - | Hashed password (bcrypt) |
| two_factor_secret | text | YES | NULL | 2FA secret key |
| two_factor_recovery_codes | text | YES | NULL | 2FA recovery codes |
| two_factor_confirmed_at | timestamp | YES | NULL | 2FA confirmation timestamp |
| remember_token | varchar(100) | YES | NULL | Remember me token |
| created_at | timestamp | YES | NULL | Record creation timestamp |
| updated_at | timestamp | YES | NULL | Last update timestamp |

**Indexes:**
- `PRIMARY KEY (id)`
- `UNIQUE (email)`

**Relationships:**
- `belongsToMany: Role` (via model_has_roles)
- `belongsToMany: Permission` (via model_has_permissions)
- `hasMany: Transaction` (as creator)
- `hasMany: Transaction` (as approver, via approved_by)

**Business Rules:**
- Email must be unique
- Password minimum 8 characters
- Email verification required before full access
- Soft deletes not enabled (user deletion is permanent)

---

### transactions

Stores all petty cash transactions (cash in and cash out).

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint | NO | AUTO | Primary key |
| transaction_number | varchar(50) | NO | - | Unique ID (TXN-YYYY-00001) |
| type | enum('in','out') | NO | - | Transaction type |
| amount | decimal(15,2) | NO | - | Transaction amount |
| description | text | NO | - | Transaction details |
| transaction_date | date | NO | - | Date of transaction |
| category_id | bigint | YES | NULL | FK to categories |
| vendor_id | bigint | YES | NULL | FK to vendors (future) |
| user_id | bigint | NO | - | FK to users (creator) |
| status | enum('pending','approved','rejected') | NO | 'pending' | Transaction status |
| approved_by | bigint | YES | NULL | FK to users (approver) |
| approved_at | timestamp | YES | NULL | Approval timestamp |
| rejection_reason | text | YES | NULL | Reason for rejection |
| notes | text | YES | NULL | Additional notes |
| created_at | timestamp | YES | NULL | Record creation |
| updated_at | timestamp | YES | NULL | Last update |
| deleted_at | timestamp | YES | NULL | Soft delete timestamp |

**Indexes:**
- `PRIMARY KEY (id)`
- `UNIQUE (transaction_number)`
- `INDEX (user_id)`
- `INDEX (type)`
- `INDEX (status)`
- `INDEX (transaction_date)`
- `INDEX (approved_by)`

**Foreign Keys:**
- `user_id` → `users(id)` ON DELETE CASCADE
- `approved_by` → `users(id)` ON DELETE SET NULL
- `category_id` → `categories(id)` ON DELETE SET NULL

**Relationships:**
- `belongsTo: User` (as creator)
- `belongsTo: User` (as approver, via approved_by)
- `morphMany: Media` (receipts)
- `belongsTo: Category`
- `belongsTo: Vendor` (future)

**Business Rules:**
- Transaction number auto-generated on create
- Amount must be positive
- `approved_by` can only be set when status is 'approved'
- Soft deletes enabled for audit trail
- Only pending transactions can be edited
- Only users with approve-transactions permission can approve

**Scopes:**
- `pending()` - Where status = 'pending'
- `approved()` - Where status = 'approved'
- `rejected()` - Where status = 'rejected'
- `cashIn()` - Where type = 'in'
- `cashOut()` - Where type = 'out'
- `dateRange($start, $end)` - Within date range

---

### media

Stores file attachments (receipts, documents) using Spatie Media Library.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint | NO | AUTO | Primary key |
| model_type | varchar(255) | NO | - | Polymorphic model class |
| model_id | bigint | NO | - | Polymorphic model ID |
| uuid | char(36) | NO | - | Unique identifier |
| collection_name | varchar(255) | NO | - | Media collection |
| name | varchar(255) | NO | - | Original name |
| file_name | varchar(255) | NO | - | Stored filename |
| mime_type | varchar(255) | YES | NULL | MIME type |
| disk | varchar(255) | NO | - | Storage disk |
| conversions_disk | varchar(255) | YES | NULL | Conversions disk |
| size | bigint | NO | - | File size in bytes |
| manipulations | longtext | NO | - | JSON manipulations |
| custom_properties | longtext | NO | - | JSON properties |
| generated_conversions | longtext | NO | - | JSON conversions |
| responsive_images | longtext | NO | - | JSON responsive data |
| order_column | int | YES | NULL | Sort order |
| created_at | timestamp | YES | NULL | Upload timestamp |
| updated_at | timestamp | YES | NULL | Last update |

**Indexes:**
- `PRIMARY KEY (id)`
- `UNIQUE (uuid)`
- `INDEX (model_type, model_id)`
- `INDEX (order_column)`

**Relationships:**
- `morphTo: Model` (belongs to any model via polymorphic relation)

**Business Rules:**
- For transactions collection 'receipts':
  - Accepts: image/jpeg, image/png, image/gif, application/pdf
  - Max size: 5MB per file
  - Stored on 'public' disk
- Media is automatically deleted when parent model is deleted

---

### roles

Stores role definitions for role-based access control (Spatie Permission).

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint | NO | AUTO | Primary key |
| name | varchar(255) | NO | - | Role name |
| guard_name | varchar(255) | NO | - | Guard name (web) |
| created_at | timestamp | YES | NULL | Creation timestamp |
| updated_at | timestamp | YES | NULL | Last update |

**Indexes:**
- `PRIMARY KEY (id)`
- `UNIQUE (name, guard_name)`

**Relationships:**
- `belongsToMany: Permission` (via role_has_permissions)
- `belongsToMany: User` (via model_has_roles)

**Default Roles:**
1. **Admin** - Full system access
2. **Accountant** - Approve transactions, view reports
3. **Cashier** - Create/edit transactions, record cash
4. **Requester** - Submit transaction requests

---

### permissions

Stores permission definitions (Spatie Permission).

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint | NO | AUTO | Primary key |
| name | varchar(255) | NO | - | Permission name |
| guard_name | varchar(255) | NO | - | Guard name (web) |
| created_at | timestamp | YES | NULL | Creation timestamp |
| updated_at | timestamp | YES | NULL | Last update |

**Indexes:**
- `PRIMARY KEY (id)`
- `UNIQUE (name, guard_name)`

**Default Permissions:**
- `view-users`, `create-users`, `edit-users`, `delete-users`
- `view-transactions`, `create-transactions`, `edit-transactions`, `delete-transactions`
- `approve-transactions`
- `view-categories`, `manage-categories`
- `view-budgets`, `manage-budgets`

---

### model_has_roles

Pivot table linking users to roles (Spatie Permission).

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| role_id | bigint | NO | FK to roles |
| model_type | varchar(255) | NO | Model class name |
| model_id | bigint | NO | Model ID (user id) |

**Indexes:**
- `PRIMARY KEY (role_id, model_id, model_type)`
- `INDEX (model_id, model_type)`

**Foreign Keys:**
- `role_id` → `roles(id)` ON DELETE CASCADE

---

### model_has_permissions

Pivot table linking users to direct permissions (Spatie Permission).

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| permission_id | bigint | NO | FK to permissions |
| model_type | varchar(255) | NO | Model class name |
| model_id | bigint | NO | Model ID (user id) |

**Indexes:**
- `PRIMARY KEY (permission_id, model_id, model_type)`
- `INDEX (model_id, model_type)`

**Foreign Keys:**
- `permission_id` → `permissions(id)` ON DELETE CASCADE

---

### role_has_permissions

Pivot table linking roles to permissions (Spatie Permission).

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| permission_id | bigint | NO | FK to permissions |
| role_id | bigint | NO | FK to roles |

**Indexes:**
- `PRIMARY KEY (permission_id, role_id)`
- `INDEX (role_id)`

**Foreign Keys:**
- `permission_id` → `permissions(id)` ON DELETE CASCADE
- `role_id` → `roles(id)` ON DELETE CASCADE

---

---

### categories

Stores expense categories for transaction classification.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint | NO | AUTO | Primary key |
| name | varchar(255) | NO | - | Category name |
| slug | varchar(255) | NO | - | URL-friendly slug (auto-generated) |
| description | text | YES | NULL | Category description |
| color | varchar(7) | NO | '#6366f1' | Hex color code for UI |
| is_active | boolean | NO | TRUE | Active status |
| created_at | timestamp | YES | NULL | Record creation |
| updated_at | timestamp | YES | NULL | Last update |
| deleted_at | timestamp | YES | NULL | Soft delete timestamp |

**Indexes:**
- `PRIMARY KEY (id)`
- `UNIQUE (slug)`
- `INDEX (is_active)`

**Relationships:**
- `hasMany: Transaction`
- `hasMany: Budget`

**Business Rules:**
- Category name must be unique
- Slug is auto-generated from name using Sluggable trait
- Color must be valid hex code (#RRGGBB)
- Soft deletes enabled for data integrity
- Deactivated categories still visible in historical data
- Deleting category sets transaction category_id to NULL

**Scopes:**
- `active()` - Where is_active = true

---

### budgets

Stores budget allocations per category with date ranges.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint | NO | AUTO | Primary key |
| category_id | bigint | NO | - | FK to categories |
| amount | decimal(15,2) | NO | - | Budget amount limit |
| start_date | date | NO | - | Budget period start |
| end_date | date | NO | - | Budget period end |
| alert_threshold | decimal(5,2) | NO | 80.00 | Alert percentage (e.g., 80%) |
| created_at | timestamp | YES | NULL | Record creation |
| updated_at | timestamp | YES | NULL | Last update |

**Indexes:**
- `PRIMARY KEY (id)`
- `INDEX (category_id)`
- `UNIQUE (category_id, start_date, end_date)`

**Foreign Keys:**
- `category_id` → `categories(id)` ON DELETE CASCADE

**Relationships:**
- `belongsTo: Category`

**Business Rules:**
- One budget per category per time period
- Date ranges cannot overlap for same category
- Amount must be positive
- Alert threshold is percentage (0-100)
- Spent amount calculated from approved 'out' transactions
- Budget status: 
  - Green: < alert_threshold %
  - Yellow: ≥ alert_threshold % and < 100%
  - Red: ≥ 100%

**Accessors:**
- `spent_amount` - Calculated sum of approved 'out' transactions in period
- `percentage_used` - (spent / amount) * 100
- `remaining_amount` - amount - spent_amount

**Scopes:**
- `activePeriods()` - Where end_date ≥ today
- `forCategory($categoryId)` - Where category_id = $categoryId
- `inDateRange($start, $end)` - Overlapping date ranges

---

## Future Tables (Planned)

### vendors
Vendor/supplier information (Sprint 8).

### cash_balances
Cash balance tracking and reconciliation (Sprint 4).

### approvals
Multi-level approval workflow records (Sprint 5).

### reimbursements
Staff reimbursement requests (Sprint 7).

### audit_logs
Activity logging and audit trail (Sprint 9).

## Database Maintenance

### Backup Strategy

```bash
# Daily backup
php artisan backup:run

# Manual backup
mysqldump -u root -p petty_cash > backup_$(date +%Y%m%d).sql
```

### Optimization

```bash
# Optimize tables
php artisan optimize:clear

# Analyze slow queries
SHOW FULL PROCESSLIST;
EXPLAIN SELECT ...;
```

### Migrations

```bash
# Check migration status
php artisan migrate:status

# Rollback last batch
php artisan migrate:rollback

# Fresh install (⚠️ destroys data)
php artisan migrate:fresh --seed
```

## Performance Considerations

### Indexes
- All foreign keys have indexes
- Frequently queried columns (type, status, date) have indexes
- Transaction number has unique index for fast lookups

### Query Optimization
- Use eager loading to prevent N+1 queries:
  ```php
  Transaction::with(['user', 'approver', 'media'])->get();
  ```
- Use pagination for large datasets
- Implement caching for frequently accessed data

### Storage
- Media files stored separately from database
- Use CDN for static assets in production
- Implement storage cleanup for deleted transactions

---

**Database Version**: MySQL 8.0 / MariaDB 10.x  
**Last Updated**: November 25, 2024  
**Schema Version**: 1.1.0 (Sprint 3)

