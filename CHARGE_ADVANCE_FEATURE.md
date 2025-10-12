# Charge Advance Feature for Weekly Seller Invoices

## Overview
Added comprehensive "Charge Advance" functionality to the Weekly Seller Invoices section, allowing administrators to apply advance charges to approved invoices and reflect them in both the UI and generated PDF files.

## 🎯 Features Implemented

### 1. **Charge Advance Action Button**
- **Location**: Actions column in the weekly invoices table
- **Visibility**: Only shown for approved invoices (status = 'approved')
- **Design**: Purple button with money icon and "Charge Advance" label
- **Permissions**: Requires `approve_seller_invoices` permission

### 2. **Charge Advance Modal**
- **Clean Interface**: Professional modal with form validation
- **Invoice Context**: Shows seller name, week period, and total amount
- **Input Fields**:
  - **Advance Amount**: Numeric input with currency prefix, validation (0 to invoice total)
  - **Note**: Optional textarea (500 character limit) for advance description
- **Form Validation**: 
  - Advance amount cannot exceed invoice total
  - Required fields validation
  - Real-time character counter for notes

### 3. **Database Integration**
- **New Fields**: Added `advance_amount` and `advance_note` to `weekly_seller_invoices` table
- **Data Types**: 
  - `advance_amount`: DECIMAL(12,2) with default 0
  - `advance_note`: TEXT nullable
- **Model Updates**: Added fields to fillable array and casts

### 4. **Enhanced Invoice Display**
- **Dynamic Totals**: Shows both original and adjusted totals
- **Visual Indicators**:
  - Original total in bold
  - Advance amount in red with minus sign
  - Adjusted total in blue and bold
- **Conditional Display**: Only shows advance section when advance amount > 0

### 5. **PDF Invoice Integration**
- **Advance Section**: Dedicated section in PDF for advance charges
- **Visual Design**: Highlighted box with red border
- **Content Includes**:
  - Advance amount with red color
  - Optional note in italics
  - Final amount due calculation
- **Layout**: Positioned after main totals, before general notes

## 🛠️ Technical Implementation

### Backend Changes

#### 1. Database Migration
```php
// File: database/migrations/2025_10_12_135542_add_advance_fields_to_weekly_seller_invoices_table.php
$table->decimal('advance_amount', 12, 2)->default(0)->after('total_amount');
$table->text('advance_note')->nullable()->after('advance_amount');
```

#### 2. Model Updates
```php
// File: app/Models/WeeklySellerInvoice.php
protected $fillable = [
    // ... existing fields
    'advance_amount',
    'advance_note',
];

protected $casts = [
    // ... existing casts
    'advance_amount' => 'decimal:2',
];

// New helper methods
public function getAdjustedTotalAttribute()
{
    return $this->total_amount - $this->advance_amount;
}

public function hasAdvanceCharge()
{
    return $this->advance_amount > 0;
}
```

#### 3. API Endpoint
```php
// File: app/Http/Controllers/WeeklySellerInvoiceController.php
public function chargeAdvance(Request $request, $id)
{
    // Validation: advance_amount (required, numeric, 0-max), advance_note (optional, max 500)
    // Business Logic: Only approved invoices, advance cannot exceed total
    // Response: Updated invoice with adjusted_total
}
```

#### 4. Route Addition
```php
// File: routes/web.php
Route::post('/weekly-seller-invoices/{id}/charge-advance', [WeeklySellerInvoiceController::class, 'chargeAdvance']);
```

### Frontend Changes

#### 1. UI Components
- **Charge Advance Button**: Added to actions column with proper styling
- **Modal Component**: Complete form with validation and loading states
- **Enhanced Table Display**: Dynamic total calculation and visual indicators

#### 2. JavaScript Functions
```javascript
// Modal Management
openChargeAdvanceModal(invoice)    // Opens modal with invoice data
closeChargeAdvanceModal()          // Closes modal and resets form
submitChargeAdvance()              // Handles form submission and API call
```

#### 3. Form Validation
- **Client-side**: Real-time validation with visual feedback
- **Server-side**: Comprehensive validation in controller
- **Error Handling**: User-friendly error messages

### PDF Generation Updates

#### 1. Template Enhancement
```php
// File: resources/views/pdf/weekly-seller-invoice.blade.php
@if($invoice->advance_amount > 0)
<div style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #dc3545;">
    <strong>Advance Charge:</strong><br>
    <div style="color: #dc3545; font-weight: bold;">- {{ number_format($invoice->advance_amount, 0, ',', ' ') }} FCFA</div>
    @if($invoice->advance_note)
    <div style="margin-top: 5px; font-style: italic;">Note: {{ $invoice->advance_note }}</div>
    @endif
    <div style="margin-top: 10px; font-size: 14px; font-weight: bold; color: #007bff;">
        Final Amount Due: {{ number_format($totalAmount - $invoice->advance_amount, 0, ',', ' ') }} FCFA
    </div>
</div>
@endif
```

