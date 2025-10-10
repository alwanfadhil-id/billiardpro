@extends('layouts.app')

@section('header')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Add Items to Transaction</h1>
        <button onclick="toggleDarkMode()" class="btn btn-ghost">
            <span id="theme-icon">🌙</span>
        </button>
    </div>
@endsection

@section('content')
<div class="container mx-auto px-4 py-6">
    @if($transaction)
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Add Items to Transaction</h1>
            <div class="mt-2 bg-blue-50 dark:bg-gray-800 p-4 rounded-lg">
                <p class="text-gray-700 dark:text-gray-300"><span class="font-medium">Table:</span> {{ $table->name }}</p>
                <p class="text-gray-700 dark:text-gray-300"><span class="font-medium">Started at:</span> {{ $transaction->started_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <!-- Search Products -->
        <div class="mb-6">
            <input type="text" 
                   wire:model.live="search" 
                   placeholder="Search products..." 
                   class="input input-bordered w-full max-w-md">
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            @forelse($products as $product)
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title text-gray-800 dark:text-white">{{ $product->name }}</h2>
                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                        <div class="card-actions justify-end">
                            <button wire:click="addToCart({{ $product->id }})" 
                                    wire:loading.attr="disabled"
                                    class="btn btn-primary btn-sm">
                                <span wire:loading.remove wire:target="addToCart({{ $product->id }})">Add to Cart</span>
                                <span wire:loading wire:target="addToCart({{ $product->id }})">Adding...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">No products found.</p>
                </div>
            @endforelse
        </div>

        <!-- Cart Section -->
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg text-gray-800 dark:text-white">Current Items</h2>
                
                <!-- Existing Items -->
                @if($existingItems->count() > 0)
                    <div class="mb-4">
                        <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Items already in transaction:</h3>
                        <div class="overflow-x-auto">
                            <table class="table table-compact w-full">
                                <thead>
                                    <tr>
                                        <th class="text-gray-700 dark:text-gray-300">Product</th>
                                        <th class="text-gray-700 dark:text-gray-300">Price</th>
                                        <th class="text-gray-700 dark:text-gray-300">Qty</th>
                                        <th class="text-gray-700 dark:text-gray-300">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($existingItems as $item)
                                        <tr>
                                            <td class="text-gray-800 dark:text-white">{{ $item->product->name }}</td>
                                            <td class="text-gray-800 dark:text-white">Rp {{ number_format($item->price_per_item, 0, ',', '.') }}</td>
                                            <td class="text-gray-800 dark:text-white">{{ $item->quantity }}</td>
                                            <td class="text-gray-800 dark:text-white">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Items in current session cart -->
                @if(!empty($cart))
                    <div class="overflow-x-auto mb-4">
                        <table class="table table-compact w-full">
                            <thead>
                                <tr>
                                    <th class="text-gray-700 dark:text-gray-300">Product</th>
                                    <th class="text-gray-700 dark:text-gray-300">Price</th>
                                    <th class="text-gray-700 dark:text-gray-300">Qty</th>
                                    <th class="text-gray-700 dark:text-gray-300">Total</th>
                                    <th class="text-gray-700 dark:text-gray-300">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cart as $itemId => $item)
                                    <tr>
                                        <td class="text-gray-800 dark:text-white">{{ $item['name'] }}</td>
                                        <td class="text-gray-800 dark:text-white">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                        <td class="text-gray-800 dark:text-white">
                                            <div class="flex items-center">
                                                <button wire:click="updateQuantity({{ $itemId }}, {{ $item['quantity'] - 1 }})" 
                                                        class="btn btn-xs btn-outline mr-1">-</button>
                                                <span class="mx-2">{{ $item['quantity'] }}</span>
                                                <button wire:click="updateQuantity({{ $itemId }}, {{ $item['quantity'] + 1 }})" 
                                                        class="btn btn-xs btn-outline ml-1">+</button>
                                            </div>
                                        </td>
                                        <td class="text-gray-800 dark:text-white">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                                        <td>
                                            <button wire:click="removeFromCart({{ $itemId }})" 
                                                    class="btn btn-xs btn-error">Remove</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if(count($cart) > 0 || $existingItems->count() > 0)
                    <div class="card-actions justify-end">
                        <button wire:click="proceedToPayment" 
                                wire:loading.attr="disabled"
                                class="btn btn-primary">
                            <span wire:loading.remove wire:target="proceedToPayment">Proceed to Payment</span>
                            <span wire:loading wire:target="proceedToPayment">Processing...</span>
                        </button>
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-gray-400 py-4">No items added yet.</p>
                @endif
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-4">
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">← Back to Dashboard</a>
        </div>
    @else
        <div class="text-center py-8">
            <p class="text-gray-500 dark:text-gray-400">Transaction not found.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary mt-4">Go to Dashboard</a>
        </div>
    @endif

    <!-- Success/Error messages -->
    @if (session()->has('message'))
        <div class="alert alert-success mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error mb-4">
            {{ session('error') }}
        </div>
    @endif
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