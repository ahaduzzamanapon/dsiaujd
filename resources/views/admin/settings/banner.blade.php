@extends('layouts.admin')

@section('page-title', 'Promotional Banner Configuration')
@section('page-subtitle', 'Configure a premium top event banner with live countdown timers and channel shortcut play buttons')

@section('content')
<div class="max-w-2xl glass-panel p-8 rounded-3xl shadow-xl">
    <form method="POST" action="{{ route('admin.settings.banner.update') }}" class="space-y-6">
        @csrf

        <!-- Toggle Switch -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-white border-b border-gray-800 pb-2">Status</h3>
            
            <label class="flex items-center space-x-2.5 cursor-pointer bg-gray-950/40 border border-gray-800 hover:border-gray-700 px-4 py-2.5 rounded-xl w-full">
                <input type="checkbox" name="promo_banner_enabled" value="1" {{ old('promo_banner_enabled', $settings->promo_banner_enabled) ? 'checked' : '' }} class="rounded border-gray-800 bg-gray-950 text-cyan-500 focus:ring-cyan-500 w-4 h-4">
                <span class="text-sm font-semibold text-gray-300">Display Promo Banner on Home Tab</span>
            </label>
            <p class="text-[10px] text-gray-500">If enabled, a high-fidelity visual banner will appear at the top of the Live Events tab, displaying the countdown timer and channel shortcut icons.</p>
        </div>

        <!-- Banner Content Details -->
        <div class="space-y-4 pt-4 border-t border-gray-800">
            <h3 class="text-lg font-bold text-white pb-1">Banner Content</h3>

            <div>
                <label for="promo_banner_title" class="block text-sm font-medium text-gray-300 mb-2">Event Title</label>
                <input id="promo_banner_title" type="text" name="promo_banner_title" value="{{ old('promo_banner_title', $settings->promo_banner_title) }}" required
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                       placeholder="e.g. FIFA WORLD CUP 2026">
            </div>

            <div>
                <label for="promo_banner_subtitle" class="block text-sm font-medium text-gray-300 mb-2">Event Subtitle / Bengali Notice</label>
                <input id="promo_banner_subtitle" type="text" name="promo_banner_subtitle" value="{{ old('promo_banner_subtitle', $settings->promo_banner_subtitle) }}" required
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                       placeholder="e.g. 🏆 ⚽ ফিফা বিশ্বকাপ ২০২৬ সরাসরি দেখুন">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="promo_banner_logo" class="block text-sm font-medium text-gray-300 mb-2">Event Logo Image URL</label>
                    <input id="promo_banner_logo" type="url" name="promo_banner_logo" value="{{ old('promo_banner_logo', $settings->promo_banner_logo) }}"
                           class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                           placeholder="https://example.com/logo.png">
                </div>

                <div>
                    <label for="promo_banner_countdown" class="block text-sm font-medium text-gray-300 mb-2">Countdown Target Date/Time</label>
                    <input id="promo_banner_countdown" type="datetime-local" name="promo_banner_countdown" 
                           value="{{ old('promo_banner_countdown', $settings->promo_banner_countdown ? date('Y-m-d\TH:i', strtotime($settings->promo_banner_countdown)) : '') }}"
                           class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm">
                </div>
            </div>
        </div>

        <!-- Action Button Settings -->
        <div class="space-y-4 pt-4 border-t border-gray-800">
            <h3 class="text-lg font-bold text-white pb-1">Action Button</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="promo_banner_btn_text" class="block text-sm font-medium text-gray-300 mb-2">Button Text</label>
                    <input id="promo_banner_btn_text" type="text" name="promo_banner_btn_text" value="{{ old('promo_banner_btn_text', $settings->promo_banner_btn_text) }}"
                           class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                           placeholder="e.g. Watch Live on Web">
                </div>

                <div>
                    <label for="promo_banner_btn_link" class="block text-sm font-medium text-gray-300 mb-2">Button Link URL</label>
                    <input id="promo_banner_btn_link" type="url" name="promo_banner_btn_link" value="{{ old('promo_banner_btn_link', $settings->promo_banner_btn_link) }}"
                           class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                           placeholder="https://bdixtv24.com">
                </div>
            </div>
        </div>

        <!-- Linked Quick-Play Channels -->
        <div class="space-y-4 pt-4 border-t border-gray-800">
            <h3 class="text-lg font-bold text-white pb-1">Quick-Play Channels Shortcuts</h3>
            <p class="text-xs text-gray-400">Select up to 3 active streams to show as circular quick-play buttons on the event banner.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="promo_banner_stream1_id" class="block text-xs font-semibold text-gray-400 mb-1">Shortcut Channel 1</label>
                    <select id="promo_banner_stream1_id" name="promo_banner_stream1_id" class="w-full bg-gray-950/60 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-3 py-2 outline-none text-xs transition-all">
                        <option value="">-- None Selected --</option>
                        @foreach($streams as $stream)
                            <option value="{{ $stream->id }}" {{ old('promo_banner_stream1_id', $settings->promo_banner_stream1_id) == $stream->id ? 'selected' : '' }}>
                                {{ $stream->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="promo_banner_stream2_id" class="block text-xs font-semibold text-gray-400 mb-1">Shortcut Channel 2</label>
                    <select id="promo_banner_stream2_id" name="promo_banner_stream2_id" class="w-full bg-gray-950/60 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-3 py-2 outline-none text-xs transition-all">
                        <option value="">-- None Selected --</option>
                        @foreach($streams as $stream)
                            <option value="{{ $stream->id }}" {{ old('promo_banner_stream2_id', $settings->promo_banner_stream2_id) == $stream->id ? 'selected' : '' }}>
                                {{ $stream->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="promo_banner_stream3_id" class="block text-xs font-semibold text-gray-400 mb-1">Shortcut Channel 3</label>
                    <select id="promo_banner_stream3_id" name="promo_banner_stream3_id" class="w-full bg-gray-950/60 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-3 py-2 outline-none text-xs transition-all">
                        <option value="">-- None Selected --</option>
                        @foreach($streams as $stream)
                            <option value="{{ $stream->id }}" {{ old('promo_banner_stream3_id', $settings->promo_banner_stream3_id) == $stream->id ? 'selected' : '' }}>
                                {{ $stream->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Submit button -->
        <div class="pt-4 border-t border-gray-800">
            <button type="submit" 
                    class="w-full py-3 px-4 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 active:scale-[0.98] transition-all shadow-lg shadow-cyan-500/15 text-sm">
                Save Promotional Banner Settings
            </button>
        </div>
    </form>
</div>
@endsection
