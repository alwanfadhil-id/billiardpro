<div>
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Daily Summary -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl p-5 shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm opacity-80">Hari Ini</p>
                    <h3 class="text-2xl font-bold mt-1">Rp {{ number_format($dailyRevenue, 0, ',', '.') }}</h3>
                    <p class="text-xs opacity-80 mt-1">{{ $dailyTransactions }} transaksi</p>
                </div>
                <div class="p-3 bg-blue-400/30 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Weekly Summary -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl p-5 shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm opacity-80">7 Hari Terakhir</p>
                    <h3 class="text-2xl font-bold mt-1">Rp {{ number_format($weeklyRevenue, 0, ',', '.') }}</h3>
                    <p class="text-xs opacity-80 mt-1">{{ $weeklyTransactions }} transaksi</p>
                </div>
                <div class="p-3 bg-green-400/30 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-xs font-semibold {{ $revenueGrowth >= 0 ? 'text-green-200' : 'text-red-200' }}">
                    {{ $revenueGrowth >= 0 ? '+' : '' }}{{ $revenueGrowth }}% dari minggu lalu
                </span>
            </div>
        </div>

        <!-- Monthly Summary -->
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl p-5 shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm opacity-80">Bulan Ini</p>
                    <h3 class="text-2xl font-bold mt-1">Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</h3>
                    <p class="text-xs opacity-80 mt-1">{{ $monthlyTransactions }} transaksi</p>
                </div>
                <div class="p-3 bg-purple-400/30 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Yearly Summary -->
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-xl p-5 shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm opacity-80">Tahun Ini</p>
                    <h3 class="text-2xl font-bold mt-1">Rp {{ number_format($yearlyRevenue, 0, ',', '.') }}</h3>
                    <p class="text-xs opacity-80 mt-1">{{ $yearlyTransactions }} transaksi</p>
                </div>
                <div class="p-3 bg-yellow-400/30 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Additional Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Recent Transactions -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Transaksi Terbaru</h3>
            <div class="space-y-4">
                @forelse($recentTransactions as $transaction)
                    <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-3 last:border-0 last:pb-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">#{{ $transaction->id }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $transaction->table->name }} • {{ $transaction->user->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $transaction->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">Tidak ada transaksi baru</p>
                @endforelse
            </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Produk Terlaris</h3>
            <div class="space-y-4">
                @forelse($topProducts as $productData)
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $productData['product']->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $productData['quantity_sold'] }} terjual</p>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Rp {{ number_format($productData['total_revenue'], 0, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">Tidak ada data produk</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Table Usage Stats -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Penggunaan Meja (30 Hari Terakhir)</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Meja</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Penggunaan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Durasi Total (mnt)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($tableUsageStats as $tableData)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $tableData['table']->name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $tableData['usage_count'] }} kali</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $tableData['total_duration'] }} menit</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">Rp {{ number_format($tableData['total_revenue'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada data penggunaan meja
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>