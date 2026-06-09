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
}
