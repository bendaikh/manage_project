<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Product Catalog') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Product Catalog</h3>
                    <p class="text-gray-600 mb-4">This section allows agents to view product information and availability.</p>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-blue-800 text-sm">
                            <strong>Note:</strong> Product catalog functionality is currently under development. 
                            This page will be updated with actual catalog features soon.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 