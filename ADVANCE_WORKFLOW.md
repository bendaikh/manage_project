# Advance Charges Workflow & Architecture

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Weekly Seller Invoice                     │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ ID: 1                                                  │  │
│  │ Seller: John Doe                                       │  │
│  │ Week: Jan 15-21, 2025                                  │  │
│  │ Total Amount: 50,000 FCFA                              │  │
│  │ Status: approved                                       │  │
│  └───────────────────────────────────────────────────────┘  │
│                           │                                   │
│                           │ hasMany                           │
│                           ▼                                   │
│  ┌───────────────────────────────────────────────────────┐  │
│  │              Advances (Multiple Records)               │  │
│  │                                                         │  │
│  │  ┌─────────────────────────────────────────────────┐  │  │
│  │  │ Advance #1                                       │  │  │
│  │  │ Amount: 10,000 FCFA                              │  │  │
│  │  │ Note: "Payment advance"                          │  │  │
│  │  │ Created: 2025-01-20 14:30                        │  │  │
│  │  │ Created By: Admin User                           │  │  │
│  │  └─────────────────────────────────────────────────┘  │  │
│  │                                                         │  │
│  │  ┌─────────────────────────────────────────────────┐  │  │
│  │  │ Advance #2                                       │  │  │
│  │  │ Amount: 5,000 FCFA                               │  │  │
│  │  │ Note: "Additional advance"                       │  │  │
│  │  │ Created: 2025-01-21 09:15                        │  │  │
│  │  │ Created By: Manager User                         │  │  │
│  │  └─────────────────────────────────────────────────┘  │  │
│  │                                                         │  │
│  │  Total Advances: 15,000 FCFA                           │  │
│  │  Adjusted Total: 35,000 FCFA                           │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

## 📊 Database Relationships

```
users                      weekly_seller_invoices           weekly_invoice_advances
┌──────────────┐          ┌──────────────────────┐         ┌──────────────────────┐
│ id           │◄─────┐   │ id                   │◄────────│ id                   │
│ name         │      │   │ seller               │         │ weekly_seller_       │
│ email        │      │   │ week_start_date      │         │   invoice_id (FK)    │
│ ...          │      │   │ week_end_date        │         │ amount               │
└──────────────┘      │   │ total_amount         │         │ note                 │
                      │   │ status               │         │ created_by (FK)──────┤
                      │   │ ...                  │         │ created_at           │
                      └───│ approved_by (FK)     │         │ updated_at           │
                          └──────────────────────┘         └──────────────────────┘
                                    │                                │
                                    │ hasMany                        │ belongsTo
                                    └────────────────────────────────┘
```

## 🔄 User Workflow

### A. Applying an Advance

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. User Views Weekly Invoices Table                             │
│    ┌─────────────────────────────────────────────────────────┐ │
│    │ Seller    │ Week        │ Total    │ Status   │ Actions │ │
│    ├───────────┼─────────────┼──────────┼──────────┼─────────┤ │
│    │ John Doe  │ Jan 15-21   │ 50,000   │ Approved │ [...]   │ │
│    └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 2. User Clicks "Charge Advance" Button                          │
└─────────────────────────────────────────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 3. Modal Opens with Invoice Context                             │
│    ┌─────────────────────────────────────────────────────────┐ │
│    │ Seller: John Doe                                        │ │
│    │ Week: Jan 15-21, 2025                                   │ │
│    │ Total Amount: 50,000 FCFA                               │ │
│    │                                                          │ │
│    │ Advance Amount: [___________] FCFA                      │ │
│    │ Note: [________________________]                        │ │
│    │                                                          │ │
│    │ [Cancel]  [Apply Advance]                               │ │
│    └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 4. System Validates                                              │
│    ✓ Amount > 0                                                  │
│    ✓ Amount <= Remaining (Total - Existing Advances)            │
│    ✓ Invoice is approved                                         │
└─────────────────────────────────────────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 5. New Advance Record Created                                    │
│    INSERT INTO weekly_invoice_advances (...)                     │
└─────────────────────────────────────────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 6. Table Updates with New Display                                │
│    - If 1 advance: Shows amount directly                         │
│    - If 2+ advances: Shows eye icon                              │
└─────────────────────────────────────────────────────────────────┘
```

### B. Viewing Multiple Advances

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. Table Shows Eye Icon                                          │
│    ┌─────────────────────────────────────────────────────────┐ │
│    │ Total: 50,000 FCFA                                      │ │
│    │ - Advances: 15,000 FCFA [👁️]                           │ │
│    │ Adjusted: 35,000 FCFA                                   │ │
│    └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 2. User Clicks Eye Icon                                          │
└─────────────────────────────────────────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 3. Advances Modal Opens                                          │
│    ┌─────────────────────────────────────────────────────────┐ │
│    │ # │ Amount      │ Date           │ Note      │ Creator │ │
│    ├───┼─────────────┼────────────────┼───────────┼─────────┤ │
│    │ 1 │ 10,000 FCFA │ 20/01 14:30    │ Payment   │ Admin   │ │
│    │ 2 │  5,000 FCFA │ 21/01 09:15    │ Extra     │ Manager │ │
│    ├───┴─────────────┴────────────────┴───────────┴─────────┤ │
│    │ Total Advances: 15,000 FCFA                             │ │
│    │ Final Amount Due: 35,000 FCFA                           │ │
│    └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

### C. PDF Generation

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. User Clicks "Download PDF"                                    │
└─────────────────────────────────────────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 2. Controller Loads Invoice with Advances                        │
│    WeeklySellerInvoice::with('advances')->find($id)             │
└─────────────────────────────────────────────────────────────────┘
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 3. PDF Template Renders with ALL Advances                        │
│    ┌─────────────────────────────────────────────────────────┐ │
│    │ Net Total Amount: 50,000 FCFA                           │ │
│    │                                                          │ │
│    │ Advance Charges:                                         │ │
│    │ - 10,000 FCFA (20/01/2025 14:30)                        │ │
│    │   Payment advance                                        │ │
│    │ - 5,000 FCFA (21/01/2025 09:15)                         │ │
│    │   Additional advance                                     │ │
│    │                                                          │ │
│    │ Total Advances: 15,000 FCFA                             │ │
│    │ Final Amount Due: 35,000 FCFA                           │ │
│    └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

## 🔢 Calculation Logic

### Total Advances Calculation
```javascript
getTotalAdvances(invoice) {
  return invoice.advances.reduce((sum, advance) => {
    return sum + parseFloat(advance.amount)
  }, 0)
}

