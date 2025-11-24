# Technical Decisions (Architecture Decision Records)

This document records important technical decisions made during the development of the Petty Cash Book application, including context, reasoning, and consequences.

## ADR Format

Each decision follows this structure:
- **Status**: Proposed | Accepted | Deprecated | Superseded
- **Date**: When the decision was made
- **Context**: What is the issue we're seeing that is motivating this decision
- **Decision**: What we decided to do
- **Consequences**: What becomes easier or more difficult as a result

---

## ADR-001: Use Laravel 12 as Backend Framework

**Status**: Accepted  
**Date**: 2024-10-27  
**Deciders**: Development Team

### Context

Need to choose a backend framework for the petty cash management application. Requirements:
- Rapid development
- Strong ecosystem
- Built-in authentication
- ORM for database operations
- Active community support

### Decision

Use Laravel 12 (latest version) as the backend framework.

### Rationale

- **Mature Framework**: Battle-tested with 10+ years of development
- **Rich Ecosystem**: Extensive package ecosystem (Spatie, Laravel Nova, etc.)
- **Built-in Features**: Authentication, authorization, queues, caching, etc.
- **Eloquent ORM**: Powerful and intuitive database abstraction
- **Streamlined Structure**: Laravel 11+ has simplified directory structure
- **Active Community**: Large community, extensive documentation
- **Security**: Built-in protection against common vulnerabilities

### Consequences

**Positive:**
- Faster development time
- Less boilerplate code
- Well-documented solutions for common problems
- Easy to find developers familiar with Laravel

**Negative:**
- Framework lock-in
- Learning curve for team members unfamiliar with Laravel
- Some "magic" behavior that can be hard to debug

---

## ADR-002: Use Inertia.js for Frontend-Backend Integration

**Status**: Accepted  
**Date**: 2024-10-27  
**Deciders**: Development Team

### Context

Need to decide how to connect Laravel backend with Vue frontend. Options:
1. Traditional Blade templates
2. REST API + SPA (separate frontend)
3. Inertia.js (monolithic SPA)

### Decision

Use Inertia.js v2 to build a modern SPA while keeping frontend and backend in a single codebase.

### Rationale

- **Monolithic Architecture**: No need for separate API
- **Server-Side Routing**: Routes defined in Laravel, not duplicated in frontend
- **No API Layer**: Direct controller → component data flow
- **SPA Experience**: Client-side routing without full page reloads
- **Type Safety**: Works well with TypeScript and Wayfinder
- **SSR Support**: Built-in server-side rendering capabilities

### Consequences

**Positive:**
- Single codebase to maintain
- No API versioning concerns
- Simplified deployment
- Type-safe routing with Wayfinder
- Better SEO with SSR

**Negative:**
- Not suitable for mobile apps or third-party API consumers
- Tight coupling between frontend and backend
- Less flexibility for frontend-only updates

---

## ADR-003: Use Vue 3 with TypeScript for Frontend

**Status**: Accepted  
**Date**: 2024-10-27  
**Deciders**: Development Team

### Context

Need to choose a frontend framework. Options: React, Vue, Svelte, Angular.

### Decision

Use Vue 3 with Composition API and TypeScript.

### Rationale

- **Developer Experience**: Intuitive and easy to learn
- **Composition API**: Better code organization and reusability
- **TypeScript Support**: First-class TypeScript support
- **Performance**: Virtual DOM with excellent performance
- **Inertia Integration**: Official Inertia adapter for Vue
- **Ecosystem**: Rich ecosystem (Pinia, VueUse, etc.)

### Consequences

**Positive:**
- Faster onboarding for new developers
- Better code maintainability with TypeScript
- Excellent developer tools (Vue DevTools)
- Reactive system is powerful and intuitive

**Negative:**
- Smaller ecosystem compared to React
- TypeScript adds some complexity

---

## ADR-004: Use Tailwind CSS v4 for Styling

**Status**: Accepted  
**Date**: 2024-10-27  
**Deciders**: Development Team

### Context

Need a CSS solution that is maintainable, consistent, and allows rapid UI development.

### Decision

Use Tailwind CSS v4 (latest) with JIT compiler.

