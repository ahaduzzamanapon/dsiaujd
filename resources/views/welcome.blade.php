<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings->welcome_message ?? 'LiveTV BD - Premium Live Streaming' }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <!-- Video.js CSS -->
    <link href="https://vjs.zencdn.net/7.20.3/video-js.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #020617;
            color: #f3f4f6;
            background-image: radial-gradient(circle at 10% 20%, rgba(6, 182, 212, 0.05) 0%, transparent 45%),
                              radial-gradient(circle at 90% 80%, rgba(99, 102, 241, 0.05) 0%, transparent 45%);
            background-attachment: fixed;
        }
        /* Glassmorphism panels */
        .glass-panel {
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.45);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            border-color: rgba(6, 182, 212, 0.2);
            box-shadow: 0 10px 25px -5px rgba(6, 182, 212, 0.1);
            transform: translateY(-2px);
        }
        /* Carousel Customization */
        .carousel-track {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }
        /* Custom VideoJS design */
        .video-js {
            font-family: 'Outfit', sans-serif;
            border-radius: 16px;
            overflow: hidden;
            width: 100% !important;
            height: 100% !important;
        }
        .vjs-theme-custom .vjs-control-bar {
            background-color: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(8px);
        }
        .vjs-theme-custom .vjs-play-progress {
            background-color: #06b6d4 !important;
        }
        /* Blinking pulse keyframes */
        @keyframes pulse-red {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.92); }
        }
        .pulse-live {
            animation: pulse-red 2s infinite ease-in-out;
        }
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #020617;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #06b6d4;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between selection:bg-cyan-500 selection:text-slate-900">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 w-full border-b border-slate-900 bg-slate-950/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Logo Section -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-tr from-cyan-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-cyan-500/20">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polygon points="5 3 19 12 5 21 5 3" stroke-linejoin="round" fill="currentColor"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xl font-black tracking-wider text-white">LiveTV<span class="text-cyan-400">BD</span></span>
                    <span class="block text-[9px] uppercase tracking-widest text-cyan-500/80 font-bold">livetvbd.live</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center space-x-8 text-sm font-semibold">
                <a href="#live-events" class="text-cyan-400 hover:text-cyan-300 transition-colors">Sports</a>
                <a href="#live-events" class="text-slate-300 hover:text-cyan-400 transition-colors">Football</a>
                <a href="#tv-channels" class="text-slate-300 hover:text-cyan-400 transition-colors">All Live TV</a>
            </nav>

            <!-- Action Area -->
            <div class="flex items-center space-x-4">
                <!-- Search Mock -->
                <div class="relative hidden sm:block">
                    <input type="text" id="searchChannels" placeholder="Search live channels..." class="w-60 bg-slate-900/60 border border-slate-800 text-slate-300 placeholder:text-slate-600 rounded-xl px-4 py-2 text-xs focus:outline-none focus:border-cyan-500/50 transition-colors">
                    <svg class="w-4 h-4 text-slate-600 absolute right-3.5 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <!-- Admin Link -->
                <a href="{{ route('admin.login') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                    Admin
                </a>

                <!-- Download App Button -->
                <a href="{{ $settings->update_url ?? '#' }}" class="px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-slate-950 font-bold rounded-xl text-xs tracking-wider transition-all duration-300 shadow-lg shadow-cyan-500/10 flex items-center space-x-1.5">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>
                    <span>DOWNLOAD APP</span>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 flex-1 space-y-12">

        <!-- 1. PROMOTIONAL CAROUSEL BANNER SECTION -->
        @if(count($banners) > 0)
        <section class="relative group overflow-hidden rounded-3xl border border-cyan-500/20 shadow-2xl">
            <!-- Carousel Outer -->
            <div class="relative w-full overflow-hidden bg-slate-950">
                <div class="carousel-track" id="carouselTrack">
                    @foreach($banners as $index => $banner)
                    <div class="w-full shrink-0 relative p-6 sm:p-8 md:p-10 flex flex-col md:flex-row items-center justify-between gap-8 bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950/40 min-h-[280px]">
                        <!-- Background Glow Elements -->
                        <div class="absolute right-0 top-0 w-80 h-80 bg-cyan-500/10 rounded-full filter blur-3xl -z-10"></div>
                        <div class="absolute left-1/4 bottom-0 w-60 h-60 bg-indigo-500/5 rounded-full filter blur-3xl -z-10"></div>

                        <!-- Left Info Column -->
                        <div class="flex-1 space-y-4 text-center md:text-left z-10 w-full">
                            <div class="inline-flex items-center space-x-2 px-3 py-1 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 bg-cyan-500 rounded-full animate-ping"></span>
                                <span>PROMOTION BANNER</span>
                            </div>
                            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-cyan-200">
                                {{ strtoupper($banner->title) }}
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-400 font-medium max-w-xl">
                                {{ $banner->subtitle }}
                            </p>

                            <!-- Quick Play Channels Shortcuts -->
                            @if($banner->stream1 || $banner->stream2 || $banner->stream3)
                            <div class="pt-2">
                                <span class="text-[9px] uppercase tracking-widest text-slate-500 font-bold block mb-2.5">QUICK STREAMS</span>
                                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3.5">
                                    @if($banner->stream1 && count($banner->stream1->servers) > 0)
                                        <button onclick="playSecureStream('{{ $banner->stream1->name }}', '{{ base64_encode($banner->stream1->servers[0]->url) }}', true)" class="flex items-center space-x-2 px-3.5 py-1.5 bg-slate-900/80 hover:bg-cyan-950/40 border border-slate-800 hover:border-cyan-500/30 rounded-xl text-xs font-semibold text-slate-200 hover:text-cyan-400 transition-all">
                                            <div class="w-7 h-7 rounded-full bg-cyan-500/10 flex items-center justify-center overflow-hidden">
                                                @if($banner->stream1->logo)
                                                    <img src="{{ $banner->stream1->logo }}" class="w-full h-full object-cover">
                                                @else
                                                    <svg class="w-4 h-4 text-cyan-400" fill="currentColor" viewBox="0 0 24 24"><polygon points="8 5 19 12 8 19 8 5"/></svg>
                                                @endif
                                            </div>
                                            <span>{{ $banner->stream1->name }}</span>
                                        </button>
                                    @endif
                                    @if($banner->stream2 && count($banner->stream2->servers) > 0)
                                        <button onclick="playSecureStream('{{ $banner->stream2->name }}', '{{ base64_encode($banner->stream2->servers[0]->url) }}', true)" class="flex items-center space-x-2 px-3.5 py-1.5 bg-slate-900/80 hover:bg-cyan-950/40 border border-slate-800 hover:border-cyan-500/30 rounded-xl text-xs font-semibold text-slate-200 hover:text-cyan-400 transition-all">
                                            <div class="w-7 h-7 rounded-full bg-cyan-500/10 flex items-center justify-center overflow-hidden">
                                                @if($banner->stream2->logo)
                                                    <img src="{{ $banner->stream2->logo }}" class="w-full h-full object-cover">
                                                @else
                                                    <svg class="w-4 h-4 text-cyan-400" fill="currentColor" viewBox="0 0 24 24"><polygon points="8 5 19 12 8 19 8 5"/></svg>
                                                @endif
                                            </div>
                                            <span>{{ $banner->stream2->name }}</span>
                                        </button>
                                    @endif
                                    @if($banner->stream3 && count($banner->stream3->servers) > 0)
                                        <button onclick="playSecureStream('{{ $banner->stream3->name }}', '{{ base64_encode($banner->stream3->servers[0]->url) }}', true)" class="flex items-center space-x-2 px-3.5 py-1.5 bg-slate-900/80 hover:bg-cyan-950/40 border border-slate-800 hover:border-cyan-500/30 rounded-xl text-xs font-semibold text-slate-200 hover:text-cyan-400 transition-all">
                                            <div class="w-7 h-7 rounded-full bg-cyan-500/10 flex items-center justify-center overflow-hidden">
                                                @if($banner->stream3->logo)
                                                    <img src="{{ $banner->stream3->logo }}" class="w-full h-full object-cover">
                                                @else
                                                    <svg class="w-4 h-4 text-cyan-400" fill="currentColor" viewBox="0 0 24 24"><polygon points="8 5 19 12 8 19 8 5"/></svg>
                                                @endif
                                            </div>
                                            <span>{{ $banner->stream3->name }}</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Right Actions Column (Countdown / Big Image / Button) -->
                        <div class="flex flex-col items-center justify-center space-y-5 z-10 w-full md:w-auto shrink-0">
                            <!-- Countdown Area -->
                            @if($banner->countdown)
                            <div class="bg-slate-950/60 border border-slate-800 p-4 rounded-2xl w-full max-w-xs text-center">
                                <span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold block mb-2">EVENT STARTS IN</span>
                                <div class="grid grid-cols-4 gap-2 text-cyan-400 font-bold" data-countdown="{{ $banner->countdown }}">
                                    <div>
                                        <div class="bg-slate-900/80 border border-slate-800 px-2.5 py-1.5 rounded-lg text-lg font-black" id="d-{{ $index }}">00</div>
                                        <span class="text-[7px] text-slate-600 block mt-1 uppercase">Days</span>
                                    </div>
                                    <div>
                                        <div class="bg-slate-900/80 border border-slate-800 px-2.5 py-1.5 rounded-lg text-lg font-black" id="h-{{ $index }}">00</div>
                                        <span class="text-[7px] text-slate-600 block mt-1 uppercase">Hours</span>
                                    </div>
                                    <div>
                                        <div class="bg-slate-900/80 border border-slate-800 px-2.5 py-1.5 rounded-lg text-lg font-black" id="m-{{ $index }}">00</div>
                                        <span class="text-[7px] text-slate-600 block mt-1 uppercase">Mins</span>
                                    </div>
                                    <div>
                                        <div class="bg-slate-900/80 border border-slate-800 px-2.5 py-1.5 rounded-lg text-lg font-black text-rose-500" id="s-{{ $index }}">00</div>
                                        <span class="text-[7px] text-slate-600 block mt-1 uppercase">Secs</span>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Banner Action Button -->
                            @if($banner->btn_text && $banner->btn_link)
                            <a href="{{ $banner->btn_link }}" target="_blank" class="w-full max-w-xs px-6 py-3 bg-gradient-to-r from-cyan-500 to-indigo-600 hover:from-cyan-400 hover:to-indigo-500 text-white font-bold text-center text-xs tracking-wider uppercase rounded-xl transition-all duration-300 shadow-lg shadow-cyan-500/15">
                                {{ $banner->btn_text }}
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Page Indicators / Dots -->
            @if(count($banners) > 1)
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center space-x-2.5 z-20">
                @foreach($banners as $index => $banner)
                <button onclick="setCarouselSlide({{ $index }})" class="w-2.5 h-2.5 rounded-full bg-slate-800 border border-slate-700 transition-all duration-300" id="carouselDot-{{ $index }}"></button>
                @endforeach
            </div>
            @endif
        </section>
        @endif


        <!-- 2. LIVE EVENTS SECTION -->
        <section id="live-events" class="space-y-6">
            <div class="flex items-center justify-between border-l-4 border-cyan-500 pl-4 py-1">
                <div>
                    <h3 class="text-lg font-extrabold uppercase tracking-wider text-white">LIVE EVENTS & MATCHES</h3>
                    <p class="text-[10px] text-slate-500 font-semibold uppercase">Streaming live right now from livetvbd.live</p>
                </div>
                <!-- Live Pulse -->
                <div class="flex items-center space-x-2 px-3 py-1 bg-rose-500/10 border border-rose-500/20 text-rose-500 rounded-full text-[9px] font-bold uppercase tracking-wider pulse-live">
                    <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
                    <span>ONLINE</span>
                </div>
            </div>

            @if(count($liveEvents) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($liveEvents as $event)
                @php
                    $isLiveNow = $event->start_time && $event->expire_time && \Carbon\Carbon::now()->between($event->start_time, $event->expire_time);
                    $firstServerUrl = count($event->servers) > 0 ? base64_encode($event->servers[0]->url) : '';
                @endphp
                <div class="glass-card rounded-2xl overflow-hidden flex flex-col justify-between h-56 p-5">
                    <!-- Card Header -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="p-1.5 bg-cyan-500/10 rounded-lg text-cyan-400">
                                @if(strtolower($event->sport_type) == 'cricket')
                                    🏏
                                @elseif(strtolower($event->sport_type) == 'football')
                                    ⚽
                                @else
                                    📺
                                @endif
                            </span>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ $event->sport_type ?? 'Sports' }}</span>
                        </div>
                        @if($isLiveNow)
                        <span class="px-2 py-0.5 rounded-md bg-red-500 text-[8px] font-black tracking-wider uppercase text-white animate-pulse">Live</span>
                        @else
                        <span class="px-2 py-0.5 rounded-md bg-slate-900 border border-slate-800 text-[8px] font-semibold text-slate-400">UPCOMING</span>
                        @endif
                    </div>

                    <!-- Teams Body -->
                    <div class="flex items-center justify-between gap-4 py-2">
                        <!-- Team 1 -->
                        <div class="flex-1 flex flex-col items-center text-center space-y-1">
                            <div class="w-11 h-11 bg-slate-900 rounded-full border border-slate-800 flex items-center justify-center overflow-hidden">
                                @if($event->team1_logo)
                                    <img src="{{ $event->team1_logo }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xs font-bold text-cyan-400">T1</span>
                                @endif
                            </div>
                            <span class="text-[11px] font-bold text-slate-200 line-clamp-1">{{ $event->team1_name ?? 'Team A' }}</span>
                        </div>

                        <!-- Mid VS -->
                        <div class="flex flex-col items-center">
                            <span class="text-xs font-black text-cyan-500 tracking-wider">VS</span>
                            @if(!$isLiveNow && $event->start_time)
                            <span class="text-[8px] text-slate-500 font-bold block mt-1 uppercase">{{ $event->start_time->format('h:i A') }}</span>
                            <span class="text-[7px] text-slate-600 block uppercase">{{ $event->start_time->format('d/m/Y') }}</span>
                            @endif
                        </div>

                        <!-- Team 2 -->
                        <div class="flex-1 flex flex-col items-center text-center space-y-1">
                            <div class="w-11 h-11 bg-slate-900 rounded-full border border-slate-800 flex items-center justify-center overflow-hidden">
                                @if($event->team2_logo)
                                    <img src="{{ $event->team2_logo }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xs font-bold text-cyan-400">T2</span>
                                @endif
                            </div>
                            <span class="text-[11px] font-bold text-slate-200 line-clamp-1">{{ $event->team2_name ?? 'Team B' }}</span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    @if($firstServerUrl)
                        <button onclick="playSecureStream('{{ $event->name }}', '{{ $firstServerUrl }}', true)" class="w-full py-2.5 bg-slate-900 hover:bg-cyan-500/10 border border-slate-800 hover:border-cyan-500/30 text-slate-300 hover:text-cyan-400 font-bold text-center text-xs tracking-wider rounded-xl transition-all">
                            WATCH LIVE NOW
                        </button>
                    @else
                        <button onclick="triggerDownloadPrompt()" class="w-full py-2.5 bg-slate-900 border border-slate-800 text-slate-500 font-bold text-center text-xs tracking-wider rounded-xl cursor-not-allowed">
                            STREAM UNAVAILABLE
                        </button>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="glass-panel rounded-2xl p-10 text-center text-slate-400 max-w-md mx-auto">
                <span class="text-3xl">📡</span>
                <h4 class="text-sm font-bold text-white mt-3 uppercase tracking-wider">No Active Live Events</h4>
                <p class="text-xs text-slate-500 mt-1">Please download the Android APK to browse the live broadcast archives.</p>
            </div>
            @endif
        </section>


        <!-- 3. LIVE TV CHANNELS SECTION (LOCKED FOR DOWNLOAD FORCE) -->
        <section id="tv-channels" class="space-y-6">
            <div class="flex items-center justify-between border-l-4 border-indigo-500 pl-4 py-1">
                <div>
                    <h3 class="text-lg font-extrabold uppercase tracking-wider text-white">LIVE TV CHANNELS</h3>
                    <p class="text-[10px] text-slate-500 font-semibold uppercase">Download Android App to watch these premium networks</p>
                </div>
                <!-- App Store Badge -->
                <span class="text-[9px] font-bold tracking-widest text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-3 py-1 rounded-full uppercase">APP ONLY</span>
            </div>

            <!-- Category Filter Buttons -->
            @if(count($categories) > 0)
            <div class="flex flex-wrap gap-2.5 pb-2">
                <button onclick="filterTVCategory('all')" id="catBtn-all" class="px-4 py-2 rounded-xl text-xs font-bold bg-cyan-500 text-slate-950 transition-all border border-transparent">
                    All Channels
                </button>
                @foreach($categories as $category)
                    @if(count($category->streams) > 0)
                    <button onclick="filterTVCategory('cat-{{ $category->id }}')" id="catBtn-cat-{{ $category->id }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-400 hover:text-white transition-all">
                        {{ $category->name }}
                    </button>
                    @endif
                @endforeach
            </div>

            <!-- TV Channels Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4" id="tvChannelsGrid">
                @foreach($categories as $category)
                    @foreach($category->streams as $stream)
                    <div class="glass-card rounded-2xl p-4 flex flex-col items-center text-center space-y-3 relative group overflow-hidden cursor-pointer" data-categories="cat-{{ $category->id }}" onclick="playSecureStream('{{ $stream->name }}', '{{ count($stream->servers) > 0 ? base64_encode($stream->servers[0]->url) : '' }}', false)">
                        <!-- Channel Logo -->
                        <div class="w-24 h-24 rounded-2xl bg-slate-900 border border-slate-800/80 flex items-center justify-center overflow-hidden relative">
                            @if($stream->logo)
                                <img src="{{ $stream->logo }}" class="w-full h-full object-contain p-2">
                            @else
                                <span class="text-lg font-black text-cyan-400">{{ strtoupper(substr($stream->name, 0, 2)) }}</span>
                            @endif
                            <!-- Lock Overlay Badge -->
                            <div class="absolute inset-0 bg-slate-950/80 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                        </div>

                        <!-- Info -->
                        <div>
                            <span class="text-[11px] font-bold text-slate-200 block truncate max-w-[120px]">{{ $stream->name }}</span>
                            <span class="text-[8px] uppercase tracking-wider text-slate-500 font-bold">{{ $category->name }}</span>
                        </div>

                        <!-- Absolute lock icon in corner -->
                        <span class="absolute top-2 right-2 text-slate-700 group-hover:text-cyan-400 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2V7a5 5 0 00-5-5zM7 7a3 3 0 116 0v2H7V7z"/></svg>
                        </span>
                    </div>
                    @endforeach
                @endforeach
            </div>
            @endif
        </section>

    </main>

    <!-- 4. SECURE PLAYER MODAL -->
    <div id="playerModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/95 backdrop-blur-md hidden">
        <!-- Close button outside card -->
        <button onclick="closePlayerModal()" class="absolute top-6 right-6 p-2 bg-slate-900 border border-slate-800 text-slate-400 hover:text-white rounded-full transition-all hover:scale-105">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="w-full max-w-4xl bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl flex flex-col">
            <!-- Player Screen Area -->
            <div class="aspect-video w-full bg-black relative flex items-center justify-center">
                <!-- Video tag container -->
                <div class="w-full h-full" id="videoContainer">
                    <!-- Dynamic VideoJS instantiation inside JS -->
                </div>
            </div>

            <!-- Bottom Panel with info & Download APK Prompt -->
            <div class="p-6 flex flex-col sm:flex-row items-center justify-between gap-6 border-t border-slate-800 bg-slate-900/50">
                <div class="space-y-1.5 text-center sm:text-left">
                    <div class="flex items-center justify-center sm:justify-start space-x-2">
                        <span class="w-2 h-2 bg-cyan-400 rounded-full animate-ping"></span>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider" id="playerTitle">STREAMING CHANNEL</h4>
                    </div>
                    <p class="text-xs text-slate-400 max-w-md">
                        Web player is limited to live events. For the best buffer-free, full 24/7 experience, download our official Android Application.
                    </p>
                </div>

                <!-- Action Button in Player -->
                <a href="{{ $settings->update_url ?? '#' }}" class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-indigo-600 hover:from-cyan-400 hover:to-indigo-500 text-slate-950 font-black rounded-xl text-xs tracking-wider transition-all duration-300 flex items-center space-x-2 shadow-lg shadow-cyan-500/10 shrink-0">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>
                    <span>DOWNLOAD MOBILE APP</span>
                </a>
            </div>
        </div>
    </div>


    <!-- 5. APP-ONLY LOCK MODAL -->
    <div id="lockModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md hidden">
        <div class="w-full max-w-md glass-panel p-8 rounded-3xl border border-indigo-500/30 shadow-2xl relative text-center space-y-6">
            <!-- Close button -->
            <button onclick="closeLockModal()" class="absolute top-4 right-4 text-slate-500 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- Locked Icon badge -->
            <div class="w-16 h-16 bg-gradient-to-tr from-indigo-500 to-rose-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg shadow-indigo-500/20">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>

            <!-- Content -->
            <div class="space-y-2">
                <h4 class="text-lg font-black tracking-wider text-white uppercase" id="lockModalTitle">CHANNEL LOCKED</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    This premium live TV channel is exclusive to our official Android Application. Download the APK file now to unlock 24/7 high-definition streams with dual fallback server selectors.
                </p>
            </div>

            <!-- Action -->
            <div class="pt-2">
                <a href="{{ $settings->update_url ?? '#' }}" class="w-full py-3.5 bg-gradient-to-r from-cyan-500 to-indigo-600 hover:from-cyan-400 hover:to-indigo-500 text-slate-950 font-black rounded-xl text-xs tracking-widest uppercase transition-all duration-300 block shadow-lg shadow-cyan-500/10">
                    GET ANDROID APP
                </a>
                <span class="text-[9px] text-slate-500 mt-2 block font-semibold">100% Free & Secure download • Supports Android 5.0+</span>
            </div>
        </div>
    </div>


    <!-- Footer Area -->
    <footer class="border-t border-slate-950 bg-slate-950/40 py-6 text-center text-xs text-slate-500 z-10 max-w-7xl mx-auto w-full px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        <span>LiveTV BD Network &copy; {{ date('Y') }}. All rights reserved.</span>
        <a href="#live-events" class="hover:text-cyan-400 transition-colors">Back to top 🚀</a>
    </footer>

    <!-- Video.js JS -->
    <script src="https://vjs.zencdn.net/7.20.3/video.min.js"></script>

    <!-- Main Logic Script -->
    <script>
        // ----------------------------------------
        // CAROUSEL BANNER CONTROLS
        // ----------------------------------------
        let currentSlide = 0;
        const totalSlides = {{ count($banners) }};
        const track = document.getElementById('carouselTrack');
        
        function setCarouselSlide(index) {
            if (index < 0 || index >= totalSlides) return;
            currentSlide = index;
            if (track) {
                track.style.transform = `translateX(-${currentSlide * 100}%)`;
            }
            
            // Update dots styling
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.getElementById(`carouselDot-${i}`);
                if (dot) {
                    if (i === currentSlide) {
                        dot.classList.remove('bg-slate-800', 'border-slate-700', 'w-2.5');
                        dot.classList.add('bg-cyan-500', 'border-cyan-400', 'w-6');
                    } else {
                        dot.classList.remove('bg-cyan-500', 'border-cyan-400', 'w-6');
                        dot.classList.add('bg-slate-800', 'border-slate-700', 'w-2.5');
                    }
                }
            }
        }
        
        // Auto slide every 5 seconds
        if (totalSlides > 1) {
            setCarouselSlide(0);
            setInterval(() => {
                let next = (currentSlide + 1) % totalSlides;
                setCarouselSlide(next);
            }, 6000);
        }

        // ----------------------------------------
        // COUNTDOWN TIMERS ENGINE
        // ----------------------------------------
        function initCountdowns() {
            const countdownElements = document.querySelectorAll('[data-countdown]');
            countdownElements.forEach((el, index) => {
                const targetStr = el.getAttribute('data-countdown');
                const targetDate = new Date(targetStr).getTime();
                
                const dayEl = document.getElementById(`d-${index}`);
                const hourEl = document.getElementById(`h-${index}`);
                const minEl = document.getElementById(`m-${index}`);
                const secEl = document.getElementById(`s-${index}`);
                
                const interval = setInterval(() => {
                    const now = new Date().getTime();
                    const diff = targetDate - now;
                    
                    if (diff <= 0) {
                        clearInterval(interval);
                        el.innerHTML = `<span class="text-xs font-black text-rose-500 tracking-widest uppercase pulse-live flex items-center justify-center space-x-1.5"><span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span><span>LIVE BROADCAST ONGOING</span></span>`;
                        return;
                    }
                    
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                    
                    if (dayEl) dayEl.textContent = String(days).padStart(2, '0');
                    if (hourEl) hourEl.textContent = String(hours).padStart(2, '0');
                    if (minEl) minEl.textContent = String(minutes).padStart(2, '0');
                    if (secEl) secEl.textContent = String(seconds).padStart(2, '0');
                }, 1000);
            });
        }
        document.addEventListener('DOMContentLoaded', initCountdowns);

        // ----------------------------------------
        // CATEGORIES FILTER ENGINE
        // ----------------------------------------
        function filterTVCategory(catId) {
            const grid = document.getElementById('tvChannelsGrid');
            if (!grid) return;
            const items = grid.children;
            
            // Reset active button class
            const buttons = document.querySelectorAll('[id^="catBtn-"]');
            buttons.forEach(btn => {
                btn.className = "px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-400 hover:text-white transition-all";
            });
            
            const activeBtn = document.getElementById(`catBtn-${catId}`);
            if (activeBtn) {
                activeBtn.className = "px-4 py-2 rounded-xl text-xs font-bold bg-cyan-500 text-slate-950 transition-all border border-transparent";
            }
            
            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                const itemCats = item.getAttribute('data-categories');
                if (catId === 'all' || itemCats === catId) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            }
        }

        // Live search filter
        const searchInput = document.getElementById('searchChannels');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                const grid = document.getElementById('tvChannelsGrid');
                if (!grid) return;
                const items = grid.children;
                
                for (let i = 0; i < items.length; i++) {
                    const item = items[i];
                    const name = item.querySelector('span').textContent.toLowerCase();
                    if (name.includes(query)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        }

        // ----------------------------------------
        // VIDEO PLAYER MODAL & OBFUSCATOR
        // ----------------------------------------
        let vjsPlayer = null;

        function playSecureStream(title, encodedUrl, isLiveEvent) {
            if (!encodedUrl) {
                triggerDownloadPrompt(title);
                return;
            }

            if (!isLiveEvent) {
                // Force app download for TV channels
                triggerDownloadPrompt(title);
                return;
            }

            const playerModal = document.getElementById('playerModal');
            const playerTitle = document.getElementById('playerTitle');
            const videoContainer = document.getElementById('videoContainer');
            
            playerTitle.textContent = title;
            playerModal.classList.remove('hidden');

            // Decode URL securely in memory
            const decodedUrl = atob(encodedUrl);

            // Recreate video tag to reset player cleanly
            videoContainer.innerHTML = `
                <video id="my-video" class="video-js vjs-big-play-centered vjs-theme-custom" controls preload="auto" width="640" height="264">
                    <source src="${decodedUrl}" type="application/x-mpegURL">
                    <p class="vjs-no-js">
                        To view this video please enable JavaScript, and consider upgrading to a web browser that supports HTML5 video.
                    </p>
                </video>
            `;

            // Initialize Video.js player
            if (vjsPlayer) {
                vjsPlayer.dispose();
            }

            vjsPlayer = videojs('my-video', {
                fluid: true,
                autoplay: true,
                controls: true,
                userActions: {
                    doubleClick: true
                }
            });
        }

        function closePlayerModal() {
            const playerModal = document.getElementById('playerModal');
            playerModal.classList.add('hidden');
            if (vjsPlayer) {
                vjsPlayer.pause();
                vjsPlayer.dispose();
                vjsPlayer = null;
            }
            document.getElementById('videoContainer').innerHTML = '';
        }

        // ----------------------------------------
        // LOCKED CHANNELS DOWNLOAD PROMPT
        // ----------------------------------------
        function triggerDownloadPrompt(title) {
            const lockModal = document.getElementById('lockModal');
            const lockTitle = document.getElementById('lockModalTitle');
            if (title) {
                lockTitle.textContent = `${title} - APP EXCLUSIVE`;
            } else {
                lockTitle.textContent = "CHANNEL LOCKED";
            }
            lockModal.classList.remove('hidden');
        }

        function closeLockModal() {
            document.getElementById('lockModal').classList.add('hidden');
        }

        // ----------------------------------------
        // SECURITY - PREVENT CONSOLE & SCRAPING
        // ----------------------------------------
        // Disable Right-Click
        document.addEventListener('contextmenu', event => event.preventDefault());

        // Intercept DevTools keys
        document.onkeydown = function(e) {
            if (e.keyCode == 123) { // F12
                return false;
            }
            if (e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) { // Ctrl+Shift+I
                return false;
            }
            if (e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) { // Ctrl+Shift+C
                return false;
            }
            if (e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) { // Ctrl+Shift+J
                return false;
            }
            if (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) { // Ctrl+U (View source)
                return false;
            }
        };
    </script>
</body>
</html>
