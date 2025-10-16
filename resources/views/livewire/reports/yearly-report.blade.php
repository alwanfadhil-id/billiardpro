<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Laporan Tahunan</h2>
            </div>
            
            <!-- Year Picker and Summary Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
                <div class="lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Tahun</label>
                    <input 
                        type="number" 
                        wire:model="year" 
                        wire:change="updateReport" 
                        min="2000" 
                        max="2100"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white w-full"
                    />
                </div>
                
                <div class="lg:col-span-1 bg-blue-600 text-white rounded-lg p-4 shadow">
                    <h3 class="text-lg font-semibold">Total Pendapatan</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
                
                <div class="lg:col-span-1 bg-green-600 text-white rounded-lg p-4 shadow">
                    <h3 class="text-lg font-semibold">Jumlah Transaksi</h3>
                    <p class="text-2xl font-bold">{{ $transactionCount }}</p>
                </div>
                
                <div class="lg:col-span-1 bg-purple-600 text-white rounded-lg p-4 shadow">
                    <h3 class="text-lg font-semibold">Rata-rata Transaksi</h3>
                    <p class="text-2xl font-bold">Rp {{ number_format($avgTransactionValue, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Revenue Chart -->
            <div class="mb-8 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Tren Pendapatan Tahun {{ $year }}</h3>
                <canvas id="yearlyRevenueChart" width="400" height="200"></canvas>
            </div>

            <!-- Export Buttons -->
            <div class="flex flex-wrap gap-3 mb-6">
                <button wire:click="exportToCsv" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4 mr-1 inline">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export CSV
                </button>
                <button wire:click="exportToExcel" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4 mr-1 inline">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Excel
                </button>
                <button wire:click="exportToPdf" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4 mr-1 inline">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export PDF
                </button>
            </div>

            <!-- Monthly Summary -->
            <div class="mb-8">
                <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Ringkasan Bulanan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @for($i = 1; $i <= 12; $i++)
                        @php
                            $monthName = \Carbon\Carbon::create()->month($i)->format('F');
                            $yearlyReportData = $this->getYearlyData();
                            $monthlyTransactions = isset($yearlyReportData['monthly_data'][$monthName]) ? $yearlyReportData['monthly_data'][$monthName] : collect();
                            $monthlyRevenue = $monthlyTransactions->sum('total');
                            $monthlyCount = $monthlyTransactions->count();
                        @endphp
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 shadow">
                            <h4 class="font-semibold text-gray-800 dark:text-white">{{ \Carbon\Carbon::create()->month($i)->format('M') }}</h4>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $monthlyCount }} transaksi</p>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Transactions List -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Transaksi ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Meja</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Kasir</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Mulai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Selesai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Durasi (mnt)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Metode Bayar</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($this->getTransactions() as $transaction)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $transaction->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $transaction->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $transaction->table->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $transaction->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $transaction->started_at->format('H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $transaction->ended_at ? $transaction->ended_at->format('H:i') : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $transaction->duration_minutes }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ ucfirst($transaction->payment_method) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada transaksi ditemukan untuk tahun ini
                                </td>
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
        // Initialize the yearly revenue chart
        const ctx = document.getElementById('yearlyRevenueChart').getContext('2d');
        
        // Use data from the Livewire component
        const yearlyData = @json($yearlyDataChart);
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: yearlyData.labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: yearlyData.values,
                    borderColor: 'rgb(139, 92, 246)',
                    backgroundColor: 'rgba(139, 92, 246, 0.2)',
                    borderWidth: 1
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