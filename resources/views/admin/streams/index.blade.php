@extends('layouts.admin')

@section('page-title', 'Streams & Channels')
@section('page-subtitle', 'Manage all live streams, matches, TV channels, and server fallback links')

@section('content')
<div class="glass-panel p-6 rounded-3xl shadow-xl">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 space-y-4 sm:space-y-0">
        <div>
            <h3 class="text-xl font-bold text-white">Stream Inventory</h3>
            <p class="text-xs text-gray-400 mt-1">Configure stream availability across tabs, dynamic match settings, and stream servers</p>
        </div>
        <a href="{{ route('admin.streams.create') }}" class="py-2.5 px-5 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-sm shadow-lg shadow-cyan-500/15 transition-all">
            + Add Stream / Channel
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-gray-950/40 text-gray-400 uppercase text-xs font-semibold border-b border-gray-800">
                <tr>
                    <th class="p-4">Stream Name</th>
                    <th class="p-4">Sport Type</th>
                    <th class="p-4">Tabs Display</th>
                    <th class="p-4">Categories</th>
                    <th class="p-4">Time Settings</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60">
                @forelse($streams as $stream)
                    <tr class="hover:bg-gray-900/20 transition-colors">
                        <td class="p-4 flex items-center space-x-3">
                            <img src="{{ $stream->logo ?: 'https://crichd.xyz/assets/images/live.png' }}" alt="" class="w-11 h-11 object-contain rounded-xl bg-gray-900/60 p-1 border border-gray-800">
                            <div>
                                <span class="font-medium text-white block">{{ $stream->name }}</span>
                                @if($stream->team1_name && $stream->team2_name)
                                    <span class="text-xs text-gray-400 block mt-0.5">
                                        {{ $stream->team1_name }} vs {{ $stream->team2_name }}
                                    </span>
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
                        <td class="p-4">
                            <div class="flex flex-wrap gap-1 max-w-[150px]">
                                @forelse($stream->categories as $cat)
                                    <span class="px-1.5 py-0.5 rounded bg-gray-950 text-gray-400 text-[10px] border border-gray-800">{{ $cat->name }}</span>
                                @empty
                                    <span class="text-gray-600 text-xs">-</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="p-4 text-xs">
                            @if($stream->is_permanent)
                                <span class="text-emerald-400 font-semibold">Permanent</span>
                            @else
                                <div class="space-y-0.5">
                                    <span class="text-gray-300 block"><span class="text-gray-500">Starts:</span> {{ $stream->start_time?->format('M d, g:i A') }}</span>
                                    <span class="text-gray-300 block"><span class="text-gray-500">Expires:</span> {{ $stream->expire_time?->format('M d, g:i A') }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($stream->is_active)
                                <span class="flex items-center space-x-1.5 text-emerald-400 text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                    <span>Active</span>
                                </span>
                            @else
                                <span class="flex items-center space-x-1.5 text-gray-500 text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 bg-gray-600 rounded-full"></span>
                                    <span>Inactive</span>
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-right space-x-3">
                            <a href="{{ route('admin.streams.edit', $stream->id) }}" class="text-cyan-400 hover:text-cyan-300 font-semibold text-xs">Edit</a>
                            
                            <form action="{{ route('admin.streams.destroy', $stream->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this stream?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 font-semibold text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-500">No streams found. Click "Add Stream" to start.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
