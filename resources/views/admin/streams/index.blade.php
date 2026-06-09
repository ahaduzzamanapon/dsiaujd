@extends('layouts.admin')

@section('page-title', 'Streams & Channels')
@section('page-subtitle', 'Manage all live streams, matches, TV channels, and server fallback links')

@section('content')
<div class="glass-panel p-6 rounded-3xl shadow-xl">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 space-y-4 sm:space-y-0">
        <div>
            <h3 class="text-xl font-bold text-white">Stream Inventory</h3>
            <p class="text-xs text-gray-400 mt-1">Configure stream availability across tabs, dynamic match settings, and stream servers</p>
        </div>
        <a href="{{ route('admin.streams.create') }}" class="py-2.5 px-5 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-sm shadow-lg shadow-cyan-500/15 transition-all">
            + Add Stream / Channel
        </a>
    </div>

    <!-- Filters & Bulk Actions control bar -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-8 pb-6 border-b border-gray-800/60">
        <!-- Category Filter -->
        <form action="{{ route('admin.streams.index') }}" method="GET" class="flex items-center space-x-3 w-full md:w-auto">
            <select name="category_id" onchange="this.form.submit()" class="bg-gray-950/60 border border-gray-800 text-gray-300 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all min-w-[200px] cursor-pointer">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @if(request('category_id'))
                <a href="{{ route('admin.streams.index') }}" class="text-xs text-gray-400 hover:text-white transition-colors">Clear</a>
            @endif
        </form>

        <!-- Bulk Action Form and Button -->
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto md:justify-end">
            <form id="bulk-delete-form" action="{{ route('admin.streams.bulk-delete') }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete the selected streams?');">
                @csrf
            </form>
            <button type="submit" form="bulk-delete-form" id="bulk-delete-btn" disabled 
                    class="py-2.5 px-4 rounded-xl font-semibold text-xs text-white bg-gray-900 border border-gray-800 hover:bg-gray-800 hover:border-gray-700 disabled:bg-gray-950 disabled:text-gray-600 disabled:border-gray-900 disabled:cursor-not-allowed transition-all flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Delete Selected (<span id="selected-count">0</span>)</span>
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-300">
            <thead class="bg-gray-950/40 text-gray-400 uppercase text-xs font-semibold border-b border-gray-800">
                <tr>
                    <th class="p-4 w-10">
                        <input type="checkbox" id="select-all" class="rounded border-gray-800 bg-gray-950 text-cyan-500 focus:ring-cyan-500/20 w-4 h-4 cursor-pointer">
                    </th>
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
                        <td class="p-4">
                            <input type="checkbox" name="ids[]" value="{{ $stream->id }}" form="bulk-delete-form" class="stream-checkbox rounded border-gray-800 bg-gray-950 text-cyan-500 focus:ring-cyan-500/20 w-4 h-4 cursor-pointer">
                        </td>
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
                        <td class="p-4 text-right space-x-3 text-xs">
                            <a href="{{ route('admin.streams.edit', $stream->id) }}" class="text-cyan-400 hover:text-cyan-300 font-semibold">Edit</a>
                            
                            <form action="{{ route('admin.streams.destroy', $stream->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this stream?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 font-semibold">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-500">No streams found. Click "Add Stream" to start.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Links -->
    @if($streams->hasPages())
        <div class="mt-6 pt-6 border-t border-gray-800/60 flex items-center justify-between">
            <div class="w-full text-gray-400 font-sans text-xs">
                {!! $streams->links() !!}
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.stream-checkbox');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        const selectedCountSpan = document.getElementById('selected-count');

        function updateBulkButtonState() {
            const checkedCount = document.querySelectorAll('.stream-checkbox:checked').length;
            selectedCountSpan.textContent = checkedCount;
            
            if (checkedCount > 0) {
                bulkDeleteBtn.disabled = false;
                bulkDeleteBtn.classList.remove('bg-gray-950', 'text-gray-600', 'border-gray-900', 'disabled:cursor-not-allowed');
                bulkDeleteBtn.classList.add('bg-red-600', 'hover:bg-red-500', 'border-transparent');
            } else {
                bulkDeleteBtn.disabled = true;
                bulkDeleteBtn.classList.add('bg-gray-950', 'text-gray-600', 'border-gray-900', 'disabled:cursor-not-allowed');
                bulkDeleteBtn.classList.remove('bg-red-600', 'hover:bg-red-500', 'border-transparent');
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                updateBulkButtonState();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (!this.checked && selectAll) {
                    selectAll.checked = false;
                }
                if (selectAll && document.querySelectorAll('.stream-checkbox:checked').length === checkboxes.length) {
                    selectAll.checked = true;
                }
                updateBulkButtonState();
            });
        });
    });
</script>
@endpush

