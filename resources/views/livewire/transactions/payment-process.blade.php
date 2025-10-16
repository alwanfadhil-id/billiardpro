    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Proses Pembayaran</h2>
                
                <!-- Loading Overlay -->
                <div 
                    x-data="{ 
                        showLoading: false,
                        init() {
                            $wire.on('paymentStarted', () => {
                                this.showLoading = true;
                            });
                            
                            $wire.on('paymentFinished', () => {
                                this.showLoading = false;
                            });
                        }
                    }"
                    x-show="showLoading"
                    x-cloak
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-50"
                >
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 flex flex-col items-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mb-4"></div>
                        <p class="text-gray-700 dark:text-gray-300 font-medium">Memproses pembayaran...</p>
                    </div>
                </div>
                
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
                                @if($transaction->status === 'completed')
                                    <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
                                        {{ $transaction->payment_method === 'cash' ? 'Tunai' : 'QRIS' }}
                                    </div>
                                @else
                                    <select 
                                        wire:model="paymentMethod"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white"
                                    >
                                        <option value="cash">Tunai</option>
                                        <option value="qris">QRIS</option>
                                    </select>
                                @endif
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Diterima</label>
                                @if($transaction->status === 'completed')
                                    <div class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
                                        Rp {{ number_format($transaction->cash_received ?? 0, 0, ',', '.') }}
                                    </div>
                                @else
                                    <input 
                                        wire:model="amountReceived"
                                        type="number" 
                                        placeholder="Masukkan jumlah"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white"
                                    />
                                @endif
                            </div>
                            
                            <div class="bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg">
                                <div class="flex justify-between">
                                    <span class="text-gray-700 dark:text-gray-300">Kembalian:</span>
                                    @if($transaction->status === 'completed')
                                        <span class="font-bold">Rp {{ number_format($transaction->change_amount ?? 0, 0, ',', '.') }}</span>
                                    @else
                                        <span class="font-bold">Rp {{ number_format($change, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($transaction->status !== 'completed')
                            <div x-data="{ 
                                showConfirmation: false,
                                paymentMethodText: '{{ $paymentMethod === "cash" ? "Tunai" : "QRIS" }}'
                            }" class="mt-4 flex gap-2">
                                <button 
                                    type="button"
                                    wire:click="cancelPayment"
                                    class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg"
                                >
                                    Batal
                                </button>
                                <button 
                                    @click="showConfirmation = true"
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg"
                                >
                                    Proses Pembayaran
                                </button>
                                
                                <!-- Confirmation Modal -->
                                <div 
                                    x-show="showConfirmation"
                                    x-cloak
                                    class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
                                >
                                    <!-- Overlay -->
                                    <div 
                                        x-show="showConfirmation"
                                        x-transition:enter="ease-out duration-200"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                        x-transition:leave="ease-in duration-150"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0"
                                        class="fixed inset-0 bg-black bg-opacity-50 z-40"
                                        @click="showConfirmation = false"
                                    ></div>

                                    <!-- Modal Content -->
                                    <div 
                                        x-show="showConfirmation" 
                                        x-transition:enter="ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave="ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                        class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden z-50 relative"
                                        @click.stop
                                    >
                                        <!-- Header -->
                                        <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
                                            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Konfirmasi Pembayaran</h3>
                                        </div>

                                        <!-- Body -->
                                        <div class="p-6">
                                            <p class="text-gray-700 dark:text-gray-300 mb-4">
                                                Anda akan melakukan pembayaran dengan metode <strong><span x-text="paymentMethodText"></span></strong>.
                                            </p>
                                            <p class="text-gray-700 dark:text-gray-300">
                                                Apakah Anda yakin ingin melanjutkan proses pembayaran?
                                            </p>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="p-6 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex justify-end gap-3">
                                            <button 
                                                @click="showConfirmation = false"
                                                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg"
                                            >
                                                Batal
                                            </button>
                                            <button 
                                                wire:click="processPayment"
                                                @click="showConfirmation = false"
                                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg"
                                            >
                                                Ya, Proses Pembayaran
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="mt-4 p-4 bg-green-100 text-green-700 rounded-lg dark:bg-green-900/30 dark:text-green-300">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span><strong>Pembayaran Berhasil!</strong> Transaksi telah selesai diproses.</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <a href="{{ route('transactions.receipt', ['transaction' => $transaction->id]) }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg print:hidden">
                        Cetak Struk
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    @if($showReceiptModal)
    <div 
        x-data="{ open: @entangle('showReceiptModal') }"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
    >
        <!-- Overlay -->
        <div 
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black bg-opacity-50 z-40"
            @click="$wire.closeReceiptModal()"
        ></div>

        <!-- Modal Content -->
        <div 
            x-show="open" 
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden z-50 relative"
            @click.stop
        >
            <!-- Success Header -->
            <div class="bg-green-100 dark:bg-green-900/30 p-6 border-b border-gray-200 dark:border-gray-700 flex items-center">
                <div class="flex-shrink-0">
                    <!-- Success Checkmark Icon -->
                    <svg class="h-8 w-8 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-xl font-bold text-green-800 dark:text-green-200">Pembayaran Berhasil!</h3>
                    <p class="text-sm text-green-600 dark:text-green-400">Transaksi #{{ $transaction->id }} telah selesai diproses</p>
                </div>
            </div>

            <!-- Receipt Content -->
            <div class="p-6" id="receipt-content" style="font-family: monospace;">
                <div class="text-center mb-4">
                    <h1 class="text-xl font-bold">BILLIARDPRO</h1>
                    <p class="text-sm">Jl. Contoh Alamat No. 123</p>
                    <p class="text-sm">Telp: (021) 12345678</p>
                </div>
                
                <div class="border-b border-gray-400 dark:border-gray-500 mb-4"></div>
                
                <!-- Transaction Info -->
                <div class="text-sm mb-3">
                    <div class="flex justify-between">
                        <span>Receipt No:</span>
                        <span>{{ $transaction->id ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Date:</span>
                        <span>{{ (($transaction->ended_at ?? $transaction->updated_at ?? now())->format('d/m/Y')) ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Time:</span>
                        <span>{{ (($transaction->ended_at ?? $transaction->updated_at ?? now())->format('H:i')) ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Cashier:</span>
                        <span>{{ $transaction->user->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Table:</span>
                        <span class="font-semibold">{{ $transaction->table->name ?? 'N/A' }}</span>
                    </div>
                </div>
                
                <div class="border-b border-gray-400 dark:border-gray-500 mb-3"></div>
                
                <!-- Duration Info -->
                <div class="text-sm mb-3">
                    @php
                        $durationMinutes = $transaction->ended_at ? 
                            $transaction->started_at->diffInMinutes($transaction->ended_at) : 
                            $transaction->started_at->diffInMinutes(now());
                        $durationHours = ceil($durationMinutes / 60);
                    @endphp
                    <div class="flex justify-between">
                        <span>Start Time:</span>
                        <span>{{ $transaction->started_at->format('H:i') ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>End Time:</span>
                        <span>{{ ($transaction->ended_at ? $transaction->ended_at->format('H:i') : now()->format('H:i')) ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Duration (Rounded):</span>
                        <span>{{ $durationHours ?? 0 }} hour(s)</span>
                    </div>
                </div>
                
                <!-- Table Charge -->
                <div class="text-sm mb-3">
                    @php
                        $tableCharge = $transaction->table->hourly_rate * ($durationHours ?? 0);
                    @endphp
                    <div class="flex justify-between border-b pb-1">
                        <span>{{ $durationHours ?? 0 }} × Rp {{ number_format($transaction->table->hourly_rate ?? 0, 0, ',', '.') }}</span>
                        <span>Rp {{ number_format($tableCharge, 0, ',', '.') }}</span>
                    </div>
                    <div class="text-right text-xs text-gray-500">Table Rental</div>
                </div>
                
                <!-- Items -->
                @if($transactionItems && $transactionItems->count() > 0)
                <div class="text-sm mb-3">
                    @foreach($transactionItems as $item)
                    <div class="flex justify-between border-b pb-1">
                        <span>{{ $item->quantity ?? 0 }} × {{ $item->product->name ?? 'N/A' }}</span>
                        <span>Rp {{ number_format($item->total_price ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
                
                <!-- Total, Payment, Change -->
                <div class="text-sm mb-4">
                    <div class="flex justify-between font-bold text-lg pt-2 border-t">
                        <span>TOTAL:</span>
                        <span>Rp {{ number_format($transaction->total ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between mt-2">
                        <span>Payment:</span>
                        <span>{{ ucfirst($transaction->payment_method ?? 'N/A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Received:</span>
                        <span>Rp {{ number_format($transaction->cash_received ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Change:</span>
                        <span>Rp {{ number_format($transaction->change_amount ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
                
                <div class="border-b border-gray-400 dark:border-gray-500 mb-4"></div>
                
                <!-- Footer -->
                <div class="text-center text-sm">
                    <p>Terima kasih!</p>
                    <p class="text-xs mt-2">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="p-6 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex flex-col gap-3">
                <div class="flex justify-center gap-3">
                    <button 
                        onclick="window.print()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
                    >
                        Cetak Browser
                    </button>
                    <button 
                        wire:click="printReceipt"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg"
                    >
                        Cetak Thermal
                    </button>
                </div>
                <div class="flex gap-2">
                    <button 
                        @click="window.location.href='{{ route('dashboard') }}'"
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg"
                    >
                        Kembali ke Dashboard
                    </button>
                    <button 
                        @click="$wire.closeReceiptModal()"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script type="module">
    // Wait for Livewire to be fully initialized before setting up the event listener
    document.addEventListener('livewire:init', () => {
        // Use the global Livewire object which should be available after initialization
        if (window.Livewire) {
            window.Livewire.on('printReceipt', () => {
                window.print();
            });
        }
    });
</script>
</div>