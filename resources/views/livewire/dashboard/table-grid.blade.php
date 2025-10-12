<div wire:poll.30s>
    <!-- Header Waktu -->
    <div class="bg-blue-600 text-white rounded-t-xl p-4 shadow">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-sm opacity-80">Waktu Sekarang</h2>
                <div id="current-time" class="text-2xl font-semibold">{{ now()->format('H:i:s') }}</div>
                <div class="text-xs opacity-80">{{ now()->translatedFormat('l, j F Y') }}</div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
    </div>

    <!-- Summary Cards - Versi Admin (Dark Theme) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 my-4 px-4">
        <!-- Pendapatan Hari Ini -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs text-gray-400 mb-1">Pendapatan Hari Ini</p>
                    <h3 class="text-lg font-bold text-green-400">
                        Rp {{ number_format($todayRevenue, 0, ',', '.') }}
                    </h3>
                    <p class="text-xs text-gray-500">{{ $completedTransactions }} transaksi selesai</p>
                </div>
                <div class="p-2 bg-green-900/30 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Meja Tersedia -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs text-gray-400 mb-1">Meja Tersedia</p>
                    <h3 class="text-lg font-bold text-blue-400">{{ $availableTables }}/{{ $totalTables }}</h3>
                    <p class="text-xs text-gray-500">{{ $occupiedTables }} terpakai</p>
                </div>
                <div class="p-2 bg-blue-900/30 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Sesi Aktif -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs text-gray-400 mb-1">Sesi Aktif</p>
                    <h3 class="text-lg font-bold text-yellow-400">{{ $activeSessions }}</h3>
                    <p class="text-xs text-gray-500">Sedang berjalan</p>
                </div>
                <div class="p-2 bg-yellow-900/30 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Maintenance -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs text-gray-400 mb-1">Meja Maintenance</p>
                    <h3 class="text-lg font-bold text-red-400">{{ $maintenanceTables }}</h3>
                    <p class="text-xs text-gray-500">Tidak dapat digunakan</p>
                </div>
                <div class="p-2 bg-red-900/30 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 shadow mx-4 mb-4">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <input
                    wire:model.live="search"
                    type="text"
                    placeholder="Cari nomor atau tipe meja..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <select
                wire:model.live="filterStatus"
                class="md:w-48 px-4 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            >
                <option value="all">Semua Status</option>
                <option value="available">Tersedia</option>
                <option value="occupied">Terpakai</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>
        
        <!-- Legenda -->
        <div class="flex items-center gap-4 mt-3 text-sm text-gray-300">
            <span>Legenda:</span>
            <div class="flex items-center gap-1">
                <div class="w-3 h-3 bg-green-500 rounded"></div>
                <span>Tersedia</span>
            </div>
            <div class="flex items-center gap-1">
                <div class="w-3 h-3 bg-red-500 rounded"></div>
                <span>Terpakai</span>
            </div>
            <div class="flex items-center gap-1">
                <div class="w-3 h-3 bg-gray-500 rounded"></div>
                <span>Maintenance</span>
            </div>
        </div>
    </div>

    <!-- Grid Meja - Versi Admin (Dark Theme) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 px-4 pb-6">
        @foreach($tables as $table)
            @php
                $bgColor = match($table->status) {
                    'available' => 'bg-green-700 hover:bg-green-600',
                    'occupied' => 'bg-red-700 hover:bg-red-600',
                    'maintenance' => 'bg-gray-700',
                    default => 'bg-gray-600'
                };
                $statusBadge = match($table->status) {
                    'available' => 'bg-green-600 text-white',
                    'occupied' => 'bg-red-600 text-white',
                    'maintenance' => 'bg-gray-600 text-white',
                    default => 'bg-gray-500 text-white'
                };
                $isClickable = true; // All tables can be clicked to show modal
            @endphp

            <div
                wire:click="selectTable({{ $table->id }})"
                class="{{ $bgColor }} text-white rounded-lg p-4 shadow cursor-pointer transition-all duration-200 hover:scale-[1.02]"
            >
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-xl font-bold">#{{ $table->name }}</h3>
                    <span class="px-2 py-0.5 {{ $statusBadge }} text-xs rounded">
                        {{ ucfirst($table->status) }}
                    </span>
                </div>
                <div class="text-center">
                    <p class="text-sm font-semibold">Rp {{ number_format($table->hourly_rate, 0, ',', '.') }}/jam</p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal for Table Details -->
    @if($showModal && $selectedTable)
    <div 
        x-data="{ open: @entangle('showModal') }"
        x-show="open"
        x-cloak
        @keydown.escape.window="open = false"
        class="fixed inset-0 z-50 overflow-y-auto"
    >
        <div 
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            @click="open = false"
        ></div>
        <div 
            x-show="open" 
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="flex items-center justify-center min-h-screen p-4"
        >
            <div 
                class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto"
                @click.outside="open = false"
            >
                <!-- Modal Header -->
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold">Detail Meja #{{ $selectedTable->name }}</h3>
                    <button 
                        @click="open = false" 
                        class="text-gray-500 hover:text-gray-700"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6">
                    <!-- Status Indicator -->
                    <div class="flex items-center mb-6">
                        <div class="w-4 h-4 rounded-full 
                            @if($selectedTable->status === 'available') bg-green-500
                            @elseif($selectedTable->status === 'occupied') bg-red-500
                            @else bg-yellow-500 @endif 
                            mr-2"></div>
                        <span class="font-semibold capitalize text-lg">{{ $selectedTable->status }}</span>
                    </div>
                    
                    <!-- Table Details -->
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Nomor Meja:</span>
                            <span class="font-medium">#{{ $selectedTable->name }}</span>
                        </div>
                        
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">Tarif per Jam:</span>
                            <span class="font-medium">Rp {{ number_format($selectedTable->hourly_rate, 0, ',', '.') }}</span>
                        </div>
                        
                        <!-- Status-specific details -->
                        @if($selectedTable->status === 'available')
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-medium text-green-600">Tersedia</span>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-gray-600">Terakhir Dibersihkan:</span>
                                <span class="font-medium">{{ $selectedTable->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                            
                        @elseif($selectedTable->status === 'occupied')
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-medium text-red-600">Terpakai</span>
                            </div>
                            @php
                                $ongoingTransaction = $selectedTable->transactions()->where('status', 'ongoing')->first();
                            @endphp
                            @if($ongoingTransaction)
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600">Durasi:</span>
                                    <span class="font-medium">{{ $ongoingTransaction->started_at->diffForHumans() }}</span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-gray-600">Waktu Mulai:</span>
                                    <span class="font-medium">{{ $ongoingTransaction->started_at->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif
                            
                        @elseif($selectedTable->status === 'maintenance')
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-medium text-yellow-600">Maintenance</span>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-gray-600">Alasan:</span>
                                <span class="font-medium">Cleaning in progress</span>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-gray-600">Petugas:</span>
                                <span class="font-medium">-</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col space-y-3">
                        @if($selectedTable->status === 'available')
                            <button 
                                wire:click="markAsOccupied({{ $selectedTable->id }})"
                                class="btn btn-error text-white"
                            >
                                Tandai Sebagai Terpakai
                            </button>
                            
                            <button 
                                wire:click="startSession({{ $selectedTable->id }})"
                                class="btn btn-primary text-white"
                            >
                                Mulai Sesi Baru
                            </button>
                        @elseif($selectedTable->status === 'occupied')
                            <button 
                                wire:click="markAsAvailable({{ $selectedTable->id }})"
                                class="btn btn-success text-white"
                            >
                                Tandai Sebagai Tersedia
                            </button>
                            
                            <button 
                                class="btn btn-primary text-white"
                                disabled
                            >
                                Lihat Order
                            </button>
                        @elseif($selectedTable->status === 'maintenance')
                            <button 
                                wire:click="markAsAvailable({{ $selectedTable->id }})"
                                class="btn btn-success text-white"
                            >
                                Tandai Sebagai Tersedia
                            </button>
                            
                            <button 
                                class="btn btn-warning text-white"
                                disabled
                            >
                                Lihat Log Maintenance
                            </button>
                        @endif
                        
                        <button 
                            @click="open = false"
                            class="btn btn-ghost"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    // Update waktu real-time
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
        document.getElementById('current-time').textContent = timeString;
    }
    setInterval(updateTime, 1000);
    
    // Real-time updates using API
    function fetchTableStatuses() {
        fetch('/api/tables/statuses')
            .then(response => response.json())
            .then(data => {
                // Handle real-time updates if needed
                // This could trigger Livewire events to update specific parts of the UI
            })
            .catch(error => console.error('Error fetching table statuses:', error));
    }
    
    // Update periodically every 30 seconds
    setInterval(fetchTableStatuses, 30000);
</script>
@endpush