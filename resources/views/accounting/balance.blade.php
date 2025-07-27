<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Balance') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-green-100 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-800">Total Revenue</h3>
                            <p class="text-3xl font-bold text-green-900 mt-2">${{ number_format($totalIncome, 2) }}</p>
                        </div>
                        <div class="bg-red-100 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-red-800">Total Expenses</h3>
                            <p class="text-3xl font-bold text-red-900 mt-2">${{ number_format($totalExpense, 2) }}</p>
                        </div>
                        <div class="bg-blue-100 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-800">Balance</h3>
                            <p class="text-3xl font-bold text-blue-900 mt-2">${{ number_format($balance, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 