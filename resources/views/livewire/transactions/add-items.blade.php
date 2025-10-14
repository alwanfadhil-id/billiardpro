    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Tambah Item Transaksi</h2>
                
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
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Information Panel -->
                    <div class="lg:col-span-1 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Informasi Transaksi</h3>
                            <div class="mt-2 space-y-2">
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
                                        <span class="font-medium">Rp {{ number_format($this->calculateTableCost(), 0, ',', '.') }}</span>
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
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Total</h3>
                            <div class="mt-2 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                                <div class="flex justify-between text-lg font-bold">
                                    <span>Total:</span>
                                    <span>Rp {{ number_format($transaction->total ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            
                            <div class="mt-4 flex gap-2">
                                <a href="{{ route('dashboard') }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg text-center">
                                    Kembali
                                </a>
                                <button wire:click="goToPayment" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">
                                    Bayar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Products Selection -->
                    <div class="lg:col-span-2">
                        <div class="mb-4">
                            <input 
                                wire:model.live="search" 
                                type="text" 
                                placeholder="Cari produk..." 
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            />
                        </div>
                        
                        <div class="mb-4">
                            <form wire:submit.prevent="addItem" class="flex gap-2 mb-4">
                                <select 
                                    wire:model="selectedProduct"
                                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white"
                                >
                                    <option value="">Pilih Produk</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} - Rp {{ number_format($product->price, 0, ',', '.') }}</option>
                                    @endforeach
                                </select>
                                
                                <input 
                                    wire:model="quantity"
                                    type="number" 
                                    min="1"
                                    placeholder="Qty"
                                    class="w-20 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white"
                                />
                                
                                <button 
                                    type="submit"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg"
                                >
                                    Tambah
                                </button>
                            </form>
                            
                            @error('selectedProduct') <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
                            @error('quantity') <div class="text-red-500 text-sm mt-1">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($products as $product)
                                <div 
                                    wire:click="selectProduct({{ $product->id }})"
                                    class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 shadow cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                >
                                    <div class="text-center">
                                        <div class="bg-gray-200 dark:bg-gray-600 h-16 rounded mb-2 flex items-center justify-center">
                                            <span class="text-gray-500 dark:text-gray-400">{{ $product->name[0] ?? 'P' }}</span>
                                        </div>
                                        <h4 class="font-semibold text-gray-800 dark:text-white">{{ $product->name }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Selected Items -->
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">Item Terpilih</h3>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="space-y-2">
                                    @forelse($transactionItems as $item)
                                        <div class="flex justify-between items-center border-b pb-2 last:border-0 last:pb-0">
                                            <span>{{ $item->product->name }}</span>
                                            <div class="flex items-center gap-2">
                                                <span class="mr-2">x{{ $item->quantity }}</span>
                                                <span>Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                                                <button 
                                                    wire:click="removeFromTransaction({{ $item->id }})"
                                                    class="ml-2 text-red-600 hover:text-red-800"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-gray-600 dark:text-gray-400 text-center py-2">Belum ada item ditambahkan</p>
                                    @endforelse
                                    
                                    @if($transactionItems->count() > 0)
                                        <div class="flex justify-between pt-2 font-bold">
                                            <span>Subtotal:</span>
                                            <span>Rp {{ number_format($transactionItems->sum('total_price'), 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // For product selection using card clicks
        window.addEventListener('livewire:init', () => {
            Livewire.on('productSelected', (productId) => {
                // This would handle the product selection if we add a method for it
            });
        });
    </script>