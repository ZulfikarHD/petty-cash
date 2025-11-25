# Changelog

All notable changes to the Petty Cash Book application will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Cash balance tracking and reconciliation
- Approval workflow system
- Advanced reporting and analytics
- Vendor management
- Audit trail and activity logging
- Multi-currency support
- Mobile optimizations

---

## [0.3.0] - 2024-11-25

### Added - Sprint 3: Categories & Budget Management

#### Backend
- **Categories Module**
  - `categories` table with soft deletes
  - `Category` model with Sluggable trait
  - Auto-generated slugs from category names
  - Category CRUD with authorization
  - Color picker support (hex colors)
  - Active/inactive status management
  - CategoryController with full CRUD
  - StoreCategoryRequest and UpdateCategoryRequest validation

- **Budgets Module**
  - `budgets` table with unique period constraints
  - `Budget` model with relationships
  - Budget CRUD with date overlap validation
  - BudgetService for business logic calculations
  - Alert threshold system (percentage-based)
  - Budget vs actual spending calculations
  - Budget period management (start/end dates)
  - BudgetController with full CRUD + overview
  - StoreBudgetRequest and UpdateBudgetRequest validation

- **Transaction Integration**
  - Added foreign key constraint to transactions.category_id
  - Updated Transaction model with category relationship
  - Category filtering in transaction list
  - Optional category assignment to transactions

- **Permissions**
  - `manage-categories` - Admin only
  - `view-categories` - All roles
  - `manage-budgets` - Admin only
  - `view-budgets` - All roles
  - Updated RolesAndPermissionsSeeder

#### Frontend
- **Category Pages**
  - Categories list with search and status filter
  - Category create form with color picker
  - Category edit form
  - Category detail view with transaction history
  - Category badge component with color display

- **Budget Pages**
  - Budget list with progress bars
  - Budget create form with category selector
  - Budget edit form with validation
  - Budget detail view with spending breakdown
  - Budget overview page (comparison view)
  - Progress visualization with color indicators:
    - Green: < 80% (below alert threshold)
    - Yellow: 80-99% (approaching limit)
    - Red: ≥ 100% (exceeded)

- **Dashboard Integration**
  - Budget alerts widget
  - Shows budgets approaching/exceeding limits
  - Alert severity levels (warning/danger)
  - Links to budget details

- **Transaction Forms**
  - Added category select dropdown to Create/Edit
  - Optional category assignment
  - Category display in transaction list
  - Category filter in transaction search

- **Navigation**
  - Added "Categories" to sidebar (Tag icon)
  - Added "Budgets" to sidebar (Wallet icon)
  - Permission-based menu visibility
  - Wayfinder integration for routes

#### Testing
- 38 new tests added (total: 107 tests)
- CategoryCRUDTest - 8 tests
- CategoryAuthorizationTest - 6 tests
- BudgetCRUDTest - 10 tests
- BudgetCalculationTest - 8 tests
- TransactionCategoryTest - 6 tests
- All tests passing with 254 assertions

### Fixed
- SelectItem empty value error in transaction forms
  - Changed from empty string to `null` for "no category"
  - Updated form submission logic to handle nullable category
- CategoryFactory missing default definition
- Budget date format assertion in tests
- Form validation for budget date overlaps
- Foreign key constraint on transactions.category_id

### Changed
- Transaction forms now support optional category selection
- Dashboard displays budget alert information
- HandleInertiaRequests shares category/budget permissions
- AppSidebar dynamically builds menu based on permissions
- Transaction model includes category relationship

### Technical
- Implemented BudgetService for calculation separation
- Added unique constraint on (category_id, start_date, end_date)
- Implemented date overlap validation in StoreBudgetRequest
- Added spent_amount accessor to Budget model
- Optimized queries with eager loading
- Added database indexes for performance

---

## [0.2.0] - 2024-11-24

### Added - Sprint 2: Core Transaction Management

#### Backend
- Transaction CRUD functionality
- `transactions` table with comprehensive fields
- Auto-generated transaction numbers (TXN-YYYY-00001 format)
- Transaction status management (pending, approved, rejected)
- Receipt upload support via Spatie Media Library v11
- Transaction filtering by type, status, and date range
- Search functionality for transaction number and description
- Transaction summary calculations (total in, total out, balance)
- Soft deletes for transactions
- Authorization checks using Spatie Permission
- `TransactionObserver` for auto-number generation
- Form Request validation classes

#### Frontend
- Transaction list page with pagination
- Transaction creation form
- Transaction edit form
- Transaction detail view
- Search and filter interface
- Date range picker
- Summary cards (cash in, cash out, net balance)
- Receipt image upload with preview
- New UI components:
  - Select dropdown component (Reka UI)
  - Textarea component (Reka UI)
  - RadioGroup component (Reka UI)
- Wayfinder integration for type-safe routing
- Dashboard transaction widgets

#### Testing
- 42 new tests for transaction features
- Test coverage for CRUD operations
- Authorization tests
- Filter and search tests
- Media upload tests
- Observer tests
- All 69 tests passing (42 transaction + 27 auth)

### Fixed
- Foreign key constraint issues in transactions table
- Spatie Media Library configuration for max file size
- Missing UI components (Select, Textarea, RadioGroup)
- Inertia.js navigation issues with client-side routing
- SelectItem empty string validation error
- Filter logic for "all" option in type and status filters

### Changed
- Updated TypeScript types to include Transaction interfaces
- Enhanced dashboard with transaction summaries
- Updated navigation to include Transactions menu item
- Improved User model with transactions relationship

