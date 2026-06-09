@extends('layouts.admin')

@section('page-title', 'Data Sync & Scheduler Console')
@section('page-subtitle', 'Trigger background scrapers and synchronization tasks manually')

@section('content')
<div class="space-y-8">
    <!-- Custom Sync Form -->
    <div class="glass-panel p-6 rounded-3xl shadow-xl">
        <h3 class="text-xl font-bold text-white mb-2">Sync Custom M3U Playlist</h3>
        <p class="text-xs text-gray-400 mb-6">Enter a custom M3U playlist URL to parse streams and import them directly into the database.</p>
        
        <form action="{{ route('admin.sync.run') }}" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
            @csrf
            <input type="hidden" name="type" value="m3u">
            <div class="flex-1 w-full">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">M3U Playlist URL</label>
                <input type="url" name="url" id="custom-m3u-url" list="suggested-m3us" placeholder="https://example.com/playlist.m3u" required
                       class="w-full bg-gray-950/60 border border-gray-800 text-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all">
                <datalist id="suggested-m3us">
                    <option value="https://iptv-org.github.io/iptv/index.m3u">
                    <option value="https://iptv-org.github.io/iptv/categories/sports.m3u">
                    <option value="https://raw.githubusercontent.com/Free-TV/IPTV/master/playlist.m3u">
                    <option value="https://raw.githubusercontent.com/RealEmperor/BDIX-IPTV/main/playlist.m3u">
                    <option value="https://raw.githubusercontent.com/Mocha-TV/MochaTV-BDIX-IPTV/main/playlist.m3u">
                </datalist>
                
                <div class="mt-3">
                    <span class="text-[10px] text-gray-500 block mb-1.5 uppercase font-semibold tracking-wider">Suggested playlists (click to fill):</span>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="fillM3uUrl('https://iptv-org.github.io/iptv/categories/sports.m3u')" class="px-2 py-1 rounded bg-gray-950 border border-gray-900 text-[10px] text-gray-400 hover:text-cyan-400 hover:border-cyan-500/30 transition-colors">IPTV Sports</button>
                        <button type="button" onclick="fillM3uUrl('https://iptv-org.github.io/iptv/index.m3u')" class="px-2 py-1 rounded bg-gray-950 border border-gray-900 text-[10px] text-gray-400 hover:text-cyan-400 hover:border-cyan-500/30 transition-colors">IPTV Global</button>
                        <button type="button" onclick="fillM3uUrl('https://raw.githubusercontent.com/RealEmperor/BDIX-IPTV/main/playlist.m3u')" class="px-2 py-1 rounded bg-gray-950 border border-gray-900 text-[10px] text-gray-400 hover:text-cyan-400 hover:border-cyan-500/30 transition-colors">RealEmperor BDIX</button>
                        <button type="button" onclick="fillM3uUrl('https://raw.githubusercontent.com/Mocha-TV/MochaTV-BDIX-IPTV/main/playlist.m3u')" class="px-2 py-1 rounded bg-gray-950 border border-gray-900 text-[10px] text-gray-400 hover:text-cyan-400 hover:border-cyan-500/30 transition-colors">MochaTV BDIX</button>
                    </div>
                </div>
            </div>
            <button type="submit" onclick="showLoader(this)" class="w-full md:w-auto py-3 px-6 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-sm shadow-lg shadow-cyan-500/15 transition-all whitespace-nowrap flex items-center justify-center gap-2 mb-[52px] md:mb-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3 3L22 4"/>
                </svg>
                Sync Custom URL
            </button>
        </form>
    </div>

    <!-- Presets Section -->
    <div>
        <h3 class="text-lg font-bold text-white mb-6">Configured Sync Presets</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($syncOptions as $option)
                <div class="glass-card p-6 rounded-2xl flex flex-col justify-between min-h-[200px]">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-md font-bold text-white">{{ $option['name'] }}</span>
                            @if($option['type'] === 'm3u')
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 uppercase">M3U Playlist</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-pink-500/10 text-pink-400 border border-pink-500/20 uppercase">Scraper</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 leading-relaxed mb-4">{{ $option['description'] }}</p>
                        @if($option['url'])
                            <div class="bg-gray-950/60 p-2 rounded-lg border border-gray-900 mb-6 overflow-hidden">
                                <span class="text-[10px] font-mono text-gray-500 block truncate">{{ $option['url'] }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <form action="{{ route('admin.sync.run') }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="type" value="{{ $option['type'] }}">
                        @if($option['url'])
                            <input type="hidden" name="url" value="{{ $option['url'] }}">
                        @endif
                        <button type="submit" onclick="showLoader(this)" class="w-full py-2.5 px-4 rounded-xl font-semibold text-xs text-white bg-gray-900 border border-gray-800 hover:bg-gray-800 hover:border-gray-700 transition-all flex items-center justify-center gap-2">
                            <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3 3L22 4"/>
                            </svg>
                            <span>Sync Now</span>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Output Terminal -->
    @if (session('sync_output'))
        <div class="glass-panel p-6 rounded-3xl border border-gray-800 shadow-2xl">
            <div class="flex items-center justify-between mb-4 border-b border-gray-800 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                    <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span class="text-xs text-gray-400 font-mono ml-2">sync_execution.log</span>
                </div>
                <button onclick="copyTerminalText()" class="text-xs text-cyan-400 hover:underline">Copy Log</button>
            </div>
            <pre id="terminal-log" class="bg-gray-950 p-4 rounded-xl font-mono text-xs text-emerald-400 overflow-x-auto max-h-96 leading-relaxed border border-gray-900 whitespace-pre-wrap">{{ session('sync_output') }}</pre>
        </div>
    @endif
</div>

<!-- Simple overlay spinner for execution -->
<div id="sync-spinner" class="fixed inset-0 bg-gray-950/80 backdrop-blur-sm z-50 flex flex-col items-center justify-center hidden">
    <div class="w-12 h-12 border-4 border-cyan-500/20 border-t-cyan-500 rounded-full animate-spin mb-4"></div>
    <p class="text-white font-medium text-sm">Executing Sync Task...</p>
    <p class="text-gray-400 text-xs mt-1">This may take a moment to parse feeds and update database records.</p>
</div>
@endsection

@push('scripts')
<script>
    function fillM3uUrl(url) {
        document.getElementById('custom-m3u-url').value = url;
    }

    function showLoader(button) {
        document.getElementById('sync-spinner').classList.remove('hidden');
    }
    
    function copyTerminalText() {
        const pre = document.getElementById('terminal-log');
        if (pre) {
            navigator.clipboard.writeText(pre.innerText).then(() => {
                alert('Terminal log copied to clipboard.');
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    }
</script>
@endpush
