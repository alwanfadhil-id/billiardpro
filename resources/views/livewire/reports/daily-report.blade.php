<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daily Report') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <!-- Date Picker and Summary Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <div class="lg:col-span-2">
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text text-gray-700 dark:text-gray-300">Select Date</span>
                            </label>
                            <input 
                                type="date" 
                                wire:model="date" 
                                wire:change="updateReport" 
                                class="input input-bordered w-full max-w-xs"
                            />
                        </div>
                    </div>
                    <div class="card bg-primary text-primary-content">
                        <div class="card-body">
                            <h2 class="card-title text-gray-800 dark:text-white">Total Revenue</h2>
                            <p class="text-3xl font-bold text-gray-800 dark:text-white">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="mb-8 card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Revenue Trend (Last 7 Days)</h2>
                        <canvas id="revenueChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <!-- Export Buttons -->
                <div class="flex flex-wrap gap-3 mb-6">
                    <button wire:click="exportToCsv" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export CSV
                    </button>
                    <button wire:click="exportToExcel" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export Excel
                    </button>
                    <button wire:click="exportToPdf" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export PDF
                    </button>
                </div>

                <!-- Transactions List -->
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr class="text-gray-700 dark:text-gray-300">
                                <th>Transaction ID</th>
                                <th>Table</th>
                                <th>Started At</th>
                                <th>Ended At</th>
                                <th>Duration (min)</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Payment Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr class="text-gray-800 dark:text-white">
                                    <td>{{ $transaction->id }}</td>
                                    <td>{{ $transaction->table->name }}</td>
                                    <td>{{ $transaction->started_at->format('H:i') }}</td>
                                    <td>{{ $transaction->ended_at ? $transaction->ended_at->format('H:i') : '-' }}</td>
                                    <td>{{ $transaction->duration_minutes }}</td>
                                    <td>
                                        @if($transaction->items->count() > 0)
                                            <ul class="text-gray-700 dark:text-gray-300">
                                                @foreach($transaction->items as $item)
                                                    <li>{{ $item->quantity }}x {{ $item->product->name }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="text-gray-800 dark:text-white">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
                                    <td class="text-gray-800 dark:text-white">{{ ucfirst($transaction->payment_method) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-gray-700 dark:text-gray-300">No transactions found for this date</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize the revenue chart
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            // Use data from the Livewire component
            const revenueData = @json($revenueData);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: revenueData.labels,
                    datasets: [{
                        label: 'Revenue (Rp)',
                        data: revenueData.values,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>