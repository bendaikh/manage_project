<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manager Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Orders Management Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Orders Management</h3>
                        <p class="text-gray-600 mb-4">View and manage all orders in the system.</p>
                        <a href="{{ route('orders.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Manage Orders
                        </a>
                    </div>
                </div>

                <!-- Products Management Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Products Management</h3>
                        <p class="text-gray-600 mb-4">Manage products and their categories.</p>
                        <a href="{{ route('products.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Manage Products
                        </a>
                    </div>
                </div>

                <!-- Reports Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reports</h3>
                        <p class="text-gray-600 mb-4">View sales and performance reports.</p>
                        <a href="{{ route('reports.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            View Reports
                        </a>
                    </div>
                </div>

                <!-- Performance Overview Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Overview</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Today's Orders</p>
                                <p class="text-2xl font-semibold text-gray-900">0</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Products</p>
                                <p class="text-2xl font-semibold text-gray-900">0</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Monthly Revenue</p>
                                <p class="text-2xl font-semibold text-gray-900">$0</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 