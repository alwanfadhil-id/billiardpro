<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Pengaturan Sistem</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">Umum</h3>
                        <ul class="space-y-2">
                            <li class="flex justify-between">
                                <span>Nama Tempat</span>
                                <span class="text-gray-600 dark:text-gray-300">BilliardPro</span>
                            </li>
                            <li class="flex justify-between">
                                <span>Zona Waktu</span>
                                <span class="text-gray-600 dark:text-gray-300">Asia/Jakarta</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">Transaksi</h3>
                        <ul class="space-y-2">
                            <li class="flex justify-between">
                                <span>Minimum Durasi Sewa</span>
                                <span class="text-gray-600 dark:text-gray-300">1 jam</span>
                            </li>
                            <li class="flex justify-between">
                                <span>Biaya Sewa</span>
                                <span class="text-gray-600 dark:text-gray-300">Per Jam</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">Pembayaran</h3>
                        <ul class="space-y-2">
                            <li class="flex justify-between">
                                <span>Metode Pembayaran</span>
                                <span class="text-gray-600 dark:text-gray-300">Tunai</span>
                            </li>
                            <li class="flex justify-between">
                                <span>Pembulatan</span>
                                <span class="text-gray-600 dark:text-gray-300">Aktif</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">Laporan</h3>
                        <ul class="space-y-2">
                            <li class="flex justify-between">
                                <span>Format Laporan</span>
                                <span class="text-gray-600 dark:text-gray-300">PDF</span>
                            </li>
                            <li class="flex justify-between">
                                <span>Waktu Laporan</span>
                                <span class="text-gray-600 dark:text-gray-300">Harian</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>