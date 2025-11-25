# Petty Cash Book App - Scrum Sprint Workflow

## Project Overview
Development of a comprehensive petty cash management system using Laravel framework with role-based access control, approval workflows, and reporting capabilities.

---

## Sprint 0: Project Setup (1 week)

### Sprint Goal
Set up development environment, project structure, and foundational infrastructure.

### User Stories
- **Setup-001**: As a developer, I need to initialize Laravel project with version control
- **Setup-002**: As a developer, I need to configure database and environment settings
- **Setup-003**: As a developer, I need to install and configure required packages

### Tasks
- [x] Initialize Laravel project (latest LTS version)
- [x] Set up Git repository and branching strategy
- [x] Configure environment files (.env)
- [x] Install core packages (Breeze/Jetstream, Spatie Permission, etc.)
- [ ] Create database schema design document
- [x] Set up development, staging environments
- [x] Configure code quality tools (PHPStan, Laravel Pint)
- [ ] Create project documentation structure
- [ ] Set up CI/CD pipeline basics

### Definition of Done
- Project runs locally without errors
- All developers can access and run the project
- Database connection established
- Basic authentication scaffolding in place

---

## Sprint 1: Authentication & User Management (2 weeks) ✅ COMPLETED

### Sprint Goal
Implement secure authentication system with role-based access control.

### User Stories
- **AUTH-001**: ✅ As a user, I can register and login to the system
- **AUTH-002**: ✅ As an admin, I can create and manage user accounts
- **AUTH-003**: ✅ As an admin, I can assign roles to users (Admin, Accountant, Cashier, Requester)
- **AUTH-004**: ✅ As an admin, I can set permissions for each role
- **AUTH-005**: ✅ As a user, I can update my profile and change password

### Tasks
- [x] Implement authentication (login, register, logout)
- [x] Create users migration and model
- [x] Install and configure Spatie Laravel Permission
- [x] Create roles and permissions seeder
- [x] Build user management CRUD interface
- [x] Create role assignment interface
- [x] Implement profile management page
- [x] Add email verification
- [x] Write unit tests for authentication
- [x] Create middleware for role checking

### Acceptance Criteria
- ✅ Users can register and login securely
- ✅ Admin can manage users and assign roles
- ✅ Different roles have different access levels
- ✅ Password reset functionality works
- ✅ All authentication tests pass (27 tests, 66 assertions)

### Sprint Outcomes
- **Status**: COMPLETED
- **End Date**: November 24, 2024
- **Test Coverage**: 27 passing tests
- **Documentation**: Complete (see docs/07-development/sprint-planning/sprint-01.md)

---

## Sprint 2: Core Transaction Management (2 weeks) ✅ COMPLETED

### Sprint Goal
Build the foundation for recording and managing petty cash transactions.

### User Stories
- **TRANS-001**: ✅ As a cashier, I can record cash-in transactions
- **TRANS-002**: ✅ As a cashier, I can record cash-out transactions
- **TRANS-003**: ✅ As a user, I can attach receipts to transactions
- **TRANS-004**: ✅ As a user, I can view all transactions in a list
- **TRANS-005**: ✅ As a user, I can edit/delete my pending transactions
- **TRANS-006**: ✅ As a user, I can search and filter transactions

### Tasks
- [x] Create transactions table migration
- [x] Create Transaction model with relationships
- [x] Build transaction CRUD interface
- [x] Implement file upload for receipts (Laravel Media Library)
- [x] Create transaction list view with filters
- [x] Add date range picker
- [x] Implement transaction status management
- [x] Add validation rules for transactions
- [x] Create transaction detail view
- [x] Write feature tests for transactions

### Acceptance Criteria
- ✅ Users can create transactions with all required fields
- ✅ Receipt images can be uploaded and viewed
- ✅ Transaction list shows with proper filtering
- ✅ Only authorized users can edit/delete
- ✅ Data validation works properly

### Sprint Outcomes
- **Status**: COMPLETED
- **End Date**: November 24, 2024
- **Test Coverage**: 42 passing tests (95 assertions)
- **Components Created**: 
  - Transaction CRUD pages (Index, Create, Edit, Show)
  - UI Components (Select, Textarea, RadioGroup)
  - Auto-generated transaction numbers (TXN-YYYY-00001)
  - Wayfinder integration for type-safe routing
- **Documentation**: Complete (see .cursor/plans/sprint-2-implementation-6203da14.plan.md)

---

## Sprint 3: Categories & Budget Management (2 weeks) ✅ COMPLETED

### Sprint Goal
Implement expense categorization and budget allocation system.

