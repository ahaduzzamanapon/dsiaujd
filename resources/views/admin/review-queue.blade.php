@extends('layouts.admin')

@section('page-title', 'Stream Review Queue')
@section('page-subtitle', 'Manually test and review stream links before publishing or discarding them')

@section('content')
<link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
<style>
    :root {
        --plyr-color-main: #06b6d4;
        --plyr-video-background: #000;
    }
    .plyr {
        width: 100%;
        height: 100%;
        border-radius: 0px;
    }
</style>

@if($pending->isEmpty())
    <div class="glass-panel p-12 rounded-3xl shadow-xl text-center max-w-2xl mx-auto my-12">
        <div class="w-20 h-20 bg-emerald-500/10 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-2xl font-bold text-white mb-2">Review Queue is Empty!</h3>
        <p class="text-sm text-gray-400 mb-8">All imported stream links passed the live validation checks, or they have already been processed. Start another sync task to fetch more channels.</p>
        <a href="{{ route('admin.sync.index') }}" class="py-3 px-6 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-sm shadow-md transition-all">
            Go to Sync Console
        </a>
    </div>
@else
    <div class="flex items-center justify-between mb-6">
        <div>
            <span class="text-xs font-semibold text-cyan-400 uppercase tracking-wider">Queue status</span>
            <p class="text-xs text-gray-400 mt-1"><span id="queue-counter" class="text-white font-bold">{{ $totalCount }}</span> streams waiting for review</p>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Approve All Form -->
            <form action="{{ route('admin.review-queue.approve-all') }}" method="POST" id="approve-all-form" onsubmit="return confirmApproveAll(event)">
                @csrf
                <button type="submit" class="py-2.5 px-4 rounded-xl font-semibold text-xs text-emerald-400 bg-emerald-500/10 border border-emerald-500/25 hover:bg-emerald-500/20 transition-all flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Approve & Publish All
                </button>
            </form>

            <!-- Reject All Form -->
            <form action="{{ route('admin.review-queue.reject-all') }}" method="POST" id="reject-all-form" onsubmit="return confirmRejectAll(event)">
                @csrf
                <button type="submit" class="py-2.5 px-4 rounded-xl font-semibold text-xs text-rose-400 bg-rose-500/10 border border-rose-500/25 hover:bg-rose-500/20 transition-all flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Reject All Streams
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left Pane: Streams List (Col span 7) -->
        <div class="lg:col-span-7 space-y-4">
            <div id="streams-list-container" class="space-y-4">
                @foreach($pending as $item)
                    <div class="stream-card glass-card p-5 rounded-2xl flex items-center justify-between cursor-pointer border border-transparent hover:border-cyan-500/30 transition-all"
                         id="stream-card-{{ $item->id }}"
                         data-id="{{ $item->id }}"
                         data-name="{{ $item->name }}"
                         data-logo="{{ $item->logo ?: 'https://crichd.xyz/assets/images/live.png' }}"
                         data-url="{{ $item->url }}"
                         data-referer="{{ $item->http_referer }}"
                         data-origin="{{ $item->http_origin }}"
                         data-category="{{ $item->category ?: 'Live Channel' }}"
                         data-source="{{ $item->source ?: 'Unknown' }}"
                         onclick="selectStream({{ $item->id }})">
                        
                        <div class="flex items-center space-x-4 min-w-0 flex-1">
                            <!-- Stream Logo -->
                            <img src="{{ $item->logo ?: 'https://crichd.xyz/assets/images/live.png' }}" 
                                 alt="" 
                                 class="w-12 h-12 object-contain rounded-xl bg-gray-950 p-1 border border-gray-800 shrink-0">
                            
                            <div class="min-w-0 flex-1">
                                <span class="font-bold text-white block text-sm truncate">{{ $item->name }}</span>
                                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 uppercase tracking-wide">
                                        {{ $item->source ?: 'Scraper' }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-800 text-gray-400 border border-gray-700 capitalize">
                                        {{ $item->category ?: 'Live Channel' }}
                                    </span>
                                    @if($item->reason === 'failed_check')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            Link Offline
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[10px] font-mono text-gray-500 block mt-2 truncate">{{ $item->url }}</span>
                            </div>
                        </div>

                        <!-- Right Icon -->
                        <div class="p-2 text-gray-500 group-hover:text-cyan-400 shrink-0 ml-4">
                            <svg class="w-6 h-6 play-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $pending->links() }}
            </div>
        </div>

        <!-- Right Pane: Sticky Player Frame (Col span 5) -->
        <div class="lg:col-span-5">
            <div class="glass-panel rounded-3xl overflow-hidden sticky top-6 shadow-2xl border border-gray-800">
                <!-- Video Container -->
                <div class="aspect-video bg-black relative flex items-center justify-center border-b border-gray-900">
                    <video id="hls-player" class="w-full h-full object-contain hidden" controls autoplay></video>
                    
                    <!-- Player Loader / Overlay -->
                    <div id="player-overlay" class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center bg-gray-950/90 z-10">
                        <div class="w-16 h-16 bg-cyan-500/10 text-cyan-400 rounded-full flex items-center justify-center mb-4 animate-pulse">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h4 class="text-sm font-bold text-white mb-1">No Stream Selected</h4>
                        <p class="text-xs text-gray-500 max-w-xs">Select any stream card from the list on the left to start loading and testing the live broadcast.</p>
                    </div>
                </div>

                <!-- Active Details Panel -->
                <div class="p-6 space-y-6">
                    <div class="flex items-start gap-4">
                        <img id="active-logo" src="https://crichd.xyz/assets/images/live.png" alt="" class="w-12 h-12 object-contain rounded-xl bg-gray-950 p-1 border border-gray-800 shrink-0">
                        <div class="min-w-0 flex-1">
                            <h3 id="active-name" class="text-lg font-bold text-white truncate">Select a Stream</h3>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                <span id="active-source" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 uppercase">N/A</span>
                                <span id="active-category" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-800 text-gray-400 border border-gray-700">N/A</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 bg-gray-950/60 p-4 rounded-xl border border-gray-900 text-xs font-mono">
                        <div class="flex flex-col gap-1.5">
                            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Stream URL (Click to open & test in new tab)</span>
                            <a id="active-url" href="#" target="_blank" class="text-cyan-400 hover:underline break-all select-all">None</a>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">HTTP Referer</span>
                            <span id="active-referer" class="text-gray-400 break-all select-all">None</span>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">HTTP Origin</span>
                            <span id="active-origin" class="text-gray-400 break-all select-all">None</span>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <button id="reject-btn" disabled onclick="processActiveStream('reject')" class="py-3 px-4 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-1.5 disabled:opacity-40 disabled:pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Reject & Delete
                        </button>
                        
                        <button id="approve-btn" disabled onclick="processActiveStream('approve')" class="py-3 px-4 rounded-xl font-bold text-xs text-gray-950 bg-gradient-to-r from-emerald-400 to-cyan-500 hover:from-emerald-300 hover:to-cyan-400 shadow-md hover:shadow-lg shadow-emerald-500/10 transition-all flex items-center justify-center gap-1.5 disabled:opacity-40 disabled:pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Approve & Publish
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
<script>
    let hls = null;
    let plyrPlayer = null;
    let selectedId = null;
    const player = document.getElementById('hls-player');
    const overlay = document.getElementById('player-overlay');
    const token = '{{ csrf_token() }}';

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-6 right-6 z-50 px-4 py-3 rounded-xl shadow-xl transition-all duration-300 transform translate-y-[-20px] opacity-0 text-xs font-semibold border ${
            type === 'success' ? 'bg-emerald-950 border-emerald-800 text-emerald-400' : 'bg-rose-950 border-rose-800 text-rose-400'
        }`;
        toast.innerText = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('translate-y-[-20px]', 'opacity-0');
        }, 10);
        
        setTimeout(() => {
            toast.classList.add('translate-y-[-20px]', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    function selectStream(id) {
        // Highlight card
        document.querySelectorAll('.stream-card').forEach(card => {
            card.classList.remove('border-cyan-500/50', 'bg-cyan-950/10');
            card.querySelector('.play-icon').classList.add('text-gray-500');
            card.querySelector('.play-icon').classList.remove('text-cyan-400');
        });

        const activeCard = document.getElementById(`stream-card-${id}`);
        if (!activeCard) return;

        activeCard.classList.add('border-cyan-500/50', 'bg-cyan-950/10');
        activeCard.querySelector('.play-icon').classList.remove('text-gray-500');
        activeCard.querySelector('.play-icon').classList.add('text-cyan-400');

        selectedId = id;
        
        // Extract data attributes
        const name = activeCard.getAttribute('data-name');
        const logo = activeCard.getAttribute('data-logo');
        const url = activeCard.getAttribute('data-url');
        const referer = activeCard.getAttribute('data-referer') || 'None';
        const origin = activeCard.getAttribute('data-origin') || 'None';
        const category = activeCard.getAttribute('data-category');
        const source = activeCard.getAttribute('data-source');

        // Update active details panel
        document.getElementById('active-name').innerText = name;
        document.getElementById('active-logo').src = logo;
        const urlEl = document.getElementById('active-url');
        urlEl.innerText = url;
        urlEl.href = url;
        document.getElementById('active-referer').innerText = referer;
        document.getElementById('active-origin').innerText = origin;
        document.getElementById('active-source').innerText = source;
        document.getElementById('active-category').innerText = category;

        // Enable buttons
        document.getElementById('approve-btn').disabled = false;
        document.getElementById('reject-btn').disabled = false;

        // Show player element
        player.classList.remove('hidden');
        overlay.classList.add('hidden');

        // Scroll to player on mobile
        if (window.innerWidth < 1024) {
            player.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Destroy previous player instances
        if (plyrPlayer) {
            plyrPlayer.destroy();
            plyrPlayer = null;
        }
        if (hls) {
            hls.destroy();
            hls = null;
        }

        function getStreamUrl(streamUrl) {
            if (!streamUrl) return '';
            const isHttps = window.location.protocol === 'https:';
            const isHttp = streamUrl.startsWith('http://');
            const isIp = /^(https?:\/\/)?(\d{1,3}\.){3}\d{1,3}/.test(streamUrl);
            const isBdix = streamUrl.toLowerCase().includes('bdix') || streamUrl.toLowerCase().includes('bdixtv');
            
            // Domains that require custom referrer/origin headers (server can reach them)
            const needsHeaderProxy = [
                '198.195.',
                'zohanayaan.com',
                'executeandship.com',
                'crichd',
                'fancode.com',
                'redforce.live'
            ].some(domain => streamUrl.toLowerCase().includes(domain));

            // For BDIX / IP streams: Only proxy if we are on HTTPS to bypass mixed-content.
            // If we are on HTTP, play them directly because our US server cannot connect to BDIX.
            const needsMixedContentProxy = (isIp || isBdix) && isHttps && isHttp;

            if (needsHeaderProxy || needsMixedContentProxy || (isHttps && isHttp && !isIp && !isBdix)) {
                // Use the Vercel proxy on live site to bypass hosting port blocks (e.g. port 1686)
                if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                    return `https://livetvbd-delta.vercel.app/api/stream-proxy?url=${encodeURIComponent(streamUrl)}`;
                }
                return `/api/stream-proxy?url=${encodeURIComponent(streamUrl)}`;
            }
            return streamUrl;
        }

        const playUrl = getStreamUrl(url);

        // Initialize Plyr immediately so the gorgeous UI displays instead of native controls
        plyrPlayer = new Plyr(player, {
            autoplay: true,
            muted: false
        });

        if (Hls.isSupported()) {
            hls = new Hls({
                maxMaxBufferLength: 10,
                enableWorker: true
            });
            hls.loadSource(playUrl);
            hls.attachMedia(player);
            window.hls = hls;

            hls.on(Hls.Events.MANIFEST_PARSED, function() {
                const levels = hls.levels.map(e => e.height);
                if (levels.length > 0) {
                    // Re-create Plyr to bind the quality options dynamically
                    plyrPlayer.destroy();
                    plyrPlayer = new Plyr(player, {
                        quality: {
                            default: levels[0],
                            options: levels,
                            forced: true,
                            onChange: (e) => {
                                window.hls.levels.forEach((n, o) => {
                                    if (n.height === e) {
                                        window.hls.currentLevel = o;
                                    }
                                });
                            }
                        },
                        autoplay: true,
                        muted: false
                    });
                    player.play().catch(() => {});
                } else {
                    player.play().catch(() => {});
                }
            });

            hls.on(Hls.Events.ERROR, function (event, data) {
                if (data.fatal) {
                    console.warn('Fatal player error:', data);
                    switch (data.type) {
                        case Hls.ErrorTypes.NETWORK_ERROR:
                            hls.startLoad();
                            break;
                        case Hls.ErrorTypes.MEDIA_ERROR:
                            hls.recoverMediaError();
                            break;
                        default:
                            break;
                    }
                }
            });
        } else if (player.canPlayType('application/vnd.apple.mpegurl')) {
            player.src = playUrl;
            player.play().catch(() => {});
        }
    }

    function processActiveStream(action) {
        if (!selectedId) return;

        const url = `/admin/review-queue/${selectedId}/${action}`;
        const activeCard = document.getElementById(`stream-card-${selectedId}`);
        
        // Find next card to select automatically
        let nextCard = null;
        if (activeCard) {
            let sibling = activeCard.nextElementSibling;
            while (sibling) {
                if (sibling.classList.contains('stream-card')) {
                    nextCard = sibling;
                    break;
                }
                sibling = sibling.nextElementSibling;
            }
            if (!nextCard) {
                // Try previous sibling if next card doesn't exist
                sibling = activeCard.previousElementSibling;
                while (sibling) {
                    if (sibling.classList.contains('stream-card')) {
                        nextCard = sibling;
                        break;
                    }
                    sibling = sibling.previousElementSibling;
                }
            }
        }

        // Disable buttons during request
        const approveBtn = document.getElementById('approve-btn');
        const rejectBtn = document.getElementById('reject-btn');
        approveBtn.disabled = true;
        rejectBtn.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                
                // Fade out card and remove
                if (activeCard) {
                    activeCard.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        activeCard.remove();
                        
                        // Check if all cards are gone
                        const remaining = document.querySelectorAll('.stream-card');
                        updateSidebarBadge(remaining.length);
                        
                        if (remaining.length === 0) {
                            window.location.reload();
                        } else if (nextCard) {
                            // Auto select next stream
                            selectStream(nextCard.getAttribute('data-id'));
                        } else {
                            resetPlayer();
                        }
                    }, 300);
                }
            } else {
                showToast(data.error || 'Operation failed.', 'error');
                approveBtn.disabled = false;
                rejectBtn.disabled = false;
            }
        })
        .catch(err => {
            showToast('Error communicating with server.', 'error');
            approveBtn.disabled = false;
            rejectBtn.disabled = false;
        });
    }

    function resetPlayer() {
        if (plyrPlayer) {
            plyrPlayer.destroy();
            plyrPlayer = null;
        }
        if (hls) {
            hls.destroy();
            hls = null;
        }
        player.pause();
        player.src = '';
        player.classList.add('hidden');
        overlay.classList.remove('hidden');
        selectedId = null;

        document.getElementById('active-name').innerText = 'Select a Stream';
        document.getElementById('active-logo').src = 'https://crichd.xyz/assets/images/live.png';
        document.getElementById('active-url').innerText = 'None';
        document.getElementById('active-referer').innerText = 'None';
        document.getElementById('active-origin').innerText = 'None';
        document.getElementById('active-source').innerText = 'N/A';
        document.getElementById('active-category').innerText = 'N/A';

        document.getElementById('approve-btn').disabled = true;
        document.getElementById('reject-btn').disabled = true;
    }

    function updateSidebarBadge(count) {
        const counters = document.querySelectorAll('#queue-counter');
        counters.forEach(el => el.innerText = count);

        // Sidebar badge
        const badge = document.querySelector('a[href$="review-queue"] span.bg-cyan-500');
        if (badge) {
            if (count > 0) {
                badge.innerText = count;
            } else {
                badge.remove();
            }
        }
    }

    function confirmRejectAll(event) {
        event.preventDefault();
        if (confirm('Are you sure you want to REJECT and DELETE ALL pending streams in the queue? This action cannot be undone.')) {
            const form = event.currentTarget;
            form.submit();
        }
        return false;
    }

    function confirmApproveAll(event) {
        event.preventDefault();
        if (confirm('Are you sure you want to APPROVE and PUBLISH ALL pending streams in the queue? This will make them live in the app.')) {
            const form = event.currentTarget;
            form.submit();
        }
        return false;
    }

    // Auto select first stream if available on load
    document.addEventListener('DOMContentLoaded', () => {
        const firstCard = document.querySelector('.stream-card');
        if (firstCard) {
            selectStream(firstCard.getAttribute('data-id'));
        }
    });
</script>
@endpush
