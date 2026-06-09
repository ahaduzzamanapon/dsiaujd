@extends('layouts.admin')

@section('page-title', 'Create Stream')
@section('page-subtitle', 'Add a new channel or live match with fallback servers')

@section('content')
<form method="POST" action="{{ route('admin.streams.store') }}" class="space-y-8 max-w-4xl">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Column 1: Stream Details -->
        <div class="glass-panel p-6 rounded-3xl space-y-6 shadow-xl">
            <h3 class="text-lg font-bold text-white border-b border-gray-800 pb-3">Basic Information</h3>
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Stream / Event Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white placeholder-gray-600 rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                       placeholder="e.g. Willow HD or BAN vs AUS">
            </div>

            <div>
                <label for="logo" class="block text-sm font-medium text-gray-300 mb-2">Logo/Icon URL</label>
                <input id="logo" type="url" name="logo" value="{{ old('logo') }}"
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white placeholder-gray-600 rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                       placeholder="https://example.com/logo.png">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="sport_type" class="block text-sm font-medium text-gray-300 mb-2">Sport Type</label>
                    <select id="sport_type" name="sport_type" required
                            class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm">
                        <option value="other" {{ old('sport_type') == 'other' ? 'selected' : '' }}>Other / General</option>
                        <option value="cricket" {{ old('sport_type') == 'cricket' ? 'selected' : '' }}>Cricket</option>
                        <option value="football" {{ old('sport_type') == 'football' ? 'selected' : '' }}>Football</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                    <label class="relative inline-flex items-center mt-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-gray-400 after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-500 peer-checked:after:bg-white"></div>
                        <span class="ml-3 text-sm font-medium text-gray-400">Active</span>
                    </label>
                </div>
            </div>

            <!-- Tab Display Checkboxes -->
            <div class="pt-4 border-t border-gray-800 space-y-3">
                <label class="block text-sm font-medium text-gray-300">Display in App Tabs</label>
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center space-x-2.5 cursor-pointer bg-gray-950/40 border border-gray-800 hover:border-gray-700 px-4 py-2 rounded-xl">
                        <input type="checkbox" name="show_in_events" id="show_in_events" value="1" class="rounded border-gray-800 bg-gray-950 text-cyan-500 focus:ring-cyan-500">
                        <span class="text-sm text-gray-300">Live Events</span>
                    </label>
                    <label class="flex items-center space-x-2.5 cursor-pointer bg-gray-950/40 border border-gray-800 hover:border-gray-700 px-4 py-2 rounded-xl">
                        <input type="checkbox" name="show_in_sports" id="show_in_sports" value="1" class="rounded border-gray-800 bg-gray-950 text-cyan-500 focus:ring-cyan-500">
                        <span class="text-sm text-gray-300">Sports Tab</span>
                    </label>
                    <label class="flex items-center space-x-2.5 cursor-pointer bg-gray-950/40 border border-gray-800 hover:border-gray-700 px-4 py-2 rounded-xl">
                        <input type="checkbox" name="show_in_tv" id="show_in_tv" value="1" class="rounded border-gray-800 bg-gray-950 text-cyan-500 focus:ring-cyan-500" onchange="toggleCategoriesSection()">
                        <span class="text-sm text-gray-300">Live TV</span>
                    </label>
                </div>
            </div>

            <!-- Categories Selection (Conditional) -->
            <div id="categories-section" class="hidden pt-4 border-t border-gray-800 space-y-3">
                <label class="block text-sm font-medium text-gray-300">Live TV Categories</label>
                <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto p-2 bg-gray-950/30 rounded-xl border border-gray-800/80">
                    @foreach($categories as $category)
                        <label class="flex items-center space-x-2.5 cursor-pointer py-1">
                            <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="rounded border-gray-800 bg-gray-950 text-cyan-500 focus:ring-cyan-500">
                            <span class="text-xs text-gray-300">{{ $category->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-[10px] text-gray-500">Note: Select one or more categories where this channel will appear.</p>
            </div>
        </div>

        <!-- Column 2: Date-Time Expiry & Match Details -->
        <div class="glass-panel p-6 rounded-3xl space-y-6 shadow-xl h-fit">
            <!-- Availability Configurations -->
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-white border-b border-gray-800 pb-3">Availability (Expiry)</h3>
                
                <label class="flex items-center space-x-2.5 cursor-pointer bg-gray-950/40 border border-gray-800 px-4 py-2.5 rounded-xl">
                    <input type="checkbox" name="is_permanent" id="is_permanent" value="1" checked onchange="toggleExpirySection()" class="rounded border-gray-800 bg-gray-950 text-cyan-500 focus:ring-cyan-500">
                    <span class="text-sm text-gray-300">Permanent (Never Expires)</span>
                </label>

                <!-- Expiring Stream Date Times -->
                <div id="expiry-section" class="hidden space-y-4 pt-2">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-300 mb-2">Start Date-Time</label>
                        <input id="start_time" type="datetime-local" name="start_time"
                               class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2 outline-none transition-all text-sm">
                    </div>
                    <div>
                        <label for="expire_time" class="block text-sm font-medium text-gray-300 mb-2">Expire Date-Time</label>
                        <input id="expire_time" type="datetime-local" name="expire_time"
                               class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2 outline-none transition-all text-sm">
                    </div>
                    <p class="text-[10px] text-gray-500">Stream will automatically show up at Start Time and hide after Expire Time.</p>
                </div>
            </div>

            <!-- Teams Configuration (Optional) -->
            <div class="space-y-4 pt-4 border-t border-gray-800">
                <h3 class="text-lg font-bold text-white pb-1">Event Teams (Optional)</h3>
                <p class="text-[10px] text-gray-400">Configure for specific live matches/events</p>
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- Team 1 -->
                    <div class="space-y-3 p-3 bg-gray-950/20 border border-gray-800/80 rounded-2xl">
                        <span class="text-xs font-bold text-gray-400 block border-b border-gray-800 pb-1">Team 1 (Left)</span>
                        <input type="text" name="team1_name" placeholder="Name (e.g. BAN)" class="w-full bg-gray-950/40 border border-gray-800 focus:border-cyan-500 text-white rounded-lg px-2.5 py-1.5 outline-none text-xs">
                        <input type="url" name="team1_logo" placeholder="Flag/Logo URL" class="w-full bg-gray-950/40 border border-gray-800 focus:border-cyan-500 text-white rounded-lg px-2.5 py-1.5 outline-none text-xs">
                    </div>

                    <!-- Team 2 -->
                    <div class="space-y-3 p-3 bg-gray-950/20 border border-gray-800/80 rounded-2xl">
                        <span class="text-xs font-bold text-gray-400 block border-b border-gray-800 pb-1">Team 2 (Right)</span>
                        <input type="text" name="team2_name" placeholder="Name (e.g. AUS)" class="w-full bg-gray-950/40 border border-gray-800 focus:border-cyan-500 text-white rounded-lg px-2.5 py-1.5 outline-none text-xs">
                        <input type="url" name="team2_logo" placeholder="Flag/Logo URL" class="w-full bg-gray-950/40 border border-gray-800 focus:border-cyan-500 text-white rounded-lg px-2.5 py-1.5 outline-none text-xs">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Playback Servers List -->
    <div class="glass-panel p-6 rounded-3xl shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-800 pb-4 mb-6">
            <div>
                <h3 class="text-xl font-bold text-white">Streaming Servers</h3>
                <p class="text-xs text-gray-400 mt-1">Add fallback stream servers in order. If one fails, the app plays the next.</p>
            </div>
            <button type="button" onclick="addServerRow()" class="py-2 px-3 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-white font-semibold text-xs transition-colors">
                + Add Server Link
            </button>
        </div>

        <div id="servers-container" class="space-y-4">
            @forelse($prefilledServers ?? [] as $index => $prefServer)
                <div class="server-row p-4 bg-gray-950/35 border border-gray-800 rounded-2xl relative space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                        <div class="sm:col-span-3">
                            <label class="text-xs text-gray-500 block mb-1">Server Name</label>
                            <input type="text" name="servers[{{ $index }}][name]" value="{{ $prefServer['name'] }}" required class="w-full bg-gray-950/60 border border-gray-800 text-white rounded-xl px-3 py-2 outline-none text-xs">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs text-gray-500 block mb-1">Type</label>
                            <select name="servers[{{ $index }}][stream_type]" required class="w-full bg-gray-950/60 border border-gray-800 text-white rounded-xl px-3 py-2 outline-none text-xs">
                                <option value="iframe" {{ $prefServer['stream_type'] == 'iframe' ? 'selected' : '' }}>Iframe / Web</option>
                                <option value="m3u8" {{ $prefServer['stream_type'] == 'm3u8' ? 'selected' : '' }}>M3U8 / HLS</option>
                            </select>
                        </div>
                        <div class="sm:col-span-5">
                            <label class="text-xs text-gray-500 block mb-1">Streaming URL</label>
                            <input type="text" name="servers[{{ $index }}][url]" value="{{ $prefServer['url'] }}" required class="w-full bg-gray-950/60 border border-gray-800 text-white rounded-xl px-3 py-2 outline-none text-xs" placeholder="https://...">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="text-xs text-gray-500 block mb-1">Order</label>
                            <input type="number" name="servers[{{ $index }}][order]" value="{{ $prefServer['order'] + 1 }}" required class="w-full bg-gray-950/60 border border-gray-800 text-white rounded-xl px-3 py-2 outline-none text-xs">
                        </div>
                        <div class="sm:col-span-1 flex items-end justify-center">
                            <button type="button" onclick="removeServerRow(this)" class="p-2 text-red-500 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-gray-800/40 pt-3">
                        <div>
                            <label class="text-[10px] text-gray-500 block mb-1">Custom Referer Header (Optional)</label>
                            <input type="text" name="servers[{{ $index }}][http_referer]" value="{{ $prefServer['http_referer'] }}" class="w-full bg-gray-950/40 border border-gray-800/80 text-white rounded-xl px-3 py-1.5 outline-none text-xs" placeholder="e.g. https://executeandship.com/">
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 block mb-1">Custom Origin Header (Optional)</label>
                            <input type="text" name="servers[{{ $index }}][http_origin]" value="{{ $prefServer['http_origin'] }}" class="w-full bg-gray-950/40 border border-gray-800/80 text-white rounded-xl px-3 py-1.5 outline-none text-xs" placeholder="e.g. https://executeandship.com">
                        </div>
                    </div>
                </div>
            @empty
                <div class="server-row p-4 bg-gray-950/35 border border-gray-800 rounded-2xl relative space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                        <div class="sm:col-span-3">
                            <label class="text-xs text-gray-500 block mb-1">Server Name</label>
                            <input type="text" name="servers[0][name]" value="Server 1" required class="w-full bg-gray-950/60 border border-gray-800 text-white rounded-xl px-3 py-2 outline-none text-xs">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs text-gray-500 block mb-1">Type</label>
                            <select name="servers[0][stream_type]" required class="w-full bg-gray-950/60 border border-gray-800 text-white rounded-xl px-3 py-2 outline-none text-xs">
                                <option value="iframe">Iframe / Web</option>
                                <option value="m3u8">M3U8 / HLS</option>
                            </select>
                        </div>
                        <div class="sm:col-span-5">
                            <label class="text-xs text-gray-500 block mb-1">Streaming URL</label>
                            <input type="text" name="servers[0][url]" required class="w-full bg-gray-950/60 border border-gray-800 text-white rounded-xl px-3 py-2 outline-none text-xs" placeholder="https://...">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="text-xs text-gray-500 block mb-1">Order</label>
                            <input type="number" name="servers[0][order]" value="1" required class="w-full bg-gray-950/60 border border-gray-800 text-white rounded-xl px-3 py-2 outline-none text-xs">
                        </div>
                        <div class="sm:col-span-1 flex items-end justify-center">
                            <button type="button" onclick="removeServerRow(this)" class="p-2 text-red-500 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-gray-800/40 pt-3">
                        <div>
                            <label class="text-[10px] text-gray-500 block mb-1">Custom Referer Header (Optional)</label>
                            <input type="text" name="servers[0][http_referer]" class="w-full bg-gray-950/40 border border-gray-800/80 text-white rounded-xl px-3 py-1.5 outline-none text-xs" placeholder="e.g. https://executeandship.com/">
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 block mb-1">Custom Origin Header (Optional)</label>
                            <input type="text" name="servers[0][http_origin]" class="w-full bg-gray-950/40 border border-gray-800/80 text-white rounded-xl px-3 py-1.5 outline-none text-xs" placeholder="e.g. https://executeandship.com">
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('admin.streams.index') }}" class="py-3 px-6 rounded-xl font-semibold text-gray-300 bg-gray-900 border border-gray-800 hover:bg-gray-800 transition-all text-sm">
            Cancel
        </a>
        <button type="submit" class="py-3 px-8 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 shadow-lg shadow-cyan-500/15 transition-all text-sm">
            Create Stream
        </button>
    </div>
