# Quick Start Guide

Welcome to the Petty Cash Book Application! This guide will help you get started quickly.

## First Time Login

### 1. Access the Application

Open your web browser and navigate to:
```
https://pettycash.yourcompany.com
```

Or for development:
```
http://localhost:8000
```

### 2. Log In

Use the credentials provided by your administrator:

- **Email**: your.email@company.com
- **Password**: [provided by admin]

> 💡 **Tip**: You will be prompted to verify your email on first login.

### 3. Verify Your Email

1. Check your email inbox for verification link
2. Click the verification link
3. You'll be redirected back to the application

### 4. Change Your Password (Recommended)

1. Click your profile icon in the top-right
2. Select **"Profile"**
3. Click **"Change Password"**
4. Enter current and new password
5. Click **"Update Password"**

## Understanding Your Role

Your access level depends on your assigned role:

| Role | What You Can Do |
|------|----------------|
| 👑 **Admin** | Full access - manage users, approve transactions, view all reports |
| 📊 **Accountant** | Approve transactions, manage budgets, view reports |
| 💰 **Cashier** | Create transactions, record cash in/out, manage daily operations |
| 📝 **Requester** | Submit transaction requests, view own transactions |

> ℹ️ Check your role by hovering over your name in the top-right corner.

## Creating Your First Transaction

### For Cashiers

#### Recording a Cash Out Transaction

1. Click **"Transactions"** in the left sidebar
2. Click **"New Transaction"** button (top-right)
3. Fill in the form:
   - **Type**: Select "Cash Out"
   - **Amount**: Enter the amount (e.g., 150.00)
   - **Description**: Describe the expense (e.g., "Office supplies purchase")
   - **Date**: Select transaction date
   - **Notes**: (Optional) Add any additional notes
4. Upload receipt (optional but recommended):
   - Click "Upload Receipt" or drag & drop
   - Accepts: JPG, PNG, GIF, PDF
   - Max size: 5MB per file
5. Click **"Create Transaction"**

✅ Success! Your transaction is created with status "Pending"

#### Recording a Cash In Transaction

1. Click **"Transactions"** → **"New Transaction"**
2. Select **Type**: "Cash In"
3. Fill in amount and description (e.g., "Petty cash replenishment")
4. Select date
5. Click **"Create Transaction"**

### For Requesters

1. Click **"Transactions"** → **"New Transaction"**
2. Fill in transaction details
3. Upload receipt/proof
4. Click **"Create Transaction"**
5. Your request will be sent for approval
6. You'll receive an email when it's approved/rejected

## Viewing Transactions

### Transaction List

1. Click **"Transactions"** in the sidebar
2. You'll see a list of all transactions (based on your permissions)

Each transaction shows:
- Transaction Number (e.g., TXN-2024-00001)
- Date
- Type (In/Out)
- Amount
- Status (Pending/Approved/Rejected)
- Who created it

### Filtering Transactions

Use the filters at the top to find specific transactions:

**Search**: Type transaction number or description

**Type Filter**: 
- All Types
- Cash In
- Cash Out

**Status Filter**:
- All Status
- Pending
- Approved
- Rejected

**Date Range**:
- Click "Start Date" and "End Date" to filter by date range

Click **"Search"** to apply filters.

### Viewing Transaction Details

Click on any transaction to see full details:
- All transaction information
- Attached receipts (click to view/download)
- Who approved it (if approved)
- Rejection reason (if rejected)
- Edit/Delete buttons (if allowed)

## Dashboard Overview

Click **"Dashboard"** in the sidebar to see:

### Summary Cards

- 💵 **Total Cash In**: Sum of all approved cash in transactions
- 💸 **Total Cash Out**: Sum of all approved cash out transactions
- 💰 **Net Balance**: Cash In minus Cash Out

### Recent Transactions

- Shows your 5 most recent transactions
- Quick access to transaction details

### Pending Approvals (Accountants/Admins only)

- List of transactions waiting for approval
- Click to review and approve/reject

## Common Tasks

### Editing a Transaction

> ⚠️ You can only edit **pending** transactions that you created.

1. Go to **Transactions**
2. Click on the transaction you want to edit
3. Click **"Edit"** button
4. Make your changes
5. Click **"Update Transaction"**

### Deleting a Transaction

> ⚠️ You can only delete **pending** transactions.

1. Go to **Transactions**
2. Click on the transaction
3. Click **"Delete"** button
4. Confirm deletion in the popup
5. Transaction is soft-deleted (can be restored by admin)

### Approving a Transaction (Accountants/Admins)

1. Go to **Dashboard** → **Pending Approvals**
   OR go to **Transactions** and filter by "Pending"
2. Click on the transaction to review
3. Review the details and receipt
4. Click **"Approve"** or **"Reject"**
5. If rejecting, enter a reason
6. Click **"Confirm"**
7. User will be notified via email (future feature)

## Tips for Success

### 💡 Best Practices

1. **Always attach receipts** - Makes approval faster and provides proof
2. **Use clear descriptions** - Makes searching easier later
3. **Record transactions daily** - Don't let them pile up
4. **Check your dashboard** - Stay updated on your cash position
5. **Review before submitting** - Ensure all details are correct

### ⚡ Keyboard Shortcuts (Future)

- `Ctrl/Cmd + N` - New transaction
- `Ctrl/Cmd + K` - Search
- `/` - Focus search box

### 📱 Mobile Use

The application is mobile-responsive! You can:
- Record transactions on your phone
- Take photos of receipts directly
- Approve transactions on the go

Simply open the app URL in your mobile browser.

## Getting Help

### Within the App

- Hover over ℹ️ icons for helpful tips
- Check field validation messages for guidance
- Use the search function to find past transactions

### Need More Help?

1. **Check the FAQ**: [Link to FAQ](faq.md)
2. **User Manual**: [Full User Manual](user-manual.md)
3. **Troubleshooting**: [Troubleshooting Guide](troubleshooting.md)
4. **Contact Support**: support@yourcompany.com

## What's Next?

Now that you know the basics:

1. ✅ Create a few test transactions
2. ✅ Practice filtering and searching
3. ✅ Upload some receipts
4. ✅ Explore the dashboard
5. ✅ Read the [Full User Manual](user-manual.md) for advanced features

## Common Questions

**Q: Why can't I edit my transaction?**  
A: Only pending transactions can be edited. Once approved/rejected, they're locked.

**Q: How do I know my transaction is approved?**  
A: Check the status badge - it will show "Approved" in green. You'll also receive an email (coming soon).

**Q: Can I delete a transaction after it's approved?**  
A: No. Contact an administrator if you need to reverse an approved transaction.

**Q: What file types can I upload?**  
A: JPG, PNG, GIF, and PDF files up to 5MB each.

**Q: Can I upload multiple receipts?**  
A: Currently one receipt per transaction. Multiple receipts coming soon!

---

## Quick Reference Card

### Transaction Status Colors

- 🟡 **Yellow (Pending)** - Waiting for approval
- 🟢 **Green (Approved)** - Approved and processed
- 🔴 **Red (Rejected)** - Rejected by approver

### Transaction Types

- ➕ **Cash In** - Money coming into petty cash
- ➖ **Cash Out** - Money going out of petty cash

### Your Dashboard Numbers

- **Total In** - All approved cash in transactions
- **Total Out** - All approved cash out transactions
- **Balance** - In minus Out (remaining cash)

---

**Need Help?** Contact your administrator or check the [Full User Manual](user-manual.md)

**Last Updated**: November 24, 2024  
**Version**: 1.0.0

