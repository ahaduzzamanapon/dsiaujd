<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Stream;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\SyncTask;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index()
    {
        $now = Carbon::now();
        
        $stats = [
            'total_categories' => Category::count(),
            'total_streams' => Stream::count(),
            'active_streams' => Stream::where('is_active', true)->count(),
            'live_events' => Stream::where('show_in_events', true)
                ->where('expire_time', '>', $now)
                ->count(),
            'sports_streams' => Stream::where('show_in_sports', true)
                ->where(function ($query) use ($now) {
                    $query->where('is_permanent', true)
                          ->orWhere('expire_time', '>', $now);
                })
                ->count(),
        ];

        // Fetch recent active streams
        $recentStreams = Stream::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentStreams'));
    }

    /**
     * Display the sync console.
     */
    public function syncConsole()
    {
        $syncOptions = [
            [
                'id' => 1,
                'name' => 'Roar Zone Playlist',
                'description' => 'Fetch TV streams from Roar Zone M3U playlist repository.',
                'type' => 'm3u',
                'url' => 'https://raw.githubusercontent.com/abusaeeidx/BDxTV/refs/heads/main/roar-zone-playlist.m3u'
            ],
            [
                'id' => 2,
                'name' => 'CricHD Scraper',
                'description' => 'Fetch active cricket and live streams from CricHD playlist.',
                'type' => 'm3u',
                'url' => 'https://raw.githubusercontent.com/abusaeeidx/IPTV-Scraper-Zilla/refs/heads/main/CricHD.m3u'
            ],
            [
                'id' => 3,
                'name' => 'BD Scraper',
                'description' => 'Fetch Bangladeshi TV channels and live streams.',
                'type' => 'm3u',
                'url' => 'https://raw.githubusercontent.com/abusaeeidx/IPTV-Scraper-Zilla/refs/heads/main/BD.m3u'
            ],
            [
                'id' => 4,
                'name' => 'Combined Playlist',
                'description' => 'Comprehensive master TV channels and events playlist.',
                'type' => 'm3u',
                'url' => 'https://raw.githubusercontent.com/abusaeeidx/IPTV-Scraper-Zilla/main/combined-playlist.m3u'
            ],
            [
                'id' => 5,
                'name' => 'BDIX IPTV Playlist',
                'description' => 'Fast local BDIX IPTV playlist for channels.',
                'type' => 'm3u',
                'url' => 'https://raw.githubusercontent.com/abusaeeidx/Mrgify-BDIX-IPTV/refs/heads/main/playlist.m3u'
            ],
            [
                'id' => 6,
                'name' => 'FanCode Scraper',
                'description' => 'Fetch match fixtures and live streaming CDNs directly from FanCode.',
                'type' => 'fancode',
                'url' => null
            ],
            [
                'id' => 7,
                'name' => 'Stream Link Checker & Cleaner',
                'description' => 'Validate and verify all stream links in the database. Auto deletes dead servers, and deletes empty streams.',
                'type' => 'link-checker',
                'url' => null
            ]
        ];

        $recentTasks = SyncTask::latest()->take(10)->get();

        return view('admin.sync', compact('syncOptions', 'recentTasks'));
    }

    /**
     * Run sync Artisan command in the background.
     */
    public function runSync(Request $request)
    {
        $type = $request->input('type');
        $url = $request->input('url');

        if ($type === 'm3u') {
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                if ($request->ajax()) {
                    return response()->json(['error' => 'A valid M3U playlist URL is required.'], 422);
                }
                return back()->with('error', 'A valid M3U playlist URL is required.');
            }
            
            // Deduce a readable name from url, or use preset name
            $name = 'Custom M3U Playlist';
            if (str_contains($url, 'roar-zone-playlist')) {
                $name = 'Roar Zone Playlist';
            } elseif (str_contains($url, 'CricHD')) {
                $name = 'CricHD Scraper';
            } elseif (str_contains($url, 'BD.m3u')) {
                $name = 'BD Scraper';
            } elseif (str_contains($url, 'combined-playlist')) {
                $name = 'Combined Playlist';
            } elseif (str_contains($url, 'Mrgify-BDIX-IPTV') || str_contains($url, 'playlist.m3u')) {
                $name = 'BDIX IPTV Playlist';
            }
        } elseif ($type === 'fancode') {
            $name = 'FanCode Scraper';
            $url = null;
        } elseif ($type === 'link-checker') {
            $name = 'Stream Link Checker & Cleaner';
            $url = null;
        } else {
            if ($request->ajax()) {
                return response()->json(['error' => 'Invalid sync task type specified.'], 400);
            }
            return back()->with('error', 'Invalid sync task type specified.');
        }

        try {
            $task = SyncTask::create([
                'type' => $type,
                'name' => $name,
                'url' => $url,
                'status' => 'pending',
            ]);

            $artisanPath = base_path('artisan');
            $logPath = storage_path('logs/sync/task_' . $task->id . '.log');
            
            if (!file_exists(dirname($logPath))) {
                mkdir(dirname($logPath), 0755, true);
            }

            $phpBinary = PHP_BINARY;

            if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                // Windows background execution using popen + start /B
                $cmd = "start /B cmd /c \"\"{$phpBinary}\" \"{$artisanPath}\" sync:run-task {$task->id} > \"{$logPath}\" 2>&1\"";
                pclose(popen($cmd, "r"));
            } else {
                // Linux background execution
                $cmd = "\"{$phpBinary}\" \"{$artisanPath}\" sync:run-task {$task->id} > \"{$logPath}\" 2>&1 &";
                exec($cmd);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sync task started in background.',
                    'task' => $task
                ]);
            }

            return back()->with('success', 'Sync task started in the background.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to start sync task: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to start sync task: ' . $e->getMessage());
        }
    }

    /**
     * Get recent sync tasks status for polling.
     */
    public function getSyncTasks()
    {
        $tasks = SyncTask::latest()->take(10)->get()->map(function ($task) {
            $duration = null;
            if ($task->started_at) {
                $end = $task->completed_at ?: now();
                $duration = $task->started_at->diffInSeconds($end) . 's';
            }
            
            return [
                'id' => $task->id,
                'name' => $task->name,
                'type' => $task->type,
                'status' => $task->status,
                'duration' => $duration,
                'triggered_at' => $task->created_at->diffForHumans(),
            ];
        });

        return response()->json($tasks);
    }

    /**
     * Get logs and status of a sync task.
     */
    public function getSyncTaskLog($id)
    {
        $task = SyncTask::findOrFail($id);
        $logPath = storage_path('logs/sync/task_' . $id . '.log');

        $content = '';
        if (file_exists($logPath)) {
            $content = file_get_contents($logPath);
        }

        return response()->json([
            'content' => $content ?: ($task->status === 'pending' ? 'Waiting for execution to start...' : 'Initializing console output...'),
            'status' => $task->status,
            'duration' => $task->started_at ? $task->started_at->diffInSeconds($task->completed_at ?: now()) . 's' : null
        ]);
    }
}
