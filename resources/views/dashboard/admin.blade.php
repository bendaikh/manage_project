<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Welcome, {{ Auth::user()->name }}!</h3>
                    <p class="text-gray-600 mb-4">You are logged in as an <strong>Admin</strong>.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h4 class="font-semibold text-blue-800 mb-2">User Management</h4>
                            <p class="text-blue-600">Manage all users and their roles</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg">
                            <h4 class="font-semibold text-green-800 mb-2">System Settings</h4>
                            <p class="text-green-600">Configure application settings</p>
                        </div>
                        <div class="bg-purple-50 p-6 rounded-lg">
                            <h4 class="font-semibold text-purple-800 mb-2">Reports</h4>
                            <p class="text-purple-600">View system reports and analytics</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 