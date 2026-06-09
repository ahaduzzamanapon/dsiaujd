<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AllTV - Premium Live Streaming App</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
    <style>
        body {
            background-color: #080b11;
            color: #f3f4f6;
            background-image: radial-gradient(circle at 10% 20%, rgba(6, 182, 212, 0.08) 0%, transparent 50%),
                              radial-gradient(circle at 90% 80%, rgba(59, 130, 246, 0.08) 0%, transparent 50%);
        }
        .glass-panel {
            background: rgba(17, 24, 39, 0.55);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .glowing-btn {
            box-shadow: 0 0 20px 0 rgba(6, 182, 212, 0.3);
            transition: all 0.3s;
        }
        .glowing-btn:hover {
            box-shadow: 0 0 30px 5px rgba(6, 182, 212, 0.5);
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between">

    <!-- Top Navigation Bar -->
    <nav class="max-w-7xl mx-auto w-full px-6 py-5 flex items-center justify-between z-10">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-tr from-cyan-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-cyan-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                </svg>
            </div>
            <span class="text-2xl font-bold tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">AllTV</span>
        </div>
        <a href="{{ route('admin.login') }}" class="px-5 py-2 rounded-xl text-sm font-semibold border border-gray-800 bg-gray-900/60 hover:bg-gray-800 text-gray-300 transition-all">
            Admin Panel
        </a>
    </nav>

    <!-- Main Hero Section -->
    <main class="max-w-7xl mx-auto w-full px-6 flex flex-col lg:flex-row items-center justify-between py-12 lg:py-24 space-y-12 lg:space-y-0 lg:space-x-12 z-10">
        
        <!-- Left Hero Text -->
        <div class="max-w-xl text-center lg:text-left space-y-6">
            <div class="inline-flex items-center space-x-2.5 px-3 py-1.5 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 rounded-full text-xs font-semibold uppercase tracking-wider">
                <span class="w-2 h-2 bg-cyan-500 rounded-full animate-ping"></span>
                <span>Live & On-Demand Streaming App</span>
            </div>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-tight">
                Stream Your Favorite <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">Live Channels</span> Instantly
            </h1>
            <p class="text-base sm:text-lg text-gray-400 leading-relaxed">
                Watch Cricket, Football, and live TV channels in real-time. Featuring automatic fallback server selectors, high-definition streams, and live match countdown alerts.
            </p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-4">
                <a href="#" class="glowing-btn flex items-center justify-center space-x-3 px-8 py-4 bg-gradient-to-r from-cyan-500 to-blue-600 rounded-2xl font-bold text-white text-base w-full sm:w-auto">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.523 15.3l-5.385-3.11V5.992c0-.55-.45-1-1-1s-1 .45-1 1v6.79c0 .35.18.68.49.86l5.895 3.4c.48.28 1.09.11 1.37-.37.28-.48.12-1.09-.37-1.37zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
                    </svg>
                    <span>Download App APK</span>
                </a>
                <span class="text-xs text-gray-500">Compatible with Android 5.0+</span>
            </div>
        </div>

        <!-- Right Hero Visual Panel (Mock Phone Screen) -->
        <div class="relative w-full max-w-sm flex justify-center">
            <!-- Back Glow decoration -->
            <div class="absolute inset-0 bg-gradient-to-tr from-cyan-500/10 to-blue-600/10 rounded-full filter blur-3xl -z-10"></div>
            
            <!-- Glass Mockup phone frame -->
            <div class="w-full aspect-[9/18.5] glass-panel rounded-[40px] p-4 shadow-2xl relative border border-gray-800">
                <!-- Phone Top Bar -->
                <div class="w-32 h-6 bg-black rounded-b-2xl mx-auto absolute top-0 left-1/2 -translate-x-1/2 flex items-center justify-center">
                    <div class="w-2.5 h-2.5 bg-gray-900 rounded-full"></div>
                </div>
                
                <!-- Inner Screen Container -->
                <div class="w-full h-full bg-[#0b0f19] rounded-[30px] overflow-hidden p-3 pt-6 flex flex-col space-y-4">
                    <!-- App Header -->
                    <div class="flex items-center justify-between border-b border-gray-900 pb-2">
                        <span class="text-sm font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">AllTV Player</span>
                        <div class="flex space-x-1">
                            <span class="w-1.5 h-1.5 bg-cyan-500 rounded-full"></span>
                            <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                        </div>
                    </div>

                    <!-- Marquee Mock -->
                    <div class="bg-cyan-500/5 border border-cyan-500/10 py-1.5 px-3 rounded-lg text-center overflow-hidden">
                        <span class="text-[9px] text-cyan-400 font-medium whitespace-nowrap animate-pulse">Enjoy live streaming with the latest updates</span>
                    </div>

                    <!-- Live TV Player Mock -->
                    <div class="aspect-video w-full bg-black rounded-xl border border-gray-900 relative overflow-hidden flex items-center justify-center">
                        <div class="absolute inset-0 bg-cover bg-center opacity-40" style="background-image: url('https://flagcdn.com/w320/bd.png')"></div>
                        <div class="w-10 h-10 rounded-full bg-cyan-500 flex items-center justify-center cursor-pointer shadow-lg shadow-cyan-500/30 z-10">
                            <svg class="w-5 h-5 text-white fill-white ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                        <span class="absolute top-2 left-2 px-1.5 py-0.5 rounded bg-red-500 text-[8px] font-bold tracking-wider uppercase text-white animate-pulse">Live</span>
                    </div>

                    <!-- Mini Categories Selection List -->
                    <div class="space-y-1.5 flex-1 overflow-y-auto">
                        <span class="text-[9px] uppercase tracking-wider text-gray-500 font-bold block mb-1">Featured Categories</span>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="p-2.5 bg-gray-900/60 rounded-xl border border-gray-800/60 flex items-center space-x-2">
                                <div class="w-5 h-5 rounded bg-cyan-500/15 flex items-center justify-center text-[10px] text-cyan-400 font-bold">JI</div>
                                <span class="text-[9px] font-medium text-gray-300">JIOTV+</span>
                            </div>
                            <div class="p-2.5 bg-gray-900/60 rounded-xl border border-gray-800/60 flex items-center space-x-2">
                                <div class="w-5 h-5 rounded bg-blue-500/15 flex items-center justify-center text-[10px] text-blue-400 font-bold">SO</div>
                                <span class="text-[9px] font-medium text-gray-300">SONY IN</span>
                            </div>
                            <div class="p-2.5 bg-gray-900/60 rounded-xl border border-gray-800/60 flex items-center space-x-2">
                                <div class="w-5 h-5 rounded bg-pink-500/15 flex items-center justify-center text-[10px] text-pink-400 font-bold">BA</div>
                                <span class="text-[9px] font-medium text-gray-300">Bangla TV</span>
                            </div>
                            <div class="p-2.5 bg-gray-900/60 rounded-xl border border-gray-800/60 flex items-center space-x-2">
                                <div class="w-5 h-5 rounded bg-purple-500/15 flex items-center justify-center text-[10px] text-purple-400 font-bold">SP</div>
                                <span class="text-[9px] font-medium text-gray-300">Sports</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer Area -->
    <footer class="border-t border-gray-900 py-6 text-center text-xs text-gray-500 z-10 max-w-7xl mx-auto w-full">
        AllTV Entertainment Group &copy; {{ date('Y') }}. All rights reserved.
    </footer>

</body>
</html>