// Example:
// Advance 1: 10,000
// Advance 2: 5,000
// Total: 15,000
```

### Adjusted Total Calculation
```javascript
getTotalAfterAdvances(invoice) {
  return invoice.total_amount - getTotalAdvances(invoice)
}

// Example:
// Invoice Total: 50,000
// Total Advances: 15,000
// Adjusted Total: 35,000
```

### Remaining Available for New Advance
```javascript
// When opening charge advance modal:
const remaining = getTotalAfterAdvances(selectedInvoice)
// This becomes the max value for new advance

// Example:
// Invoice Total: 50,000
// Existing Advances: 15,000
// Remaining Available: 35,000
// User can add advance up to 35,000
```

## 🎯 Display Decision Tree

```
                    Check Invoice Advances
                            │
                            ▼
                    Has any advances?
                    ┌──────┴──────┐
                   YES            NO
                    │              │
                    ▼              ▼
            How many advances?   Display nothing
            ┌──────┴──────┐
           ONE         TWO+
            │            │
            ▼            ▼
    Show amount     Show total
    directly        with eye icon
            │            │
            │            ▼
            │      Eye icon clicked?
            │            │
            │           YES
            │            │
            └────────────▼
                  Open Modal
                  Show all advances
```

## 🔐 Validation Flow

```
User submits advance amount
        │
        ▼
    Amount > 0?
        │
       NO ──► Error: "Amount must be greater than 0"
        │
       YES
        ▼
    Invoice approved?
        │
       NO ──► Error: "Can only charge advance on approved invoices"
        │
       YES
        ▼
Calculate: total_existing = sum of all current advances
Calculate: remaining = invoice.total - total_existing
        │
        ▼
  New amount <= remaining?
        │
       NO ──► Error: "Total advances cannot exceed invoice total"
        │
       YES
        ▼
    ✅ Create new advance record
    ✅ Return success
```

## 📱 UI States

### Invoice with No Advances
```
┌────────────────────────────────┐
│ Total Amount: 50,000 FCFA      │
│                                │
│ [Charge Advance] button shown  │
└────────────────────────────────┘
```

### Invoice with Single Advance
```
┌────────────────────────────────┐
│ Total Amount: 50,000 FCFA      │
│ - Advance: 10,000 FCFA         │
│ Adjusted: 40,000 FCFA          │
│                                │
│ [Charge Advance] button shown  │
└────────────────────────────────┘
```

### Invoice with Multiple Advances
```
┌────────────────────────────────┐
│ Total Amount: 50,000 FCFA      │
│ - Advances: 15,000 FCFA 👁️    │
│ Adjusted: 35,000 FCFA          │
│                                │
│ [Charge Advance] button shown  │
└────────────────────────────────┘
```

### Invoice Fully Advanced
```
┌────────────────────────────────┐
│ Total Amount: 50,000 FCFA      │
│ - Advances: 50,000 FCFA 👁️    │
│ Adjusted: 0 FCFA               │
│                                │
│ [Charge Advance] - max reached │
└────────────────────────────────┘
```

## 🎨 Color Coding

- **Black/Bold**: Original invoice total
- **Red**: Advance amounts (deductions)
- **Blue**: Adjusted/final amounts (what's due)
- **Gray**: Metadata (dates, creators)
- **Purple**: Charge Advance button (action)
- **Green**: Success states

---

This workflow ensures consistent, reliable tracking of all advances with full audit history and proper validation at every step! 🚀
