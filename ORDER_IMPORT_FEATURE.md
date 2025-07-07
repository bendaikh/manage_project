# Order Import Feature

## 🚀 **Overview**

The Order Import feature allows you to upload Excel (.xlsx, .xls) or CSV files to import multiple orders at once, significantly speeding up the order creation process.

## 📋 **Features**

- ✅ **Bulk Order Import**: Upload spreadsheets with multiple orders
- ✅ **File Format Support**: Excel (.xlsx, .xls) and CSV files
- ✅ **Data Validation**: Automatic validation of all required fields
- ✅ **Error Handling**: Detailed error reporting for failed rows
- ✅ **Template Download**: Pre-formatted template with sample data
- ✅ **Progress Tracking**: Real-time import progress
- ✅ **Success Summary**: Complete import results with statistics

## 📁 **Required File Format**

### **Required Columns:**
- `product_sku` - Product SKU (must exist in database)
- `seller_username` - Seller email or name (must exist in database)
- `client_name` - Customer name
- `client_phone` - Customer phone number
- `client_address` - Customer address
- `quantity` - Order quantity (must be > 0)
- `price` - Order price (must be > 0)

### **Optional Columns:**
- `zone` - Delivery zone
- `delivery_method` - Delivery method (default: "Standard")
- `notes` - Additional notes

### **Sample Data:**
```csv
product_sku,seller_username,client_name,client_phone,client_address,quantity,price,zone,delivery_method,notes
SKU001,seller@example.com,John Doe,+1234567890,123 Main St,2,5000,Zone A,Standard,Sample order
SKU002,seller@example.com,Jane Smith,+0987654321,456 Oak Ave,1,3000,Zone B,Express,Another order
```

## 🎯 **How to Use**

### **Step 1: Access Import Feature**
1. Go to "Add Order" page
2. Click the green "Import Sheet Orders" button
3. The import modal will open

### **Step 2: Download Template (Optional)**
1. Click "Download Template" button
2. Open the downloaded CSV file
3. Fill in your order data
4. Save the file

### **Step 3: Upload File**
1. Click "Choose File" or drag and drop your file
2. Select your Excel or CSV file
3. Click "Import Orders"

### **Step 4: Review Results**
- View import progress in real-time
- See success/failure statistics
- Review detailed error messages
- Close modal when complete

## 🔧 **Technical Implementation**

### **Backend Components:**

#### **OrdersImport Class** (`app/Imports/OrdersImport.php`)
- Handles file processing and data validation
- Maps spreadsheet columns to database fields
- Validates product SKU and seller username
- Creates order records with proper relationships
- Tracks success/error counts and messages

#### **OrderImportController** (`app/Http/Controllers/OrderImportController.php`)
- Handles file upload and validation
- Processes import requests
- Provides template download
- Returns import statistics

### **Frontend Components:**

#### **OrderImportModal** (`resources/js/components/OrderImportModal.vue`)
- File upload interface
- Progress tracking
- Results display
- Error reporting

#### **OrderCreate** (Updated)
- Import button integration
- Modal trigger
- Success handling

### **Routes:**
```php
POST /orders/import          // Upload and process file
GET  /orders/import/template // Download CSV template
GET  /orders/import/stats    // Get system statistics
```

## ⚠️ **Validation Rules**

### **Data Validation:**
- **Product SKU**: Must exist in products table
- **Seller Username**: Must exist in users table (matches email or name)
- **Quantity**: Must be numeric and > 0
- **Price**: Must be numeric and > 0
- **Required Fields**: All required fields must be present

### **File Validation:**
- **File Size**: Maximum 10MB
- **File Type**: .xlsx, .xls, .csv only
- **Headers**: Must include required column headers

## 📊 **Import Results**

### **Success Response:**
```json
{
  "success": true,
  "message": "Import completed successfully",
  "results": {
    "success_count": 15,
    "error_count": 2,
    "errors": [
      "Row 3: Product with SKU 'INVALID-SKU' not found",
      "Row 7: Invalid quantity '0'"
    ]
  }
}
```

### **Error Response:**
```json
{
  "success": false,
  "message": "Import failed: Invalid file format",
  "results": {
    "success_count": 0,
    "error_count": 1,
    "errors": ["Invalid file format"]
  }
}
```

## 🛠️ **Troubleshooting**

### **Common Issues:**

#### **"Product SKU not found"**
- Verify the SKU exists in your products table
- Check for extra spaces or typos
- Ensure SKU is exactly as stored in database

#### **"Seller not found"**
- Verify seller email/name exists in users table
- Check for case sensitivity
- Ensure seller has proper permissions

#### **"Invalid quantity/price"**
- Ensure values are numeric
- Quantity must be > 0
- Price must be > 0

#### **"File upload failed"**
- Check file size (max 10MB)
- Verify file format (.xlsx, .xls, .csv)
- Ensure proper file permissions

### **Best Practices:**

1. **Use Template**: Always start with the provided template
2. **Test Small**: Import a few orders first to test
3. **Backup Data**: Always backup before large imports
4. **Validate Data**: Check your data before importing
5. **Review Errors**: Always review error messages

## 🔒 **Security Features**

- **File Type Validation**: Only allows approved file types
- **Size Limits**: Prevents large file uploads
- **Data Sanitization**: Cleans input data
- **Error Logging**: Logs all import activities
- **CSRF Protection**: Protects against cross-site requests

## 📈 **Performance**

- **Batch Processing**: Processes multiple orders efficiently
- **Memory Management**: Handles large files without memory issues
- **Progress Tracking**: Real-time feedback during import
- **Error Recovery**: Continues processing even if some rows fail

## 🎯 **Benefits**

1. **Time Saving**: Import hundreds of orders in minutes
2. **Data Accuracy**: Automated validation reduces errors
3. **Bulk Operations**: Handle large order volumes efficiently
4. **Error Reporting**: Detailed feedback for troubleshooting
5. **Template System**: Standardized data format
6. **Progress Tracking**: Real-time import status

## 🔄 **Future Enhancements**

- **Export Orders**: Export existing orders to spreadsheet
- **Advanced Validation**: Custom validation rules
- **Bulk Updates**: Update existing orders via import
- **Scheduled Imports**: Automated import scheduling
- **API Integration**: Direct integration with external systems 