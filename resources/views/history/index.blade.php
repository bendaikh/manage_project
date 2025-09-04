@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Action History</h1>
    
    <!-- Filter Section -->
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
        <form method="GET" action="{{ route('history.index') }}" class="space-y-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-700">Filters</h3>
                <a href="{{ route('history.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    Clear All Filters
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input 
                        name="date_from" 
                        type="date" 
                        value="{{ request('date_from') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input 
                        name="date_to" 
                        type="date" 
                        value="{{ request('date_to') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                
                <!-- User Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    <select 
                        name="user_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Title Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input 
                        name="title" 
                        type="text" 
                        value="{{ request('title') }}"
                        placeholder="Search in title..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
            </div>
            
            <!-- Description Filter (Full Width) -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <input 
                    name="description" 
                    type="text" 
                    value="{{ request('description') }}"
                    placeholder="Search in description..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>
            
            <!-- Filter Actions -->
            <div class="mt-4 flex justify-end gap-2">
                <button 
                    type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    Apply Filters
                </button>
                <a 
                    href="{{ route('history.index') }}" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
                >
                    Clear
                </a>
            </div>
        </form>
    </div>
    
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($histories as $history)
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $history->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $history->user->name ?? 'System' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-semibold">{{ $history->title }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $history->description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-4 text-center text-gray-500">No history records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $histories->appends(request()->query())->links() }}
    </div>
</div>
@endsection 