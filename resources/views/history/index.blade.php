@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Action History</h1>
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
        {{ $histories->links() }}
    </div>
</div>
@endsection 