### Rationale

- **Utility-First**: Rapid prototyping with utility classes
- **Consistency**: Design system built-in
- **Performance**: Minimal CSS output with JIT
- **Dark Mode**: First-class dark mode support
- **Customization**: Easy to customize via CSS `@theme`
- **No CSS Files**: No need for separate CSS files

### Consequences

**Positive:**
- Faster UI development
- Smaller CSS bundle size
- Consistent design across application
- Easy to implement responsive design

**Negative:**
- HTML can become verbose
- Learning curve for utility-first approach
- Need to extract components to avoid repetition

---

## ADR-005: Use Spatie Permission for RBAC

**Status**: Accepted  
**Date**: 2024-10-27  
**Deciders**: Development Team

### Context

Need a robust role-based access control (RBAC) system with roles and permissions.

### Decision

Use Spatie Laravel Permission package.

### Rationale

- **Battle-Tested**: Used by thousands of Laravel applications
- **Flexible**: Supports roles, permissions, and direct permission assignment
- **Cache Support**: Built-in caching for performance
- **Middleware**: Ready-to-use middleware for route protection
- **Blade Directives**: Easy permission checks in views
- **Active Maintenance**: Regular updates and bug fixes

### Consequences

**Positive:**
- Comprehensive RBAC out of the box
- Well-documented with many examples
- Blade and middleware integration
- Multi-guard support

**Negative:**
- Adds database tables (roles, permissions, pivot tables)
- Cache invalidation complexity

---

## ADR-006: Use Spatie Media Library for File Uploads

**Status**: Accepted  
**Date**: 2024-11-10  
**Sprint**: Sprint 2

### Context

Need a solution for handling transaction receipt uploads with the following requirements:
- Multiple file uploads per transaction
- Image previews
- File validation (type, size)
- Easy to manage and delete files

### Decision

Use Spatie Media Library v11 for file management.

### Rationale

- **Polymorphic Relations**: Can attach media to any model
- **Collections**: Organize media into collections (e.g., "receipts")
- **Conversions**: Automatic image conversions/thumbnails
- **Validation**: Built-in file type and size validation
- **Responsive Images**: Automatic responsive image generation
- **Well Maintained**: Active development and support

### Consequences

**Positive:**
- Handles complex file operations automatically
- Works seamlessly with Eloquent models
- Media is automatically deleted when model is deleted
- Easy to generate thumbnails

**Negative:**
- Adds `media` table to database
- Adds ~2MB to vendor directory
- File conversions can be CPU-intensive

---

## ADR-007: Use Laravel Wayfinder for Type-Safe Routing

**Status**: Accepted  
**Date**: 2024-11-10  
**Sprint**: Sprint 2

### Context

Need a way to generate frontend routes that:
- Are type-safe
- Don't break when backend routes change
- Provide IDE autocompletion
- Work well with Inertia.js

### Decision

Use Laravel Wayfinder for generating TypeScript route definitions.

### Rationale

- **Type Safety**: Generates TypeScript types from Laravel routes
- **Auto-Sync**: Vite plugin keeps routes in sync during development
- **IDE Support**: Full autocompletion in IDEs
- **Zero Runtime Overhead**: Routes are generated at build time
- **Inertia Integration**: Works perfectly with Inertia.js
- **Parameter Validation**: TypeScript enforces correct parameters

### Consequences

**Positive:**
- Eliminates hardcoded URLs in frontend
- Catches routing errors at compile time
- IDE autocompletion for all routes
- Automatic updates when routes change

**Negative:**
- Must run `php artisan wayfinder:generate` after route changes (if not using Vite plugin)
- Slightly increased build time
- Learning curve for team

---

## ADR-008: Use Observers for Transaction Number Generation

**Status**: Accepted  
**Date**: 2024-11-10  
**Sprint**: Sprint 2

### Context

Need to auto-generate unique transaction numbers for each transaction in format `TXN-YYYY-00001`.

### Decision

Use Laravel Observer pattern to generate transaction numbers automatically on model creation.

### Rationale

