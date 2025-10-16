<div class="py-6">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <div class="flex justify-between items-center mb-4 print:hidden">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Struk Transaksi</h2>
                <div class="flex gap-2">
                    <button 
                        onclick="window.print()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
                    >
                        Cetak Struk
                    </button>
                    <button 
                        wire:click="printReceipt"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg"
                    >
                        Cetak Thermal
                    </button>
                </div>
            </div>
            
            <!-- For print version, only show the title -->
            <div class="text-center mb-4 hidden print:block">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">STRUK TRANSAKSI</h2>
            </div>
            
            @if(session()->has('message'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg dark:bg-green-900/30 dark:text-green-300 print:hidden">
                    {{ session('message') }}
                </div>
            @endif
            
            @if(session()->has('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg dark:bg-red-900/30 dark:text-red-300 print:hidden">
                    {{ session('error') }}
                </div>
            @endif
            
            <!-- Receipt Content -->
            <div class="border-2 border-gray-300 dark:border-gray-600 rounded-lg p-6 bg-gray-50 dark:bg-gray-700" id="receipt-content" style="font-family: monospace;">
                <div class="text-center mb-4">
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">BILLIARDPRO</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Jl. Contoh Alamat No. 123</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Telp: (021) 12345678</p>
                </div>
                
                <div class="border-b border-gray-400 dark:border-gray-500 mb-4"></div>
                
                <!-- Transaction Info -->
                <div class="text-sm mb-3">
                    <div class="flex justify-between">
                        <span>Receipt No:</span>
                        <span>{{ $this->transaction->id ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Date:</span>
                        <span>{{ (($this->transaction->ended_at ?? $this->transaction->updated_at ?? now())->format('d/m/Y')) ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Time:</span>
                        <span>{{ (($this->transaction->ended_at ?? $this->transaction->updated_at ?? now())->format('H:i')) ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Cashier:</span>
                        <span>{{ $this->transaction->user->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Table:</span>
                        <span>{{ $table->name ?? 'N/A' }}</span>
                    </div>
                </div>
                
                <div class="border-b border-gray-400 dark:border-gray-500 mb-3"></div>
                
                <!-- Duration Info -->
                <div class="text-sm mb-3">
                    @php
                        $durationMinutes = $this->transaction->ended_at ? 
                            $this->transaction->started_at->diffInMinutes($this->transaction->ended_at) : 
                            $this->transaction->started_at->diffInMinutes(now());
                        $durationHours = ceil($durationMinutes / 60);
                    @endphp
                    <div class="flex justify-between">
                        <span>Start Time:</span>
                        <span>{{ $this->transaction->started_at->format('H:i') ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>End Time:</span>
                        <span>{{ ($this->transaction->ended_at ? $this->transaction->ended_at->format('H:i') : now()->format('H:i')) ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Duration (Rounded):</span>
                        <span>{{ $durationHours ?? 0 }} hour(s)</span>
                    </div>
                </div>
                
                <!-- Table Charge -->
                <div class="text-sm mb-3">
                    @php
                        $tableCharge = $table ? $table->hourly_rate * ($durationHours ?? 0) : 0;
                    @endphp
                    <div class="flex justify-between border-b pb-1">
                        <span>{{ $durationHours ?? 0 }} × Rp {{ number_format($table->hourly_rate ?? 0, 0, ',', '.') }}</span>
                        <span>Rp {{ number_format($tableCharge, 0, ',', '.') }}</span>
                    </div>
                    <div class="text-right text-xs text-gray-500 dark:text-gray-400">Table Rental</div>
                </div>
                
                <!-- Items -->
                @if($items && $items->count() > 0)
                <div class="text-sm mb-3">
                    @foreach($items as $item)
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
                        <span>Rp {{ number_format($this->transaction->total ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between mt-2">
                        <span>Payment:</span>
                        <span>{{ ucfirst($this->transaction->payment_method ?? 'N/A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Received:</span>
                        <span>Rp {{ number_format($this->transaction->cash_received ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Change:</span>
                        <span>Rp {{ number_format($this->transaction->change_amount ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
                
                <div class="border-b border-gray-400 dark:border-gray-500 mb-4"></div>
                
                <!-- Footer -->
                <div class="text-center text-sm">
                    <p class="text-gray-600 dark:text-gray-300">Terima kasih!</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
                </div>
            </div>
            
            <div class="mt-6 flex justify-center print:hidden">
                <button 
                    onclick="window.print()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg mr-3"
                >
                    Cetak Struk
                </button>
                <button 
                    wire:click="printReceipt"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg"
                >
                    Cetak Thermal
                </button>
            </div>
        </div>
    </div>
</div>

<script type="module">
    document.addEventListener('livewire:init', () => {
        // Dispatch printReceipt event for browser print functionality
        if (window.Livewire) {
            window.Livewire.on('printReceipt', () => {
                window.print();
            });
        }
    });
</script>