<!-- 6203da14-e435-47a9-b1f7-4edef0ab2b85 bb92c4cb-d9ad-4a8a-aa99-35a0f5cde814 -->
# Sprint 1: Authentication & User Management Implementation

## Overview

Implement secure authentication system with role-based access control (RBAC) for the Petty Cash Book application. The system already has Laravel Fortify configured for authentication, so we'll build upon it by adding Spatie Permission for roles, creating user management interfaces, and implementing profile management.

## Current State

- Laravel 12 with Fortify authentication configured
- Inertia.js with Vue 3 frontend
- Basic User model exists (`app/Models/User.php`)
- Authentication scaffolding in place (login, register, password reset)
- Two-factor authentication already configured

## Implementation Steps

### 1. Install and Configure Spatie Laravel Permission

**Backend Setup:**

- Install `spatie/laravel-permission` package via Composer
- Publish the package configuration and migration files
- Run migration to create `roles`, `permissions`, and pivot tables
- Add `HasRoles` trait to User model (`app/Models/User.php`)

**Files to modify:**

- `composer.json` - add spatie/laravel-permission dependency
- `app/Models/User.php` - add HasRoles trait
- `config/permission.php` - configure after publishing

### 2. Create Roles and Permissions Seeder

**Create seeder file:** `database/seeders/RolesAndPermissionsSeeder.php`

**Roles to create:**

- Admin (full access)
- Accountant (manage finances, approve transactions, view reports)
- Cashier (record transactions, manage cash balance)
- Requester (submit transaction requests, view own transactions)

**Permissions to create:**

- User management: `manage-users`, `view-users`, `create-users`, `edit-users`, `delete-users`
- Transaction management: `manage-transactions`, `view-transactions`, `create-transactions`, `approve-transactions`
- Reports: `view-reports`, `export-reports`
- Settings: `manage-settings`

**Files to create:**

- `database/seeders/RolesAndPermissionsSeeder.php`
- Update `database/seeders/DatabaseSeeder.php` to call the new seeder

### 3. Create Role Checking Middleware

**Create middleware:** `app/Http/Middleware/CheckRole.php` and `app/Http/Middleware/CheckPermission.php`

These will be used to protect routes based on user roles and permissions.

**Files to create:**

- `app/Http/Middleware/CheckRole.php`
- `app/Http/Middleware/CheckPermission.php`

**Files to modify:**

- `bootstrap/app.php` or `app/Http/Kernel.php` - register middleware aliases

### 4. Build User Management CRUD Interface

**Backend:**

- Create `UserController` at `app/Http/Controllers/UserController.php`
- Implement methods: `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`
- Add validation using Form Requests: `app/Http/Requests/StoreUserRequest.php` and `app/Http/Requests/UpdateUserRequest.php`
- Add routes in `routes/web.php` under admin middleware

**Frontend (Vue/Inertia):**

- Create `resources/js/pages/Users/Index.vue` - list all users with search/filter
- Create `resources/js/pages/Users/Create.vue` - create new user form
- Create `resources/js/pages/Users/Edit.vue` - edit user form
- Create reusable components:
  - `resources/js/components/Users/UserTable.vue` - table component
  - `resources/js/components/Users/UserForm.vue` - form component

**Files to create:**

- `app/Http/Controllers/UserController.php`
- `app/Http/Requests/StoreUserRequest.php`
- `app/Http/Requests/UpdateUserRequest.php`
- `resources/js/pages/Users/Index.vue`
- `resources/js/pages/Users/Create.vue`
- `resources/js/pages/Users/Edit.vue`
- `resources/js/components/Users/UserTable.vue`
- `resources/js/components/Users/UserForm.vue`

**Files to modify:**

- `routes/web.php` - add user management routes

### 5. Create Role Assignment Interface

**Backend:**

- Create `RoleController` at `app/Http/Controllers/RoleController.php`
- Implement methods: `index()`, `assignRole()`, `removeRole()`
- Add validation

**Frontend:**

- Create `resources/js/pages/Roles/Index.vue` - manage roles
- Create `resources/js/pages/Users/AssignRole.vue` - assign roles to users
- Add role selection to user create/edit forms

**Files to create:**

- `app/Http/Controllers/RoleController.php`
- `resources/js/pages/Roles/Index.vue`
- `resources/js/components/Users/RoleSelector.vue`

**Files to modify:**

