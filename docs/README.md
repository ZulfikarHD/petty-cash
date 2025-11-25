# Petty Cash Book Application - Documentation

Welcome to the Petty Cash Book application documentation. This system helps organizations manage petty cash transactions with role-based access control, approval workflows, and comprehensive reporting.

## 📋 Quick Links

### For New Users
- [Quick Start Guide](06-user-guides/quick-start.md)
- [User Manual](06-user-guides/user-manual.md)
- [FAQ](06-user-guides/faq.md)

### For Developers
- [Setup Guide](04-technical/setup-guide.md)
- [Coding Standards](04-technical/coding-standards.md)
- [Testing Strategy](04-technical/testing-strategy.md)
- [Git Workflow](04-technical/git-workflow.md)

### For Project Managers
- [Project Charter](01-project-overview/project-charter.md)
- [Sprint Planning](07-development/sprint-planning/sprint-01.md)
- [Technical Decisions](07-development/technical-decisions.md)

### For Administrators
- [Admin Guide](06-user-guides/admin-guide.md)
- [Deployment Guide](04-technical/deployment-guide.md)
- [Operations & Monitoring](09-operations/monitoring.md)

## 🏗️ Project Overview

- **Project Name**: Petty Cash Book Application
- **Version**: 0.4.0 (In Development)
- **Status**: Sprint 4 Completed ✅
- **Tech Stack**: Laravel 12, Vue 3, Inertia.js, Tailwind CSS
- **Last Updated**: November 25, 2024

## 🎯 Key Features

### ✅ Completed (Sprints 1, 2, 3 & 4)
- User authentication with email verification
- Role-based access control (Admin, Accountant, Cashier, Requester)
- User management interface
- Transaction management (create, read, update, delete)
- Receipt image uploads
- Transaction filtering and search
- Auto-generated transaction numbers
- **Categories management** (create, edit, delete with color coding)
- **Budget allocation** (set limits per category with date ranges)
- **Budget tracking** (real-time budget vs actual spending)
- **Budget alerts** (visual indicators and dashboard notifications)
- Category assignment to transactions
- **Cash Balance tracking** (real-time balance from approved transactions)
- **Opening balance management** (period-based balance tracking)
- **Cash reconciliation** (reconcile physical cash with system balance)
- **Balance history** (daily snapshots and period tracking)
- **Low balance alerts** (configurable threshold notifications)
- Comprehensive testing (144 tests passing)

### 🚧 In Progress
- Approval Workflow System (Sprint 5)

### 📅 Planned
- Advanced Reporting & Analytics
- Dashboard improvements
- Reimbursement management
- Vendor management
- Audit trail & activity logging
- Multi-currency support
- Mobile optimizations

## 📚 Documentation Structure

```
docs/
├── 01-project-overview/     # Business context and goals
├── 02-requirements/         # Functional & non-functional requirements
├── 03-design/              # Architecture, database, UI/UX designs
├── 04-technical/           # Developer guides and standards
├── 05-api-documentation/   # API endpoints (Inertia-based)
├── 06-user-guides/         # End-user documentation
├── 07-development/         # Sprint plans and decisions
├── 08-testing/            # Test plans and results
├── 09-operations/         # Deployment and maintenance
├── 10-meetings/           # Meeting notes and retrospectives
└── 11-assets/             # Diagrams, screenshots, templates
```

## 🚀 Getting Started

### Prerequisites
- PHP 8.4+
- Composer
- MySQL/MariaDB
- Node.js 18+
- Yarn

### Quick Setup
```bash
# Clone repository
git clone <repository-url>
cd petty-cash-app

# Install dependencies
composer install
yarn install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Start development
yarn dev
php artisan serve
```

For detailed setup instructions, see the [Setup Guide](04-technical/setup-guide.md).

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --filter=Transaction
```

Current test coverage: **144 tests, 354+ assertions passing**

## 📦 Tech Stack

### Backend
- **PHP 8.4.1**
- **Laravel 12** (latest)
- **Spatie Permission** (RBAC)
- **Spatie Media Library** (file uploads)
- **Laravel Fortify** (authentication)
- **PHPUnit 11** (testing)
- **Laravel Pint** (code formatting)

### Frontend
- **Vue 3.5.13**
- **Inertia.js v2**
- **Tailwind CSS v4**
- **Reka UI v2.4.1** (headless components)
- **TypeScript 5.2.2**
- **Vite 7**
- **Laravel Wayfinder** (type-safe routing)

### Development Tools
- **ESLint 9** & **Prettier 3**
- **Yarn** (package manager)
- **Docker** (via Laravel Sail)

## 👥 Team Roles

| Role | Responsibilities |
|------|-----------------|
| **Admin** | Full system access, user management, system configuration |
| **Accountant** | Transaction approval, reporting, budget management |
| **Cashier** | Record transactions, manage daily cash operations |
| **Requester** | Submit transaction requests, view own transactions |

## 📊 Project Progress

### Sprint 1: Authentication & User Management ✅
- Duration: 2 weeks
- Status: Completed
- Tests: 27 passing

### Sprint 2: Core Transaction Management ✅
- Duration: 2 weeks
- Status: Completed
- Tests: 42 passing

### Sprint 3: Categories & Budget Management ✅
- Duration: 2 weeks
- Status: Completed
- Tests: 38 new tests (total: 107)
- End Date: November 25, 2024

### Sprint 4: Cash Balance & Reconciliation ✅
- Duration: 2 weeks
- Status: Completed
- Tests: 37 new tests (total: 144)
- End Date: November 25, 2024

## 📖 Additional Resources

- [Project Charter](01-project-overview/project-charter.md)
- [Functional Requirements](02-requirements/functional-requirements.md)
- [System Architecture](03-design/system-architecture.md)
- [Database Schema](03-design/database-schema.md)
- [Changelog](07-development/changelog.md)

## 🤝 Contributing

Please read our [Coding Standards](04-technical/coding-standards.md) and [Git Workflow](04-technical/git-workflow.md) before contributing.

## 📞 Support

For issues or questions:
- Check the [FAQ](06-user-guides/faq.md)
- Review [Troubleshooting Guide](06-user-guides/troubleshooting.md)
- Contact the development team

---

**Last Updated**: November 25, 2024  
**Version**: 0.4.0-dev  
**Maintained by**: Development Team
