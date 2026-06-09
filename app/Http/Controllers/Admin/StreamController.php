<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Stream;
use App\Models\StreamServer;
use Illuminate\Http\Request;

class StreamController extends Controller
{
    /**
     * Display a listing of the streams.
     */
    public function index(Request $request)
    {
        $query = Stream::with('categories')->orderBy('id', 'desc');

        if ($categoryId = $request->input('category_id')) {
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        if ($tab = $request->input('tab')) {
            if ($tab === 'events') {
                $query->where('show_in_events', true);
            } elseif ($tab === 'sports') {
                $query->where('show_in_sports', true);
            } elseif ($tab === 'tv') {
                $query->where('show_in_tv', true);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('team1_name', 'like', '%' . $search . '%')
                  ->orWhere('team2_name', 'like', '%' . $search . '%')
                  ->orWhere('sport_type', 'like', '%' . $search . '%');
            });
        }

        $streams = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('order')->get();
        $allStreamsForMerge = Stream::select('id', 'name')->orderBy('name')->get();

        return view('admin.streams.index', compact('streams', 'categories', 'allStreamsForMerge'));
    }

    /**
     * Show the form for creating a new stream.
     */
    public function create(Request $request)
    {
        $categories = Category::orderBy('order')->get();
        
        $prefilledServers = [];
        if ($fromIds = $request->input('from_ids')) {
            $streams = Stream::with('servers')->whereIn('id', $fromIds)->get();
            $serverOrder = 0;
            foreach ($streams as $stream) {
                foreach ($stream->servers as $server) {
                    $prefilledServers[] = [
                        'name' => $stream->name . ' - ' . $server->name,
                        'stream_type' => $server->stream_type,
                        'url' => $server->url,
                        'http_referer' => $server->http_referer,
                        'http_origin' => $server->http_origin,
                        'order' => $serverOrder++,
                    ];
                }
            }
        }

        return view('admin.streams.create', compact('categories', 'prefilledServers'));
    }

    /**
     * Store a newly created stream in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|url|max:500',
            'sport_type' => 'required|string|in:cricket,football,other',
            
            // Teams
            'team1_name' => 'nullable|string|max:255',
            'team1_logo' => 'nullable|url|max:500',
            'team2_name' => 'nullable|string|max:255',
            'team2_logo' => 'nullable|url|max:500',

            // Expiry
            'is_permanent' => 'nullable|boolean',
            'start_time' => 'required_without:is_permanent|nullable|date',
            'expire_time' => 'required_without:is_permanent|nullable|date|after:start_time',

            // Tabs
            'show_in_events' => 'nullable|boolean',
            'show_in_sports' => 'nullable|boolean',
            'show_in_tv' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',

            // Categories (Array)
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',

            // Servers (Arrays)
            'servers' => 'required|array|min:1',
            'servers.*.name' => 'required|string|max:255',
            'servers.*.stream_type' => 'required|string|in:iframe,m3u8',
            'servers.*.url' => 'required|string',
            'servers.*.http_referer' => 'nullable|string|max:1000',
            'servers.*.http_origin' => 'nullable|string|max:1000',
            'servers.*.order' => 'nullable|integer',
        ]);

        // Map checkboxes
        $streamData = [
            'name' => $data['name'],
            'logo' => $data['logo'],
            'sport_type' => $data['sport_type'],
            'team1_name' => $data['team1_name'],
            'team1_logo' => $data['team1_logo'],
            'team2_name' => $data['team2_name'],
            'team2_logo' => $data['team2_logo'],
            'is_permanent' => $request->has('is_permanent'),
            'start_time' => $request->has('is_permanent') ? null : $data['start_time'],
            'expire_time' => $request->has('is_permanent') ? null : $data['expire_time'],
            'show_in_events' => $request->has('show_in_events'),
            'show_in_sports' => $request->has('show_in_sports'),
            'show_in_tv' => $request->has('show_in_tv'),
            'is_active' => $request->has('is_active'),
        ];

        // Create Stream
        $stream = Stream::create($streamData);

        // Attach Categories
        if ($request->has('categories') && $stream->show_in_tv) {
            $stream->categories()->sync($data['categories']);
        }

        // Create Servers
        foreach ($data['servers'] as $server) {
            StreamServer::create([
                'stream_id' => $stream->id,
                'name' => $server['name'],
                'stream_type' => $server['stream_type'],
                'url' => $server['url'],
                'http_referer' => $server['http_referer'] ?? null,
                'http_origin' => $server['http_origin'] ?? null,
                'order' => $server['order'] ?? 0,
            ]);
        }

        return redirect()->route('admin.streams.index')->with('success', 'Stream created successfully.');
    }

    /**
     * Show the form for editing the specified stream.
     */
    public function edit(Stream $stream)
    {
        $categories = Category::orderBy('order')->get();
        $stream->load('servers');
        return view('admin.streams.edit', compact('stream', 'categories'));
    }

    /**
     * Update the specified stream in storage.
     */
    public function update(Request $request, Stream $stream)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|url|max:500',
            'sport_type' => 'required|string|in:cricket,football,other',
            
            // Teams
            'team1_name' => 'nullable|string|max:255',
            'team1_logo' => 'nullable|url|max:500',
            'team2_name' => 'nullable|string|max:255',
            'team2_logo' => 'nullable|url|max:500',

            // Expiry
            'is_permanent' => 'nullable|boolean',
            'start_time' => 'required_without:is_permanent|nullable|date',
            'expire_time' => 'required_without:is_permanent|nullable|date|after:start_time',

