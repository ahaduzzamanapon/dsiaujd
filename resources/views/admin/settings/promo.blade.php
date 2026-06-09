@extends('layouts.admin')

@section('page-title', 'Promotional Alert Configuration')
@section('page-subtitle', 'Configure custom modal popup notifications for mobile app users')

@section('content')
<div class="max-w-2xl glass-panel p-8 rounded-3xl shadow-xl">
    <form method="POST" action="{{ route('admin.settings.promo.update') }}" class="space-y-6">
        @csrf

        <!-- Toggle Switch -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-white border-b border-gray-800 pb-2">Status</h3>
            
            <label class="flex items-center space-x-2.5 cursor-pointer bg-gray-950/40 border border-gray-800 hover:border-gray-700 px-4 py-2.5 rounded-xl w-full">
                <input type="checkbox" name="promo_show_alert" value="1" {{ old('promo_show_alert', $settings->promo_show_alert) ? 'checked' : '' }} class="rounded border-gray-800 bg-gray-950 text-cyan-500 focus:ring-cyan-500 w-4 h-4">
                <span class="text-sm font-semibold text-gray-300">Display Alert Modal on App Open</span>
            </label>
            <p class="text-[10px] text-gray-500">If enabled, a popup modal will appear immediately when the app is launched, prompting users to take action (e.g. join Telegram).</p>
        </div>

        <!-- Alert Content Details -->
        <div class="space-y-4 pt-4 border-t border-gray-800">
            <h3 class="text-lg font-bold text-white pb-1">Alert Content</h3>

            <div>
                <label for="promo_title" class="block text-sm font-medium text-gray-300 mb-2">Alert Title</label>
                <input id="promo_title" type="text" name="promo_title" value="{{ old('promo_title', $settings->promo_title) }}" required
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                       placeholder="e.g. Join Telegram Channel">
            </div>

            <div>
                <label for="promo_message" class="block text-sm font-medium text-gray-300 mb-2">Alert Description Message</label>
                <textarea id="promo_message" name="promo_message" rows="3" required
                          class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                          placeholder="e.g. Get live matches notifications, request new BDIX TV channels, and chat with other members instantly on Telegram!">{{ old('promo_message', $settings->promo_message) }}</textarea>
            </div>

            <div>
                <label for="promo_link" class="block text-sm font-medium text-gray-300 mb-2">Target Action Link (URL)</label>
                <input id="promo_link" type="url" name="promo_link" value="{{ old('promo_link', $settings->promo_link) }}" required
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                       placeholder="https://t.me/AllTV">
            </div>
        </div>

        <!-- Button Customization -->
        <div class="space-y-4 pt-4 border-t border-gray-800">
            <h3 class="text-lg font-bold text-white pb-1">Button Configuration</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="promo_close_text" class="block text-sm font-medium text-gray-300 mb-2">Close Button Text</label>
                    <input id="promo_close_text" type="text" name="promo_close_text" value="{{ old('promo_close_text', $settings->promo_close_text) }}" required
                           class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                           placeholder="e.g. Close">
                </div>
                
                <div>
                    <label for="promo_go_text" class="block text-sm font-medium text-gray-300 mb-2">Redirect Button Text</label>
                    <input id="promo_go_text" type="text" name="promo_go_text" value="{{ old('promo_go_text', $settings->promo_go_text) }}" required
                           class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                           placeholder="e.g. Join Now">
                </div>
            </div>
        </div>

        <!-- Submit button -->
        <div class="pt-4 border-t border-gray-800">
            <button type="submit" 
                    class="w-full py-3 px-4 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 active:scale-[0.98] transition-all shadow-lg shadow-cyan-500/15 text-sm">
                Save Promotional Alert Settings
            </button>
        </div>
    </form>
</div>
@endsection
