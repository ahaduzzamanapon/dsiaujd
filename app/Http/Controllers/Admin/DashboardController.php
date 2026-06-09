<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Stream;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

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
            ]
        ];

        return view('admin.sync', compact('syncOptions'));
    }

    /**
     * Run sync Artisan command.
     */
    public function runSync(Request $request)
    {
        $type = $request->input('type');
        $url = $request->input('url');

        try {
            if ($type === 'm3u') {
                if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                    return back()->with('error', 'A valid M3U playlist URL is required.');
                }
                
                Artisan::call('m3u:sync', ['url' => $url]);
                $output = Artisan::output();
                return back()->with('success', 'M3U sync finished successfully.')->with('sync_output', $output);
            } elseif ($type === 'fancode') {
                Artisan::call('fancode:sync');
                $output = Artisan::output();
                return back()->with('success', 'FanCode sync finished successfully.')->with('sync_output', $output);
            } else {
                return back()->with('error', 'Invalid sync task type specified.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to run sync command: ' . $e->getMessage());
        }
    }
}
