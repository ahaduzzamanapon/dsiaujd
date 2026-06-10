<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoBanner;
use App\Models\Stream;
use Illuminate\Http\Request;

class PromoBannerController extends Controller
{
    /**
     * Display a listing of the promotional banners.
     */
    public function index()
    {
        $banners = PromoBanner::orderBy('order')->orderBy('id', 'desc')->get();
        return view('admin.promo_banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new promotional banner.
     */
    public function create()
    {
        $streams = Stream::where('is_active', true)->orderBy('name')->get();
        return view('admin.promo_banners.create', compact('streams'));
    }

    /**
     * Store a newly created promotional banner in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:1000',
            'logo' => 'nullable|url|max:1000',
            'countdown' => 'nullable|date_format:Y-m-d\TH:i|max:50',
            'btn_text' => 'nullable|string|max:255',
            'btn_link' => 'nullable|url|max:1000',
            'stream1_id' => 'nullable|integer|exists:streams,id',
            'stream2_id' => 'nullable|integer|exists:streams,id',
            'stream3_id' => 'nullable|integer|exists:streams,id',
            'order' => 'required|integer',
        ]);

        PromoBanner::create([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'],
            'logo' => $data['logo'],
            'countdown' => $data['countdown'] ? str_replace('T', ' ', $data['countdown']) . ':00' : null,
            'btn_text' => $data['btn_text'],
            'btn_link' => $data['btn_link'],
            'stream1_id' => $data['stream1_id'],
            'stream2_id' => $data['stream2_id'],
            'stream3_id' => $data['stream3_id'],
            'is_active' => $request->has('is_active'),
            'order' => $data['order'],
        ]);

        return redirect()->route('admin.promo-banners.index')->with('success', 'Promo banner created successfully.');
    }

    /**
     * Show the form for editing the specified promotional banner.
     */
    public function edit(PromoBanner $promoBanner)
    {
        $streams = Stream::where('is_active', true)->orderBy('name')->get();
        return view('admin.promo_banners.edit', compact('promoBanner', 'streams'));
    }

    /**
     * Update the specified promotional banner in storage.
     */
    public function update(Request $request, PromoBanner $promoBanner)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string|max:1000',
            'logo' => 'nullable|url|max:1000',
            'countdown' => 'nullable|date_format:Y-m-d\TH:i|max:50',
            'btn_text' => 'nullable|string|max:255',
            'btn_link' => 'nullable|url|max:1000',
            'stream1_id' => 'nullable|integer|exists:streams,id',
            'stream2_id' => 'nullable|integer|exists:streams,id',
            'stream3_id' => 'nullable|integer|exists:streams,id',
            'order' => 'required|integer',
        ]);

        $promoBanner->update([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'],
            'logo' => $data['logo'],
            'countdown' => $data['countdown'] ? str_replace('T', ' ', $data['countdown']) . ':00' : null,
            'btn_text' => $data['btn_text'],
            'btn_link' => $data['btn_link'],
            'stream1_id' => $data['stream1_id'],
            'stream2_id' => $data['stream2_id'],
            'stream3_id' => $data['stream3_id'],
            'is_active' => $request->has('is_active'),
            'order' => $data['order'],
        ]);

        return redirect()->route('admin.promo-banners.index')->with('success', 'Promo banner updated successfully.');
    }

    /**
     * Remove the specified promotional banner from storage.
     */
    public function destroy(PromoBanner $promoBanner)
    {
        $promoBanner->delete();
        return redirect()->route('admin.promo-banners.index')->with('success', 'Promo banner deleted successfully.');
    }
}
