# Weekly Invoice Advance Charges - Quick Reference

## 🎯 What Changed

### Problem 1: Advances Not in PDFs ❌ → ✅
**Before**: Advances sometimes missing from downloaded invoices  
**After**: Always included using proper database relationships

### Problem 2: Advances Being Replaced ❌ → ✅
**Before**: New advance overwrote previous one  
**After**: Each advance saved as separate record with full history

## 🎨 New Features

### 1. Smart Table Display
- **1 advance**: Shows amount directly
- **2+ advances**: Shows total with 👁️ eye icon

### 2. Advances Details Modal
Click eye icon to see:
- All advances with dates and notes
- Who created each advance
- Total advances sum
- Final amount due

### 3. Enhanced Advance Form
Now shows:
- Existing advances total
- Remaining available amount
- Better validation

## 📊 Database Structure

### New Table: `weekly_invoice_advances`
```
├── id
├── weekly_seller_invoice_id (FK)
├── amount
├── note
├── created_by (FK)
└── timestamps
```

### Relationships
- Invoice → Many Advances
- Advance → One Creator (User)
- Cascade delete when invoice deleted

## 🔧 API Changes

### Charge Advance Endpoint
```
POST /weekly-seller-invoices/{id}/charge-advance
Body: { advance_amount, advance_note }
```

**What it does now**:
1. Validates amount against remaining total
2. Creates NEW advance record (doesn't update)
3. Returns updated invoice with all advances

## 💡 Usage Examples

### Example 1: Single Advance
```
Invoice: 50,000 FCFA
Apply advance: 10,000 FCFA
Result: Shows "- Advance: 10,000 FCFA"
```

### Example 2: Multiple Advances
```
Invoice: 50,000 FCFA
Apply advance 1: 10,000 FCFA
Apply advance 2: 5,000 FCFA
Result: Shows "- Advances: 15,000 FCFA 👁️"
Click eye → See both advances listed
```

### Example 3: Maximum Validation
```
Invoice: 50,000 FCFA
Existing: 30,000 FCFA
Try to add: 25,000 FCFA → ❌ ERROR
Try to add: 20,000 FCFA → ✅ SUCCESS
```

## 📄 PDF Changes

### Before
```
Advance Charge: 10,000 FCFA
Note: Payment advance
```

### After
```
Advance Charges:
- 10,000 FCFA (12/10/2025 14:30)
  Note: Payment advance
- 5,000 FCFA (13/10/2025 09:15)
  Note: Additional advance

Total Advances: 15,000 FCFA
Final Amount Due: 35,000 FCFA
```

## ✅ Key Benefits

1. **Accurate History**: Every advance tracked with timestamp and creator
2. **Consistent PDFs**: Advances always included in downloads
3. **Better UX**: Smart display keeps interface clean
4. **Data Integrity**: Proper relationships and validation
5. **Scalability**: Supports unlimited advances per invoice

## 🚀 Quick Start

### For Administrators
1. Go to **Sellers Invoices** → **Weekly** tab
2. Find approved invoice
3. Click **"Charge Advance"** button
4. Enter amount (validates against remaining)
5. Add optional note
6. Submit
7. View in table (single amount or eye icon)
8. Download PDF to see in invoice

### For Viewing Multiple Advances
1. Look for 👁️ eye icon in table
2. Click the eye icon
3. See all advances with details
4. View total and final amount
5. Close modal

## 📞 Support

See `CHARGE_ADVANCE_IMPROVEMENTS.md` for complete technical documentation.

---

**Version**: 2.0  
**Date**: October 12, 2025  
**Status**: ✅ Production Ready
