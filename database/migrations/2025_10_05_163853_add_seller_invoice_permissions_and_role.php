<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add seller invoice permissions
        $permissions = [
            ['name' => 'view_seller_invoices', 'description' => 'Can view seller invoices list'],
            ['name' => 'create_seller_invoices', 'description' => 'Can create seller invoices'],
            ['name' => 'edit_seller_invoices', 'description' => 'Can edit seller invoices'],
            ['name' => 'delete_seller_invoices', 'description' => 'Can delete seller invoices'],
            ['name' => 'download_seller_invoices', 'description' => 'Can download seller invoices'],
            ['name' => 'approve_seller_invoices', 'description' => 'Can approve seller invoices'],
            ['name' => 'reject_seller_invoices', 'description' => 'Can reject seller invoices'],
            ['name' => 'mark_seller_invoices_paid', 'description' => 'Can mark seller invoices as paid'],
            ['name' => 'generate_seller_invoices', 'description' => 'Can generate seller invoices'],
            ['name' => 'manage_seller_invoices', 'description' => 'Can manage seller invoices (full access)'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], $permission);
        }

        // Add seller role
        $sellerRole = Role::updateOrCreate(
            ['name' => 'seller'],
            ['description' => 'Seller with limited access to their own invoices']
        );

        // Assign basic permissions to seller role
        $sellerRole->permissions()->syncWithoutDetaching(
            Permission::whereIn('name', [
                'view_dashboard', 
                'view_dashboard_overview',
                'view_seller_invoices', 
                'download_seller_invoices',
                'view_accounting', 
                'view_incomes', 
                'view_expenses',
            ])->get()
        );

        // Assign seller invoice permissions to superadmin role
        $superadminRole = Role::where('name', 'superadmin')->first();
        if ($superadminRole) {
            $superadminRole->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', [
                    'view_seller_invoices',
                    'create_seller_invoices',
                    'edit_seller_invoices',
                    'delete_seller_invoices',
                    'download_seller_invoices',
                    'approve_seller_invoices',
                    'reject_seller_invoices',
                    'mark_seller_invoices_paid',
                    'generate_seller_invoices',
                    'manage_seller_invoices',
                ])->get()
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove seller role
        $sellerRole = Role::where('name', 'seller')->first();
        if ($sellerRole) {
            $sellerRole->delete();
        }

        // Remove seller invoice permissions
        Permission::whereIn('name', [
            'view_seller_invoices',
            'create_seller_invoices',
            'edit_seller_invoices',
            'delete_seller_invoices',
            'download_seller_invoices',
            'approve_seller_invoices',
            'reject_seller_invoices',
            'mark_seller_invoices_paid',
            'generate_seller_invoices',
            'manage_seller_invoices',
        ])->delete();
    }
};