<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Agent Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Orders Processing Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Orders Processing</h3>
                        <p class="text-gray-600 mb-4">Process and manage customer orders.</p>
                        <a href="{{ route('orders.process') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Process Orders
                        </a>
                    </div>
                </div>

                <!-- Customer Support Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Support</h3>
                        <p class="text-gray-600 mb-4">Handle customer inquiries and support tickets.</p>
                        <a href="{{ route('support.tickets') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            View Tickets
                        </a>
                    </div>
                </div>

                <!-- Product Catalog Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Catalog</h3>
                        <p class="text-gray-600 mb-4">View product information and availability.</p>
                        <a href="{{ route('products.catalog') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            View Catalog
                        </a>
                    </div>
                </div>

                <!-- Performance Overview Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Overview</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Orders Processed Today</p>
                                <p class="text-2xl font-semibold text-gray-900">0</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Open Support Tickets</p>
                                <p class="text-2xl font-semibold text-gray-900">0</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Customer Satisfaction</p>
                                <p class="text-2xl font-semibold text-gray-900">100%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 