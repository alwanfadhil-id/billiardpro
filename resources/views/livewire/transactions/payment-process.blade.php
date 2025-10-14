    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Proses Pembayaran</h2>
                
                @if(session()->has('message'))
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg dark:bg-green-900/30 dark:text-green-300">
                        {{ session('message') }}
                    </div>
                @endif
                
                @if(session()->has('error'))
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg dark:bg-red-900/30 dark:text-red-300">
                        {{ session('error') }}
                    </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Transaction Details -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Detail Transaksi</h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Meja:</span>
                                <span class="font-medium">{{ $transaction->table->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Waktu Mulai:</span>
                                <span class="font-medium">{{ $transaction->started_at ? $transaction->started_at->format('d M Y H:i') : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Waktu Selesai:</span>
                                <span class="font-medium">
                                    @if($transaction->ended_at)
                                        {{ $transaction->ended_at->format('d M Y H:i') }}
                                    @else
                                        <span class="text-orange-500">Sedang Berlangsung</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Durasi:</span>
                                <span class="font-medium">
                                    @if($transaction->ended_at)
                                        {{ $transaction->started_at->diffInHours($transaction->ended_at) }} jam 
                                        {{ $transaction->started_at->diffInMinutes($transaction->ended_at) % 60 }} menit
                                    @else
                                        @php
                                            $currentDurationMinutes = $transaction->started_at->diffInMinutes(now());
                                            $currentDurationHours = intdiv($currentDurationMinutes, 60);
                                            $currentRemainingMinutes = $currentDurationMinutes % 60;
                                        @endphp
                                        {{ $currentDurationHours }} jam 
                                        {{ $currentRemainingMinutes }} menit
                                    @endif
                                </span>
                            </div>
                            <div class="border-t pt-2 mt-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Tarif per Jam:</span>
                                    <span class="font-medium">Rp {{ number_format($transaction->table->hourly_rate ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Subtotal Meja:</span>
                                    <span class="font-medium">Rp {{ number_format($tableCost, 0, ',', '.') }}</span>
                                </div>
                                <div class="pt-2 space-y-1">
                                    <div class="font-semibold text-gray-700 dark:text-gray-300">Item Tambahan:</div>
                                    @forelse($transactionItems as $item)
                                        <div class="flex justify-between pl-2">
                                            <span class="text-sm">{{ $item->product->name }} (x{{ $item->quantity }})</span>
                                            <span class="text-sm">Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                                        </div>
                                    @empty
                                        <div class="text-sm text-gray-500 dark:text-gray-400 pl-2">Tidak ada item tambahan</div>
                                    @endforelse
                                    <div class="flex justify-between font-medium mt-1">
                                        <span>Total Item:</span>
                                        <span>Rp {{ number_format($transactionItems->sum('total_price'), 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="border-t pt-2 mt-2 font-bold text-lg">
                                <div class="flex justify-between">
                                    <span>Total:</span>
                                    <span>Rp {{ number_format($transaction->total ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Form -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Metode Pembayaran</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Metode Pembayaran</label>
                                <select 
                                    wire:model="paymentMethod"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white"
                                >
                                    <option value="cash">Tunai</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Diterima</label>
                                <input 
                                    wire:model="amountReceived"
                                    type="number" 
                                    placeholder="Masukkan jumlah"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white"
                                />
                            </div>
                            
                            <div class="bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg">
                                <div class="flex justify-between">
                                    <span class="text-gray-700 dark:text-gray-300">Kembalian:</span>
                                    <span class="font-bold">Rp {{ number_format($change, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            
                            <div class="mt-4 flex gap-2">
                                <button 
                                    type="button"
                                    wire:click="cancelPayment"
                                    class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg"
                                >
                                    Batal
                                </button>
                                <button 
                                    type="button"
                                    wire:click="processPayment"
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg"
                                >
                                    Proses Pembayaran
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Cetak Struk
                    </button>
                </div>
            </div>
        </div>
    </div>