## 🎨 User Interface Design

### Color Scheme
- **Primary Button**: Purple (#7C3AED) - Professional and distinct from other actions
- **Advance Amount**: Red (#DC3545) - Indicates deduction
- **Adjusted Total**: Blue (#007BFF) - Highlights final amount
- **Modal**: Clean white background with subtle shadows

### Visual Hierarchy
1. **Original Total**: Bold, primary display
2. **Advance Deduction**: Red text with minus sign
3. **Final Amount**: Blue, emphasized for clarity

### Responsive Design
- **Modal**: Responsive width (max-w-md) with proper mobile spacing
- **Table**: Maintains readability on all screen sizes
- **Form**: Touch-friendly inputs on mobile devices

## 📋 Usage Workflow

### For Administrators
1. Navigate to **Sellers Invoices** → **Weekly** tab
2. Locate an **approved** invoice
3. Click **"Charge Advance"** button (purple)
4. Enter advance amount (cannot exceed invoice total)
5. Optionally add a note explaining the advance
6. Click **"Apply Advance"**
7. View updated totals in the table
8. Download PDF to see advance reflected in invoice

### For Sellers
- Can view invoices with advance charges applied
- PDF downloads show clear breakdown of advance deductions
- Final amount due is prominently displayed

## 🔒 Security & Permissions

### Access Control
- **Required Permission**: `approve_seller_invoices`
- **Business Rules**: Only approved invoices can have advance charges
- **Validation**: Server-side validation prevents invalid amounts

### Data Integrity
- **Constraints**: Advance amount cannot exceed invoice total
- **Audit Trail**: Advance changes are tracked in invoice updates
- **Backup**: Original totals preserved, advance is additive field

## 🧪 Testing Scenarios

### Functional Tests
1. **Basic Advance Application**
   - Apply advance to approved invoice
   - Verify table display updates
   - Confirm PDF generation includes advance

2. **Edge Cases**
   - Advance amount = 0
   - Advance amount = invoice total
   - Very long notes (500+ characters)
   - Special characters in notes

3. **Validation Tests**
   - Advance exceeds invoice total (should fail)
   - Negative advance amounts (should fail)
   - Non-numeric advance amounts (should fail)

4. **Permission Tests**
   - Users without permission cannot see button
   - API endpoints properly protected

### UI/UX Tests
- Modal opens/closes properly
- Form validation provides clear feedback
- Loading states work correctly
- Responsive design on mobile devices

## 🚀 Future Enhancements

### Potential Improvements
1. **Bulk Advance Operations**: Apply advance to multiple invoices
2. **Advance Templates**: Predefined advance amounts for common scenarios
3. **Advance History**: Track changes to advance amounts over time
4. **Email Notifications**: Notify sellers when advance is applied
5. **Advance Reversal**: Allow removal of advance charges
6. **Reporting**: Generate reports on advance charges by period/seller

### Technical Optimizations
1. **Real-time Updates**: WebSocket integration for live invoice updates
2. **Caching**: Cache frequently accessed invoice data
3. **Batch Processing**: Optimize bulk operations
4. **Audit Logging**: Enhanced tracking of all advance operations

## 📁 Files Modified

### Database
- `database/migrations/2025_10_12_135542_add_advance_fields_to_weekly_seller_invoices_table.php`

### Backend
- `app/Models/WeeklySellerInvoice.php`
- `app/Http/Controllers/WeeklySellerInvoiceController.php`
- `routes/web.php`
- `resources/views/pdf/weekly-seller-invoice.blade.php`

### Frontend
- `resources/js/components/SellerInvoicesList.vue`

### Built Assets
- `public/build/` (compiled JavaScript and CSS)

## 🎉 Success Criteria Met

✅ **Charge Advance Action**: Purple button added to approved invoices  
✅ **Modal Interface**: Clean form with amount and note inputs  
✅ **Invoice Total Updates**: Dynamic display shows original and adjusted totals  
✅ **Database Persistence**: Advance data saved and retrievable  
✅ **PDF Integration**: Advance charges included in downloaded invoices  
✅ **User Experience**: Intuitive workflow with proper validation  
✅ **Security**: Proper permissions and validation implemented  

The Charge Advance feature is now fully functional and ready for production use! 🚀
