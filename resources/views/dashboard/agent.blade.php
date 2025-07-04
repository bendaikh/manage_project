<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Agent Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Welcome, {{ Auth::user()->name }}!</h3>
                    <p class="text-gray-600 mb-4">You are logged in as an <strong>Agent</strong>.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div class="bg-red-50 p-6 rounded-lg">
                            <h4 class="font-semibold text-red-800 mb-2">My Tasks</h4>
                            <p class="text-red-600">View and manage your assigned tasks</p>
                        </div>
                        <div class="bg-yellow-50 p-6 rounded-lg">
                            <h4 class="font-semibold text-yellow-800 mb-2">Customer Support</h4>
                            <p class="text-yellow-600">Handle customer inquiries and tickets</p>
                        </div>
                        <div class="bg-pink-50 p-6 rounded-lg">
                            <h4 class="font-semibold text-pink-800 mb-2">My Profile</h4>
                            <p class="text-pink-600">Update your profile information</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 