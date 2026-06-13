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

    /**
     * Register or update a device's ping state.
     */
    public function pingDevice(Request $request)
    {
        $request->validate([
            'uuid' => 'required|string',
            'platform' => 'nullable|string',
            'model' => 'nullable|string',
            'os_version' => 'nullable|string',
            'app_version' => 'nullable|string',
        ]);

        $device = \App\Models\Device::updateOrCreate(
            ['uuid' => $request->uuid],
            [
                'platform' => $request->platform,
                'model' => $request->model,
                'os_version' => $request->os_version,
                'app_version' => $request->app_version,
                'last_ping_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Device pinged successfully',
            'data' => $device
        ]);
    }

    /**
     * Proxy HLS streams to bypass CORS and mixed-content restrictions.
     */
    public function proxyStream(Request $request)
    {
        $url = $request->query('url');
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return response('Invalid URL', 400);
        }

        try {
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ];

            // 1. Try to find the stream server in the database to fetch configured headers
            $server = \App\Models\StreamServer::where('url', $url)->first();
            $referer = $server ? $server->http_referer : null;
            $origin = $server ? $server->http_origin : null;

            // 2. Resolve headers dynamically using the unified StreamServer helper
            $resolved = \App\Models\StreamServer::resolveHeadersForUrl($url, $referer, $origin);

            if (!empty($resolved['referer'])) {
                $headers['Referer'] = $resolved['referer'];
            }
            if (!empty($resolved['origin'])) {
                $headers['Origin'] = $resolved['origin'];
            }

            $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
                ->withOptions(['verify' => false])
                ->get($url);

            if (!$response->successful()) {
                return response('Failed to fetch stream from source', $response->status());
            }

            $body = $response->body();
            $contentType = $response->header('Content-Type') ?: 'application/vnd.apple.mpegurl';

            // Check if this content is an m3u8 playlist
            if (
                str_contains($contentType, 'mpegurl') || 
                str_contains($contentType, 'application/x-mpegURL') || 
                str_contains($url, '.m3u8') || 
                str_contains($body, '#EXTM3U')
            ) {
                $lines = explode("\n", $body);
                $rewrittenLines = [];
                
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;

                    if (str_starts_with($line, '#')) {
                        // EXTM3U metadata / tags
                        $rewrittenLines[] = $line;
                    } else {
                        // Video segment (.ts) or sub-playlist URL
                        $absoluteUrl = $this->resolveAbsoluteUrl($url, $line);
                        
                        if (str_contains($absoluteUrl, '.m3u8') || str_contains($absoluteUrl, '.m3u')) {
                            // Rewrite sub-playlist to go through proxy
                            $proxyUrl = '/api/stream-proxy?url=' . urlencode($absoluteUrl);
                            $rewrittenLines[] = $proxyUrl;
                        } else {
                            // Video segment - return absolute URL directly so the browser streams it directly
                            $rewrittenLines[] = $absoluteUrl;
                        }
                    }
                }
                
                $body = implode("\n", $rewrittenLines);
                $contentType = 'application/vnd.apple.mpegurl';
            }

            return response($body, 200)
                ->header('Content-Type', $contentType)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', '*');

        } catch (\Exception $e) {
            return response('Proxy Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Helper to resolve relative URLs to absolute URLs based on a base URL.
     */
    private function resolveAbsoluteUrl($base, $relative)
    {
        if (preg_match('/^https?:\/\//i', $relative)) {
            return $relative;
        }

        $baseParts = parse_url($base);
        $scheme = $baseParts['scheme'] ?? 'http';
        $host = $baseParts['host'] ?? '';
        $port = isset($baseParts['port']) ? ':' . $baseParts['port'] : '';
        $path = $baseParts['path'] ?? '';

        if (str_starts_with($relative, '/')) {
            return $scheme . '://' . $host . $port . $relative;
        }

        $dir = dirname($path);
        if ($dir === '/' || $dir === '\\') {
            $dir = '';
        }

        return $scheme . '://' . $host . $port . $dir . '/' . $relative;
    }



    /**
     * Play RedForce stream by dynamically parsing the live source URL.
     */
    public function playRedforceStream($streamId)
    {
        $url = "http://redforce.live/player.php?stream={$streamId}";
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Referer' => 'http://redforce.live/',
            ])->timeout(6)->get($url);

            if (!$response->successful()) {
                return response('Failed to load RedForce player page', 502);
            }

            $html = $response->body();
            
            // Regex to find primarySource variable value
            $pattern = '/var\s+primarySource\s*=\s*[\'"]([^\'"]+)[\'"]/s';

            if (preg_match($pattern, $html, $matches)) {
                $realM3u8Url = $matches[1];
                return redirect()->away($realM3u8Url);
            }

            return response('Stream source not found in page payload', 404);

        } catch (\Exception $e) {
            return response('Error loading RedForce stream: ' . $e->getMessage(), 500);
        }
    }
}

