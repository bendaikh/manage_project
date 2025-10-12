# Advance Charges Migration Guide

## 📋 Overview

This guide explains how to migrate from the old single-advance system to the new multiple-advances system.

## 🔍 System Changes

### Old System (Deprecated)
```php
weekly_seller_invoices table:
├── advance_amount (DECIMAL)    // Single value
└── advance_note (TEXT)         // Single note
```

### New System (Active)
```php
weekly_invoice_advances table:
├── id
├── weekly_seller_invoice_id (FK)
├── amount
├── note
├── created_by
└── timestamps
```

## ⚠️ Important Notes

### No Automatic Data Migration Needed

**Why?** The old system had a critical flaw:
- Each new advance **replaced** the previous one
- No historical data was preserved
- Only the **last advance** ever existed

**Result**: Any existing `advance_amount` values in the database represent only the most recent advance, with all previous advances lost.

### Decision: Clean Slate Approach

Rather than migrate potentially incomplete/misleading data, we recommend:
1. Keep old fields for reference (don't drop them yet)
2. All new advances use the new system
3. Old advance data can be manually reviewed if needed

## 🔄 Optional: Manual Data Review

If you want to review existing advance data before the system goes live:

### Step 1: Check for Existing Advances

```sql
SELECT 
    id,
    seller,
    week_start_date,
    week_end_date,
    total_amount,
    advance_amount,
    advance_note,
    status
FROM weekly_seller_invoices
WHERE advance_amount > 0;
```

### Step 2: Decide on Each Case

For each invoice with an existing advance:

**Option A: Keep as Reference Only**
- Do nothing
- Old advance_amount field stays as-is
- New system starts fresh
- No historical confusion

**Option B: Migrate to New System**
- Manually create advance record
- Use script below
- Attribute to system/unknown creator

### Step 3: Migration Script (Optional)

⚠️ **Only run if you decide to migrate existing data**

```php
<?php
// File: database/scripts/migrate_existing_advances.php

use App\Models\WeeklySellerInvoice;
use App\Models\WeeklyInvoiceAdvance;

// Get all invoices with existing advances
$invoices = WeeklySellerInvoice::where('advance_amount', '>', 0)->get();

echo "Found {$invoices->count()} invoices with existing advances\n";

foreach ($invoices as $invoice) {
    // Create new advance record
    WeeklyInvoiceAdvance::create([
        'weekly_seller_invoice_id' => $invoice->id,
        'amount' => $invoice->advance_amount,
        'note' => $invoice->advance_note ?: 'Migrated from old system',
        'created_by' => null, // Unknown creator
        'created_at' => $invoice->updated_at, // Best guess for timestamp
        'updated_at' => $invoice->updated_at,
    ]);
    
    echo "Migrated advance for invoice #{$invoice->id} - {$invoice->seller}\n";
}

echo "\nMigration complete!\n";
```

**To run:**
```bash
php database/scripts/migrate_existing_advances.php
```

## 🧹 Future Cleanup (Optional)

After the new system has been in production for a while and you're confident all is working:

### Step 1: Create Cleanup Migration

```bash
php artisan make:migration remove_old_advance_fields_from_weekly_seller_invoices
```

### Step 2: Migration Content

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weekly_seller_invoices', function (Blueprint $table) {
            $table->dropColumn(['advance_amount', 'advance_note']);
        });
    }

    public function down(): void
    {
        Schema::table('weekly_seller_invoices', function (Blueprint $table) {
            $table->decimal('advance_amount', 12, 2)->default(0);
            $table->text('advance_note')->nullable();
        });
    }
};
```

### Step 3: Run Cleanup (ONLY when ready)

```bash
php artisan migrate
```

⚠️ **Do NOT run cleanup migration until:**
- New system has been tested thoroughly
- You're 100% sure old fields are not needed
- Sufficient time has passed (recommend 3-6 months)

## 📊 Verification Queries

### Check New System Usage

```sql
-- Count advances in new system
SELECT 
    COUNT(*) as total_advances,
    SUM(amount) as total_amount
FROM weekly_invoice_advances;

