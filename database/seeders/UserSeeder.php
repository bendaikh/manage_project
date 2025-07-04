<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'adminuser',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        // Create manager user
        $manager = User::create([
            'name' => 'Manager User',
            'username' => 'manageruser',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
        ]);
        $manager->roles()->attach(Role::where('name', 'manager')->first());

        // Create agent user
        $agent = User::create([
            'name' => 'Agent User',
            'username' => 'agentuser',
            'email' => 'agent@example.com',
            'password' => Hash::make('password'),
        ]);
        $agent->roles()->attach(Role::where('name', 'agent')->first());
    }
}