</form>

@push('scripts')
<script>
    let serverIndex = {{ isset($prefilledServers) ? count($prefilledServers) : 1 }};

    function toggleExpirySection() {
        const isPermanent = document.getElementById('is_permanent').checked;
        const expirySection = document.getElementById('expiry-section');
        const startTime = document.getElementById('start_time');
        const expireTime = document.getElementById('expire_time');

        if (isPermanent) {
            expirySection.classList.add('hidden');
            startTime.removeAttribute('required');
            expireTime.removeAttribute('required');
        } else {
            expirySection.classList.remove('hidden');
            startTime.setAttribute('required', 'required');
            expireTime.setAttribute('required', 'required');
        }
    }

    function toggleCategoriesSection() {
        const showInTv = document.getElementById('show_in_tv').checked;
        const categoriesSection = document.getElementById('categories-section');

        if (showInTv) {
            categoriesSection.classList.remove('hidden');
        } else {
            categoriesSection.classList.add('hidden');
        }
    }

    function addServerRow() {
        const container = document.getElementById('servers-container');
        const index = serverIndex++;
        
        const row = document.createElement('div');
        row.className = 'server-row p-4 bg-gray-950/35 border border-gray-800 rounded-2xl relative space-y-4';
        row.innerHTML = `
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-3">
                    <label class="text-xs text-gray-500 block mb-1">Server Name</label>
                    <input type="text" name="servers[${index}][name]" value="Server ${index + 1}" required class="w-full bg-gray-950/60 border border-gray-800 text-white rounded-xl px-3 py-2 outline-none text-xs">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs text-gray-500 block mb-1">Type</label>
                    <select name="servers[${index}][stream_type]" required class="w-full bg-gray-950/60 border border-gray-800 text-white rounded-xl px-3 py-2 outline-none text-xs">
                        <option value="iframe">Iframe / Web</option>
                        <option value="m3u8">M3U8 / HLS</option>
                    </select>
                </div>
                <div class="sm:col-span-5">
                    <label class="text-xs text-gray-500 block mb-1">Streaming URL</label>
                    <input type="text" name="servers[${index}][url]" required class="w-full bg-gray-950/60 border border-gray-800 text-white rounded-xl px-3 py-2 outline-none text-xs" placeholder="https://...">
                </div>
                <div class="sm:col-span-1">
                    <label class="text-xs text-gray-500 block mb-1">Order</label>
                    <input type="number" name="servers[${index}][order]" value="${index + 1}" required class="w-full bg-gray-950/60 border border-gray-800 text-white rounded-xl px-3 py-2 outline-none text-xs">
                </div>
                <div class="sm:col-span-1 flex items-end justify-center">
                    <button type="button" onclick="removeServerRow(this)" class="p-2 text-red-500 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-gray-800/40 pt-3">
                <div>
                    <label class="text-[10px] text-gray-500 block mb-1">Custom Referer Header (Optional)</label>
                    <input type="text" name="servers[${index}][http_referer]" class="w-full bg-gray-950/40 border border-gray-800/80 text-white rounded-xl px-3 py-1.5 outline-none text-xs" placeholder="e.g. https://executeandship.com/">
                </div>
                <div>
                    <label class="text-[10px] text-gray-500 block mb-1">Custom Origin Header (Optional)</label>
                    <input type="text" name="servers[${index}][http_origin]" class="w-full bg-gray-950/40 border border-gray-800/80 text-white rounded-xl px-3 py-1.5 outline-none text-xs" placeholder="e.g. https://executeandship.com">
                </div>
            </div>
        `;
        container.appendChild(row);
    }

    function removeServerRow(button) {
        const rows = document.getElementsByClassName('server-row');
        if (rows.length <= 1) {
            alert("At least one streaming server link is required!");
            return;
        }
        
        const row = button.closest('.server-row');
        row.remove();
    }
</script>
@endpush
@endsection
