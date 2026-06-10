<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Stream;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ApiController extends Controller
{
    /**
     * Get global app settings (version info, welcome message).
     */
    public function getSettings()
    {
        $settings = AppSetting::first();
        if (!$settings) {
            return response()->json([
                'status' => 'error',
                'message' => 'Settings not found',
            ], 404);
        }

        // Load active promo banners along with their streams
        $activeBanners = \App\Models\PromoBanner::with([
            'stream1' => function ($q) {
                $q->where('is_active', true)->with('servers');
            },
            'stream2' => function ($q) {
                $q->where('is_active', true)->with('servers');
            },
            'stream3' => function ($q) {
                $q->where('is_active', true)->with('servers');
            }
        ])
        ->where('is_active', true)
        ->orderBy('order')
        ->orderBy('id', 'desc')
        ->get();

        $settingsData = $settings->toArray();
        $settingsData['promo_banners'] = $activeBanners;

        return response()->json([
            'status' => 'success',
            'data' => $settingsData
        ]);
    }

    /**
     * Get all categories sorted by order.
     */
    public function getCategories()
    {
        $categories = Category::orderBy('order')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Get active TV channels/streams belonging to a specific category.
     */
    public function getStreamsByCategory($categoryId)
    {
        $category = Category::find($categoryId);
        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found',
            ], 404);
        }

        $now = Carbon::now();
        $streams = $category->streams()
            ->where('show_in_tv', true)
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->where('is_permanent', true)
                      ->orWhere('expire_time', '>', $now);
            })
            ->with(['servers' => function ($query) {
                $query->orderBy('order');
            }])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $streams
        ]);
    }

    /**
     * Get active and upcoming live events/matches (not yet expired).
     */
    public function getLiveEvents()
    {
        $now = Carbon::now();
        $events = Stream::where('show_in_events', true)
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->where('is_permanent', true)
                      ->orWhere('expire_time', '>', $now);
            })
            ->with(['servers' => function ($query) {
                $query->orderBy('order');
            }])
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $events
        ]);
    }

    /**
     * Get active sports streams (permanent or temporary not yet expired).
     */
    public function getSportsStreams()
    {
        $now = Carbon::now();
        $sports = Stream::where('show_in_sports', true)
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->where('is_permanent', true)
                      ->orWhere('expire_time', '>', $now);
            })
            ->with(['servers' => function ($query) {
                $query->orderBy('order');
            }])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sports
        ]);
    }

    /**
     * Get all active TV channels/streams.
     */
    public function getAllStreams(Request $request)
    {
        $now = Carbon::now();
        $query = Stream::where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->where('is_permanent', true)
                  ->orWhere('expire_time', '>', $now);
            });

        // Filter for TV channels specifically, if wanted, or all streams
        // Let's search across all streams that have show_in_tv true OR show_in_sports true
        $query->where(function ($q) {
            $q->where('show_in_tv', true)
              ->orWhere('show_in_sports', true);
        });

        if ($request->has('q')) {
            $search = $request->query('q');
            $query->where('name', 'like', "%{$search}%");
        }

        $streams = $query->with(['servers' => function ($q) {
                $q->orderBy('order');
            }])
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $streams
        ]);
    }
}