- `routes/web.php` - add role management routes
- `resources/js/pages/Users/Edit.vue` - integrate role assignment

### 6. Implement Profile Management

**Backend:**

- Create `ProfileController` at `app/Http/Controllers/ProfileController.php`
- Implement methods: `show()`, `update()`, `updatePassword()`
- Add validation for profile updates

**Frontend:**

- Create `resources/js/pages/Profile/Show.vue` - view/edit profile
- Create `resources/js/pages/Profile/UpdatePassword.vue` - change password form
- Add profile link to navigation

**Files to create:**

- `app/Http/Controllers/ProfileController.php`
- `app/Http/Requests/UpdateProfileRequest.php`
- `app/Http/Requests/UpdatePasswordRequest.php`
- `resources/js/pages/Profile/Show.vue`
- `resources/js/pages/Profile/UpdatePassword.vue`

**Files to modify:**

- `routes/web.php` - add profile routes
- `resources/js/layouts/AppLayout.vue` (or equivalent) - add profile navigation

### 7. Email Verification Enhancement

**Backend:**

- Ensure User model implements `MustVerifyEmail` contract
- Configure email verification routes
- Add verification middleware to protected routes

**Frontend:**

- Create `resources/js/pages/Auth/VerifyEmail.vue` - email verification notice
- Handle verification status in UI

**Files to modify:**

- `app/Models/User.php` - implement MustVerifyEmail
- `routes/web.php` - ensure verification routes are configured
- `config/fortify.php` - enable email verification feature

**Files to create:**

- `resources/js/pages/Auth/VerifyEmail.vue`

### 8. Write Tests

**Feature Tests:**

- `tests/Feature/Auth/AuthenticationTest.php` - login, logout, registration
- `tests/Feature/Auth/EmailVerificationTest.php` - email verification flow
- `tests/Feature/Users/UserManagementTest.php` - CRUD operations
- `tests/Feature/Users/RoleAssignmentTest.php` - role assignment
- `tests/Feature/Profile/ProfileManagementTest.php` - profile updates

**Unit Tests:**

- `tests/Unit/Models/UserTest.php` - User model relationships and methods
- `tests/Unit/Middleware/CheckRoleTest.php` - role middleware

**Files to create:**

- All test files listed above

### 9. Update Navigation and Dashboard

**Tasks:**

- Add user management menu item for admins
- Update dashboard to show user statistics
- Add role-based conditional rendering in navigation

**Files to modify:**

- `resources/js/layouts/AppLayout.vue` - add navigation items
- `resources/js/pages/Dashboard.vue` - add user statistics
- `resources/js/types/index.ts` - add types for User, Role, Permission

## Key Technical Decisions

1. **Package Choice:** Using Spatie Laravel Permission (industry standard for Laravel RBAC)
2. **Frontend:** Vue 3 with Inertia.js for reactive SPAs with server-side routing
3. **UI Components:** Leveraging existing reka-ui and Tailwind CSS setup
4. **Testing:** PHPUnit for backend, feature tests for integration
5. **Email:** Using Laravel's built-in mail system (configured in .env)

## Acceptance Criteria Validation

- [ ] Users can register and login securely (Fortify already provides this)
- [ ] Admin can manage users via CRUD interface
- [ ] Admin can assign roles to users (Admin, Accountant, Cashier, Requester)
- [ ] Different roles have different access levels (enforced by middleware)
- [ ] Password reset functionality works (Fortify already provides this)
- [ ] Users can update their profile and change password
- [ ] Email verification is functional
- [ ] All authentication and user management tests pass

## Commands to Run

```bash
# Install Spatie Permission
composer require spatie/laravel-permission

# Publish config and migrations
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Run migrations
php artisan migrate

# Seed roles and permissions
php artisan db:seed --class=RolesAndPermissionsSeeder

# Install frontend dependencies (if needed)
yarn install

# Run tests
php artisan test
```

### To-dos

- [ ] Install and configure Spatie Laravel Permission package
- [ ] Create roles and permissions seeder with 4 roles and required permissions
- [ ] Create role and permission checking middleware
- [ ] Build user management backend (Controller, Requests, Routes)
- [ ] Build user management frontend (Vue pages and components)
- [ ] Implement role assignment interface (backend + frontend)
- [ ] Implement profile and password management pages
- [ ] Configure and test email verification flow
- [ ] Update navigation and dashboard with role-based access
- [ ] Write comprehensive feature and unit tests for authentication and user management