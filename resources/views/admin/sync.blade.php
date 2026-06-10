@extends('layouts.admin')

@section('page-title', 'Data Sync & Scheduler Console')
@section('page-subtitle', 'Trigger background scrapers and synchronization tasks manually')

@section('content')
<div class="space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Side: Sync Trigger Controllers (Col Span 2) -->
        <div class="lg:col-span-2 space-y-8">
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
                    <button type="submit" class="w-full md:w-auto py-3 px-6 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-sm shadow-lg shadow-cyan-500/15 transition-all whitespace-nowrap flex items-center justify-center gap-2 mb-[52px] md:mb-0">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($syncOptions as $option)
                        <div class="glass-card p-6 rounded-2xl flex flex-col justify-between min-h-[200px]">
                            <div>
                                <div class="flex items-center justify-between mb-3 font-bold text-white">
                                    <span class="text-sm">{{ $option['name'] }}</span>
                                    @if($option['type'] === 'm3u')
                                        <span class="px-2 py-0.5 rounded text-[9px] font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 uppercase">M3U</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[9px] font-semibold bg-pink-500/10 text-pink-400 border border-pink-500/20 uppercase">Scraper</span>
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
                                <button type="submit" class="w-full py-2.5 px-4 rounded-xl font-semibold text-xs text-white bg-gray-900 border border-gray-800 hover:bg-gray-800 hover:border-gray-700 transition-all flex items-center justify-center gap-2">
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
        </div>

        <!-- Right Side: Sync Tasks Monitor -->
        <div class="lg:col-span-1">
            <div class="glass-panel p-6 rounded-3xl shadow-xl sticky top-6">
                <h3 class="text-lg font-bold text-white mb-2">Sync Monitor & History</h3>
                <p class="text-xs text-gray-400 mb-6">Track background execution processes and logs in real-time.</p>
                
                <div id="tasks-container" class="space-y-4 max-h-[600px] overflow-y-auto pr-1">
                    @forelse($recentTasks as $task)
                        <div class="task-card bg-gray-950/40 border border-gray-900 rounded-xl p-4 transition-all hover:border-gray-800" id="task-{{ $task->id }}" data-status="{{ $task->status }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-bold text-white truncate block">{{ $task->name }}</span>
                                    </div>
                                    <span class="text-[10px] text-gray-400 block font-mono truncate">{{ $task->url ?: ucfirst($task->type) }}</span>
                                    <div class="flex items-center gap-2 mt-3 text-[10px] text-gray-500">
                                        <span>{{ $task->created_at->diffForHumans() }}</span>
                                        <span>•</span>
                                        <span class="task-duration">{{ $task->started_at ? $task->started_at->diffInSeconds($task->completed_at ?: now()) . 's' : '' }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-3 shrink-0">
                                    <span class="status-badge px-2 py-0.5 rounded text-[10px] font-semibold flex items-center gap-1.5 uppercase
                                        @if($task->status === 'completed') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                        @elseif($task->status === 'failed') bg-rose-500/10 text-rose-400 border border-rose-500/20
                                        @elseif($task->status === 'running') bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 animate-pulse
                                        @else bg-gray-500/10 text-gray-400 border border-gray-500/20 @endif">
                                        @if($task->status === 'running')
                                            <svg class="animate-spin h-2.5 w-2.5" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        @endif
                                        {{ $task->status }}
                                    </span>
                                    
                                    <button onclick="openLogConsole({{ $task->id }}, '{{ addslashes($task->name) }}')" class="py-1 px-2.5 rounded bg-gray-900 border border-gray-800 text-[10px] text-cyan-400 hover:bg-cyan-500/10 hover:border-cyan-500/30 transition-all flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Console
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div id="no-tasks-msg" class="text-center py-8 text-gray-500 text-xs">
                            No sync tasks triggered yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Terminal Drawer -->
<div id="console-drawer" class="fixed bottom-0 right-0 left-0 lg:left-64 bg-gray-950/95 border-t border-gray-800 shadow-2xl z-40 transform translate-y-full transition-transform duration-300 ease-out hidden flex-col h-[400px]">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-3 border-b border-gray-800 bg-gray-950/50">
        <div class="flex items-center space-x-3">
            <span id="console-status-indicator" class="w-2.5 h-2.5 rounded-full bg-yellow-500 animate-pulse"></span>
            <span class="text-xs font-bold text-white" id="console-title">Live Log Console</span>
            <span id="console-timer" class="text-[10px] text-gray-400 font-mono bg-gray-900 px-2 py-0.5 rounded border border-gray-800">0s</span>
        </div>
        <div class="flex items-center space-x-4">
            <button onclick="copyConsoleLog()" class="text-xs text-gray-400 hover:text-cyan-400 transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Copy Log
            </button>
            <button onclick="closeLogConsole()" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    <!-- Body -->
    <pre id="console-output" class="p-6 font-mono text-[11px] text-emerald-400 leading-relaxed whitespace-pre-wrap select-text bg-gray-950 overflow-y-auto" style="height: 350px; max-height: 350px;"></pre>
</div>
@endsection

@push('scripts')
<script>
    let activeConsoleTaskId = null;
    let consolePollInterval = null;
    let tasksPollInterval = null;

    function fillM3uUrl(url) {
        document.getElementById('custom-m3u-url').value = url;
    }

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

    function submitSyncForm(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const formData = new FormData(form);
        
        const btn = form.querySelector('button[type="submit"]');
        const btnHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="animate-spin inline-block w-3.5 h-3.5 border-2 border-white/20 border-t-white rounded-full"></span> Queueing...`;
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = btnHTML;
            
            if (data.success) {
                prependTaskToList(data.task);
                showToast(data.message, 'success');
                startTasksPolling();
                openLogConsole(data.task.id, data.task.name);
            } else {
                showToast(data.error || 'Failed to start sync task.', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = btnHTML;
            showToast('Error connecting to server.', 'error');
        });
    }

    function openLogConsole(taskId, taskName) {
        activeConsoleTaskId = taskId;
        
        const drawer = document.getElementById('console-drawer');
        drawer.classList.remove('hidden');
        setTimeout(() => {
            drawer.classList.remove('translate-y-full');
        }, 50);
        
        document.getElementById('console-title').innerText = `${taskName} - Console Live Output`;
        
        const consoleOutput = document.getElementById('console-output');
        consoleOutput.innerText = 'Initializing console session...';
        document.getElementById('console-timer').innerText = '0s';
        
        if (consolePollInterval) clearInterval(consolePollInterval);
        
        pollConsoleLog();
        consolePollInterval = setInterval(pollConsoleLog, 2000);
    }

    function closeLogConsole() {
        activeConsoleTaskId = null;
        if (consolePollInterval) {
            clearInterval(consolePollInterval);
            consolePollInterval = null;
        }
        const drawer = document.getElementById('console-drawer');
        drawer.classList.add('translate-y-full');
        setTimeout(() => drawer.classList.add('hidden'), 300);
    }

    function pollConsoleLog() {
        if (!activeConsoleTaskId) return;
        
        fetch(`/admin/sync/tasks/${activeConsoleTaskId}/log`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!activeConsoleTaskId) return; // guard if closed during request
            
            const consoleOutput = document.getElementById('console-output');
            const indicator = document.getElementById('console-status-indicator');
            const timer = document.getElementById('console-timer');
            
            consoleOutput.innerText = data.content;
            
            setTimeout(() => {
                consoleOutput.scrollTop = consoleOutput.scrollHeight;
            }, 50);
            
            if (data.duration) {
                timer.innerText = data.duration;
            }
            
            indicator.className = 'w-2.5 h-2.5 rounded-full';
            if (data.status === 'running') {
                indicator.classList.add('bg-yellow-500', 'animate-pulse');
            } else if (data.status === 'completed') {
                indicator.classList.add('bg-emerald-500');
                if (consolePollInterval) {
                    clearInterval(consolePollInterval);
                    consolePollInterval = null;
                }
            } else if (data.status === 'failed') {
                indicator.classList.add('bg-rose-500');
                if (consolePollInterval) {
                    clearInterval(consolePollInterval);
                    consolePollInterval = null;
                }
            } else {
                indicator.classList.add('bg-gray-500');
            }
        })
        .catch(err => {
            console.error(err);
        });
    }

    function startTasksPolling() {
        if (tasksPollInterval) return;
        
        const activeTasks = document.querySelectorAll('[data-status="running"], [data-status="pending"]');
        if (activeTasks.length > 0) {
            tasksPollInterval = setInterval(pollTasksList, 3000);
        }
    }

    function pollTasksList() {
        fetch('/admin/sync/tasks', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(tasks => {
            let hasActive = false;
            
            tasks.forEach(task => {
                if (task.status === 'running' || task.status === 'pending') {
                    hasActive = true;
                }
                
                let card = document.getElementById(`task-${task.id}`);
                if (card) {
                    card.setAttribute('data-status', task.status);
                    
                    if (task.duration) {
                        card.querySelector('.task-duration').innerText = task.duration;
                    }
                    
                    const badge = card.querySelector('.status-badge');
                    badge.className = `status-badge px-2 py-0.5 rounded text-[10px] font-semibold flex items-center gap-1.5 uppercase`;
                    
                    let badgeHTML = '';
                    if (task.status === 'completed') {
                        badge.classList.add('bg-emerald-500/10', 'text-emerald-400', 'border', 'border-emerald-500/20');
                        badgeHTML = 'completed';
                    } else if (task.status === 'failed') {
                        badge.classList.add('bg-rose-500/10', 'text-rose-400', 'border', 'border-rose-500/20');
                        badgeHTML = 'failed';
                    } else if (task.status === 'running') {
                        badge.classList.add('bg-yellow-500/10', 'text-yellow-400', 'border', 'border-yellow-500/20', 'animate-pulse');
                        badgeHTML = `
                            <svg class="animate-spin h-2.5 w-2.5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            running`;
                    } else {
                        badge.classList.add('bg-gray-500/10', 'text-gray-400', 'border', 'border-gray-500/20');
                        badgeHTML = 'pending';
                    }
                    badge.innerHTML = badgeHTML;
                } else {
                    prependTaskToList(task);
                }
            });
            
            if (!hasActive && tasksPollInterval) {
                clearInterval(tasksPollInterval);
                tasksPollInterval = null;
            }
        })
        .catch(err => {
            console.error(err);
        });
    }

    function prependTaskToList(task) {
        const noTasksMsg = document.getElementById('no-tasks-msg');
        if (noTasksMsg) {
            noTasksMsg.remove();
        }
        
        if (document.getElementById(`task-${task.id}`)) return;
        
        const container = document.getElementById('tasks-container');
        const card = document.createElement('div');
        card.className = 'task-card bg-gray-950/40 border border-gray-900 rounded-xl p-4 transition-all hover:border-gray-800';
        card.id = `task-${task.id}`;
        card.setAttribute('data-status', task.status);
        
        let badgeClasses = '';
        let badgeSpinner = '';
        if (task.status === 'completed') {
            badgeClasses = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
        } else if (task.status === 'failed') {
            badgeClasses = 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
        } else if (task.status === 'running') {
            badgeClasses = 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 animate-pulse';
            badgeSpinner = `
                <svg class="animate-spin h-2.5 w-2.5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>`;
        } else {
            badgeClasses = 'bg-gray-500/10 text-gray-400 border border-gray-500/20';
        }
        
        card.innerHTML = `
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-bold text-white truncate block">${task.name}</span>
                    </div>
                    <span class="text-[10px] text-gray-400 block font-mono truncate">${task.url || ucfirst(task.type)}</span>
                    <div class="flex items-center gap-2 mt-3 text-[10px] text-gray-500">
                        <span>${task.triggered_at || 'Just now'}</span>
                        <span>•</span>
                        <span class="task-duration">${task.duration || ''}</span>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-3 shrink-0">
                    <span class="status-badge px-2 py-0.5 rounded text-[10px] font-semibold flex items-center gap-1.5 uppercase ${badgeClasses}">
                        ${badgeSpinner}
                        ${task.status}
                    </span>
                    <button onclick="openLogConsole(${task.id}, '${task.name.replace(/'/g, "\\'")}')" class="py-1 px-2.5 rounded bg-gray-900 border border-gray-800 text-[10px] text-cyan-400 hover:bg-cyan-500/10 hover:border-cyan-500/30 transition-all flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Console
                    </button>
                </div>
            </div>
        `;
        
        container.insertBefore(card, container.firstChild);
    }

    function copyConsoleLog() {
        const consoleOutput = document.getElementById('console-output');
        if (consoleOutput) {
            navigator.clipboard.writeText(consoleOutput.innerText).then(() => {
                showToast('Console log copied to clipboard.', 'success');
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    }

    function ucfirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form[action$="/sync/run"]').forEach(form => {
            form.addEventListener('submit', submitSyncForm);
        });
        
        startTasksPolling();
    });
</script>
@endpush
