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
        <!-- Search & Category Filter -->
        <form action="{{ route('admin.streams.index') }}" method="GET" class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full md:w-auto">
            <!-- Search Input -->
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search streams or teams..." 
                       class="w-full bg-gray-950/60 border border-gray-800 text-gray-300 rounded-xl pl-9 pr-4 py-2 text-xs focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all">
            </div>

             <!-- Category Select -->
            <select name="category_id" onchange="this.form.submit()" class="bg-gray-950/60 border border-gray-800 text-gray-300 rounded-xl px-4 py-2 text-xs focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all min-w-[150px] cursor-pointer">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <!-- Tab Filter -->
            <select name="tab" onchange="this.form.submit()" class="bg-gray-950/60 border border-gray-800 text-gray-300 rounded-xl px-4 py-2 text-xs focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all min-w-[150px] cursor-pointer">
                <option value="">All Display Tabs</option>
                <option value="events" {{ request('tab') == 'events' ? 'selected' : '' }}>Live Events Tab</option>
                <option value="sports" {{ request('tab') == 'sports' ? 'selected' : '' }}>Sports Tab</option>
                <option value="tv" {{ request('tab') == 'tv' ? 'selected' : '' }}>Live TV Tab</option>
            </select>

            <button type="submit" class="py-2 px-4 rounded-xl font-semibold text-xs text-white bg-cyan-600 hover:bg-cyan-500 transition-all">
                Search
            </button>

            @if(request('category_id') || request('search') || request('tab'))
                <a href="{{ route('admin.streams.index') }}" class="text-xs text-gray-400 hover:text-white transition-colors whitespace-nowrap">Clear All</a>
            @endif
        </form>

        <!-- Bulk Action Form and Buttons -->
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
            <button type="button" id="bulk-create-btn" onclick="redirectToCreateWithSelected()" disabled 
                    class="py-2.5 px-4 rounded-xl font-semibold text-xs text-white bg-gray-900 border border-gray-800 hover:bg-gray-800 hover:border-gray-700 disabled:bg-gray-950 disabled:text-gray-600 disabled:border-gray-900 disabled:cursor-not-allowed transition-all flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Create with Selected (<span id="create-count">0</span>)</span>
            </button>
            <button type="button" id="bulk-merge-btn" onclick="openMergeModal()" disabled 
                    class="py-2.5 px-4 rounded-xl font-semibold text-xs text-white bg-gray-900 border border-gray-800 hover:bg-gray-800 hover:border-gray-700 disabled:bg-gray-950 disabled:text-gray-600 disabled:border-gray-900 disabled:cursor-not-allowed transition-all flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>Merge Selected (<span id="merge-count">0</span>)</span>
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