            // Tabs
            'show_in_events' => 'nullable|boolean',
            'show_in_sports' => 'nullable|boolean',
            'show_in_tv' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',

            // Categories (Array)
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',

            // Servers (Arrays)
            'servers' => 'required|array|min:1',
            'servers.*.name' => 'required|string|max:255',
            'servers.*.stream_type' => 'required|string|in:iframe,m3u8',
            'servers.*.url' => 'required|string',
            'servers.*.http_referer' => 'nullable|string|max:1000',
            'servers.*.http_origin' => 'nullable|string|max:1000',
            'servers.*.order' => 'nullable|integer',
        ]);

        // Map checkboxes
        $streamData = [
            'name' => $data['name'],
            'logo' => $data['logo'],
            'sport_type' => $data['sport_type'],
            'team1_name' => $data['team1_name'],
            'team1_logo' => $data['team1_logo'],
            'team2_name' => $data['team2_name'],
            'team2_logo' => $data['team2_logo'],
            'is_permanent' => $request->has('is_permanent'),
            'start_time' => $request->has('is_permanent') ? null : $data['start_time'],
            'expire_time' => $request->has('is_permanent') ? null : $data['expire_time'],
            'show_in_events' => $request->has('show_in_events'),
            'show_in_sports' => $request->has('show_in_sports'),
            'show_in_tv' => $request->has('show_in_tv'),
            'is_active' => $request->has('is_active'),
        ];

        // Update Stream
        $stream->update($streamData);

        // Sync Categories
        if ($request->has('categories') && $stream->show_in_tv) {
            $stream->categories()->sync($data['categories']);
        } else {
            $stream->categories()->detach();
        }

        // Recreate Servers: delete old ones, write new ones
        $stream->servers()->delete();
        foreach ($data['servers'] as $server) {
            StreamServer::create([
                'stream_id' => $stream->id,
                'name' => $server['name'],
                'stream_type' => $server['stream_type'],
                'url' => $server['url'],
                'http_referer' => $server['http_referer'] ?? null,
                'http_origin' => $server['http_origin'] ?? null,
                'order' => $server['order'] ?? 0,
            ]);
        }

        return redirect()->route('admin.streams.index')->with('success', 'Stream updated successfully.');
    }

    /**
     * Remove the specified stream from storage.
     */
    public function destroy(Stream $stream)
    {
        $stream->delete(); // Automatically cascades to category relations and stream servers
        return redirect()->route('admin.streams.index')->with('success', 'Stream deleted successfully.');
    }

    /**
     * Bulk delete selected streams.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!empty($ids)) {
            Stream::whereIn('id', $ids)->delete();
            return redirect()->route('admin.streams.index')->with('success', 'Selected streams deleted successfully.');
        }
        return redirect()->route('admin.streams.index')->with('error', 'No streams selected for deletion.');
    }

    /**
     * Merge selected streams into a new single stream and delete the originals.
     */
    public function merge(Request $request)
    {
        $ids = $request->input('ids', []);
        $newName = $request->input('new_name');

        if (empty($ids) || count($ids) < 2) {
            return redirect()->route('admin.streams.index')->with('error', 'At least two channels must be selected to merge.');
        }

        if (empty($newName)) {
            return redirect()->route('admin.streams.index')->with('error', 'A new channel name is required.');
        }

        // Fetch streams with servers and categories
        $streams = Stream::with(['servers', 'categories'])->whereIn('id', $ids)->get();

        if ($streams->isEmpty()) {
            return redirect()->route('admin.streams.index')->with('error', 'Selected channels could not be found.');
        }

        // Use the first stream as the base for logos, categories, sport type, etc.
        $base = $streams->first();

        // Create the new merged Stream
        $mergedStream = Stream::create([
            'name' => $newName,
            'logo' => $base->logo,
            'sport_type' => $base->sport_type,
            'team1_name' => $base->team1_name,
            'team1_logo' => $base->team1_logo,
            'team2_name' => $base->team2_name,
            'team2_logo' => $base->team2_logo,
            'is_permanent' => $base->is_permanent,
            'start_time' => $base->start_time,
            'expire_time' => $base->expire_time,
            'show_in_events' => $base->show_in_events,
            'show_in_sports' => $base->show_in_sports,
            'show_in_tv' => $base->show_in_tv,
            'is_active' => true, // Active by default
        ]);

        // Merge categories
        $categoryIds = $streams->flatMap->categories->pluck('id')->unique()->toArray();
        if (!empty($categoryIds)) {
            $mergedStream->categories()->sync($categoryIds);
        }

        // Copy servers with prefixing
        $serverOrder = 0;
        foreach ($streams as $stream) {
            foreach ($stream->servers as $server) {
                // Prefix server name with parent stream name to preserve context
                $serverName = $stream->name . ' - ' . $server->name;
                
                StreamServer::create([
                    'stream_id' => $mergedStream->id,
                    'name' => $serverName,
                    'stream_type' => $server->stream_type,
                    'url' => $server->url,
                    'http_referer' => $server->http_referer,
                    'http_origin' => $server->http_origin,
                    'order' => $serverOrder++,
                ]);
            }
        }

        // Delete the original streams conditionally (cascades and deletes original servers and pivots)
        if ($request->boolean('delete_original', false)) {
            Stream::whereIn('id', $ids)->delete();
            $message = "Successfully merged " . $streams->count() . " channels into '{$newName}' and deleted the source channels.";
        } else {
            $message = "Successfully combined " . $streams->count() . " channels into a new channel '{$newName}' (original channels kept).";
        }

        return redirect()->route('admin.streams.index')->with('success', $message);
    }
}
