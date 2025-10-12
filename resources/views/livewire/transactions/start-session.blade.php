<div>
    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                Mulai Sesi untuk Meja #{{ $table?->name }}
            </h3>
            
            <div class="mb-4">
                <p class="text-gray-600 dark:text-gray-300 mb-2">
                    Apakah Anda yakin ingin memulai sesi baru untuk meja ini?
                </p>
                <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                    <p class="text-sm font-medium text-gray-800 dark:text-white">Tarif: Rp {{ number_format($table?->hourly_rate ?? 0, 0, ',', '.') }}/jam</p>
                </div>
            </div>
            
            <div class="flex justify-end gap-2">
                <button 
                    wire:click="closeModal"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    Batal
                </button>
                <button 
                    wire:click="startSession"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50"
                >
                    <span wire:loading.remove>Mulai Sesi</span>
                    <span wire:loading>Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Success/Error messages -->
    @if (session()->has('message'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg z-50">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg z-50">
            {{ session('error') }}
        </div>
    @endif
</div>