- **Separation of Concerns**: Keeps model clean
- **Automatic**: No need to remember to generate number
- **Centralized**: All generation logic in one place
- **Testable**: Easy to unit test
- **Consistent**: Guarantees format consistency

### Consequences

**Positive:**
- Clean model code
- Automatic and reliable
- Easy to modify format in future
- Well-tested pattern in Laravel

**Negative:**
- Observer must be registered in service provider
- Slightly harder to debug if issues occur

---

## ADR-009: Use Reka UI for Component Library

**Status**: Accepted  
**Date**: 2024-11-10  
**Sprint**: Sprint 1

### Context

Need a component library for Vue that:
- Is accessible (ARIA compliant)
- Is headless (unstyled)
- Works well with Tailwind
- Is actively maintained

### Decision

Use Reka UI v2 as the headless component library.

### Rationale

- **Headless**: Fully customizable with Tailwind
- **Accessible**: Built-in ARIA support
- **Composition API**: Modern Vue 3 approach
- **TypeScript**: Full TypeScript support
- **Comprehensive**: Covers most UI needs
- **Active Development**: Regular updates

### Consequences

**Positive:**
- Full control over styling
- Accessibility built-in
- Modern component architecture
- Type-safe props

**Negative:**
- Need to style components ourselves
- Learning curve for Reka UI patterns
- Some components need custom implementation

---

## ADR-010: Use Yarn for Package Management

**Status**: Accepted  
**Date**: 2024-10-27  
**Deciders**: User Request

### Context

Need to choose between npm, Yarn, or pnpm for JavaScript package management.

### Decision

Use Yarn as the package manager (user requirement).

### Rationale

- **User Preference**: Explicitly requested by project owner
- **Performance**: Faster than npm
- **Lock File**: yarn.lock ensures consistent installs
- **Workspaces**: Better monorepo support if needed later
- **Offline Mode**: Can install packages offline

### Consequences

**Positive:**
- Consistent with user's workflow
- Faster installations
- Reliable dependency resolution

**Negative:**
- Team must have Yarn installed
- Mix with npm can cause issues (must stick to one)

---

## ADR-011: Use PHPUnit Over Pest

**Status**: Accepted  
**Date**: 2024-10-27  
**Sprint**: Sprint 1

### Context

Laravel supports both PHPUnit and Pest for testing.

### Decision

Use PHPUnit for all tests.

### Rationale

- **Standard**: PHPUnit is the de facto standard for PHP
- **Maturity**: More mature and stable
- **Team Familiarity**: Most PHP developers know PHPUnit
- **Documentation**: Extensive documentation and examples
- **IDE Support**: Better IDE integration

### Consequences

**Positive:**
- Standard testing approach
- More verbose but explicit
- Better IDE support

**Negative:**
- More boilerplate than Pest
- Less modern syntax

---

## ADR-012: Implement Soft Deletes on Transactions

**Status**: Accepted  
**Date**: 2024-11-10  
**Sprint**: Sprint 2

### Context

Need to decide whether to hard delete or soft delete transactions.

### Decision

Use soft deletes for transactions (and most other entities).

### Rationale

- **Audit Trail**: Keep history for compliance
- **Recovery**: Ability to restore accidentally deleted records
- **Reporting**: Historical reports remain accurate
- **Relationships**: Prevents cascade deletion issues

### Consequences

**Positive:**
- Better audit trail
- Can restore deleted records
- Safer deletion operation

**Negative:**
- Increased database size
- Queries must explicitly exclude soft deleted records
- Need periodic cleanup strategy

---

## Future Decisions (Proposed)

### ADR-013: Queue System for Email Notifications
**Status**: Proposed  
**Target Sprint**: Sprint 11  
Need to decide on queue driver (database, Redis, SQS)

### ADR-014: Caching Strategy
**Status**: Proposed  
**Target Sprint**: Sprint 12  
Need to decide on cache driver and what to cache

### ADR-015: Export Library for Reports
**Status**: Proposed  
**Target Sprint**: Sprint 6  
Need to choose library for PDF/Excel exports

---

## Deprecated Decisions

None yet.

---

**Last Updated**: November 24, 2024  
**Total ADRs**: 12 accepted, 3 proposed, 0 deprecated