-- Advances per invoice
SELECT 
    wsi.seller,
    wsi.week_start_date,
    COUNT(wia.id) as advance_count,
    SUM(wia.amount) as total_advances
FROM weekly_seller_invoices wsi
LEFT JOIN weekly_invoice_advances wia ON wia.weekly_seller_invoice_id = wsi.id
WHERE wsi.status = 'approved'
GROUP BY wsi.id
HAVING advance_count > 0;
```

### Compare Old vs New

```sql
-- See which invoices have data in old vs new system
SELECT 
    wsi.id,
    wsi.seller,
    wsi.advance_amount as old_advance,
    COALESCE(SUM(wia.amount), 0) as new_advances,
    COUNT(wia.id) as advance_count
FROM weekly_seller_invoices wsi
LEFT JOIN weekly_invoice_advances wia ON wia.weekly_seller_invoice_id = wsi.id
WHERE wsi.advance_amount > 0 OR wia.id IS NOT NULL
GROUP BY wsi.id;
```

## 🎯 Deployment Checklist

### Pre-Deployment
- [ ] Review existing advance data
- [ ] Decide on migration approach
- [ ] Run migration script (if applicable)
- [ ] Verify new tables created
- [ ] Test charge advance functionality
- [ ] Test PDF generation with advances

### Deployment
- [ ] Run migration: `php artisan migrate`
- [ ] Build frontend assets: `npm run build`
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Test in production environment

### Post-Deployment
- [ ] Monitor for any issues
- [ ] Verify advances appearing in PDFs
- [ ] Check multiple advances functionality
- [ ] Ensure modal displays correctly
- [ ] Validate calculations are accurate

### After 1 Week
- [ ] Review advance creation logs
- [ ] Check for any edge cases
- [ ] Gather user feedback
- [ ] Document any issues

### After 1 Month
- [ ] Confirm system stability
- [ ] Evaluate if old fields can be dropped
- [ ] Plan cleanup migration (if desired)

## 🆘 Rollback Plan

If critical issues are discovered:

### Step 1: Immediate Rollback

```bash
# Rollback the migration
php artisan migrate:rollback --step=1

# Rebuild old assets
git checkout HEAD~1 resources/js/components/SellerInvoicesList.vue
npm run build
```

### Step 2: Restore Old Code

```bash
# Restore old controller
git checkout HEAD~1 app/Http/Controllers/WeeklySellerInvoiceController.php

# Restore old model
git checkout HEAD~1 app/Models/WeeklySellerInvoice.php

# Restore old PDF template
git checkout HEAD~1 resources/views/pdf/weekly-seller-invoice.blade.php
```

### Step 3: Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## 📞 Support & Troubleshooting

### Common Issues

#### Issue: "Column 'advances' not found"
**Cause**: Frontend looking for advances but database not migrated  
**Fix**: Run `php artisan migrate`

#### Issue: Advances not showing in PDF
**Cause**: Invoice not loaded with advances relationship  
**Fix**: Already fixed in download method with `->with('advances')`

#### Issue: Can't add advance - validation error
**Cause**: Total advances exceed invoice total  
**Fix**: Check existing advances first, ensure remaining amount available

#### Issue: Old advances still showing
**Cause**: Using old fields instead of new relationship  
**Fix**: Verify using latest code, rebuild assets

### Debug Queries

```php
// Check if invoice has advances
$invoice = WeeklySellerInvoice::with('advances')->find($id);
dd($invoice->advances); // Should be collection

// Check total advances
dd($invoice->total_advances); // Should be sum

// Check adjusted total
dd($invoice->adjusted_total); // Should be total - advances
```

## ✅ Success Metrics

Track these to ensure successful migration:

- [ ] New advances created successfully
- [ ] Multiple advances per invoice working
- [ ] PDFs include all advances consistently
- [ ] No data loss or corruption
- [ ] User feedback is positive
- [ ] No critical bugs reported
- [ ] Performance is acceptable

---

**Remember**: The old system was fundamentally flawed (advances were replaced, not added). The new system provides a proper foundation for advance tracking with full history and audit trail. 

Migration is simple because there's minimal meaningful historical data to preserve. Focus on ensuring the new system works perfectly going forward! 🚀
