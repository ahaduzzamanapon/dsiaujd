<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Stream;
use Carbon\Carbon;

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
}
