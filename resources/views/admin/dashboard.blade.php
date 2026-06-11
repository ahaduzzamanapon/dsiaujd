@extends('layouts.admin')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of AllTV application status and streaming assets')

@section('content')
<!-- Statistics Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-10">
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

    <!-- Stat Card 5 (Total Installs) -->
    <div class="glass-card p-6 rounded-2xl flex items-center justify-between">
        <div>
            <span class="text-xs font-semibold text-purple-400 uppercase tracking-wider">Total Installs</span>
            <h3 class="text-4xl font-bold text-white mt-2">{{ $stats['total_devices'] }}</h3>
            <span class="text-xs text-gray-400 mt-2 block">Total registered phones</span>
        </div>
        <div class="p-3 bg-purple-500/10 text-purple-400 rounded-xl">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
        </div>
    </div>

    <!-- Stat Card 6 (Active App Users) -->
    <div class="glass-card p-6 rounded-2xl flex items-center justify-between">
        <div>
            <span class="text-xs font-semibold text-cyan-400 uppercase tracking-wider">Active Users</span>
            <h3 class="text-4xl font-bold text-white mt-2">{{ $stats['active_devices'] }}</h3>
            <span class="text-xs text-gray-400 mt-2 block">Online right now</span>
        </div>
        <div class="p-3 bg-cyan-500/10 text-cyan-400 rounded-xl">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
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

<!-- Recent App Devices Table -->
<div class="glass-panel p-6 rounded-3xl shadow-xl mt-10">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-bold text-white">Active App Devices</h3>
            <p class="text-xs text-gray-400 mt-1">Status of mobile phones running AllTV app</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></span>
            <span class="text-xs font-semibold text-gray-400">Live Session Tracking</span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-gray-950/40 text-gray-400 uppercase text-xs font-semibold border-b border-gray-800">
                <tr>
                    <th class="p-4">Device Info</th>
                    <th class="p-4">Platform</th>
                    <th class="p-4">App Version</th>
                    <th class="p-4">OS Version</th>
                    <th class="p-4">Last Ping Time</th>
                    <th class="p-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60">
                @forelse($recentDevices as $device)
                    @php
                        $isOnline = $device->last_ping_at && $device->last_ping_at->greaterThanOrEqualTo(now()->subMinutes(5));
                    @endphp
                    <tr class="hover:bg-gray-900/20 transition-colors">
                        <td class="p-4">
                            <span class="font-medium text-white block">{{ $device->model ?: 'Generic Device' }}</span>
                            <span class="text-[10px] text-gray-500 font-mono block">{{ $device->uuid }}</span>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-800 border border-gray-700 capitalize flex items-center gap-1.5 w-fit">
                                @if(strtolower($device->platform) === 'android')
                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.523 15.3l1.816 3.146a.5.5 0 01-.173.682l-.42.242a.5.5 0 01-.682-.172l-1.83-3.17A8.939 8.939 0 0112 17a8.939 8.939 0 01-4.234-.972l-1.83 3.17a.5.5 0 01-.682.172l-.42-.242a.5.5 0 01-.173-.682L6.477 15.3A8.977 8.977 0 013 7.994V7h18v.994a8.977 8.977 0 01-3.477 7.306zM7 11.5a1 1 0 100-2 1 1 0 000 2zm10 0a1 1 0 100-2 1 1 0 000 2zM12 2a1 1 0 011 1v2.05A8.995 8.995 0 0121 14H3a8.995 8.995 0 018-8.95V3a1 1 0 011-1z"/>
                                    </svg>
                                    <span>Android</span>
                                @elseif(strtolower($device->platform) === 'ios')
                                    <svg class="w-3.5 h-3.5 fill-current text-white" viewBox="0 0 24 24">
                                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 4.17c.66-.81 1.11-1.93.99-3.06-1 .04-2.2.67-2.92 1.5-.63.72-1.18 1.87-1.03 2.97 1.1.09 2.2-.57 2.96-1.41z"/>
                                    </svg>
                                    <span>iOS</span>
                                @else
                                    <span>{{ $device->platform ?: 'Unknown' }}</span>
                                @endif
                            </span>
                        </td>
                        <td class="p-4 text-xs font-mono">v{{ $device->app_version ?: '1.0.0' }}</td>
                        <td class="p-4 text-xs font-mono">{{ $device->os_version ?: 'N/A' }}</td>
                        <td class="p-4 text-xs">
                            {{ $device->last_ping_at ? $device->last_ping_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="p-4">
                            @if($isOnline)
                                <span class="flex items-center space-x-1.5 text-emerald-400 text-xs font-medium">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span>
                                    <span>Online</span>
                                </span>
                            @else
                                <span class="flex items-center space-x-1.5 text-gray-500 text-xs font-medium">
                                    <span class="w-1.5 h-1.5 bg-gray-600 rounded-full"></span>
                                    <span>Offline</span>
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">No active devices tracked yet. Let users launch the mobile app.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
