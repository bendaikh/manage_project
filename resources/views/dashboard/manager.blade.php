<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manager Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Welcome, {{ Auth::user()->name }}!</h3>
                    <p class="text-gray-600 mb-4">You are logged in as a <strong>Manager</strong>.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div class="bg-orange-50 p-6 rounded-lg">
                            <h4 class="font-semibold text-orange-800 mb-2">Team Management</h4>
                            <p class="text-orange-600">Manage your team members</p>
                        </div>
                        <div class="bg-indigo-50 p-6 rounded-lg">
                            <h4 class="font-semibold text-indigo-800 mb-2">Project Overview</h4>
                            <p class="text-indigo-600">View project status and progress</p>
                        </div>
                        <div class="bg-teal-50 p-6 rounded-lg">
                            <h4 class="font-semibold text-teal-800 mb-2">Performance</h4>
                            <p class="text-teal-600">Track team performance metrics</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 