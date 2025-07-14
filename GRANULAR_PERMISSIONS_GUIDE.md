# 🔐 Granular Permissions System Guide

This guide explains the comprehensive granular permission system implemented in your Laravel application.

## 📋 Overview

The system now uses **granular permissions** instead of general module permissions. This means each specific action (create, view, edit, delete) has its own permission, giving you precise control over what users can do.

## 🎯 Permission Categories

### 1. **Dashboard Permissions**
- `view_dashboard` - Can view main dashboard
- `view_dashboard_overview` - Can view dashboard overview
- `view_dashboard_analytics` - Can view dashboard analytics

### 2. **User Management Permissions**
- `view_users` - Can view users list
- `create_users` - Can create new users
- `edit_users` - Can edit existing users
- `delete_users` - Can delete users
- `manage_users` - Full user management access

### 3. **Role & Permission Management**
- `view_roles` - Can view roles list
- `create_roles` - Can create new roles
- `edit_roles` - Can edit existing roles
- `delete_roles` - Can delete roles
- `manage_roles` - Full role management access

- `view_permissions` - Can view permissions list
- `create_permissions` - Can create new permissions
- `edit_permissions` - Can edit existing permissions
- `delete_permissions` - Can delete permissions
- `manage_permissions` - Full permission management access

### 4. **Order Permissions (Granular)**
- `view_orders` - Can view orders list
- `create_orders` - Can create new orders
- `edit_orders` - Can edit existing orders
- `delete_orders` - Can delete orders
- `view_order_details` - Can view order details
- `update_order_status` - Can update order status
- `view_confirmation_orders` - Can view orders pending confirmation
- `confirm_orders` - Can confirm orders
- `view_delivery_orders` - Can view orders for delivery
- `process_delivery_orders` - Can process delivery orders
- `import_orders` - Can import orders from files
- `export_orders` - Can export orders to files
- `manage_orders` - Full order management access

### 5. **Product Permissions (Granular)**
- `view_products` - Can view products list
- `create_products` - Can create new products
- `edit_products` - Can edit existing products
- `delete_products` - Can delete products
- `view_product_details` - Can view product details
- `view_product_catalog` - Can view product catalog
- `manage_products` - Full product management access

### 6. **Category Permissions**
- `view_categories` - Can view categories list
- `create_categories` - Can create new categories
- `edit_categories` - Can edit existing categories
- `delete_categories` - Can delete categories
- `manage_categories` - Full category management access

### 7. **Invoice & PDF Permissions**
- `view_invoices` - Can view invoices
- `create_invoices` - Can create invoices
- `download_invoices` - Can download invoices
- `view_delivery_notes` - Can view delivery notes
- `create_delivery_notes` - Can create delivery notes
- `download_delivery_notes` - Can download delivery notes
- `view_delivery_invoices` - Can view delivery invoices
- `create_delivery_invoices` - Can create delivery invoices
- `download_delivery_invoices` - Can download delivery invoices

### 8. **Accounting Permissions (Granular)**

#### Income Permissions
- `view_incomes` - Can view incomes list
- `create_incomes` - Can create new incomes
- `edit_incomes` - Can edit existing incomes
- `delete_incomes` - Can delete incomes
- `manage_incomes` - Full income management access

#### Income Category Permissions
- `view_income_categories` - Can view income categories
- `create_income_categories` - Can create income categories
- `edit_income_categories` - Can edit income categories
- `delete_income_categories` - Can delete income categories
- `manage_income_categories` - Full income category management access

#### Expense Permissions
- `view_expenses` - Can view expenses list
- `create_expenses` - Can create new expenses
- `edit_expenses` - Can edit existing expenses
- `delete_expenses` - Can delete expenses
- `manage_expenses` - Full expense management access

#### Expense Category Permissions
- `view_expense_categories` - Can view expense categories
- `create_expense_categories` - Can create expense categories
- `edit_expense_categories` - Can edit expense categories
- `delete_expense_categories` - Can delete expense categories
- `manage_expense_categories` - Full expense category management access

