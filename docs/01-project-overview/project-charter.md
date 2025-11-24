# Project Charter - Petty Cash Book Application

## Executive Summary

The Petty Cash Book Application is a comprehensive web-based system designed to digitize and streamline the management of petty cash transactions in organizations. The system provides role-based access control, approval workflows, receipt management, and detailed reporting capabilities.

## Project Information

| Attribute | Value |
|-----------|-------|
| **Project Name** | Petty Cash Book Application |
| **Project Code** | PCB-2024 |
| **Version** | 1.0.0 (In Development) |
| **Start Date** | October 27, 2024 |
| **Target Launch** | February 2025 |
| **Current Phase** | Sprint 2 (Completed) |
| **Status** | 🟢 On Track |

## Business Case

### Problem Statement

Many organizations still manage petty cash using manual processes:
- **Paper-based ledgers** prone to errors and loss
- **Lack of real-time visibility** into cash flow
- **Manual approval processes** causing delays
- **Difficult reconciliation** at month-end
- **No audit trail** for compliance
- **Receipt management** is cumbersome and disorganized
- **Reporting** requires manual compilation

### Proposed Solution

A modern web application that:
- Digitizes all petty cash transactions
- Provides real-time cash balance tracking
- Automates approval workflows
- Manages receipt attachments digitally
- Generates comprehensive reports
- Maintains complete audit trail
- Supports role-based access control

### Expected Benefits

**Financial Benefits:**
- Reduce cash handling errors by 90%
- Decrease reconciliation time by 75%
- Minimize cash discrepancies
- Better budget control and forecasting

**Operational Benefits:**
- Faster approval processes
- Real-time visibility into cash position
- Automated transaction numbering
- Digital receipt storage
- Instant reporting and analytics

**Compliance Benefits:**
- Complete audit trail
- Document retention
- Access control and security
- Regulatory compliance support

## Project Objectives

### Primary Objectives

1. **Digitize Cash Transactions**
   - Record all cash in/out transactions digitally
   - Auto-generate unique transaction numbers
   - Support receipt attachments

2. **Implement Access Control**
   - Define user roles (Admin, Accountant, Cashier, Requester)
   - Permission-based feature access
   - Secure authentication

3. **Enable Approval Workflows**
   - Multi-level approval process
   - Email notifications
   - Approval history tracking

4. **Provide Real-Time Reporting**
   - Cash balance dashboard
   - Transaction history
   - Budget tracking
   - Customizable reports

### Secondary Objectives

- Support multiple currencies (future)
- Mobile-responsive design
- Recurring transaction templates
- Integration with accounting systems (future)

## Scope

### In Scope

#### Phase 1 (Sprint 0-5)
- ✅ User authentication and authorization
- ✅ Transaction management (CRUD)
- ✅ Receipt upload and management
- 🔄 Categories and budgets
- 🔄 Cash balance tracking
- 🔄 Approval workflows

#### Phase 2 (Sprint 6-10)
- Dashboard and basic reporting
- Reimbursement management
- Vendor management
- Audit trail
- Advanced reporting

#### Phase 3 (Sprint 11-15)
- Notifications system
- Security hardening
- Performance optimization
- Multi-currency support
- Mobile optimization
- Production deployment

### Out of Scope (Current Version)

- Integration with external accounting software
- Mobile native applications (iOS/Android)
- Payroll integration
- Inventory management
- Purchase order system
- Multi-company/multi-branch support
- Cryptocurrency payments

## Stakeholders

### Primary Stakeholders

| Role | Name/Department | Interest | Influence |
|------|----------------|----------|-----------|
| **Project Sponsor** | [Name] / Finance Department | High | High |
| **Product Owner** | [Name] / IT Department | High | High |
| **End Users** | Cashiers, Accountants | High | Medium |

### Secondary Stakeholders

| Role | Interest | Involvement |
|------|----------|-------------|
| **CFO** | Budget control, compliance | Quarterly reviews |
| **IT Team** | System maintenance | Deployment, support |
| **Audit Team** | Compliance, controls | Audit trail review |
| **Management** | Oversight, reporting | Monthly reports |

## Project Organization

### Team Structure

```
Project Sponsor
       |
   Product Owner
       |
   Scrum Master
       |
  Development Team
    /    |    \
  Dev   Dev   QA
```

### Roles and Responsibilities

**Project Sponsor**
- Provide funding and resources
- Remove organizational impediments
- Final approval on major decisions

**Product Owner**
- Define and prioritize backlog
- Accept completed work
- Stakeholder communication

**Scrum Master**
- Facilitate scrum ceremonies
- Remove team impediments
- Coach team on agile practices

**Development Team**
- Design and build features
- Write tests
- Deploy to environments

**QA**
- Test features
- Report bugs
- User acceptance testing

## Success Criteria

### Quantitative Metrics

