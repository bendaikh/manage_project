<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}

                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-2">Recent Activity</h3>
                        @php
                            $recentHistories = \App\Models\ActionHistory::latest()->take(5)->get();
                        @endphp
                        <ul>
                            @forelse($recentHistories as $history)
                                <li class="mb-1 text-sm">
                                    <span class="font-semibold">{{ $history->title }}</span>
                                    <span class="text-gray-600">— {{ $history->user->name ?? 'System' }} • {{ $history->created_at->diffForHumans() }}</span>
                                </li>
                            @empty
                                <li class="text-gray-500 text-sm">No recent activity.</li>
                            @endforelse
                        </ul>
                        <div class="mt-2">
                            <a href="{{ route('history.index') }}" class="text-blue-600 hover:underline text-sm">See more</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