#### Refund Permissions
- `view_refunds` - Can view refunds list
- `create_refunds` - Can create new refunds
- `edit_refunds` - Can edit existing refunds
- `delete_refunds` - Can delete refunds
- `manage_refunds` - Full refund management access

#### Transfer Permissions
- `view_transfers` - Can view transfers list
- `create_transfers` - Can create new transfers
- `edit_transfers` - Can edit existing transfers
- `delete_transfers` - Can delete transfers
- `manage_transfers` - Full transfer management access

#### User Transfer Permissions
- `view_user_transfers` - Can view user transfers
- `create_user_transfers` - Can create user transfers
- `edit_user_transfers` - Can edit user transfers
- `delete_user_transfers` - Can delete user transfers
- `manage_user_transfers` - Full user transfer management access

#### Account Permissions
- `view_accounts` - Can view accounts list
- `create_accounts` - Can create new accounts
- `edit_accounts` - Can edit existing accounts
- `delete_accounts` - Can delete accounts
- `manage_accounts` - Full account management access

### 9. **Settings Permissions**
- `view_settings` - Can view settings
- `edit_settings` - Can edit settings
- `manage_settings` - Full settings management access

### 10. **Reports Permissions**
- `view_reports` - Can view reports
- `generate_reports` - Can generate reports
- `export_reports` - Can export reports
- `manage_reports` - Full reports management access

### 11. **Support Permissions**
- `view_support_tickets` - Can view support tickets
- `create_support_tickets` - Can create support tickets
- `respond_to_tickets` - Can respond to support tickets
- `manage_support_tickets` - Full support tickets management access

## 👥 Predefined Roles

### **Superadmin**
- **Description**: Super Administrator with all permissions
- **Access**: All permissions in the system
- **Use Case**: System administrator who needs full access

### **Admin**
- **Description**: Administrator with comprehensive permissions
- **Access**: All permissions except role and permission management
- **Use Case**: Business administrator who manages the system but doesn't need to modify roles/permissions

### **Manager**
- **Description**: Manager with specific business permissions
- **Access**: 
  - Full order management (create, edit, view, confirm, process delivery)
  - Full product management
  - Category management (view, create, edit)
  - Invoice and PDF management
  - Accounting (view, create, edit - no delete)
  - Reports and support
- **Use Case**: Business manager who oversees operations

### **Agent**
- **Description**: Regular agent with basic permissions
- **Access**:
  - View dashboard overview
  - Orders (view, create, view details, view confirmation/delivery)
  - Products (view only)
  - Categories (view only)
  - Basic invoices (view only)
  - Basic accounting (view incomes/expenses, create incomes/expenses)
  - Support (view and create tickets)
- **Use Case**: Regular employee who handles basic operations

### **Accountant**
- **Description**: Accountant with financial permissions
- **Access**:
  - Dashboard (full access)
  - Orders and products (view only)
  - Full accounting access (all financial operations)
  - Reports (full access)
- **Use Case**: Financial specialist who handles all money-related operations

### **Sales Agent**
- **Description**: Sales agent with order and product permissions
- **Access**:
  - Dashboard overview
  - Full order management
  - Full product management
  - Category management
  - Invoice and PDF management
  - Basic accounting (view only)
  - Support (view and create tickets)
- **Use Case**: Sales representative who handles orders and products

## 🔧 How to Use

### 1. **Checking Permissions in Vue.js**
```javascript
// In your Vue components
const hasPermission = (permission) => {
  return window.Laravel?.user?.permissions?.includes(permission) || false
}

// Example usage
const canCreateOrders = computed(() => hasPermission('create_orders'))
const canEditProducts = computed(() => hasPermission('edit_products'))
```

### 2. **Checking Permissions in Laravel Controllers**
```php
// In your controllers
if (auth()->user()->hasPermission('create_orders')) {
    // User can create orders
}

if (auth()->user()->hasPermission('edit_products')) {
    // User can edit products
}
```

