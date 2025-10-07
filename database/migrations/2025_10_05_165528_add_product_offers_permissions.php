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
        // Add product offers permissions
        $productOffersPermissions = [
            ['name' => 'view_product_offers', 'description' => 'Can view product offers list'],
            ['name' => 'toggle_product_offers', 'description' => 'Can activate/deactivate product offers'],
            ['name' => 'manage_product_offers', 'description' => 'Can manage product offers (full access)'],
        ];

        foreach ($productOffersPermissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], $permission);
        }

        // Assign permissions to roles
        $superadminRole = Role::where('name', 'superadmin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $agentRole = Role::where('name', 'agent')->first();
        $salesAgentRole = Role::where('name', 'sales_agent')->first();

        if ($superadminRole) {
            // Superadmin gets all permissions (already handled by existing logic)
            $superadminRole->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['view_product_offers', 'toggle_product_offers', 'manage_product_offers'])->get()
            );
        }

        if ($adminRole) {
            // Admin gets all permissions
            $adminRole->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['view_product_offers', 'toggle_product_offers', 'manage_product_offers'])->get()
            );
        }

        if ($managerRole) {
            // Manager gets all product offers permissions
            $managerRole->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['view_product_offers', 'toggle_product_offers', 'manage_product_offers'])->get()
            );
        }

        if ($agentRole) {
            // Agent gets view only
            $agentRole->permissions()->syncWithoutDetaching(
                Permission::where('name', 'view_product_offers')->get()
            );
        }

        if ($salesAgentRole) {
            // Sales Agent gets all permissions
            $salesAgentRole->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', ['view_product_offers', 'toggle_product_offers', 'manage_product_offers'])->get()
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove permissions from roles
        $roles = Role::whereIn('name', ['superadmin', 'admin', 'manager', 'agent', 'sales_agent'])->get();
        $permissions = Permission::whereIn('name', ['view_product_offers', 'toggle_product_offers', 'manage_product_offers'])->get();
        
        foreach ($roles as $role) {
            $role->permissions()->detach($permissions);
        }

        // Delete the permissions
        Permission::whereIn('name', ['view_product_offers', 'toggle_product_offers', 'manage_product_offers'])->delete();
    }
};
