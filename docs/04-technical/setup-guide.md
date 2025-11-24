# Development Environment Setup Guide

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- **PHP** 8.4 or higher
- **Composer** 2.x
- **MySQL** 8.0 or **MariaDB** 10.x
- **Node.js** 18+ 
- **Yarn** (package manager)
- **Git**

Optional but recommended:
- **Docker** (for Laravel Sail)
- **Redis** (for caching and queues)

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

If you encounter memory issues, try:
```bash
COMPOSER_MEMORY_LIMIT=-1 composer install
```

### 3. Install JavaScript Dependencies

```bash
yarn install
```

### 4. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit the `.env` file with your local configuration:

```env
APP_NAME="Petty Cash Book"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=petty_cash
DB_USERNAME=root
DB_PASSWORD=

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database

# Queue (optional for development)
QUEUE_CONNECTION=sync

# Mail (for testing, use Mailpit or Mailtrap)
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@pettycash.local"
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Database Setup

#### Option A: Manual Database Creation

```bash
# Create database using MySQL CLI
mysql -u root -p
```

```sql
CREATE DATABASE petty_cash CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

#### Option B: Using Laravel Sail (Docker)

```bash
# Start Sail containers
./vendor/bin/sail up -d

# The database will be created automatically
```

### 6. Run Migrations and Seeders

```bash
# Run migrations
php artisan migrate

# Seed the database with roles, permissions, and test users
php artisan db:seed
```

### 7. Storage Link

Create symbolic link for file storage:

```bash
php artisan storage:link
```

### 8. Build Frontend Assets

For development with hot reload:
```bash
yarn dev
```

For production build:
```bash
yarn build
```

### 9. Start Development Server

```bash
# Laravel development server
php artisan serve
```

The application will be available at: **http://localhost:8000**

## Default Login Credentials

After seeding, you can log in with these accounts:

### Admin Account
- **Email**: `admin@pettycash.local`
- **Password**: `password`
- **Role**: Admin (Full Access)

### Accountant Account
- **Email**: `accountant@pettycash.local`
- **Password**: `password`
- **Role**: Accountant

### Cashier Account
- **Email**: `cashier@pettycash.local`
- **Password**: `password`
- **Role**: Cashier

### Requester Account
- **Email**: `requester@pettycash.local`
- **Password**: `password`
- **Role**: Requester

> **⚠️ Security Warning**: Change these passwords immediately in production!

## Running Tests

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Feature

# Run specific test file
php artisan test tests/Feature/TransactionTest.php

# Run specific test method
php artisan test --filter=test_user_can_create_transaction
```

**Current Test Coverage**: 69 tests, 161 assertions passing ✅

## Code Quality Tools

### Laravel Pint (PHP Code Formatter)

```bash
# Fix all code style issues
vendor/bin/pint

# Check specific directory
vendor/bin/pint app/Http/Controllers

# Dry run (check without fixing)
vendor/bin/pint --test
```

### ESLint & Prettier (JavaScript/Vue)

```bash
# Run ESLint
yarn lint

# Fix auto-fixable issues
yarn lint:fix

# Format with Prettier
yarn format
```

## Development with Laravel Sail (Docker)

If you prefer Docker-based development:

```bash
# Start all containers
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# Run artisan commands
./vendor/bin/sail artisan migrate

# Run composer
./vendor/bin/sail composer install

# Run yarn
./vendor/bin/sail yarn dev

# Run tests
./vendor/bin/sail test

# Access MySQL CLI
./vendor/bin/sail mysql
```

## Troubleshooting

### Issue: Permission Denied on Storage

**Solution:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Or on development:
```bash
sudo chmod -R 777 storage bootstrap/cache
```

### Issue: Database Connection Failed

**Check these:**
1. MySQL/MariaDB is running: `sudo systemctl status mysql`
2. Database exists: `SHOW DATABASES;`
3. Credentials in `.env` are correct
4. Port is correct (default: 3306)

**Restart MySQL:**
```bash
sudo systemctl restart mysql
```

### Issue: Assets Not Loading / Vite Error

**Solution:**
```bash
# Clear all caches
php artisan optimize:clear

