<div>
    <!-- Enhanced responsive table grid -->
    <div class="table-grid">
        @foreach($tables as $table)
            @php
                $statusBg = [
                    'available' => 'bg-gradient-to-br from-green-500 to-green-600',
                    'occupied' => 'bg-gradient-to-br from-red-500 to-red-600', 
                    'maintenance' => 'bg-gradient-to-br from-gray-500 to-gray-600'
                ][$table->status] ?? 'bg-gradient-to-br from-gray-500 to-gray-600';
                
                $statusBgDark = [
                    'available' => 'dark:from-green-600 dark:to-green-700',
                    'occupied' => 'dark:from-red-600 dark:to-red-700', 
                    'maintenance' => 'dark:from-gray-600 dark:to-gray-700'
                ][$table->status] ?? 'dark:from-gray-600 dark:to-gray-700';
                
                // Get the first ongoing transaction for this table (using eager-loaded relationship)
                $ongoingTransaction = $table->transactions->first();
            @endphp
            
            <div
                class="table-card {{ $table->status === 'available' ? 'table-available' : ($table->status === 'occupied' ? 'table-occupied' : 'table-maintenance') }}"
                wire:click="handleTableClick({{ $table->id }})"
                role="button"
                tabindex="0"
                aria-label="{{ $table->name }}, Status: {{ ucfirst($table->status) }}"
            >
                <div class="p-5">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h2 class="table-title">{{ $table->name }}</h2>
                            <p class="table-subtitle">Meja Billiard</p>
                        </div>
                        <span class="status-badge">
                            {{ ucfirst($table->status) }}
                        </span>
                    </div>
                    
                    <div class="mb-4">
                        <div class="rate-display">
                            <div class="rate-label">Tarif</div>
                            <div class="rate-value">
                                Rp {{ number_format($table->hourly_rate, 0, ',', '.') }}/jam
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        @if($table->status === 'available')
                            <div class="mt-2">
                                <p class="text-white font-semibold">Klik untuk Mulai</p>
                                <div class="mt-3">
                                    <div class="action-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @elseif($table->status === 'occupied')
                            @if($ongoingTransaction)
                                @php
                                    $start = $ongoingTransaction->started_at;
                                    $now = now();
                                    $diffInMinutes = $start->diffInMinutes($now);
                                    $hours = floor($diffInMinutes / 60);
                                    $minutes = $diffInMinutes % 60;
                                    $durationText = $hours . ' jam ' . $minutes . ' menit';
                                @endphp
                                <div class="mt-2">
                                    <div class="timer-display">{{ $durationText }}</div>
                                    <p class="cost-display">Biaya: Rp {{ number_format(ceil($diffInMinutes / 60) * $table->hourly_rate, 0, ',', '.') }}</p>
                                    <div class="mt-2">
                                        <div class="action-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="text-white font-semibold mt-2">Klik untuk Selesai</p>
                                </div>
                            @endif
                        @elseif($table->status === 'maintenance')
                            <div class="mt-2">
                                <p class="text-white">Dalam Perawatan</p>
                                <div class="mt-3">
                                    <div class="action-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>