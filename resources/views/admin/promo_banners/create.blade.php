@extends('layouts.admin')

@section('page-title', 'Create Promo Banner')
@section('page-subtitle', 'Configure a premium top event banner with live countdown timers and channel play shortcuts')

@section('content')
<div class="max-w-2xl glass-panel p-8 rounded-3xl shadow-xl">
    <form method="POST" action="{{ route('admin.promo-banners.store') }}" class="space-y-6">
        @csrf

        <!-- Toggle Status & Order -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-white border-b border-gray-800 pb-2">Configuration</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="flex items-center space-x-2.5 cursor-pointer bg-gray-950/40 border border-gray-800 hover:border-gray-700 px-4 py-2.5 rounded-xl">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-800 bg-gray-950 text-cyan-500 focus:ring-cyan-500 w-4 h-4">
                    <span class="text-sm font-semibold text-gray-300">Set Active (Display on App)</span>
                </label>

                <div>
                    <label for="order" class="block text-sm font-medium text-gray-300 mb-2">Display Order</label>
                    <input id="order" type="number" name="order" value="{{ old('order', 0) }}" required
                           class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm">
                </div>
            </div>
        </div>

        <!-- Banner Content Details -->
        <div class="space-y-4 pt-4 border-t border-gray-800">
            <h3 class="text-lg font-bold text-white pb-1">Banner Content</h3>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Event Title</label>
                <input id="title" type="text" name="title" value="{{ old('title') }}" required
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                       placeholder="e.g. FIFA WORLD CUP 2026">
            </div>

            <div>
                <label for="subtitle" class="block text-sm font-medium text-gray-300 mb-2">Event Subtitle / Bengali Notice</label>
                <input id="subtitle" type="text" name="subtitle" value="{{ old('subtitle') }}" required
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                       placeholder="e.g. 🏆 ⚽ ফিফা বিশ্বকাপ ২০২৬ সরাসরি দেখুন">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-300 mb-2">Event Logo Image URL</label>
                    <input id="logo" type="url" name="logo" value="{{ old('logo') }}"
                           class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                           placeholder="https://example.com/logo.png">
                </div>

                <div>
                    <label for="countdown" class="block text-sm font-medium text-gray-300 mb-2">Countdown Target Date/Time</label>
                    <input id="countdown" type="datetime-local" name="countdown" value="{{ old('countdown') }}"
                           class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm">
                </div>
            </div>
        </div>

        <!-- Action Button Settings -->
        <div class="space-y-4 pt-4 border-t border-gray-800">
            <h3 class="text-lg font-bold text-white pb-1">Action Button</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="btn_text" class="block text-sm font-medium text-gray-300 mb-2">Button Text</label>
                    <input id="btn_text" type="text" name="btn_text" value="{{ old('btn_text') }}"
                           class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                           placeholder="e.g. Watch Live on Web">
                </div>

                <div>
                    <label for="btn_link" class="block text-sm font-medium text-gray-300 mb-2">Button Link URL</label>
                    <input id="btn_link" type="url" name="btn_link" value="{{ old('btn_link') }}"
                           class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                           placeholder="https://example.com">
                </div>
            </div>
        </div>

        <!-- Linked Quick-Play Channels -->
        <div class="space-y-4 pt-4 border-t border-gray-800">
            <h3 class="text-lg font-bold text-white pb-1">Quick-Play Channels Shortcuts</h3>
            <p class="text-xs text-gray-400">Select up to 3 active streams to show as circular quick-play buttons on the event banner.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="stream1_id" class="block text-xs font-semibold text-gray-400 mb-1">Shortcut Channel 1</label>
                    <select id="stream1_id" name="stream1_id" class="w-full bg-gray-950/60 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-3 py-2 outline-none text-xs transition-all">
                        <option value="">-- None Selected --</option>
                        @foreach($streams as $stream)
                            <option value="{{ $stream->id }}" {{ old('stream1_id') == $stream->id ? 'selected' : '' }}>
                                {{ $stream->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="stream2_id" class="block text-xs font-semibold text-gray-400 mb-1">Shortcut Channel 2</label>
                    <select id="stream2_id" name="stream2_id" class="w-full bg-gray-950/60 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-3 py-2 outline-none text-xs transition-all">
                        <option value="">-- None Selected --</option>
                        @foreach($streams as $stream)
                            <option value="{{ $stream->id }}" {{ old('stream2_id') == $stream->id ? 'selected' : '' }}>
                                {{ $stream->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="stream3_id" class="block text-xs font-semibold text-gray-400 mb-1">Shortcut Channel 3</label>
                    <select id="stream3_id" name="stream3_id" class="w-full bg-gray-950/60 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white rounded-xl px-3 py-2 outline-none text-xs transition-all">
                        <option value="">-- None Selected --</option>
                        @foreach($streams as $stream)
                            <option value="{{ $stream->id }}" {{ old('stream3_id') == $stream->id ? 'selected' : '' }}>
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
                Create Promo Banner
            </button>
        </div>
    </form>
</div>
@endsection