### 3. **Using Middleware**
```php
// In routes
Route::middleware(['auth', 'permission:create_orders'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
});

Route::middleware(['auth', 'permission:edit_products'])->group(function () {
    Route::put('/products/{product}', [ProductController::class, 'update']);
});
```

### 4. **Conditional UI Elements**
```vue
<!-- In your Vue templates -->
<button v-if="hasCreateOrdersPermission" @click="createOrder">
  Create Order
</button>

<div v-if="hasEditProductsPermission">
  <button @click="editProduct">Edit Product</button>
</div>
```

## 🛠️ Customization

### **Adding New Permissions**
1. Add the permission to the `$permissions` array in `RolesAndPermissionsSeeder.php`
2. Run the seeder: `php artisan db:seed --class=RolesAndPermissionsSeeder`
3. Assign the permission to appropriate roles

### **Creating Custom Roles**
1. Add the role to the `$roles` array in `RolesAndPermissionsSeeder.php`
2. Define which permissions the role should have
3. Run the seeder

### **Modifying Existing Roles**
1. Edit the permission assignments in `RolesAndPermissionsSeeder.php`
2. Run the seeder (this will reset all role assignments)

## 📊 Permission Matrix

| Action | Superadmin | Admin | Manager | Agent | Accountant | Sales Agent |
|--------|------------|-------|---------|-------|------------|-------------|
| **Dashboard** | ✅ All | ✅ All | ✅ All | ✅ Overview | ✅ All | ✅ Overview |
| **Users** | ✅ All | ✅ All | ❌ | ❌ | ❌ | ❌ |
| **Roles/Permissions** | ✅ All | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Orders** | ✅ All | ✅ All | ✅ All | ✅ View/Create | ✅ View | ✅ All |
| **Products** | ✅ All | ✅ All | ✅ All | ✅ View | ✅ View | ✅ All |
| **Categories** | ✅ All | ✅ All | ✅ Create/Edit | ✅ View | ❌ | ✅ Create/Edit |
| **Invoices/PDFs** | ✅ All | ✅ All | ✅ All | ✅ View | ❌ | ✅ All |
| **Incomes** | ✅ All | ✅ All | ✅ Create/Edit | ✅ Create | ✅ All | ✅ View |
| **Expenses** | ✅ All | ✅ All | ✅ Create/Edit | ✅ Create | ✅ All | ✅ View |
| **Refunds** | ✅ All | ✅ All | ✅ Create/Edit | ❌ | ✅ All | ❌ |
| **Transfers** | ✅ All | ✅ All | ✅ Create/Edit | ❌ | ✅ All | ❌ |
| **Accounts** | ✅ All | ✅ All | ✅ Create/Edit | ❌ | ✅ All | ❌ |
| **Reports** | ✅ All | ✅ All | ✅ All | ❌ | ✅ All | ❌ |
| **Support** | ✅ All | ✅ All | ✅ Respond | ✅ Create | ❌ | ✅ Create |

## 🔒 Security Best Practices

1. **Always check permissions** before allowing actions
2. **Use middleware** for route protection
3. **Validate permissions** on both frontend and backend
4. **Log permission violations** for security monitoring
5. **Regularly audit** role assignments
6. **Use principle of least privilege** - give minimum required permissions

## 🚀 Getting Started

1. **Run the seeder** (already done):
   ```bash
   php artisan db:seed --class=RolesAndPermissionsSeeder
   ```

2. **Login as superadmin**:
   - Email: `superadmin@example.com`
   - Password: `password`

3. **Assign roles to users** through the admin interface

4. **Test permissions** by logging in with different user roles

## 📝 Notes

- The system is **backward compatible** - existing code using general permissions will still work
- **Legacy permissions** (`hasClientsPermission`, `hasHistoryPermission`) are set to `false` by default
- All menu items now use **granular permissions** for precise control
- The **AI chatbot** respects user permissions and only shows relevant data

---

**Need help?** Check the Laravel logs or contact your system administrator. 