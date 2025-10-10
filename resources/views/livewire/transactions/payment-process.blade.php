@extends('layouts.app')

@section('header')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Payment Process</h1>
        <button onclick="toggleDarkMode()" class="btn btn-ghost">
            <span id="theme-icon">🌙</span>
        </button>
    </div>
@endsection

@section('content')
<div class="container mx-auto px-4 py-6">
    @if($transaction)
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Payment Process</h1>
            <div class="mt-2 bg-blue-50 p-4 rounded-lg">
                <p class="text-gray-700"><span class="font-medium">Table:</span> {{ $table->name }}</p>
                <p class="text-gray-700"><span class="font-medium">Started at:</span> {{ $transaction->started_at->format('d/m/Y H:i') }}</p>
                <p class="text-gray-700"><span class="font-medium">Current Duration:</span> 
                    @php
                        $duration = $transaction->started_at->diff($transaction->ended_at);
                        $hours = $duration->h + ($duration->days * 24);
                        $minutes = $duration->i;
                    @endphp
                    {{ $hours }}h {{ $minutes }}m (Rounded: {{ ceil($transaction->started_at->diffInMinutes($transaction->ended_at) / 60) }} hours)
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Bill Summary -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-lg">Bill Summary</h2>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Table Rate ({{ ceil($transaction->started_at->diffInMinutes($transaction->ended_at) / 60) }} hours × Rp {{ number_format($table->hourly_rate, 0, ',', '.') }}):</span>
                            <span>Rp {{ number_format($table->hourly_rate * ceil($transaction->started_at->diffInMinutes($transaction->ended_at) / 60), 0, ',', '.') }}</span>
                        </div>
                        
                        @if($items->count() > 0)
                            <div class="border-t pt-2 mt-2">
                                <h3 class="font-medium mb-2">Additional Items:</h3>
                                @foreach($items as $item)
                                    <div class="flex justify-between text-sm">
                                        <span>{{ $item->quantity }} × {{ $item->product->name }}</span>
                                        <span>Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        
                        <div class="border-t pt-2 mt-2">
                            <div class="flex justify-between font-bold text-lg">
                                <span>Total:</span>
                                <span>Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-lg">Payment Information</h2>
                    
                    <form wire:submit="processPayment">
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Payment Method</span>
                            </label>
                            <select wire:model="paymentMethod" class="select select-bordered" required>
                                <option value="cash">Cash</option>
                                <option value="qris">QRIS</option>
                                <option value="debit">Debit Card</option>
                                <option value="credit">Credit Card</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Cash Received (Rp)</span>
                            </label>
                            <input type="number" 
                                   wire:model.live="cashReceived" 
                                   step="100" 
                                   min="{{ $totalAmount }}"
                                   class="input input-bordered" 
                                   required>
                            <label class="label">
                                <span class="label-text-alt">Minimum: Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                            </label>
                        </div>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Change Amount (Rp)</span>
                            </label>
                            <input type="number" 
                                   wire:model="changeAmount" 
                                   readonly
                                   class="input input-bordered bg-gray-100" >
                        </div>

                        <div class="alert alert-info mt-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Duration will be rounded up to the nearest hour for billing purposes.</span>
                        </div>

                        <div class="card-actions justify-end mt-6">
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="btn btn-primary btn-block">
                                <span wire:loading.remove wire:target="processPayment">Process Payment</span>
                                <span wire:loading wire:target="processPayment">Processing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-4">
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">← Back to Dashboard</a>
        </div>
    @else
        <div class="text-center py-8">
            <p class="text-gray-500">Transaction not found.</p>
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