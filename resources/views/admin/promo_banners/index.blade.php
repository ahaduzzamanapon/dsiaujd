@extends('layouts.admin')

@section('page-title', 'Promo Banners Management')
@section('page-subtitle', 'Manage horizontal sliding promo banner cards on the mobile app home screen')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-xl font-bold text-white">Promo Banners List</h2>
    <a href="{{ route('admin.promo-banners.create') }}" 
       class="py-2.5 px-4 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 active:scale-[0.98] transition-all shadow-lg shadow-cyan-500/15 text-sm">
        + Create New Banner
    </a>
</div>

@if(session('success'))
    <div class="bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 p-4 rounded-xl mb-6 text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="glass-panel rounded-3xl shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-800 text-gray-400 text-xs uppercase tracking-wider bg-gray-950/30">
                    <th class="p-4 font-semibold">Title</th>
                    <th class="p-4 font-semibold">Subtitle</th>
                    <th class="p-4 font-semibold">Target Countdown</th>
                    <th class="p-4 font-semibold">Order</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/50 text-sm text-gray-300">
                @forelse($banners as $banner)
                    <tr class="hover:bg-gray-800/10 transition-colors">
                        <td class="p-4 font-semibold text-white flex items-center space-x-3">
                            @if($banner->logo)
                                <img src="{{ $banner->logo }}" alt="Logo" class="w-8 h-8 rounded-lg object-contain bg-gray-950">
                            @else
                                <div class="w-8 h-8 rounded-lg bg-gray-950 flex items-center justify-center text-cyan-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            <span>{{ $banner->title }}</span>
                        </td>
                        <td class="p-4 max-w-xs truncate text-xs">{{ $banner->subtitle }}</td>
                        <td class="p-4 text-xs font-mono">
                            {{ $banner->countdown ? date('Y-m-d H:i', strtotime($banner->countdown)) : 'No Timer' }}
                        </td>
                        <td class="p-4 font-mono text-xs">{{ $banner->order }}</td>
                        <td class="p-4">
                            @if($banner->is_active)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold tracking-wide bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                    ACTIVE
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold tracking-wide bg-gray-800/40 text-gray-500 border border-gray-800">
                                    INACTIVE
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-right space-x-2">
                            <a href="{{ route('admin.promo-banners.edit', $banner->id) }}" 
                               class="inline-block py-1.5 px-3 rounded-lg bg-gray-800 hover:bg-gray-700 text-white font-semibold text-xs transition-colors">
                                Edit
                            </a>
                            <form action="{{ route('admin.promo-banners.destroy', $banner->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this promo banner?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="py-1.5 px-3 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 font-semibold text-xs border border-red-500/20 transition-colors">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500 text-sm">
                            No promo banners created yet. Click "+ Create New Banner" above to add one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
