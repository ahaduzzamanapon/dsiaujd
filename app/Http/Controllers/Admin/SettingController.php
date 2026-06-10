<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Show the settings edit form.
     */
    public function edit()
    {
        $settings = AppSetting::firstOrCreate(['id' => 1], [
            'app_version' => '1.0.0',
            'is_mandatory_update' => false,
            'update_message' => 'Please update the app to the latest version.',
            'update_url' => 'https://play.google.com/store',
            'welcome_message' => '• Enjoy live events and TV streaming with the latest updates! •',
        ]);
        
        return view('admin.settings.edit', compact('settings'));
    }

    /**
     * Update the settings in storage.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'app_version' => 'required|string|max:50',
            'is_mandatory_update' => 'nullable|boolean',
            'update_message' => 'required|string|max:1000',
            'update_url' => 'required|url|max:500',
            'welcome_message' => 'required|string|max:2000',
        ]);

        $settings = AppSetting::firstOrCreate(['id' => 1]);
        $settings->update([
            'app_version' => $data['app_version'],
            'is_mandatory_update' => $request->has('is_mandatory_update'),
            'update_message' => $data['update_message'],
            'update_url' => $data['update_url'],
            'welcome_message' => $data['welcome_message'],
        ]);

        return redirect()->route('admin.settings.edit')->with('success', 'Settings updated successfully.');
    }

    /**
     * Show the promo alert settings form.
     */
    public function editPromo()
    {
        $settings = AppSetting::firstOrCreate(['id' => 1], [
            'app_version' => '1.0.0',
            'is_mandatory_update' => false,
            'update_message' => 'Please update the app to the latest version.',
            'update_url' => 'https://play.google.com/store',
            'welcome_message' => '• Enjoy live events and TV streaming with the latest updates! •',
            'promo_show_alert' => false,
            'promo_title' => 'Join Telegram',
            'promo_message' => 'Get the latest channel updates, schedule requests, and chat with community on Telegram!',
            'promo_link' => 'https://t.me/AllTV',
            'promo_close_text' => 'Close',
            'promo_go_text' => 'Join Now',
        ]);
        
        return view('admin.settings.promo', compact('settings'));
    }

    /**
     * Update the promo alert settings.
     */
    public function updatePromo(Request $request)
    {
        $data = $request->validate([
            'promo_title' => 'required|string|max:255',
            'promo_message' => 'nullable|string|max:2000',
            'promo_link' => 'required|url|max:1000',
            'promo_close_text' => 'required|string|max:50',
            'promo_go_text' => 'required|string|max:50',
        ]);

        $settings = AppSetting::firstOrCreate(['id' => 1]);
        $settings->update([
            'promo_show_alert' => $request->has('promo_show_alert'),
            'promo_title' => $data['promo_title'],
            'promo_message' => $data['promo_message'],
            'promo_link' => $data['promo_link'],
            'promo_close_text' => $data['promo_close_text'],
            'promo_go_text' => $data['promo_go_text'],
        ]);

        return redirect()->route('admin.settings.promo.edit')->with('success', 'Promotional alert settings updated successfully.');
    }

    /**
     * Show the promotional banner settings form.
     */
    public function editBanner()
    {
        $settings = AppSetting::firstOrCreate(['id' => 1], [
            'app_version' => '1.0.0',
            'is_mandatory_update' => false,
            'update_message' => 'Please update the app to the latest version.',
            'update_url' => 'https://play.google.com/store',
            'welcome_message' => '• Enjoy live events and TV streaming with the latest updates! •',
        ]);
        
        $streams = \App\Models\Stream::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.settings.banner', compact('settings', 'streams'));
    }

    /**
     * Update the promotional banner settings.
     */
    public function updateBanner(Request $request)
    {
        $data = $request->validate([
            'promo_banner_title' => 'required|string|max:255',
            'promo_banner_subtitle' => 'required|string|max:1000',
            'promo_banner_logo' => 'nullable|url|max:1000',
            'promo_banner_countdown' => 'nullable|date_format:Y-m-d\TH:i|max:50',
            'promo_banner_btn_text' => 'nullable|string|max:255',
            'promo_banner_btn_link' => 'nullable|url|max:1000',
            'promo_banner_stream1_id' => 'nullable|integer',
            'promo_banner_stream2_id' => 'nullable|integer',
            'promo_banner_stream3_id' => 'nullable|integer',
        ]);

        $settings = AppSetting::firstOrCreate(['id' => 1]);
        $settings->update([
            'promo_banner_enabled' => $request->has('promo_banner_enabled'),
            'promo_banner_title' => $data['promo_banner_title'],
            'promo_banner_subtitle' => $data['promo_banner_subtitle'],
            'promo_banner_logo' => $data['promo_banner_logo'],
            'promo_banner_countdown' => $data['promo_banner_countdown'] ? str_replace('T', ' ', $data['promo_banner_countdown']) . ':00' : null,
            'promo_banner_btn_text' => $data['promo_banner_btn_text'],
            'promo_banner_btn_link' => $data['promo_banner_btn_link'],
            'promo_banner_stream1_id' => $data['promo_banner_stream1_id'],
            'promo_banner_stream2_id' => $data['promo_banner_stream2_id'],
            'promo_banner_stream3_id' => $data['promo_banner_stream3_id'],
        ]);

        return redirect()->route('admin.settings.banner.edit')->with('success', 'Promotional banner settings updated successfully.');
    }
}
