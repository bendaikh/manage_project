<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $superadminRole = Role::where('name', 'superadmin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $agentRole = Role::where('name', 'agent')->first();

        // Create test users for each role
        $users = [
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'),
                'role' => $superadminRole
            ],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => $adminRole
            ],
            [
                'name' => 'Manager User',
                'username' => 'manager',
                'email' => 'manager@example.com',
                'password' => Hash::make('password'),
                'role' => $managerRole
            ],
            [
                'name' => 'Agent User',
                'username' => 'agent',
                'email' => 'agent@example.com',
                'password' => Hash::make('password'),
                'role' => $agentRole
            ]
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);
            
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            
            // Assign role if not already assigned
            if (!$user->hasRole($role->name)) {
                $user->roles()->attach($role);
            }
        }
    }
}
