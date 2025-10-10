@extends('layouts.app')

@section('header')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Product Management</h1>
        <button onclick="toggleDarkMode()" class="btn btn-ghost">
            <span id="theme-icon">🌙</span>
        </button>
    </div>
@endsection

@section('content')
<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Form Section -->
        <div class="card bg-base-100 shadow-xl mb-8">
            <div class="card-body">
                <h2 class="card-title text-lg text-gray-800 dark:text-white">
                    {{ $productId ? 'Edit Product' : 'Add New Product' }}
                </h2>
                
                <form wire:submit="save">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text text-gray-700 dark:text-gray-300">Product Name</span>
                            </label>
                            <input type="text" 
                                   wire:model="name" 
                                   class="input input-bordered" 
                                   placeholder="Product Name" 
                                   required />
                            @error('name') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text text-gray-700 dark:text-gray-300">Price (Rp)</span>
                            </label>
                            <input type="number" 
                                   wire:model="price" 
                                   step="0.01"
                                   class="input input-bordered" 
                                   placeholder="0.00" 
                                   required />
                            @error('price') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text text-gray-700 dark:text-gray-300">Category</span>
                            </label>
                            <input type="text" 
                                   wire:model="category" 
                                   class="input input-bordered" 
                                   placeholder="Category" />
                            @error('category') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text text-gray-700 dark:text-gray-300">Stock Quantity</span>
                            </label>
                            <input type="number" 
                                   wire:model="stock_quantity" 
                                   class="input input-bordered" 
                                   min="0"
                                   placeholder="Stock Quantity" 
                                   required />
                            @error('stock_quantity') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control md:col-span-2">
                            <label class="label">
                                <span class="label-text text-gray-700 dark:text-gray-300">Minimum Stock Level</span>
                            </label>
                            <input type="number" 
                                   wire:model="min_stock_level" 
                                   class="input input-bordered" 
                                   min="0"
                                   placeholder="Minimum Stock Level" 
                                   required />
                            @error('min_stock_level') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="card-actions justify-end mt-6">
                        @if($productId)
                            <button type="button" 
                                    wire:click="cancel" 
                                    class="btn btn-ghost">Cancel</button>
                        @endif
                        <button type="submit" 
                                class="btn btn-primary">
                            {{ $productId ? 'Update Product' : 'Create Product' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products List -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-lg text-gray-800 dark:text-white">Products List</h2>

                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th class="text-gray-700 dark:text-gray-300">ID</th>
                                <th class="text-gray-700 dark:text-gray-300">Name</th>
                                <th class="text-gray-700 dark:text-gray-300">Price</th>
                                <th class="text-gray-700 dark:text-gray-300">Category</th>
                                <th class="text-gray-700 dark:text-gray-300">Stock</th>
                                <th class="text-gray-700 dark:text-gray-300">Status</th>
                                <th class="text-gray-700 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr class="text-gray-800 dark:text-white">
                                    <td>{{ $product->id }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                    <td>{{ $product->category ?: '-' }}</td>
                                    <td>
                                        <span class="badge {{ $product->isLowStock() ? 'badge-warning' : 'badge-success' }}">
                                            {{ $product->stock_quantity }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($product->isLowStock())
                                            <span class="badge badge-warning">Low Stock</span>
                                        @else
                                            <span class="badge badge-success">In Stock</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button wire:click="edit({{ $product->id }})" 
                                                    class="btn btn-xs btn-outline btn-info">Edit</button>
                                            <button wire:click="delete({{ $product->id }})" 
                                                    class="btn btn-xs btn-outline btn-error"
                                                    onclick="confirm('Are you sure you want to delete this product?') || event.stopImmediatePropagation()">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-gray-500 dark:text-gray-400 py-8">
                                        No products found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Update the theme icon based on current theme
    document.addEventListener('DOMContentLoaded', function() {
        const theme = document.documentElement.getAttribute('data-theme');
        const themeIcon = document.getElementById('theme-icon');
        if (theme === 'dark') {
            themeIcon.textContent = '☀️';
        } else {
            themeIcon.textContent = '🌙';
        }
    });
</script>
@endpush>