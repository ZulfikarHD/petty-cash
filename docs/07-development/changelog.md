# Changelog

All notable changes to the Petty Cash Book application will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Categories and budget management
- Cash balance tracking and reconciliation
- Approval workflow system
- Advanced reporting and analytics
- Vendor management
- Audit trail and activity logging

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
| 0.2.0 | 2024-11-24 | Sprint 2 | ✅ Completed | 69 passing |
| 0.1.0 | 2024-11-10 | Sprint 1 | ✅ Completed | 27 passing |
| 0.0.1 | 2024-10-27 | Sprint 0 | ✅ Completed | - |

---

## Migration Guide

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

### 0.2.0
- None (additive changes only)

### 0.1.0
- Initial release

---

## Security Updates

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

### Current Version (0.2.0)
- Category and vendor dropdowns show null (waiting for Sprint 3 & 8)
- No batch operations for transactions yet
- No Excel export functionality yet
- Dashboard only shows basic statistics

---

## Upcoming in Next Release (0.3.0)

- **Categories Management**: Create and manage expense categories
- **Budget Allocation**: Set budget limits per category
- **Budget Tracking**: Real-time budget vs actual spending
- **Budget Alerts**: Notifications when approaching limits
- **Category Reports**: Category-wise expense breakdown

**Target Release Date**: December 8, 2024

---

## Contributors

- Development Team: [Names]
- Product Owner: [Name]
- Scrum Master: [Name]
- QA: [Name]

---

**Documentation Updated**: November 24, 2024  
**Project Status**: Active Development  
**Current Version**: 0.2.0

