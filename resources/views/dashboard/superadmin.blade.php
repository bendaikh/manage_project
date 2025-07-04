<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Users Management Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Users Management</h3>
                        <p class="text-gray-600 mb-4">Manage user accounts and their roles.</p>
                        <a href="{{ route('users.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Manage Users
                        </a>
                    </div>
                </div>

                <!-- Roles Management Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Roles Management</h3>
                        <p class="text-gray-600 mb-4">Create and manage user roles and their permissions.</p>
                        <a href="{{ route('roles.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Manage Roles
                        </a>
                    </div>
                </div>

                <!-- Permissions Management Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Permissions Management</h3>
                        <p class="text-gray-600 mb-4">Define and manage system permissions.</p>
                        <a href="{{ route('permissions.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Manage Permissions
                        </a>
                    </div>
                </div>

                <!-- System Overview Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">System Overview</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Users</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\User::count() }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Roles</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\Role::count() }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Permissions</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\Permission::count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 