### User Stories
- **CAT-001**: ✅ As an admin, I can create and manage expense categories
- **CAT-002**: ✅ As an admin, I can set budget limits for each category
- **CAT-003**: ✅ As a user, I can assign categories to transactions
- **CAT-004**: ✅ As a user, I can see budget vs actual spending per category
- **CAT-005**: ✅ As a system, I should alert when budget limit is reached

### Tasks
- [x] Create categories table migration
- [x] Create budgets table migration
- [x] Build category management interface
- [x] Create budget allocation interface
- [x] Add category dropdown to transaction form
- [x] Implement budget tracking logic
- [x] Create budget vs actual comparison view
- [x] Add budget alert notifications
- [x] Create budget period management
- [x] Write tests for budget calculations

### Acceptance Criteria
- ✅ Categories can be created and managed
- ✅ Budgets can be set per category per period
- ✅ Transactions are properly categorized
- ✅ Budget tracking is accurate
- ✅ Alerts work when limits are approached

### Sprint Outcomes
- **Status**: COMPLETED
- **End Date**: November 25, 2024
- **Test Coverage**: 107 passing tests (38 new tests added)
- **Components Created**:
  - Category CRUD pages (Index, Create, Edit, Show)
  - Budget CRUD pages (Index, Create, Edit, Show, Overview)
  - BudgetService for calculations
  - Budget alerts widget on dashboard
  - Category integration in transaction forms
  - Sidebar navigation updated with Categories & Budgets
- **Documentation**: Complete (see docs/07-development/sprint-planning/sprint-03.md)

---

## Sprint 4: Cash Balance & Reconciliation (2 weeks) ✅ COMPLETED

### Sprint Goal
Implement real-time cash balance tracking and reconciliation features.

### User Stories
- **BAL-001**: ✅ As a cashier, I can see current cash balance in real-time
- **BAL-002**: ✅ As a cashier, I can set opening balance for a period
- **BAL-003**: ✅ As a cashier, I can reconcile cash on hand with system balance
- **BAL-004**: ✅ As a user, I can view balance history
- **BAL-005**: ✅ As a system, I should alert when cash balance is low

### Tasks
- [x] Create cash_balances table migration
- [x] Implement balance calculation logic
- [x] Create opening balance form
- [x] Build cash reconciliation interface
- [x] Add real-time balance display to dashboard
- [x] Create balance history view
- [x] Implement low balance alerts
- [x] Add balance discrepancy tracking
- [ ] Create balance adjustment feature (moved to future sprint)
- [x] Write tests for balance calculations

### Acceptance Criteria
- ✅ Balance updates automatically with transactions
- ✅ Opening balance can be set per period
- ✅ Reconciliation process works smoothly
- ✅ Balance history is accurate
- ✅ Low balance alerts trigger correctly

### Sprint Outcomes
- **Status**: COMPLETED
- **End Date**: November 25, 2024
- **Test Coverage**: 144 passing tests (37 new tests added)
- **Components Created**:
  - CashBalance CRUD pages (Index, Create, Show, Reconcile)
  - BalanceService for calculations
  - Real-time balance widget on dashboard
  - Low balance alerts with configurable threshold
  - Daily balance history tracking
  - Discrepancy tracking during reconciliation
  - Sidebar navigation updated with Cash Balance
- **Documentation**: Complete (see docs/07-development/sprint-planning/sprint-04.md)

---

## Sprint 5: Approval Workflow System (2 weeks) ✅ COMPLETED

### Sprint Goal
Build single-level approval workflow for Requester role transactions with in-app notifications.

### User Stories
- **APPR-001**: ✅ As a requester, I can submit transactions for approval
- **APPR-002**: ✅ As an approver, I can view pending approval requests
- **APPR-003**: ✅ As an approver, I can approve or reject transactions
- **APPR-004**: ✅ As a user, I can track approval status of my requests
- **APPR-005**: ✅ As a user, I receive in-app notifications for approvals

### Tasks
- [x] Create approvals table migration
- [x] Create app_notifications table migration
- [x] Build Approval and AppNotification models
- [x] Create ApprovalService and NotificationService
- [x] Implement transaction submission for approval (Requester role)
- [x] Create pending approvals dashboard
- [x] Build approve/reject action buttons with dialogs
- [x] Add rejection reason field with validation
- [x] Implement in-app notifications for approvals
- [x] Create approval history view in transaction details
- [x] Add NotificationBell component to header
- [x] Create ApprovalPolicy and TransactionPolicy
- [x] Write tests for approval workflow (17 tests)

