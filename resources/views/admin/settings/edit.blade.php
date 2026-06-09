@extends('layouts.admin')

@section('page-title', 'App Settings & Notice')
@section('page-subtitle', 'Manage scrolling notice banner, update alerts, and version configurations')

@section('content')
<div class="max-w-2xl glass-panel p-8 rounded-3xl shadow-xl">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf

        <!-- scrolling banner welcome text (NOTICE) -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-white border-b border-gray-800 pb-2">Marquee Ticker Notice Configuration</h3>

            <div>
                <label for="welcome_message" class="block text-sm font-medium text-gray-300 mb-2">Scrolling Marquee Notice Message</label>
                <textarea id="welcome_message" name="welcome_message" rows="3" required
                          class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                          placeholder="Notice ticker text running at the top of the app screens...">{{ old('welcome_message', $settings->welcome_message) }}</textarea>
                <p class="text-[10px] text-gray-500 mt-1">Tip: Wrap the text with bullets like '• Enjoy live streaming with the latest updates •' for a better visual look.</p>
            </div>
        </div>

        <!-- Versioning Settings -->
        <div class="space-y-4 pt-4 border-t border-gray-800">
            <h3 class="text-lg font-bold text-white pb-1">Version Configs</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="app_version" class="block text-sm font-medium text-gray-300 mb-2">Latest App Version</label>
                    <input id="app_version" type="text" name="app_version" value="{{ old('app_version', $settings->app_version) }}" required
                           class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                           placeholder="e.g. 1.0.0">
                </div>
                
                <div class="flex items-end pb-1.5">
                    <label class="flex items-center space-x-2.5 cursor-pointer bg-gray-950/40 border border-gray-800 hover:border-gray-700 px-4 py-2.5 rounded-xl w-full">
                        <input type="checkbox" name="is_mandatory_update" value="1" {{ old('is_mandatory_update', $settings->is_mandatory_update) ? 'checked' : '' }} class="rounded border-gray-800 bg-gray-950 text-cyan-500 focus:ring-cyan-500">
                        <span class="text-sm text-gray-300">Enforce Mandatory Update</span>
                    </label>
                </div>
            </div>
            
            <p class="text-[10px] text-gray-500">If Mandatory Update is checked, users with older app versions will be blocked until they update.</p>
        </div>

        <!-- Update details -->
        <div class="space-y-4 pt-4 border-t border-gray-800">
            <h3 class="text-lg font-bold text-white pb-1">Update Alert Details</h3>

            <div>
                <label for="update_url" class="block text-sm font-medium text-gray-300 mb-2">Download / Store URL</label>
                <input id="update_url" type="url" name="update_url" value="{{ old('update_url', $settings->update_url) }}" required
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                       placeholder="https://play.google.com/store/apps/details?id=...">
            </div>

            <div>
                <label for="update_message" class="block text-sm font-medium text-gray-300 mb-2">Update Alert Message</label>
                <textarea id="update_message" name="update_message" rows="3" required
                          class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                          placeholder="Explain what's new in this version...">{{ old('update_message', $settings->update_message) }}</textarea>
            </div>
        </div>

        <!-- Submit button -->
        <div class="pt-4 border-t border-gray-800">
            <button type="submit" 
                    class="w-full py-3 px-4 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 active:scale-[0.98] transition-all shadow-lg shadow-cyan-500/15 text-sm">
                Save Settings & Notice
            </button>
        </div>
    </form>
</div>
@endsection
