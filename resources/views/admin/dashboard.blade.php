@extends('layouts.admin')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of AllTV application status and streaming assets')

@section('content')
<!-- Statistics Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <!-- Stat Card 1 -->
    <div class="glass-card p-6 rounded-2xl flex items-center justify-between">
        <div>
            <span class="text-xs font-semibold text-cyan-400 uppercase tracking-wider">Total Categories</span>
            <h3 class="text-4xl font-bold text-white mt-2">{{ $stats['total_categories'] }}</h3>
            <a href="{{ route('admin.categories.index') }}" class="text-xs text-gray-400 hover:text-cyan-400 mt-2 block transition-colors">Manage categories &rarr;</a>
        </div>
        <div class="p-3 bg-cyan-500/10 text-cyan-400 rounded-xl">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
    </div>

    <!-- Stat Card 2 -->
    <div class="glass-card p-6 rounded-2xl flex items-center justify-between">
        <div>
            <span class="text-xs font-semibold text-blue-400 uppercase tracking-wider">Total Streams</span>
            <h3 class="text-4xl font-bold text-white mt-2">{{ $stats['total_streams'] }}</h3>
            <a href="{{ route('admin.streams.index') }}" class="text-xs text-gray-400 hover:text-blue-400 mt-2 block transition-colors">Manage streams &rarr;</a>
        </div>
        <div class="p-3 bg-blue-500/10 text-blue-400 rounded-xl">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        </div>
    </div>

    <!-- Stat Card 3 -->
    <div class="glass-card p-6 rounded-2xl flex items-center justify-between">
        <div>
            <span class="text-xs font-semibold text-pink-400 uppercase tracking-wider">Live Events</span>
            <h3 class="text-4xl font-bold text-white mt-2">{{ $stats['live_events'] }}</h3>
            <span class="text-xs text-gray-400 mt-2 block">Active upcoming matches</span>
        </div>
        <div class="p-3 bg-pink-500/10 text-pink-400 rounded-xl">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>

    <!-- Stat Card 4 -->
    <div class="glass-card p-6 rounded-2xl flex items-center justify-between">
        <div>
            <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Active Streams</span>
            <h3 class="text-4xl font-bold text-white mt-2">{{ $stats['active_streams'] }}</h3>
            <span class="text-xs text-gray-400 mt-2 block">Online right now</span>
        </div>
        <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-xl">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>
</div>

<!-- Recent Streams Table -->
<div class="glass-panel p-6 rounded-3xl shadow-xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-bold text-white">Recently Added Streams</h3>
            <p class="text-xs text-gray-400 mt-1">Latest channels and streams registered in the system</p>
        </div>
        <a href="{{ route('admin.streams.create') }}" class="py-2.5 px-4 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-xs shadow-md transition-all">
            + New Stream
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-gray-950/40 text-gray-400 uppercase text-xs font-semibold border-b border-gray-800">
                <tr>
                    <th class="p-4">Stream Name</th>
                    <th class="p-4">Sport Type</th>
                    <th class="p-4">Display Tabs</th>
                    <th class="p-4">Availability</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60">
                @forelse($recentStreams as $stream)
                    <tr class="hover:bg-gray-900/20 transition-colors">
                        <td class="p-4 flex items-center space-x-3">
                            <img src="{{ $stream->logo ?: 'https://crichd.xyz/assets/images/live.png' }}" alt="" class="w-10 h-10 object-contain rounded-lg bg-gray-900/60 p-1 border border-gray-800">
                            <div>
                                <span class="font-medium text-white block">{{ $stream->name }}</span>
                                @if($stream->team1_name && $stream->team2_name)
                                    <span class="text-xs text-gray-400 block">{{ $stream->team1_name }} vs {{ $stream->team2_name }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-800 border border-gray-700 capitalize">
                                {{ $stream->sport_type }}
                            </span>
                        </td>
                        <td class="p-4 space-x-1">
                            @if($stream->show_in_events)
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-pink-500/10 text-pink-400 border border-pink-500/20">Events</span>
                            @endif
                            @if($stream->show_in_sports)
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">Sports</span>
                            @endif
                            @if($stream->show_in_tv)
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">TV</span>
                            @endif
                        </td>
                        <td class="p-4 text-xs">
                            @if($stream->is_permanent)
                                <span class="text-emerald-400 font-medium">Permanent</span>
                            @else
                                <span class="text-gray-400">
                                    {{ $stream->start_time?->format('M d, g:i A') }} - {{ $stream->expire_time?->format('g:i A') }}
                                </span>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($stream->is_active)
                                <span class="flex items-center space-x-1.5 text-emerald-400 text-xs font-medium">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                    <span>Active</span>
                                </span>
                            @else
                                <span class="flex items-center space-x-1.5 text-gray-500 text-xs font-medium">
                                    <span class="w-1.5 h-1.5 bg-gray-600 rounded-full"></span>
                                    <span>Inactive</span>
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('admin.streams.edit', $stream->id) }}" class="text-cyan-400 hover:text-cyan-300 font-semibold text-xs">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">No streams found. Add a stream to get started.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