### Acceptance Criteria
- ✅ Requesters must submit transactions for approval
- ✅ Cashiers/Accountants/Admins transactions are auto-approved
- ✅ Approvers receive in-app notifications
- ✅ Approval/rejection updates transaction status
- ✅ Approval history is maintained and displayed
- ✅ Cannot approve own submissions

### Sprint Outcomes
- **Status**: COMPLETED
- **End Date**: November 25, 2024
- **Test Coverage**: 161 passing tests (17 new tests added)
- **Components Created**:
  - Approvals CRUD pages (Index, Show)
  - Notifications page and NotificationBell component
  - ApprovalService and NotificationService
  - Approval history timeline in transaction details
  - Dashboard approval widgets
  - Sidebar navigation for Approvals
- **Documentation**: Complete (see docs/07-development/sprint-planning/sprint-05.md)

---

## Sprint 6: Dashboard & Basic Reporting (2 weeks)

### Sprint Goal
Create informative dashboard and basic reporting capabilities.

### User Stories
- **DASH-001**: As a user, I can see summary of cash flow on dashboard
- **DASH-002**: As a user, I can view recent transactions on dashboard
- **DASH-003**: As a user, I can see pending approvals count
- **DASH-004**: As a user, I can generate daily/weekly/monthly reports
- **DASH-005**: As a user, I can export reports to PDF/Excel

### Tasks
- [ ] Design and implement main dashboard layout
- [ ] Create summary widgets (total in/out, balance)
- [ ] Add recent transactions widget
- [ ] Create pending approvals widget
- [ ] Implement charts (Laravel Charts or Chart.js)
- [ ] Build report generation page with filters
- [ ] Implement PDF export (DomPDF)
- [ ] Implement Excel export (Laravel Excel)
- [ ] Add date range selector for reports
- [ ] Create category-wise expense report

### Acceptance Criteria
- Dashboard shows accurate real-time data
- Charts display properly
- Reports can be generated for any date range
- PDF and Excel exports work correctly
- Reports are accurate and formatted well

---

## Sprint 7: Reimbursement Management (2 weeks)

### Sprint Goal
Implement staff reimbursement request and tracking system.

### User Stories
- **REIMB-001**: As a staff member, I can submit reimbursement requests
- **REIMB-002**: As a staff member, I can attach multiple receipts to reimbursement
- **REIMB-003**: As an accountant, I can review and process reimbursements
- **REIMB-004**: As a user, I can track reimbursement payment status
- **REIMB-005**: As an accountant, I can generate reimbursement reports

### Tasks
- [ ] Create reimbursements table migration
- [ ] Build reimbursement request form
- [ ] Implement multiple receipt uploads
- [ ] Create reimbursement review interface
- [ ] Add reimbursement status tracking
- [ ] Implement reimbursement approval workflow
- [ ] Create reimbursement payment recording
- [ ] Build reimbursement list and filter view
- [ ] Add reimbursement reports
- [ ] Write tests for reimbursement flow

### Acceptance Criteria
- Staff can submit reimbursement requests
- Multiple receipts can be attached
- Accountant can process reimbursements
- Payment status is tracked accurately
- Reimbursement reports are accurate

---

## Sprint 8: Vendor Management (1 week)

### Sprint Goal
Add vendor/supplier tracking for better expense management.

### User Stories
- **VEND-001**: As a user, I can add and manage vendor details
- **VEND-002**: As a user, I can link transactions to vendors
- **VEND-003**: As a user, I can view vendor-wise expense reports
- **VEND-004**: As a user, I can see vendor payment history

### Tasks
- [ ] Create vendors table migration
- [ ] Build vendor management CRUD interface
- [ ] Add vendor selection to transaction form
- [ ] Create vendor profile page
- [ ] Implement vendor-wise expense report
- [ ] Add vendor payment history view
- [ ] Create vendor list with search/filter
- [ ] Write tests for vendor management

### Acceptance Criteria
- Vendors can be created and managed
- Transactions can be linked to vendors
- Vendor reports show accurate data
- Payment history is complete

---

## Sprint 9: Audit Trail & Activity Logging (1 week)

### Sprint Goal
Implement comprehensive audit trail for all system activities.

### User Stories
- **AUDIT-001**: As an admin, I can view all system activities
- **AUDIT-002**: As an admin, I can see who created/modified records
- **AUDIT-003**: As an admin, I can filter activity logs by user/date/action
- **AUDIT-004**: As a system, I should maintain soft deletes for data integrity

### Tasks
- [ ] Install and configure Laravel Activity Log
- [ ] Implement activity logging on all models
- [ ] Add soft deletes to all relevant tables
- [ ] Build activity log viewer interface
- [ ] Create activity log filters
- [ ] Add detailed change tracking
- [ ] Implement log retention policy
- [ ] Create audit report generation
- [ ] Write tests for audit logging