### Technical
- Installed and configured Spatie Media Library v11
- Set up media collections for receipt attachments
- Implemented polymorphic relationships for media
- Added database indexes for performance
- Implemented query scopes on Transaction model

---

## [0.1.0] - 2024-11-10

### Added - Sprint 1: Authentication & User Management

#### Backend
- Laravel 12 application initialization
- User authentication with Laravel Fortify
  - Registration
  - Login
  - Logout
  - Password reset
  - Email verification
- Role-based access control with Spatie Permission
  - Roles: Admin, Accountant, Cashier, Requester
  - Permissions: view, create, edit, delete for users and transactions
- User management CRUD interface
- Profile management
- Two-factor authentication support
- Database migrations for users, roles, permissions
- Seeders for default roles and permissions
- Test users seeder

#### Frontend
- Vue 3.5.13 with TypeScript setup
- Inertia.js v2 integration
- Tailwind CSS v4 styling
- Reka UI v2.4.1 components
- Laravel Wayfinder for routing
- Authentication pages:
  - Login
  - Register
  - Forgot Password
  - Reset Password
  - Email Verification
- User management interface
- Profile management page
- Main application layout
- Sidebar navigation

#### Testing
- 27 authentication and authorization tests
- PHPUnit 11 configuration
- Feature tests for:
  - Registration flow
  - Login/logout
  - Password reset
  - Email verification
  - User management
  - Role assignment
  - Permission checks

#### Development Tools
- Laravel Pint for PHP code formatting
- ESLint 9 for JavaScript linting
- Prettier 3 for code formatting
- Laravel Sail (Docker) configuration
- Vite 7 for asset bundling
- TypeScript 5.2.2

### Infrastructure
- Git repository initialization
- Environment configuration (.env.example)
- MySQL/MariaDB database setup
- Redis configuration (optional)
- Mail configuration
- Queue configuration
- File storage configuration

---

## [0.0.1] - 2024-10-27

### Added - Sprint 0: Project Setup

- Project initialization
- Laravel 12 installation
- Git repository setup
- Basic directory structure
- Development environment configuration
- CI/CD pipeline foundation
- Project documentation structure
- Code quality tools setup

---

## Version History Summary

| Version | Date | Sprint | Status | Tests |
|---------|------|--------|--------|-------|
| 0.3.0 | 2024-11-25 | Sprint 3 | ✅ Completed | 107 passing |
| 0.2.0 | 2024-11-24 | Sprint 2 | ✅ Completed | 69 passing |
| 0.1.0 | 2024-11-10 | Sprint 1 | ✅ Completed | 27 passing |
| 0.0.1 | 2024-10-27 | Sprint 0 | ✅ Completed | - |

---

## Migration Guide

### Upgrading from 0.2.0 to 0.3.0

1. **Run new migrations:**
   ```bash
   php artisan migrate
   ```

2. **Update permissions:**
   ```bash
   php artisan db:seed --class=RolesAndPermissionsSeeder
   ```

3. **Generate Wayfinder routes:**
   ```bash
   php artisan wayfinder:generate
   ```

4. **Rebuild frontend assets:**
   ```bash
   yarn install
   yarn build
   ```

5. **Clear caches:**
   ```bash
   php artisan optimize:clear
   ```

### Upgrading from 0.1.0 to 0.2.0

1. **Run new migrations:**
   ```bash
   php artisan migrate
   ```

2. **Install Spatie Media Library:**
   ```bash
   composer require "spatie/laravel-medialibrary:^11.0"
   php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
   php artisan migrate
   ```

3. **Create storage link (if not exists):**
   ```bash
   php artisan storage:link
   ```

4. **Update permissions:**
   ```bash
   php artisan db:seed --class=RolesAndPermissionsSeeder
   ```

5. **Rebuild frontend assets:**
   ```bash
   yarn install
   yarn build
   ```

6. **Clear caches:**
   ```bash
   php artisan optimize:clear
   ```

---

## Breaking Changes

### 0.3.0
- None (additive changes only)
- Transaction category is optional (nullable)

### 0.2.0
- None (additive changes only)

### 0.1.0
- Initial release

---

## Security Updates

### 0.3.0
- Category and budget authorization checks
- Permission-based menu rendering
- Budget calculation validation
- Date overlap validation for budgets

### 0.2.0
- Transaction authorization checks enforced
- File upload validation (type and size)
- Soft deletes for audit trail

### 0.1.0
- Laravel Fortify authentication
- Spatie Permission authorization
- CSRF protection
- SQL injection prevention
- XSS protection

---

## Known Issues

### Current Version (0.3.0)
- Vendor dropdown not implemented yet (Sprint 8)
- No batch operations for transactions yet
- No Excel export functionality yet
- Budget email alerts not implemented (in-app only)
- No budget templates or rollover feature

---

## Upcoming in Next Release (0.4.0)

- **Cash Balance Tracking**: Real-time balance calculations
- **Opening Balance**: Set starting balance for periods
- **Cash Reconciliation**: Reconcile physical cash with system
- **Balance History**: Track balance over time
- **Low Balance Alerts**: Notifications when cash is low

**Target Release Date**: December 22, 2024

---

## Contributors

- Development Team: [Names]
- Product Owner: [Name]
- Scrum Master: [Name]
- QA: [Name]

---

**Documentation Updated**: November 25, 2024  
**Project Status**: Active Development  
**Current Version**: 0.3.0

