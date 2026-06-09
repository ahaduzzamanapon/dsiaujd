@extends('layouts.admin')

@section('page-title', 'TV Categories')
@section('page-subtitle', 'Manage Live TV category folders and sorting order')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Categories List Table -->
    <div class="lg:col-span-2 glass-panel p-6 rounded-3xl shadow-xl">
        <h3 class="text-xl font-bold text-white mb-6">Existing Categories</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="bg-gray-950/40 text-gray-400 uppercase text-xs font-semibold border-b border-gray-800">
                    <tr>
                        <th class="p-4">Logo</th>
                        <th class="p-4">Category Name</th>
                        <th class="p-4">Sort Order</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-900/20 transition-colors">
                            <td class="p-4">
                                @if($category->logo)
                                    <img src="{{ $category->logo }}" alt="" class="w-10 h-10 object-contain rounded-lg bg-gray-900/60 p-1 border border-gray-800">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-800 flex items-center justify-center text-gray-500 text-xs font-bold uppercase">
                                        {{ substr($category->name, 0, 2) }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-4 font-medium text-white">{{ $category->name }}</td>
                            <td class="p-4 text-gray-400">{{ $category->order }}</td>
                            <td class="p-4 text-right space-x-3">
                                <!-- Inline Edit Trigger -->
                                <button onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ $category->logo }}', {{ $category->order }})" 
                                        class="text-cyan-400 hover:text-cyan-300 font-semibold text-xs">
                                    Edit
                                </button>
                                <!-- Delete Form -->
                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure? All TV channels inside this category will be removed from it.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 font-semibold text-xs">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-500">No categories found. Create one on the right.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Category Sidebar Form -->
    <div class="glass-panel p-6 rounded-3xl shadow-xl h-fit">
        <h3 class="text-xl font-bold text-white mb-6">Create New Category</h3>
        
        <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Category Name</label>
                <input id="name" type="text" name="name" required
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white placeholder-gray-600 rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                       placeholder="e.g. Sony Channels">
            </div>

            <div>
                <label for="logo" class="block text-sm font-medium text-gray-300 mb-2">Logo URL (Optional)</label>
                <input id="logo" type="url" name="logo"
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white placeholder-gray-600 rounded-xl px-4 py-2.5 outline-none transition-all text-sm"
                       placeholder="https://example.com/logo.png">
            </div>

            <div>
                <label for="order" class="block text-sm font-medium text-gray-300 mb-2">Sort Order</label>
                <input id="order" type="number" name="order" value="0"
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white placeholder-gray-600 rounded-xl px-4 py-2.5 outline-none transition-all text-sm">
            </div>

            <button type="submit" 
                    class="w-full py-2.5 px-4 rounded-xl font-semibold text-white bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 active:scale-[0.98] transition-all shadow-md text-sm">
                Add Category
            </button>
        </form>
    </div>
</div>

<!-- Edit Category Modal Dialog -->
<div id="edit-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="w-full max-w-md glass-panel rounded-3xl p-6 shadow-2xl relative">
        <h3 class="text-xl font-bold text-white mb-6">Edit Category</h3>
        
        <form id="edit-form" method="POST" action="" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="edit-name" class="block text-sm font-medium text-gray-300 mb-2">Category Name</label>
                <input id="edit-name" type="text" name="name" required
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white placeholder-gray-600 rounded-xl px-4 py-2.5 outline-none transition-all text-sm">
            </div>

            <div>
                <label for="edit-logo" class="block text-sm font-medium text-gray-300 mb-2">Logo URL (Optional)</label>
                <input id="edit-logo" type="url" name="logo"
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white placeholder-gray-600 rounded-xl px-4 py-2.5 outline-none transition-all text-sm">
            </div>

            <div>
                <label for="edit-order" class="block text-sm font-medium text-gray-300 mb-2">Sort Order</label>
                <input id="edit-order" type="number" name="order"
                       class="w-full bg-gray-950/50 border border-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-white placeholder-gray-600 rounded-xl px-4 py-2.5 outline-none transition-all text-sm">
            </div>

            <div class="flex space-x-3 pt-2">
                <button type="button" onclick="closeEditModal()" 
                        class="flex-1 py-2.5 px-4 rounded-xl font-semibold text-gray-300 bg-gray-800 hover:bg-gray-700 transition-all text-sm">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 py-2.5 px-4 rounded-xl font-semibold text-white bg-cyan-500 hover:bg-cyan-400 active:scale-[0.98] transition-all text-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openEditModal(id, name, logo, order) {
        const modal = document.getElementById('edit-modal');
        const form = document.getElementById('edit-form');
        
        // Set Action URL
        form.action = `/admin/categories/${id}`;
        
        // Set Field Values
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-logo').value = logo;
        document.getElementById('edit-order').value = order;
        
        // Show Modal
        modal.classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }
</script>
@endpush
@endsection