| Metric | Target | Current | Status |
|--------|--------|---------|--------|
| **Test Coverage** | ≥ 80% | ~85% | ✅ Achieved |
| **Page Load Time** | < 2s | TBD | 🟡 Testing |
| **Uptime** | ≥ 99.5% | N/A | 🔵 Not Live |
| **User Adoption** | ≥ 90% | N/A | 🔵 Not Live |
| **Error Rate** | < 1% | 0% | ✅ On Track |

### Qualitative Criteria

- ✅ User-friendly interface (intuitive navigation)
- ✅ Mobile-responsive design
- ✅ Comprehensive documentation
- 🔄 Positive user feedback (pending UAT)
- 🔄 Stakeholder satisfaction (pending demo)

## Assumptions

1. Users have basic computer literacy
2. Internet connectivity is available
3. Organization has MySQL/MariaDB database access
4. Modern web browsers are used (Chrome, Firefox, Safari, Edge)
5. Email server is available for notifications
6. File storage is adequate for receipt images
7. Existing cash processes will be documented
8. Users will attend training sessions

## Constraints

### Technical Constraints
- Must use PHP 8.4+ and Laravel 12
- Must use existing MySQL database
- Must integrate with organization's email system
- Must work on existing server infrastructure

### Business Constraints
- Budget: [Amount]
- Timeline: 6 months (15 sprints)
- Resources: 3 developers, 1 QA, 1 Product Owner

### Regulatory Constraints
- Must comply with financial record-keeping regulations
- Must maintain audit trail for 7 years
- Must implement access controls per security policy

## Risks and Mitigation

| Risk | Probability | Impact | Mitigation Strategy |
|------|------------|--------|---------------------|
| **Scope Creep** | High | High | Strict change control, prioritized backlog |
| **User Resistance** | Medium | High | Training program, change management |
| **Data Migration Issues** | Medium | Medium | Thorough testing, phased rollout |
| **Technical Complexity** | Low | Medium | Use proven technologies, code reviews |
| **Resource Availability** | Medium | Medium | Buffer in estimates, cross-training |
| **Security Vulnerabilities** | Low | High | Security audit, penetration testing |

## Project Timeline

### High-Level Milestones

| Milestone | Target Date | Status |
|-----------|-------------|--------|
| **Sprint 0: Project Setup** | Week 1 | ✅ Completed |
| **Sprint 1: Auth & Users** | Week 2-3 | ✅ Completed |
| **Sprint 2: Transactions** | Week 4-5 | ✅ Completed |
| **Sprint 3: Categories** | Week 6-7 | 🔵 Upcoming |
| **Sprint 4: Cash Balance** | Week 8-9 | 🔵 Planned |
| **Sprint 5: Approvals** | Week 10-11 | 🔵 Planned |
| **Sprint 6-10: Features** | Week 12-21 | 🔵 Planned |
| **Sprint 11-14: Polish** | Week 22-29 | 🔵 Planned |
| **Sprint 15: Deployment** | Week 30 | 🔵 Planned |
| **UAT** | Week 31-32 | 🔵 Planned |
| **Go-Live** | Week 33 | 🔵 Target |

### Current Progress

- **Completed**: 3 sprints (Sprint 0, 1, 2)
- **Progress**: 20% complete
- **On Schedule**: Yes
- **Within Budget**: Yes

## Budget

| Category | Allocated | Spent | Remaining |
|----------|-----------|-------|-----------|
| **Development** | [Amount] | [Amount] | [Amount] |
| **Infrastructure** | [Amount] | [Amount] | [Amount] |
| **Training** | [Amount] | [Amount] | [Amount] |
| **Contingency** | [Amount] | [Amount] | [Amount] |
| **Total** | [Total] | [Total] | [Total] |

## Communication Plan

| Communication | Frequency | Audience | Owner |
|---------------|-----------|----------|-------|
| **Daily Standup** | Daily | Dev Team | Scrum Master |
| **Sprint Planning** | Every 2 weeks | Team | Product Owner |
| **Sprint Review** | Every 2 weeks | Stakeholders | Product Owner |
| **Sprint Retro** | Every 2 weeks | Team | Scrum Master |
| **Status Report** | Weekly | Sponsor | Product Owner |
| **Stakeholder Demo** | Monthly | All | Product Owner |

## Approval

This project charter is approved by:

| Role | Name | Signature | Date |
|------|------|-----------|------|
| **Project Sponsor** | [Name] | _________ | ______ |
| **Product Owner** | [Name] | _________ | ______ |
| **Scrum Master** | [Name] | _________ | ______ |
| **IT Director** | [Name] | _________ | ______ |

## Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2024-10-27 | [Name] | Initial charter |
| 1.1 | 2024-11-10 | [Name] | Updated progress after Sprint 1 |
| 1.2 | 2024-11-24 | [Name] | Updated progress after Sprint 2 |

---

**Document Status**: Active  
**Next Review Date**: December 15, 2024  
**Document Owner**: Product Owner
