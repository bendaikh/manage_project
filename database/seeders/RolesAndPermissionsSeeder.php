<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create comprehensive permissions
        $permissions = [
            // Dashboard permissions
            ['name' => 'view_dashboard', 'description' => 'Can view dashboard'],
            ['name' => 'view_dashboard_overview', 'description' => 'Can view dashboard overview'],
            ['name' => 'view_dashboard_analytics', 'description' => 'Can view dashboard analytics'],
            
            // User management permissions
            ['name' => 'view_users', 'description' => 'Can view users list'],
            ['name' => 'create_users', 'description' => 'Can create new users'],
            ['name' => 'edit_users', 'description' => 'Can edit existing users'],
            ['name' => 'delete_users', 'description' => 'Can delete users'],
            ['name' => 'manage_users', 'description' => 'Can manage users (full access)'],
            
            // Role management permissions
            ['name' => 'view_roles', 'description' => 'Can view roles list'],
            ['name' => 'create_roles', 'description' => 'Can create new roles'],
            ['name' => 'edit_roles', 'description' => 'Can edit existing roles'],
            ['name' => 'delete_roles', 'description' => 'Can delete roles'],
            ['name' => 'manage_roles', 'description' => 'Can manage roles (full access)'],
            
            // Permission management permissions
            ['name' => 'view_permissions', 'description' => 'Can view permissions list'],
            ['name' => 'create_permissions', 'description' => 'Can create new permissions'],
            ['name' => 'edit_permissions', 'description' => 'Can edit existing permissions'],
            ['name' => 'delete_permissions', 'description' => 'Can delete permissions'],
            ['name' => 'manage_permissions', 'description' => 'Can manage permissions (full access)'],
            
            // Order permissions (granular)
            ['name' => 'view_orders', 'description' => 'Can view orders list'],
            ['name' => 'create_orders', 'description' => 'Can create new orders'],
            ['name' => 'edit_orders', 'description' => 'Can edit existing orders'],
            ['name' => 'delete_orders', 'description' => 'Can delete orders'],
            ['name' => 'view_order_details', 'description' => 'Can view order details'],
            ['name' => 'update_order_status', 'description' => 'Can update order status'],
            ['name' => 'view_confirmation_orders', 'description' => 'Can view orders pending confirmation'],
            ['name' => 'confirm_orders', 'description' => 'Can confirm orders'],
            ['name' => 'view_delivery_orders', 'description' => 'Can view orders for delivery'],
            ['name' => 'process_delivery_orders', 'description' => 'Can process delivery orders'],
            ['name' => 'import_orders', 'description' => 'Can import orders from files'],
            ['name' => 'export_orders', 'description' => 'Can export orders to files'],
            ['name' => 'manage_orders', 'description' => 'Can manage orders (full access)'],
            // Assign to agent permission
            ['name' => 'assign_orders_to_agents', 'description' => 'Can assign orders to agents'],
            
            // Product permissions (granular)
            ['name' => 'view_products', 'description' => 'Can view products list'],
            ['name' => 'create_products', 'description' => 'Can create new products'],
            ['name' => 'edit_products', 'description' => 'Can edit existing products'],
            ['name' => 'delete_products', 'description' => 'Can delete products'],
            ['name' => 'view_product_details', 'description' => 'Can view product details'],
            ['name' => 'view_product_catalog', 'description' => 'Can view product catalog'],
            ['name' => 'manage_products', 'description' => 'Can manage products (full access)'],
            
            // Category permissions
            ['name' => 'view_categories', 'description' => 'Can view categories list'],
            ['name' => 'create_categories', 'description' => 'Can create new categories'],
            ['name' => 'edit_categories', 'description' => 'Can edit existing categories'],
            ['name' => 'delete_categories', 'description' => 'Can delete categories'],
            ['name' => 'manage_categories', 'description' => 'Can manage categories (full access)'],
            
            // Invoice and PDF permissions
            ['name' => 'view_invoices', 'description' => 'Can view invoices'],
            ['name' => 'create_invoices', 'description' => 'Can create invoices'],
            ['name' => 'download_invoices', 'description' => 'Can download invoices'],
            ['name' => 'view_delivery_notes', 'description' => 'Can view delivery notes'],
            ['name' => 'create_delivery_notes', 'description' => 'Can create delivery notes'],
            ['name' => 'download_delivery_notes', 'description' => 'Can download delivery notes'],
            ['name' => 'view_delivery_invoices', 'description' => 'Can view delivery invoices'],
            ['name' => 'create_delivery_invoices', 'description' => 'Can create delivery invoices'],
            ['name' => 'download_delivery_invoices', 'description' => 'Can download delivery invoices'],
            
            // Accounting permissions (granular)
            ['name' => 'view_accounting', 'description' => 'Can view accounting section'],
            ['name' => 'view_accounting_overview', 'description' => 'Can view accounting overview'],
            
            // Income permissions
            ['name' => 'view_incomes', 'description' => 'Can view incomes list'],
            ['name' => 'create_incomes', 'description' => 'Can create new incomes'],
            ['name' => 'edit_incomes', 'description' => 'Can edit existing incomes'],
            ['name' => 'delete_incomes', 'description' => 'Can delete incomes'],
            ['name' => 'manage_incomes', 'description' => 'Can manage incomes (full access)'],
            
            // Income category permissions
            ['name' => 'view_income_categories', 'description' => 'Can view income categories'],
            ['name' => 'create_income_categories', 'description' => 'Can create income categories'],
            ['name' => 'edit_income_categories', 'description' => 'Can edit income categories'],
            ['name' => 'delete_income_categories', 'description' => 'Can delete income categories'],
            ['name' => 'manage_income_categories', 'description' => 'Can manage income categories (full access)'],
            
            // Expense permissions
            ['name' => 'view_expenses', 'description' => 'Can view expenses list'],
            ['name' => 'create_expenses', 'description' => 'Can create new expenses'],
            ['name' => 'edit_expenses', 'description' => 'Can edit existing expenses'],
            ['name' => 'delete_expenses', 'description' => 'Can delete expenses'],
            ['name' => 'manage_expenses', 'description' => 'Can manage expenses (full access)'],
            
            // Expense category permissions
            ['name' => 'view_expense_categories', 'description' => 'Can view expense categories'],
            ['name' => 'create_expense_categories', 'description' => 'Can create expense categories'],
            ['name' => 'edit_expense_categories', 'description' => 'Can edit expense categories'],
            ['name' => 'delete_expense_categories', 'description' => 'Can delete expense categories'],
            ['name' => 'manage_expense_categories', 'description' => 'Can manage expense categories (full access)'],
            
            // Refund permissions
            ['name' => 'view_refunds', 'description' => 'Can view refunds list'],
            ['name' => 'create_refunds', 'description' => 'Can create new refunds'],
            ['name' => 'edit_refunds', 'description' => 'Can edit existing refunds'],
            ['name' => 'delete_refunds', 'description' => 'Can delete refunds'],
            ['name' => 'manage_refunds', 'description' => 'Can manage refunds (full access)'],
            
            // Transfer permissions
            ['name' => 'view_transfers', 'description' => 'Can view transfers list'],
            ['name' => 'create_transfers', 'description' => 'Can create new transfers'],
            ['name' => 'edit_transfers', 'description' => 'Can edit existing transfers'],
            ['name' => 'delete_transfers', 'description' => 'Can delete transfers'],
            ['name' => 'manage_transfers', 'description' => 'Can manage transfers (full access)'],
            
            // User transfer permissions
            ['name' => 'view_user_transfers', 'description' => 'Can view user transfers'],
            ['name' => 'create_user_transfers', 'description' => 'Can create user transfers'],
            ['name' => 'edit_user_transfers', 'description' => 'Can edit user transfers'],
            ['name' => 'delete_user_transfers', 'description' => 'Can delete user transfers'],
            ['name' => 'manage_user_transfers', 'description' => 'Can manage user transfers (full access)'],
            
            // Account permissions
            ['name' => 'view_accounts', 'description' => 'Can view accounts list'],
            ['name' => 'create_accounts', 'description' => 'Can create new accounts'],
            ['name' => 'edit_accounts', 'description' => 'Can edit existing accounts'],
            ['name' => 'delete_accounts', 'description' => 'Can delete accounts'],
            ['name' => 'manage_accounts', 'description' => 'Can manage accounts (full access)'],
            
            // Settings permissions
            ['name' => 'view_settings', 'description' => 'Can view settings'],
            ['name' => 'edit_settings', 'description' => 'Can edit settings'],
            ['name' => 'manage_settings', 'description' => 'Can manage settings (full access)'],
            
            // Reports permissions
            ['name' => 'view_reports', 'description' => 'Can view reports'],
            ['name' => 'generate_reports', 'description' => 'Can generate reports'],
            ['name' => 'export_reports', 'description' => 'Can export reports'],
            ['name' => 'manage_reports', 'description' => 'Can manage reports (full access)'],
            
            // Support permissions
            ['name' => 'view_support_tickets', 'description' => 'Can view support tickets'],
            ['name' => 'create_support_tickets', 'description' => 'Can create support tickets'],
            ['name' => 'respond_to_tickets', 'description' => 'Can respond to support tickets'],
            ['name' => 'manage_support_tickets', 'description' => 'Can manage support tickets (full access)'],
            
            // Shipment permissions
            ['name' => 'view_shipments', 'description' => 'Can view shipments list'],
            ['name' => 'create_shipments', 'description' => 'Can create new shipments'],
            ['name' => 'edit_shipments', 'description' => 'Can edit existing shipments'],
            ['name' => 'delete_shipments', 'description' => 'Can delete shipments'],
            ['name' => 'validate_shipments', 'description' => 'Can validate shipments'],
            ['name' => 'manage_shipments', 'description' => 'Can manage shipments (full access)'],
            
            // Stock permissions
            ['name' => 'view_stock', 'description' => 'Can view stock list'],
            ['name' => 'manage_stock', 'description' => 'Can manage stock (full access)'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], $permission);
        }

        // Create roles
        $roles = [
            [
                'name' => 'superadmin',
                'description' => 'Super Administrator with all permissions',
            ],
            [
                'name' => 'admin',
                'description' => 'Administrator with comprehensive permissions',
            ],
            [
                'name' => 'manager',
                'description' => 'Manager with specific business permissions',
            ],
            [
                'name' => 'agent',
                'description' => 'Regular agent with basic permissions',
            ],
            [
                'name' => 'accountant',
                'description' => 'Accountant with financial permissions',
            ],
            [
                'name' => 'sales_agent',
                'description' => 'Sales agent with order and product permissions',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }

        // Assign permissions to roles
        $superadminRole = Role::where('name', 'superadmin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $agentRole = Role::where('name', 'agent')->first();
        $accountantRole = Role::where('name', 'accountant')->first();
        $salesAgentRole = Role::where('name', 'sales_agent')->first();

        // Superadmin gets all permissions
        $superadminRole->permissions()->syncWithoutDetaching(Permission::all());

        // Admin gets all except role and permission management
        $adminRole->permissions()->syncWithoutDetaching(
            Permission::whereNotIn('name', [
                'manage_roles', 'create_roles', 'edit_roles', 'delete_roles',
                'manage_permissions', 'create_permissions', 'edit_permissions', 'delete_permissions'
            ])->get()
        );

        // Manager gets comprehensive business permissions
        $managerRole->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                // Dashboard
                'view_dashboard', 'view_dashboard_overview', 'view_dashboard_analytics',
                
                // Orders (full access)
                'view_orders', 'create_orders', 'edit_orders', 'view_order_details', 
                'update_order_status', 'view_confirmation_orders', 'confirm_orders',
                'view_delivery_orders', 'process_delivery_orders', 'import_orders', 'export_orders',
                
                // Products (full access)
                'view_products', 'create_products', 'edit_products', 'view_product_details',
                'view_product_catalog',
                
                // Categories
                'view_categories', 'create_categories', 'edit_categories',
                
                // Invoices and PDFs
                'view_invoices', 'create_invoices', 'download_invoices',
                'view_delivery_notes', 'create_delivery_notes', 'download_delivery_notes',
                'view_delivery_invoices', 'create_delivery_invoices', 'download_delivery_invoices',
                
                // Accounting (full access)
                'view_accounting', 'view_accounting_overview',
                'view_incomes', 'create_incomes', 'edit_incomes',
                'view_income_categories', 'create_income_categories', 'edit_income_categories',
                'view_expenses', 'create_expenses', 'edit_expenses',
                'view_expense_categories', 'create_expense_categories', 'edit_expense_categories',
                'view_refunds', 'create_refunds', 'edit_refunds',
                'view_transfers', 'create_transfers', 'edit_transfers',
                'view_user_transfers', 'create_user_transfers', 'edit_user_transfers',
                'view_accounts', 'create_accounts', 'edit_accounts',
                
                // Shipments and Stock (full access)
                'view_shipments', 'create_shipments', 'edit_shipments', 'validate_shipments',
                'view_stock', 'manage_stock',
                
                // Reports
                'view_reports', 'generate_reports', 'export_reports',
                
                // Support
                'view_support_tickets', 'create_support_tickets', 'respond_to_tickets',
            ])->get()
        );

        // Agent gets basic permissions
        $agentRole->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                // Dashboard
                'view_dashboard', 'view_dashboard_overview',
                
                // Orders (view and create only)
                'view_orders', 'create_orders', 'view_order_details',
                'view_confirmation_orders', 'view_delivery_orders',
                
                // Products (view only)
                'view_products', 'view_product_details', 'view_product_catalog',
                
                // Categories (view only)
                'view_categories',
                
                // Basic invoices
                'view_invoices', 'view_delivery_notes',
                
                // Basic accounting
                'view_accounting', 'view_incomes', 'create_incomes',
                'view_expenses', 'create_expenses',
                
                // Shipments (view and validate)
                'view_shipments', 'validate_shipments', 'view_stock',
                
                // Support
                'view_support_tickets', 'create_support_tickets',
            ])->get()
        );

        // Accountant gets financial permissions
        $accountantRole->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                // Dashboard
                'view_dashboard', 'view_dashboard_overview', 'view_dashboard_analytics',
                
                // Orders (view only)
                'view_orders', 'view_order_details',
                
                // Products (view only)
                'view_products', 'view_product_details',
                
                // Accounting (full access)
                'view_accounting', 'view_accounting_overview',
                'view_incomes', 'create_incomes', 'edit_incomes', 'delete_incomes',
                'view_income_categories', 'create_income_categories', 'edit_income_categories', 'delete_income_categories',
                'view_expenses', 'create_expenses', 'edit_expenses', 'delete_expenses',
                'view_expense_categories', 'create_expense_categories', 'edit_expense_categories', 'delete_expense_categories',
                'view_refunds', 'create_refunds', 'edit_refunds', 'delete_refunds',
                'view_transfers', 'create_transfers', 'edit_transfers', 'delete_transfers',
                'view_user_transfers', 'create_user_transfers', 'edit_user_transfers', 'delete_user_transfers',
                'view_accounts', 'create_accounts', 'edit_accounts', 'delete_accounts',
                
                // Reports
                'view_reports', 'generate_reports', 'export_reports',
            ])->get()
        );

        // Sales Agent gets order and product permissions
        $salesAgentRole->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                // Dashboard
                'view_dashboard', 'view_dashboard_overview',
                
                // Orders (full access)
                'view_orders', 'create_orders', 'edit_orders', 'view_order_details', 
                'update_order_status', 'view_confirmation_orders', 'confirm_orders',
                'view_delivery_orders', 'process_delivery_orders', 'import_orders', 'export_orders',
                
                // Products (full access)
                'view_products', 'create_products', 'edit_products', 'view_product_details',
                'view_product_catalog',
                
                // Categories
                'view_categories', 'create_categories', 'edit_categories',
                
                // Invoices and PDFs
                'view_invoices', 'create_invoices', 'download_invoices',
                'view_delivery_notes', 'create_delivery_notes', 'download_delivery_notes',
                'view_delivery_invoices', 'create_delivery_invoices', 'download_delivery_invoices',
                
                // Shipments (full access)
                'view_shipments', 'create_shipments', 'edit_shipments', 'validate_shipments',
                'view_stock', 'manage_stock',
                
                // Basic accounting (view only)
                'view_accounting', 'view_incomes', 'view_expenses',
                
                // Support
                'view_support_tickets', 'create_support_tickets',
            ])->get()
        );

        // Create superadmin user
        $superadmin = User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'),
            ]
        );

        // Assign superadmin role
        $superadmin->roles()->syncWithoutDetaching($superadminRole);
    }
}