### Acceptance Criteria
- All CRUD operations are logged
- Logs show detailed before/after values
- Soft deletes work on all tables
- Activity logs can be filtered and searched
- Audit reports can be generated

---

## Sprint 10: Advanced Reporting & Analytics (2 weeks)

### Sprint Goal
Build comprehensive reporting and analytics features.

### User Stories
- **REP-001**: As a user, I can generate expense breakdown by category
- **REP-002**: As a user, I can view trend analysis with charts
- **REP-003**: As a user, I can create custom reports with date ranges
- **REP-004**: As a user, I can schedule automated report emails
- **REP-005**: As a user, I can generate variance analysis reports

### Tasks
- [ ] Create advanced report builder interface
- [ ] Implement category breakdown reports
- [ ] Add trend analysis with charts
- [ ] Build variance analysis (budget vs actual)
- [ ] Create custom report templates
- [ ] Implement scheduled report emails (Laravel Jobs)
- [ ] Add report comparison features
- [ ] Create financial summary reports
- [ ] Implement data visualization dashboard
- [ ] Write tests for report generation

### Acceptance Criteria
- All report types generate accurate data
- Charts and visualizations display correctly
- Scheduled reports send via email
- Custom reports can be saved as templates
- Variance reports show correct calculations

---

## Sprint 11: Notifications & Alerts (1 week)

### Sprint Goal
Implement comprehensive notification system for key events.

### User Stories
- **NOTIF-001**: As a user, I receive notifications for pending approvals
- **NOTIF-002**: As a user, I receive alerts when budget limits are reached
- **NOTIF-003**: As a user, I receive low balance alerts
- **NOTIF-004**: As a user, I can configure my notification preferences
- **NOTIF-005**: As a user, I can view notification history

### Tasks
- [ ] Create notifications table migration
- [ ] Set up Laravel notification system
- [ ] Create email notification templates
- [ ] Implement in-app notifications
- [ ] Add notification preferences page
- [ ] Create notification center UI
- [ ] Implement real-time notifications (optional: Pusher)
- [ ] Add notification triggers for key events
- [ ] Create notification history view
- [ ] Write tests for notifications

### Acceptance Criteria
- Users receive timely notifications
- Email notifications are formatted properly
- In-app notifications display correctly
- Users can manage notification preferences
- Notification history is maintained

---

## Sprint 12: Security & Performance Optimization (2 weeks)

### Sprint Goal
Enhance security measures and optimize application performance.

### User Stories
- **SEC-001**: As an admin, I can enable two-factor authentication
- **SEC-002**: As a system, I should implement rate limiting
- **SEC-003**: As an admin, I can configure password policies
- **SEC-004**: As a system, I should optimize database queries
- **SEC-005**: As a user, I experience fast page loads

### Tasks
- [ ] Implement two-factor authentication
- [ ] Add rate limiting to sensitive endpoints
- [ ] Configure password policies (strength, expiry)
- [ ] Implement session management
- [ ] Add IP whitelisting feature (optional)
- [ ] Optimize database queries (N+1 problem)
- [ ] Add database indexes
- [ ] Implement caching strategy (Redis)
- [ ] Optimize image loading and storage
- [ ] Run performance testing and optimization
- [ ] Security audit and penetration testing
- [ ] Write security tests

### Acceptance Criteria
- 2FA works for user accounts
- Rate limiting prevents abuse
- Password policies are enforced
- Page load times are under 2 seconds
- No N+1 query issues
- Security vulnerabilities addressed

---

## Sprint 13: Multi-Currency & Recurring Transactions (1 week)

### Sprint Goal
Add support for multiple currencies and recurring transactions.

### User Stories
- **CURR-001**: As a user, I can record transactions in different currencies
- **CURR-002**: As an admin, I can manage exchange rates
- **CURR-003**: As a user, I can set up recurring transactions
- **CURR-004**: As a system, I should automatically create recurring transactions

### Tasks
- [ ] Create currencies table migration
- [ ] Create exchange_rates table migration
- [ ] Add multi-currency support to transactions
- [ ] Build currency management interface
- [ ] Create exchange rate management
- [ ] Add currency conversion logic
- [ ] Create recurring_transactions table
- [ ] Build recurring transaction setup interface
- [ ] Implement scheduled job for recurring transactions
- [ ] Write tests for currency and recurring features

### Acceptance Criteria
- Transactions can be recorded in multiple currencies
- Exchange rates can be managed
- Currency conversion is accurate
- Recurring transactions generate automatically
- Recurring transaction schedule works correctly