<!-- Merge Channels Modal Overlay -->
<div id="merge-modal" class="fixed inset-0 bg-gray-950/85 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="glass-panel w-full max-w-lg p-6 rounded-3xl border border-gray-800 shadow-2xl mx-4 relative">
        <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-800">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <h3 class="text-lg font-bold text-white">Channel Merger</h3>
            </div>
            <button type="button" onclick="closeMergeModal()" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="merge-channels-form" action="{{ route('admin.streams.merge') }}" method="POST" class="space-y-6">
            @csrf
            <!-- Container for hidden inputs representing selected IDs -->
            <div id="merge-ids-inputs"></div>

            <!-- New Name input -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Merged Channel Name</label>
                <input type="text" name="new_name" placeholder="e.g. Sony Sports Combined" required
                       class="w-full bg-gray-950/60 border border-gray-800 text-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all">
            </div>

            <!-- Optional Deletion -->
            <div class="flex items-center space-x-3 bg-gray-950/40 p-3 rounded-xl border border-gray-800/40">
                <input type="checkbox" name="delete_original" id="delete-original" value="1" checked
                       class="rounded border-gray-800 bg-gray-950 text-cyan-500 focus:ring-cyan-500/20 w-4 h-4 cursor-pointer">
                <label for="delete-original" class="text-xs text-gray-300 cursor-pointer select-none">Delete source channels after combining</label>
            </div>

            <!-- Search bar to search and add more streams inside modal -->
            <div class="relative">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Search & Add More Channels</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" id="modal-search-input" placeholder="Search other channels by name..." autocomplete="off"
                           class="w-full bg-gray-950/60 border border-gray-800 text-gray-300 rounded-xl pl-9 pr-4 py-2.5 text-xs focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all">
                </div>
                <!-- Search Suggestions -->
                <div id="modal-search-suggestions" class="hidden absolute bg-gray-950/95 border border-gray-800 rounded-xl max-h-40 overflow-y-auto z-50 w-full mt-1.5 shadow-xl divide-y divide-gray-900"></div>
            </div>

            <!-- List of selected channels -->
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Selected Channels to Combine (<span id="modal-selected-count">0</span>)</label>
                <div id="modal-selected-list" class="flex flex-wrap gap-2 max-h-40 overflow-y-auto bg-gray-950/40 p-3 rounded-xl border border-gray-850">
                    <!-- Badges will render here -->
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-800">
                <button type="button" onclick="closeMergeModal()" class="py-2 px-4 rounded-xl text-xs font-semibold text-gray-400 hover:text-white transition-all">
                    Cancel
                </button>
                <button type="submit" class="py-2.5 px-6 rounded-xl font-semibold text-xs text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 shadow-lg shadow-cyan-500/15 transition-all">
                    Merge Channels
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // All channels in the database for the merger search feature
    const allChannelsList = {!! json_encode($allStreamsForMerge->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->toArray()) !!};
    
    // In-memory state of streams currently chosen for merging
    let selectedMergeStreams = [];

    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.stream-checkbox');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        const bulkMergeBtn = document.getElementById('bulk-merge-btn');
        const bulkCreateBtn = document.getElementById('bulk-create-btn');
        const selectedCountSpan = document.getElementById('selected-count');
        const mergeCountSpan = document.getElementById('merge-count');
        const createCountSpan = document.getElementById('create-count');
        
        // Modal elements
        const modalSearchInput = document.getElementById('modal-search-input');
        const suggestionsContainer = document.getElementById('modal-search-suggestions');

        // Functions to maintain state
        function syncSelectionFromTable() {
            selectedMergeStreams = [];
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    const row = cb.closest('tr');
                    // Extract channel name from row layout
                    const nameSpan = row.querySelector('.font-medium.text-white');
                    const name = nameSpan ? nameSpan.textContent.trim() : 'Unnamed Stream';
                    selectedMergeStreams.push({
                        id: parseInt(cb.value),
                        name: name
                    });
                }
            });
            updateButtonsState();
        }

        function updateButtonsState() {
            const checkedCount = selectedMergeStreams.length;
            selectedCountSpan.textContent = checkedCount;
            mergeCountSpan.textContent = checkedCount;
            createCountSpan.textContent = checkedCount;
            
            // Toggle Delete Button
            if (checkedCount > 0) {
                bulkDeleteBtn.disabled = false;
                bulkDeleteBtn.classList.remove('bg-gray-950', 'text-gray-600', 'border-gray-900', 'disabled:cursor-not-allowed');
                bulkDeleteBtn.classList.add('bg-red-600', 'hover:bg-red-500', 'border-transparent');
            } else {
                bulkDeleteBtn.disabled = true;
                bulkDeleteBtn.classList.add('bg-gray-950', 'text-gray-600', 'border-gray-900', 'disabled:cursor-not-allowed');
                bulkDeleteBtn.classList.remove('bg-red-600', 'hover:bg-red-500', 'border-transparent');
            }

            // Toggle Create with Selected Button
            if (checkedCount > 0) {
                bulkCreateBtn.disabled = false;
                bulkCreateBtn.classList.remove('bg-gray-950', 'text-gray-600', 'border-gray-900', 'disabled:cursor-not-allowed');
                bulkCreateBtn.classList.add('bg-gray-900', 'text-white', 'border-gray-800', 'hover:bg-gray-800');
            } else {
                bulkCreateBtn.disabled = true;
                bulkCreateBtn.classList.add('bg-gray-950', 'text-gray-600', 'border-gray-900', 'disabled:cursor-not-allowed');
                bulkCreateBtn.classList.remove('bg-gray-900', 'text-white', 'border-gray-800', 'hover:bg-gray-800');
            }

            // Toggle Merge Button (Requires at least 2 channels to combine)
            if (checkedCount >= 2) {
                bulkMergeBtn.disabled = false;
                bulkMergeBtn.classList.remove('bg-gray-950', 'text-gray-600', 'border-gray-900', 'disabled:cursor-not-allowed');
                bulkMergeBtn.classList.add('bg-gradient-to-r', 'from-cyan-500', 'to-blue-600', 'border-transparent', 'hover:from-cyan-400', 'hover:to-blue-500');
            } else {
                bulkMergeBtn.disabled = true;
                bulkMergeBtn.classList.add('bg-gray-950', 'text-gray-600', 'border-gray-900', 'disabled:cursor-not-allowed');
                bulkMergeBtn.classList.remove('bg-gradient-to-r', 'from-cyan-500', 'to-blue-600', 'border-transparent', 'hover:from-cyan-400', 'hover:to-blue-500');
            }
        }

        // Checkbox events
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                syncSelectionFromTable();
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
                syncSelectionFromTable();
            });
        });

        // Search features inside the Modal
        if (modalSearchInput) {
            modalSearchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                if (query.length < 1) {
                    suggestionsContainer.classList.add('hidden');
                    return;
                }

                // Filter matching streams not already selected
                const matches = allChannelsList.filter(ch => {
                    const matchesQuery = ch.name.toLowerCase().includes(query);
                    const isAlreadySelected = selectedMergeStreams.some(selected => selected.id === ch.id);
                    return matchesQuery && !isAlreadySelected;
                });

                if (matches.length > 0) {
                    suggestionsContainer.innerHTML = '';
                    matches.slice(0, 10).forEach(match => {
                        const item = document.createElement('div');
                        item.className = 'px-4 py-2.5 text-xs text-gray-300 hover:bg-cyan-500/10 hover:text-cyan-400 cursor-pointer transition-colors';
                        item.textContent = match.name;
                        item.addEventListener('click', function() {
                            addChannelToMerge(match.id, match.name);
                            modalSearchInput.value = '';
                            suggestionsContainer.classList.add('hidden');
                        });
                        suggestionsContainer.appendChild(item);
                    });
                    suggestionsContainer.classList.remove('hidden');
                } else {
                    suggestionsContainer.innerHTML = '<div class="px-4 py-2.5 text-xs text-gray-500">No matching channels found</div>';
                    suggestionsContainer.classList.remove('hidden');
                }
            });

            // Close suggestions dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!modalSearchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                    suggestionsContainer.classList.add('hidden');
                }
            });
        }
    });

    // Helper functions to manage modal state
    function addChannelToMerge(id, name) {
        if (!selectedMergeStreams.some(ch => ch.id === id)) {
            selectedMergeStreams.push({ id, name });
            
            // Also check the row on the main table if visible on the page
            const tableCheckbox = document.querySelector(`.stream-checkbox[value="${id}"]`);
            if (tableCheckbox) {
                tableCheckbox.checked = true;
            }
            
            renderModalSelectedList();
        }
    }

    function removeChannelFromMerge(id) {
        selectedMergeStreams = selectedMergeStreams.filter(ch => ch.id !== id);
        
        // Uncheck on the main table
        const tableCheckbox = document.querySelector(`.stream-checkbox[value="${id}"]`);
        if (tableCheckbox) {
            tableCheckbox.checked = false;
        }
        
        // If select all was checked, uncheck it since we removed an item
        const selectAll = document.getElementById('select-all');
        if (selectAll) {
            selectAll.checked = false;
        }

        renderModalSelectedList();
    }

    function renderModalSelectedList() {
        const listContainer = document.getElementById('modal-selected-list');
        const inputsContainer = document.getElementById('merge-ids-inputs');
        const modalCountSpan = document.getElementById('modal-selected-count');
        const mergeCountSpan = document.getElementById('merge-count');
        const createCountSpan = document.getElementById('create-count');
        const selectedCountSpan = document.getElementById('selected-count');
        
        listContainer.innerHTML = '';
        inputsContainer.innerHTML = '';

        modalCountSpan.textContent = selectedMergeStreams.length;
        selectedCountSpan.textContent = selectedMergeStreams.length;
        mergeCountSpan.textContent = selectedMergeStreams.length;
        createCountSpan.textContent = selectedMergeStreams.length;

        selectedMergeStreams.forEach(ch => {
            // Render Badge HTML
            const badge = document.createElement('div');
            badge.className = 'inline-flex items-center space-x-1 px-2.5 py-1 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 rounded-xl text-xs font-medium';
            badge.innerHTML = `
                <span>${ch.name}</span>
                <button type="button" onclick="removeChannelFromMerge(${ch.id})" class="text-cyan-500 hover:text-cyan-300 font-bold ml-1 focus:outline-none">&times;</button>
            `;
            listContainer.appendChild(badge);

            // Append Hidden Form Input
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = ch.id;
            inputsContainer.appendChild(input);
        });

        // Trigger dynamic button states updates on the main table page
        const bulkMergeBtn = document.getElementById('bulk-merge-btn');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        const bulkCreateBtn = document.getElementById('bulk-create-btn');

        if (selectedMergeStreams.length >= 2) {
            bulkMergeBtn.disabled = false;
            bulkMergeBtn.classList.remove('bg-gray-950', 'text-gray-600', 'border-gray-900', 'disabled:cursor-not-allowed');
            bulkMergeBtn.classList.add('bg-gradient-to-r', 'from-cyan-500', 'to-blue-600', 'border-transparent', 'hover:from-cyan-400', 'hover:to-blue-500');
        } else {
            bulkMergeBtn.disabled = true;
            bulkMergeBtn.classList.add('bg-gray-950', 'text-gray-600', 'border-gray-900', 'disabled:cursor-not-allowed');
            bulkMergeBtn.classList.remove('bg-gradient-to-r', 'from-cyan-500', 'to-blue-600', 'border-transparent', 'hover:from-cyan-400', 'hover:to-blue-500');
        }

        if (selectedMergeStreams.length > 0) {
            bulkCreateBtn.disabled = false;
            bulkCreateBtn.classList.remove('bg-gray-950', 'text-gray-600', 'border-gray-900', 'disabled:cursor-not-allowed');
            bulkCreateBtn.classList.add('bg-gray-900', 'text-white', 'border-gray-800', 'hover:bg-gray-800');
            
            bulkDeleteBtn.disabled = false;
            bulkDeleteBtn.classList.remove('bg-gray-950', 'text-gray-600', 'border-gray-900', 'disabled:cursor-not-allowed');
            bulkDeleteBtn.classList.add('bg-red-600', 'hover:bg-red-500', 'border-transparent');
        } else {
            bulkCreateBtn.disabled = true;
            bulkCreateBtn.classList.add('bg-gray-950', 'text-gray-600', 'border-gray-900', 'disabled:cursor-not-allowed');
            bulkCreateBtn.classList.remove('bg-gray-900', 'text-white', 'border-gray-800', 'hover:bg-gray-800');

            bulkDeleteBtn.disabled = true;
            bulkDeleteBtn.classList.add('bg-gray-950', 'text-gray-600', 'border-gray-900', 'disabled:cursor-not-allowed');
            bulkDeleteBtn.classList.remove('bg-red-600', 'hover:bg-red-500', 'border-transparent');
        }
    }

    function redirectToCreateWithSelected() {
        const ids = selectedMergeStreams.map(ch => ch.id);
        if (ids.length === 0) return;
        
        // Build query string
        const params = new URLSearchParams();
        ids.forEach(id => params.append('from_ids[]', id));
        
        // Redirect to create page
        window.location.href = "{{ route('admin.streams.create') }}?" + params.toString();
    }

    function openMergeModal() {
        renderModalSelectedList();
        
        // Show Modal
        document.getElementById('merge-modal').classList.remove('hidden');
    }

    function closeMergeModal() {
        // Hide Modal
        document.getElementById('merge-modal').classList.add('hidden');
        document.getElementById('modal-search-input').value = '';
        document.getElementById('modal-search-suggestions').classList.add('hidden');
    }
</script>
@endpush

