# Development Environment Setup Guide

## Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ and Yarn
- MySQL 8.0 or SQLite (for development)
- Git

## Installation Steps

### 1. Clone Repository

```bash
git clone <repository-url>
cd petty-cash-app
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

**Note: This project uses Yarn, not npm**

```bash
yarn install
```

### 4. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:

```env
APP_NAME="Petty Cash Book"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# For MySQL, use:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=petty_cash
# DB_USERNAME=root
# DB_PASSWORD=

MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@pettycash.local"
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Database Setup

#### Using SQLite (Development)
```bash
touch database/database.sqlite
php artisan migrate
php artisan db:seed
```

#### Using MySQL (Production-like)
```bash
# Create database
mysql -u root -p
CREATE DATABASE petty_cash;
exit;

# Run migrations
php artisan migrate
php artisan db:seed
```

### 6. Generate Wayfinder Routes

```bash
php artisan wayfinder:generate
```

### 7. Build Assets

```bash
# Development
yarn dev

# Production
yarn build
```

### 8. Start Development Server

```bash
# Option 1: Simple Laravel server
php artisan serve

# Option 2: Full development stack (recommended)
composer run dev
```

Visit: http://localhost:8000

## Default Login Credentials

After seeding, you can create an admin user:

```bash
php artisan tinker
```

```php
$user = User::factory()->create([
    'name' => 'Admin User',
    'email' => 'admin@pettycash.local',
    'password' => bcrypt('password'),
    'email_verified_at' => now(),
]);
$user->assignRole('Admin');
```

**Login Credentials:**
- Email: admin@pettycash.local
- Password: password

## Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage (requires Xdebug)
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/Feature/Users/UserManagementTest.php
```

## Code Quality

### Laravel Pint (Code Formatter)

```bash
# Format all files
vendor/bin/pint

# Format specific directory
vendor/bin/pint app/Http/Controllers

# Check without fixing
vendor/bin/pint --test
```

### PHPStan (Static Analysis)

```bash
# If installed
vendor/bin/phpstan analyse
```

## Common Development Commands

```bash
# Clear all caches
php artisan optimize:clear

# Generate IDE helper files (if installed)
php artisan ide-helper:generate
php artisan ide-helper:models
php artisan ide-helper:meta

# Create new migration
php artisan make:migration create_table_name

# Create new model with migration and factory
php artisan make:model ModelName -mf

# Create new controller
php artisan make:controller ControllerName

# Create new request
php artisan make:request RequestName

# Create new test
php artisan make:test TestName --phpunit

# List all routes
php artisan route:list

# List all artisan commands
php artisan list
```

## Troubleshooting

### Issue: Permission Denied on Storage

```bash
chmod -R 775 storage bootstrap/cache
```

### Issue: Database Connection Failed

- Verify database service is running
- Check database credentials in `.env`
- Ensure database exists
- For SQLite, ensure file exists: `touch database/database.sqlite`

### Issue: Assets Not Loading

```bash
yarn build
php artisan optimize:clear
```

### Issue: Vite Manifest Not Found (in tests)

Tests use `withoutVite()` helper to skip asset compilation. This is normal for feature tests.

### Issue: 2FA Enabled (Development)

2FA is temporarily disabled in development. Check `config/fortify.php`:

```php
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
    // 2FA commented out for development
],
```

### Issue: Yarn Command Not Found

Install Yarn globally:

```bash
npm install -g yarn
```

## IDE Setup (VS Code)

Recommended extensions:
- **PHP Intelephense**: PHP language support
- **Laravel Blade Snippets**: Blade template support
- **Volar**: Vue.js 3 support
- **ESLint**: JavaScript linting
- **Prettier**: Code formatting
- **GitLens**: Git integration

## Browser DevTools

For Vue.js development:
- Install **Vue.js DevTools** extension for Chrome/Firefox

## Environment Variables Reference

| Variable | Description | Default |
|----------|-------------|---------|
| APP_NAME | Application name | "Petty Cash Book" |
| APP_ENV | Environment (local/production) | local |
| APP_DEBUG | Debug mode | true |
| APP_URL | Application URL | http://localhost:8000 |
| DB_CONNECTION | Database driver | sqlite |
| MAIL_MAILER | Mail driver | log |

## Next Steps

1. Review [Coding Standards](coding-standards.md)
2. Check [Git Workflow](git-workflow.md)
3. Read [Testing Strategy](testing-strategy.md)
4. Explore [User Manual](../06-user-guides/user-manual.md)

## Getting Help

- Check [FAQ](../06-user-guides/faq.md)
- Review [Troubleshooting Guide](../06-user-guides/troubleshooting.md)
- Contact development team
- Review Laravel documentation: https://laravel.com/docs

---

**Document Version**: 1.0  
**Last Updated**: November 24, 2024