# Rebuild assets
yarn build

# If still issues, delete node_modules
rm -rf node_modules yarn.lock
yarn install
```

### Issue: "Class not found" Errors

**Solution:**
```bash
# Regenerate autoload files
composer dump-autoload

# Clear config cache
php artisan config:clear
php artisan cache:clear
```

### Issue: Migration Already Exists Error

**Solution:**
```bash
# Rollback migrations
php artisan migrate:rollback

# Or reset database (⚠️ destroys all data)
php artisan migrate:fresh --seed
```

### Issue: Port 8000 Already in Use

**Solution:**
```bash
# Use different port
php artisan serve --port=8001

# Or kill the process using port 8000
lsof -ti:8000 | xargs kill -9
```

## IDE Setup

### VS Code (Recommended)

Install these extensions:
- **PHP Intelephense** (bmewburn.vscode-intelephense-client)
- **Laravel Blade Snippets** (onecentlin.laravel-blade)
- **Vue - Official** (Vue.volar)
- **ESLint** (dbaeumer.vscode-eslint)
- **Prettier** (esbenp.prettier-vscode)
- **Tailwind CSS IntelliSense** (bradlc.vscode-tailwindcss)

Workspace settings (`.vscode/settings.json`):
```json
{
  "editor.formatOnSave": true,
  "editor.defaultFormatter": "esbenp.prettier-vscode",
  "[php]": {
    "editor.defaultFormatter": "bmewburn.vscode-intelephense-client"
  },
  "intelephense.files.associations": ["*.php", "*.blade.php"],
  "emmet.includeLanguages": {
    "blade": "html"
  }
}
```

### PHPStorm

1. Go to **Settings → PHP → Composer**
2. Set path to composer.json
3. Enable **Synchronize IDE settings with composer.json**
4. Go to **Settings → Languages & Frameworks → PHP**
5. Set PHP language level to 8.4
6. Configure Laravel plugin

## Additional Configuration

### Queue Worker (Optional for Development)

If you plan to use queued jobs:

```bash
# In .env, set
QUEUE_CONNECTION=database

# Run migrations for jobs table
php artisan queue:table
php artisan migrate

# Start queue worker
php artisan queue:work

# Or use Horizon (if installed)
php artisan horizon
```

### Mail Testing with Mailpit

Mailpit is included in Laravel Sail:

```bash
# With Sail running
./vendor/bin/sail up -d

# Access Mailpit UI at:
# http://localhost:8025
```

For manual Mailpit installation:
```bash
# Install Mailpit
brew install mailpit  # macOS
# or download from: https://github.com/axllent/mailpit

# Run Mailpit
mailpit

# Configure .env
MAIL_HOST=localhost
MAIL_PORT=1025
```

### Redis (Optional)

For better performance with caching and sessions:

```bash
# Install Redis
sudo apt install redis-server  # Ubuntu/Debian
brew install redis             # macOS

# Start Redis
sudo systemctl start redis

# Update .env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Next Steps

After successful installation:

1. ✅ Read the [Coding Standards](coding-standards.md)
2. ✅ Review [Git Workflow](git-workflow.md)
3. ✅ Check [Testing Strategy](testing-strategy.md)
4. ✅ Explore the [API Documentation](../05-api-documentation/api-overview.md)
5. ✅ Join the development team standup

## Getting Help

- Check the [FAQ](../06-user-guides/faq.md)
- Review [Troubleshooting Guide](../06-user-guides/troubleshooting.md)
- Contact the development team
- Submit issues on GitHub/GitLab

---

**Last Updated**: November 24, 2024  
**Tested On**: Ubuntu 24.04, macOS 14, Windows 11 (WSL2)
