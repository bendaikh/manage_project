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
        // Create permissions
        $permissions = [
            ['name' => 'view_dashboard', 'description' => 'Can view dashboard'],
            ['name' => 'manage_users', 'description' => 'Can manage users'],
            ['name' => 'manage_roles', 'description' => 'Can manage roles'],
            ['name' => 'manage_permissions', 'description' => 'Can manage permissions'],
            ['name' => 'view_orders', 'description' => 'Can view orders'],
            ['name' => 'manage_orders', 'description' => 'Can manage orders'],
            ['name' => 'view_products', 'description' => 'Can view products'],
            ['name' => 'manage_products', 'description' => 'Can manage products'],
            // Accounting permissions
            ['name' => 'view_accounting', 'description' => 'Can view accounting section'],
            ['name' => 'manage_incomes', 'description' => 'Can manage incomes'],
            ['name' => 'manage_expenses', 'description' => 'Can manage expenses'],
            ['name' => 'manage_refunds', 'description' => 'Can manage refunds'],
            ['name' => 'manage_transfers', 'description' => 'Can manage transfers'],
            ['name' => 'manage_accounts', 'description' => 'Can manage accounts'],
            ['name' => 'manage_income_categories', 'description' => 'Can manage income categories'],
            ['name' => 'manage_expense_categories', 'description' => 'Can manage expense categories'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create roles
        $roles = [
            [
                'name' => 'superadmin',
                'description' => 'Super Administrator with all permissions',
            ],
            [
                'name' => 'admin',
                'description' => 'Administrator with limited permissions',
            ],
            [
                'name' => 'manager',
                'description' => 'Manager with specific permissions',
            ],
            [
                'name' => 'agent',
                'description' => 'Regular agent with basic permissions',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        // Assign permissions to roles
        $superadminRole = Role::where('name', 'superadmin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $agentRole = Role::where('name', 'agent')->first();

        // Superadmin gets all permissions
        $superadminRole->permissions()->attach(Permission::all());

        // Admin gets all except manage_roles and manage_permissions
        $adminRole->permissions()->attach(
            Permission::whereNotIn('name', ['manage_roles', 'manage_permissions'])->get()
        );

        // Manager gets view permissions, manage_orders, and accounting permissions
        $managerRole->permissions()->attach(
            Permission::whereIn('name', [
                'view_dashboard', 
                'view_orders', 
                'manage_orders', 
                'view_products',
                'view_accounting',
                'manage_incomes',
                'manage_expenses',
                'manage_refunds',
                'manage_transfers',
                'manage_accounts',
                'manage_income_categories',
                'manage_expense_categories'
            ])->get()
        );

        // Agent gets only view permissions and basic accounting
        $agentRole->permissions()->attach(
            Permission::whereIn('name', [
                'view_dashboard', 
                'view_orders', 
                'view_products',
                'view_accounting',
                'manage_incomes',
                'manage_expenses'
            ])->get()
        );

        // Create superadmin user
        $superadmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
        ]);

        // Assign superadmin role
        $superadmin->roles()->attach($superadminRole);
    }
}
