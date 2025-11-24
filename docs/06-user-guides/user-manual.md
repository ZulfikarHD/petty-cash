# Petty Cash Book Application - User Manual

## Table of Contents

1. [Introduction](#introduction)
2. [Getting Started](#getting-started)
3. [User Roles & Permissions](#user-roles--permissions)
4. [Managing Transactions](#managing-transactions)
5. [Approving Transactions](#approving-transactions)
6. [Using the Dashboard](#using-the-dashboard)
7. [User Management (Admins)](#user-management-admins)
8. [Best Practices](#best-practices)
9. [Troubleshooting](#troubleshooting)
10. [Appendix](#appendix)

---

## 1. Introduction

### 1.1 What is Petty Cash Book?

The Petty Cash Book Application is a web-based system designed to help organizations manage their petty cash transactions efficiently. It replaces traditional paper-based ledgers with a digital solution that provides:

- **Real-time cash tracking** - Know your cash balance at any moment
- **Digital receipts** - Store receipt images securely
- **Approval workflows** - Route transactions for approval
- **Comprehensive reporting** - Generate reports for any period
- **Audit trail** - Track all changes and actions
- **Role-based access** - Control who can do what

### 1.2 Who Should Use This Manual?

This manual is for all users of the Petty Cash Book application:
- **Requesters** - Staff submitting transaction requests
- **Cashiers** - Staff recording daily cash transactions
- **Accountants** - Staff approving transactions and managing budgets
- **Administrators** - Staff managing users and system settings

### 1.3 System Requirements

**Supported Browsers:**
- Google Chrome (recommended)
- Mozilla Firefox
- Safari (macOS/iOS)
- Microsoft Edge

**Device Requirements:**
- Desktop/Laptop: Any modern computer
- Tablet: iPad, Android tablets
- Mobile: iPhone, Android phones
- Internet connection required

### 1.4 Getting Help

If you need assistance:
1. Check the [FAQ](#troubleshooting) section
2. Contact your system administrator
3. Email: support@yourcompany.com
4. Internal helpdesk: ext. XXXX

---

## 2. Getting Started

### 2.1 Accessing the Application

**URL:** `https://pettycash.yourcompany.com`  
Or for development: `http://localhost:8000`

### 2.2 Logging In

1. Open the application URL in your browser
2. Enter your **email address**
3. Enter your **password**
4. (Optional) Check "Remember me" to stay logged in
5. Click **"Login"**

![Login Screen](../11-assets/screenshots/login.png)

**First Time Login:**
- Check your email for account credentials
- You'll be prompted to verify your email
- Change your password after first login

### 2.3 Email Verification

After registration, you must verify your email:

1. Check your inbox for "Verify Email Address" email
2. Click the verification link
3. You'll be redirected to the application
4. You can now access all features

**Didn't receive the email?**
- Check your spam/junk folder
- Click "Resend Verification Email" on the login page
- Contact your administrator if still not received

### 2.4 Resetting Your Password

**Forgot your password?**

1. Click **"Forgot Password?"** on login page
2. Enter your email address
3. Click **"Send Reset Link"**
4. Check your email for reset instructions
5. Click the link in the email (valid for 60 minutes)
6. Enter your new password (twice)
7. Click **"Reset Password"**

### 2.5 Navigating the Interface

**Main Navigation (Left Sidebar):**
- 🏠 **Dashboard** - Summary and quick access
- 💰 **Transactions** - View and manage transactions
- 👥 **Users** - User management (Admins only)
- ⚙️ **Settings** - System settings (Admins only)

**Top Bar:**
- 🔔 **Notifications** - Alerts and messages (coming soon)
- 👤 **Profile Menu** - Your profile and logout

---

## 3. User Roles & Permissions

### 3.1 Understanding Roles

The system has four user roles, each with different permissions:

#### 👑 Administrator (Admin)

**Can do everything:**
- ✅ Create, edit, delete all transactions
- ✅ Approve or reject any transaction
- ✅ Create and manage users
- ✅ Assign roles to users
- ✅ View all reports
- ✅ Configure system settings
- ✅ Restore deleted transactions

**Use case:** IT administrators, senior management

#### 📊 Accountant

**Financial oversight:**
- ✅ View all transactions
- ✅ Approve or reject transactions
- ✅ View and generate reports
- ✅ Manage budgets and categories
- ❌ Cannot create users
- ❌ Cannot change system settings

**Use case:** Finance department staff, accounting team

#### 💰 Cashier

**Day-to-day operations:**
- ✅ Create cash in/out transactions
- ✅ Edit own pending transactions
- ✅ Delete own pending transactions
- ✅ Upload receipts
- ✅ View own transactions
- ❌ Cannot approve transactions
- ❌ Cannot view others' transactions (unless approved)

**Use case:** Front desk staff, petty cash custodians

#### 📝 Requester

**Submit requests:**
- ✅ Create transaction requests
- ✅ Upload receipts
- ✅ View own transactions
- ✅ Edit own pending transactions
- ❌ Cannot approve transactions
- ❌ Cannot view others' transactions

**Use case:** Regular staff members submitting expenses

### 3.2 Checking Your Role

To see your role:
1. Click your **profile icon** (top-right)
2. Your role is displayed under your name
3. Or go to **Profile** page

---

## 4. Managing Transactions

### 4.1 Viewing Transactions

#### Accessing the Transaction List

1. Click **"Transactions"** in the left sidebar
2. You'll see a paginated list of transactions

**What you see:**
- Transaction Number (e.g., TXN-2024-00001)
- Date
- Type (Cash In / Cash Out)
- Amount
- Description
- Status (Pending / Approved / Rejected)
- Creator

**Transaction Status Colors:**
- 🟡 **Yellow badge** - Pending
- 🟢 **Green badge** - Approved  
- 🔴 **Red badge** - Rejected

#### Viewing Transaction Details

1. Click on any transaction in the list
2. View complete transaction information:
   - All fields
   - Creator information
   - Approver (if approved)
   - Rejection reason (if rejected)
   - Attached receipts
   - Timestamps

### 4.2 Creating a Transaction

#### Step-by-Step: Creating a Cash Out Transaction

1. Go to **Transactions** → Click **"New Transaction"** (top-right)

2. **Select Type:**
   - Choose **"Cash Out"** for expenses
   - Choose **"Cash In"** for receipts

3. **Enter Amount:**
   - Enter the transaction amount
   - Must be a positive number
   - Example: `150.00`

4. **Enter Description:**
   - Describe what the transaction is for
   - Be specific for easier searching later
   - Example: "Office supplies - printer paper and pens"

5. **Select Date:**
   - Click the date field
   - Choose the transaction date
   - Cannot be in the future

6. **Add Notes (Optional):**
   - Add any additional information
   - Example: "Urgent purchase for client meeting"

7. **Upload Receipt (Recommended):**
   - Click **"Upload Receipt"** or drag & drop
   - Accepted formats: JPG, PNG, GIF, PDF
   - Maximum size: 5MB per file
   - You'll see a preview after upload

8. **Click "Create Transaction"**

**Result:**
- ✅ Transaction is created with status "Pending"
- 🔢 Transaction number is auto-generated (e.g., TXN-2024-00001)
- 📧 Approvers are notified (if notifications enabled)

#### Step-by-Step: Creating a Cash In Transaction

Same steps as above, but:
1. Select **Type: "Cash In"**
2. Common examples:
   - Petty cash replenishment
   - Refund received
   - Cash deposit
   - Reimbursement received

### 4.3 Editing a Transaction

**Who can edit:**
- Creator of the transaction (if pending)
- Administrators (any transaction)

**Steps:**
1. Go to **Transactions**
2. Click on the transaction you want to edit
3. Click **"Edit"** button
4. Modify any fields (except transaction number)
5. Click **"Update Transaction"**

**Important Notes:**
- ⚠️ Can only edit **Pending** transactions
- ⚠️ Once approved/rejected, transactions cannot be edited
- ⚠️ Transaction number never changes
- ⚠️ Changes are logged for audit purposes (future)

### 4.4 Deleting a Transaction

**Who can delete:**
- Creator (pending transactions only)
- Administrators (any pending transaction)

**Steps:**
1. Go to **Transactions**
2. Click on the transaction
3. Click **"Delete"** button
4. Confirm deletion in the popup dialog
5. Click **"Yes, Delete"**

**Important Notes:**
- ⚠️ Can only delete **Pending** transactions
- ⚠️ Deletion is soft delete (can be restored by admin)
- ⚠️ Cannot be undone by regular users
- ⚠️ All associated receipts are also deleted

### 4.5 Uploading Receipts

#### Supported File Types
- **Images:** JPEG (.jpg, .jpeg), PNG (.png), GIF (.gif)
- **Documents:** PDF (.pdf)

#### Size Limits
- Maximum: 5MB per file
- Currently: 1 receipt per transaction (multiple coming soon)

#### Upload Methods

**Method 1: Click to Upload**
1. Click **"Upload Receipt"** button
2. Browse your computer
3. Select the file
4. Click **"Open"**

**Method 2: Drag and Drop**
1. Drag the file from your computer
2. Drop it onto the upload area
3. File uploads automatically

**Method 3: Mobile Camera** (on mobile devices)
1. Tap **"Upload Receipt"**
2. Choose **"Take Photo"**
3. Take a picture of the receipt
4. Confirm and upload

#### Viewing Receipts
- Click the 📎 receipt icon in transaction list
- Or view in transaction details page
- Click image to view full size
- Click download icon to download

#### Deleting Receipts
1. Go to transaction details
2. Click ❌ on the receipt thumbnail
3. Confirm deletion

### 4.6 Searching Transactions

**Quick Search:**
1. Use the search box at the top of transaction list
2. Type to search by:
   - Transaction number (e.g., TXN-2024-00001)
   - Description keywords (e.g., "office supplies")
3. Press Enter or click 🔍 Search button

**Search Tips:**
- Use specific keywords for better results
- Transaction numbers are exact match
- Description search is case-insensitive
- Partial matches work (e.g., "TXN-2024" finds all 2024 transactions)

### 4.7 Filtering Transactions

Use filters to narrow down the transaction list:

#### By Type
- **All Types** - Shows everything
- **Cash In** - Only incoming cash
- **Cash Out** - Only outgoing cash

#### By Status
- **All Status** - Shows everything
- **Pending** - Awaiting approval
- **Approved** - Approved transactions
- **Rejected** - Rejected transactions

#### By Date Range
1. Click **"Start Date"** - select beginning date
2. Click **"End Date"** - select ending date
3. Click **"Search"** to apply

**Quick Date Filters:** (coming soon)
- Today
- This Week
- This Month
- Last Month
- Custom Range

#### Combining Filters
You can use multiple filters together:
- Example: Cash Out + Approved + January 2024
- All filters work together (AND logic)

#### Clearing Filters
Click **"Clear Filters"** to reset all filters

### 4.8 Understanding Transaction Summaries

At the top of the transaction list, you'll see summary cards:

#### 💵 Total Cash In
- Sum of all **approved** Cash In transactions
- For the filtered period/type
- Updates in real-time

#### 💸 Total Cash Out
- Sum of all **approved** Cash Out transactions
- For the filtered period/type
- Updates in real-time

#### 💰 Net Balance
- Cash In minus Cash Out
- **Green** = positive balance
- **Red** = negative balance (overspent)
- Updates based on filters applied

**Important:** Summaries only count **approved** transactions!

---

## 5. Approving Transactions

*This section is for Accountants and Administrators only.*

### 5.1 Viewing Pending Approvals

**From Dashboard:**
1. Go to **Dashboard**
2. See **"Pending Approvals"** widget
3. Shows count and list of pending transactions
4. Click any transaction to review

**From Transactions:**
1. Go to **Transactions**
2. Filter by **Status: Pending**
3. Review transactions in the list

### 5.2 Reviewing a Transaction

Before approving or rejecting:

1. **Check all details:**
   - ✅ Amount is reasonable
   - ✅ Description is clear
   - ✅ Date is correct
   - ✅ Receipt is attached (if required)

2. **View the receipt:**
   - Click to enlarge
   - Verify it matches the description
   - Check the amount on receipt

3. **Check budget:** (coming soon)
   - Verify budget is available
   - Category is correct

### 5.3 Approving a Transaction

**Steps:**
1. Open the transaction details
2. Review all information
3. Click **"Approve"** button (green)
4. Confirm approval in popup
5. Click **"Yes, Approve"**

**What happens:**
- ✅ Status changes to "Approved"
- ✅ Your name recorded as approver
- ✅ Timestamp recorded
- ✅ Balance is updated
- ✅ Creator is notified (if enabled)
- ✅ Transaction is locked (cannot be edited)

### 5.4 Rejecting a Transaction

**Steps:**
1. Open the transaction details
2. Review all information
3. Click **"Reject"** button (red)
4. **Enter rejection reason** (required)
   - Be specific and clear
   - Help the user understand what's wrong
   - Example: "Receipt is not legible. Please upload a clearer image."
5. Click **"Yes, Reject"**

**What happens:**
- ❌ Status changes to "Rejected"
- ❌ Your name recorded as rejector
- ❌ Timestamp recorded
- ❌ Balance is NOT updated
- ❌ Creator is notified with reason
- ✅ Transaction can be edited and resubmitted

### 5.5 Bulk Approval (Coming Soon)

Future feature:
- Select multiple transactions
- Approve or reject in batch
- Faster processing for large volumes

---

## 6. Using the Dashboard

### 6.1 Dashboard Overview

The Dashboard is your home page after login.

**Key Sections:**
1. Summary Cards (top)
2. Recent Transactions (left)
3. Pending Approvals (right, if applicable)
4. Quick Actions (buttons)

### 6.2 Summary Cards

**Cards show:**
- 💵 **Total Cash In** - All approved incoming cash
- 💸 **Total Cash Out** - All approved outgoing cash
- 💰 **Net Balance** - Current cash position

**Period shown:**
- Current month by default
- Changes based on date selection (coming soon)

### 6.3 Recent Transactions

**Shows:**
- Your 5 most recent transactions
- Transaction number, date, type, amount, status
- Quick view of recent activity

**Actions:**
- Click any transaction to view details
- Click "View All" to go to full transaction list

### 6.4 Pending Approvals Widget

*For Accountants and Administrators only*

**Shows:**
- Count of transactions awaiting approval
- List of pending transactions
- Sorted by date (oldest first)

**Actions:**
- Click transaction to review
- Approve or reject directly

### 6.5 Quick Actions

Quick buttons for common tasks:
- **New Transaction** - Create a transaction
- **View All Transactions** - Go to transaction list
- **Generate Report** - Create a report (coming soon)

---

## 7. User Management (Admins)

*This section is for Administrators only.*

### 7.1 Viewing Users

1. Click **"Users"** in the sidebar
2. See list of all users:
   - Name
   - Email
   - Role
   - Status (Active/Inactive)
   - Last Login

### 7.2 Creating a User

**Steps:**
1. Go to **Users** → Click **"New User"**
2. Enter **Name** (full name)
3. Enter **Email** (must be unique)
4. Enter **Password** (temporary, user should change it)
5. Select **Role** (Admin, Accountant, Cashier, Requester)
6. Click **"Create User"**

**What happens:**
- ✅ User account is created
- ✅ Email is sent with login credentials
- ✅ User can log in immediately
- ✅ Email verification is bypassed (admin-created users)

### 7.3 Editing a User

**Steps:**
1. Go to **Users**
2. Click on the user you want to edit
3. Click **"Edit"**
4. Modify:
   - Name
   - Email
   - Role
5. Click **"Update User"**

**Restrictions:**
- ⚠️ Cannot edit your own role
- ⚠️ Cannot remove your own admin access
- ⚠️ Email must remain unique

### 7.4 Deleting a User

**Steps:**
1. Go to **Users**
2. Click on the user
3. Click **"Delete"** button
4. Confirm deletion

**Important:**
- ⚠️ Cannot delete yourself
- ⚠️ Soft delete (can be restored)
- ⚠️ User cannot log in after deletion
- ⚠️ User's transactions remain in system

### 7.5 Assigning Roles

When creating or editing a user:
1. Select from dropdown:
   - **Admin** - Full access
   - **Accountant** - Approve and report
   - **Cashier** - Record transactions
   - **Requester** - Submit requests
2. Role takes effect immediately

**Best Practices:**
- Give minimum required permissions
- Regular staff = Requester
- Petty cash custodians = Cashier
- Finance team = Accountant
- IT/Management = Admin

---

## 8. Best Practices

### 8.1 For All Users

**Security:**
- ✅ Use a strong, unique password
- ✅ Never share your login credentials
- ✅ Log out when done, especially on shared computers
- ✅ Report suspicious activity immediately
- ✅ Keep your email address current

**Data Entry:**
- ✅ Enter transactions promptly (same day if possible)
- ✅ Use clear, descriptive descriptions
- ✅ Always attach receipts
- ✅ Double-check amounts before saving
- ✅ Select correct transaction type (In/Out)

### 8.2 For Cashiers

**Daily Routine:**
1. **Morning:** Count opening cash balance
2. **During Day:** Record all transactions as they occur
3. **End of Day:** Count closing balance, reconcile
4. **Upload Receipts:** Scan and attach all receipts

**Receipt Management:**
- Take clear, legible photos
- Include entire receipt (top to bottom)
- Good lighting, avoid shadows
- Store paper receipts in file after uploading

**Accuracy:**
- Count cash carefully
- Record correct amounts
- Use proper transaction dates
- Review before submitting

### 8.3 For Approvers

**Review Checklist:**
- ✅ Amount is reasonable and justified
- ✅ Description clearly explains purpose
- ✅ Receipt is attached and legible
- ✅ Receipt amount matches transaction
- ✅ Date is appropriate
- ✅ Budget is available (if applicable)
- ✅ Follows company policies

**Rejection Reasons:**
- Provide specific feedback
- Explain what needs to be corrected
- Be professional and helpful
- Examples:
  - ❌ "No" - too vague
  - ✅ "Receipt image is too dark to read. Please re-upload."

**Timely Approval:**
- Review pending transactions daily
- Don't let requests pile up
- Approve within 24-48 hours when possible

### 8.4 For Administrators

**User Management:**
- Review user list quarterly
- Deactivate users who have left
- Update roles when responsibilities change
- Monitor for security issues

**System Maintenance:**
- Regular backups (automated)
- Review audit logs periodically
- Monitor system performance
- Update documentation as system evolves

---

## 9. Troubleshooting

### 9.1 Login Issues

**Problem: Cannot log in**

Possible solutions:
1. **Check password** - Try resetting password
2. **Check email** - Ensure correct email address
3. **Clear browser cache** - Ctrl+Shift+Delete
4. **Try different browser** - Chrome, Firefox, etc.
5. **Check Caps Lock** - Password is case-sensitive
6. **Contact admin** - Account may be deactivated

**Problem: Email not verified**

Solutions:
1. Check spam/junk folder
2. Click "Resend Verification Email"
3. Contact administrator if still not received

### 9.2 Transaction Issues

**Problem: Cannot create transaction**

Possible causes:
1. **Missing required fields** - Check all red fields
2. **No permission** - Check your role
3. **Invalid amount** - Must be positive number
4. **Future date** - Date cannot be in future

**Problem: Cannot edit transaction**

Possible causes:
1. **Already approved/rejected** - Cannot edit after approval
2. **Not yours** - Can only edit own transactions
3. **No permission** - Check your role

**Problem: Cannot upload receipt**

Possible causes:
1. **File too large** - Max 5MB per file
2. **Wrong format** - Only JPG, PNG, GIF, PDF
3. **Browser issue** - Try different browser
4. **Connection issue** - Check internet

### 9.3 Performance Issues

**Problem: Slow page load**

Solutions:
1. **Clear browser cache** - Free up memory
2. **Close other tabs** - Reduce browser load
3. **Check internet** - Speed test
4. **Try different browser** - Some are faster
5. **Contact IT** - May be server issue

**Problem: Page not loading**

Solutions:
1. **Refresh page** - F5 or Ctrl+R
2. **Check internet** - Verify connection
3. **Check URL** - Ensure correct address
4. **Try incognito mode** - Rule out extensions
5. **Contact IT** - Server may be down

### 9.4 Receipt Upload Issues

**Problem: Image preview not showing**

Solutions:
1. **Wait** - Large files take time
2. **Refresh page** - Reload the page
3. **Check file** - Ensure it's valid image
4. **Try smaller file** - Compress image

**Problem: Poor image quality**

Solutions:
1. **Better lighting** - Use flash or natural light
2. **Hold steady** - Avoid blurry images
3. **Closer shot** - Get closer to receipt
4. **Scan instead** - Use scanner for best quality

### 9.5 Getting Additional Help

If you cannot resolve an issue:

1. **Take a screenshot** - Shows the problem
2. **Note error messages** - Write them down
3. **Note what you were doing** - Steps to reproduce
4. **Contact support:**
   - Email: support@yourcompany.com
   - Phone: ext. XXXX
   - Internal ticket system

**Include in your support request:**
- Your name and email
- Your role
- Description of problem
- What you've tried
- Screenshot (if applicable)
- Browser and device info

---

## 10. Appendix

### 10.1 Glossary

**Terms used in the application:**

| Term | Definition |
|------|------------|
| **Transaction** | A record of money coming in or going out of petty cash |
| **Cash In** | Money added to petty cash (receipts, replenishment) |
| **Cash Out** | Money spent from petty cash (expenses) |
| **Transaction Number** | Unique identifier for each transaction (e.g., TXN-2024-00001) |
| **Status** | Current state of transaction (Pending, Approved, Rejected) |
| **Pending** | Transaction created but not yet approved |
| **Approved** | Transaction reviewed and approved by authorized user |
| **Rejected** | Transaction reviewed and rejected, with reason given |
| **Receipt** | Proof of transaction (image or PDF attachment) |
| **Approver** | User with permission to approve/reject transactions |
| **Soft Delete** | Deletion that keeps record in system but hides it |
| **Audit Trail** | Record of all actions taken in the system |

### 10.2 Keyboard Shortcuts

*Coming in future version*

| Shortcut | Action |
|----------|--------|
| `Ctrl/Cmd + N` | New transaction |
| `Ctrl/Cmd + K` | Search |
| `/` | Focus search box |
| `Esc` | Close dialog |
| `Ctrl/Cmd + S` | Save form |

### 10.3 Transaction Status Workflow

```
┌─────────────┐
│   PENDING   │ ← Transaction created
└──────┬──────┘
       │
       ├─ Approve → ┌──────────┐
       │             │ APPROVED │ (Final)
       │             └──────────┘
       │
       └─ Reject → ┌──────────┐
                   │ REJECTED │ (Can edit & resubmit)
                   └──────────┘
```

### 10.4 File Format Reference

**Accepted Receipt Formats:**

| Format | Extension | Max Size | Notes |
|--------|-----------|----------|-------|
| JPEG | .jpg, .jpeg | 5MB | Most common, good compression |
| PNG | .png | 5MB | Best for screenshots |
| GIF | .gif | 5MB | Animated images allowed |
| PDF | .pdf | 5MB | Multi-page documents supported |

### 10.5 Browser Compatibility

| Browser | Version | Status |
|---------|---------|--------|
| Chrome | Latest 3 versions | ✅ Fully Supported |
| Firefox | Latest 3 versions | ✅ Fully Supported |
| Safari | Latest 2 versions | ✅ Fully Supported |
| Edge | Latest 3 versions | ✅ Fully Supported |
| IE 11 | - | ❌ Not Supported |

### 10.6 Mobile Usage Tips

**Best Practices for Mobile:**
- Portrait mode recommended
- Use camera to capture receipts directly
- Touch gestures work on all interactive elements
- Swipe to scroll through transactions
- Pinch to zoom on receipt images

**Mobile-Specific Features:**
- Camera access for receipt capture
- Responsive design adapts to screen size
- Touch-friendly buttons (larger tap targets)
- Mobile-optimized forms

### 10.7 Security Guidelines

**Password Requirements:**
- Minimum 8 characters
- Mix of uppercase and lowercase
- Include numbers and symbols (recommended)
- Don't use common words or patterns
- Change every 90 days (recommended)

**Data Protection:**
- All data encrypted in transit (HTTPS)
- Passwords hashed (never stored as plain text)
- Session timeout after 2 hours of inactivity
- Failed login attempts logged
- Receipts stored securely

### 10.8 Compliance & Audit

**Record Retention:**
- All transactions retained for 7 years
- Soft-deleted items retained for 1 year
- Audit logs retained for 3 years
- Receipts archived with transactions

**Audit Trail Includes:**
- Who created/modified record
- When action occurred
- What was changed
- IP address (for security)

### 10.9 Support Resources

**Documentation:**
- Quick Start Guide
- Video tutorials (coming soon)
- FAQ section
- Release notes

**Training:**
- New user orientation
- Role-specific training sessions
- Refresher courses available
- One-on-one support available

**Contact Information:**
- **Technical Support:** support@yourcompany.com
- **Admin Contact:** [Name], admin@yourcompany.com
- **Help Desk:** ext. XXXX
- **Emergency:** [After-hours contact]

### 10.10 Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | Nov 2024 | Initial release with core features |
| 0.2 | Nov 2024 | Sprint 2 - Transaction management |
| 0.1 | Nov 2024 | Sprint 1 - Authentication & users |

### 10.11 Upcoming Features

**Planned in Future Releases:**

**Q1 2025:**
- Categories and budget tracking
- Cash balance reconciliation
- Approval workflows
- Enhanced reporting

**Q2 2025:**
- Mobile apps (iOS & Android)
- Multi-currency support
- Recurring transactions
- Advanced analytics

**Q3 2025:**
- API for integrations
- Accounting software integration
- Bulk operations
- Custom fields

---

## Quick Reference Card

### Common Tasks

| Task | Steps |
|------|-------|
| **Create Transaction** | Transactions → New Transaction → Fill form → Create |
| **View Details** | Transactions → Click transaction |
| **Upload Receipt** | Create/Edit transaction → Upload Receipt → Choose file |
| **Search** | Transactions → Type in search box → Enter |
| **Filter** | Transactions → Select filters → Search |
| **Approve** | Click transaction → Review → Approve |
| **Reject** | Click transaction → Review → Reject → Enter reason |

### Status Colors

- 🟡 Yellow = Pending
- 🟢 Green = Approved
- 🔴 Red = Rejected

### Need Help?

📧 support@yourcompany.com  
📞 ext. XXXX  
💬 Internal helpdesk

---

**Document Version:** 1.0  
**Last Updated:** November 24, 2024  
**For Application Version:** 0.2.0

**© 2024 Your Company. All rights reserved.**