---

## Sprint 14: Mobile Responsiveness & UX Polish (1 week)

### Sprint Goal
Ensure mobile responsiveness and polish user experience.

### User Stories
- **UX-001**: As a user, I can access the app smoothly on mobile devices
- **UX-002**: As a user, I can capture receipts using mobile camera
- **UX-003**: As a user, I experience intuitive navigation
- **UX-004**: As a user, I see helpful error messages and validations

### Tasks
- [ ] Test and fix responsive design issues
- [ ] Optimize mobile navigation
- [ ] Implement camera receipt capture for mobile
- [ ] Add loading indicators for all async operations
- [ ] Improve form validation messages
- [ ] Add helpful tooltips and hints
- [ ] Implement progressive web app features (optional)
- [ ] Add keyboard shortcuts for power users
- [ ] Polish UI/UX inconsistencies
- [ ] Cross-browser testing

### Acceptance Criteria
- App works perfectly on mobile devices
- Camera capture works on mobile
- Navigation is intuitive
- No UI/UX inconsistencies
- App works on all major browsers

---

## Sprint 15: Testing, Documentation & Deployment (2 weeks)

### Sprint Goal
Comprehensive testing, complete documentation, and production deployment.

### User Stories
- **TEST-001**: As a developer, I have comprehensive test coverage
- **DOC-001**: As a user, I have access to user documentation
- **DOC-002**: As a developer, I have access to technical documentation
- **DEPLOY-001**: As a team, we can deploy to production smoothly

### Tasks
- [ ] Write missing unit tests (target 80% coverage)
- [ ] Write feature/integration tests
- [ ] Perform end-to-end testing
- [ ] Create user manual/documentation
- [ ] Create API documentation (if applicable)
- [ ] Write deployment documentation
- [ ] Set up production environment
- [ ] Configure production database
- [ ] Set up SSL certificates
- [ ] Configure backup strategy
- [ ] Implement monitoring and logging (Sentry)
- [ ] Create database seeder for production data
- [ ] Perform UAT (User Acceptance Testing)
- [ ] Fix bugs found during UAT
- [ ] Deploy to production
- [ ] Post-deployment verification

### Acceptance Criteria
- Test coverage is at least 80%
- All critical features are tested
- User documentation is complete
- Production deployment is successful
- Monitoring is in place
- Backup strategy is working

---

## Sprint Planning Notes

### Sprint Ceremonies

**Sprint Planning** (Start of each sprint)
- Review and prioritize user stories
- Estimate story points
- Commit to sprint goals
- Assign tasks to team members

**Daily Standup** (Daily, 15 minutes)
- What did you do yesterday?
- What will you do today?
- Any blockers?

**Sprint Review** (End of sprint)
- Demo completed features
- Gather stakeholder feedback
- Update product backlog

**Sprint Retrospective** (End of sprint)
- What went well?
- What could be improved?
- Action items for next sprint

### Definition of Ready
- User story is clearly defined
- Acceptance criteria are documented
- Dependencies are identified
- Story is estimated
- Designs are available (if needed)

### Definition of Done
- Code is written and follows standards
- Unit tests are written and passing
- Feature tests are written and passing
- Code is peer-reviewed
- Documentation is updated
- Feature is deployed to staging
- Product owner has approved

### Estimation Guide
- 1 point: Few hours of work
- 2 points: Half day
- 3 points: Full day
- 5 points: 2-3 days
- 8 points: Full week
- 13 points: Too large, needs breaking down

---

## Risk Management

### Identified Risks
1. **Scope creep** - Mitigation: Strict sprint planning and backlog prioritization
2. **Third-party package issues** - Mitigation: Vet packages carefully, have alternatives
3. **Performance bottlenecks** - Mitigation: Regular performance testing, optimization sprint
4. **Security vulnerabilities** - Mitigation: Security audit, penetration testing
5. **Team availability** - Mitigation: Buffer time in estimates, flexible sprint goals

---

## Success Metrics

- **Sprint Velocity**: Track completed story points per sprint
- **Code Quality**: Maintain test coverage above 80%
- **Bug Rate**: Keep production bugs under 5 per sprint
- **Deployment Frequency**: At least one deployment per sprint
- **Team Satisfaction**: Regular retrospective feedback

---

## Notes
- Each sprint is 1-2 weeks depending on complexity
- Total estimated timeline: 22-26 weeks (5.5-6.5 months)
- Adjust sprint duration based on team size and velocity
- Some sprints can run in parallel if team size permits
- Regular stakeholder demos at end of each sprint
