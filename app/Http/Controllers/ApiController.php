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
        $activeBanners = \App\Models\PromoBanner::where('is_active', true)
            ->orderBy('order')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($activeBanners as $banner) {
            $banner->stream1 = $banner->stream1_id ? Stream::with('servers')->where('is_active', true)->find($banner->stream1_id) : null;
            $banner->stream2 = $banner->stream2_id ? Stream::with('servers')->where('is_active', true)->find($banner->stream2_id) : null;
            $banner->stream3 = $banner->stream3_id ? Stream::with('servers')->where('is_active', true)->find($banner->stream3_id) : null;
        }

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
}
