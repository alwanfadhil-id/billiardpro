<div>
    <div class="max-w-6xl mx-auto p-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Tambah Item Transaksi</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Information Panel -->
                <div class="lg:col-span-1 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Informasi Transaksi</h3>
                        <div class="mt-2 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Meja:</span>
                                <span class="font-medium">#A01</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Durasi:</span>
                                <span class="font-medium">2 jam</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Tarif per jam:</span>
                                <span class="font-medium">Rp 50,000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Subtotal meja:</span>
                                <span class="font-medium">Rp 100,000</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Total</h3>
                        <div class="mt-2 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span>Rp 100,000</span>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex gap-2">
                            <button class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg">
                                Kembali
                            </button>
                            <button class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">
                                Bayar
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Products Selection -->
                <div class="lg:col-span-2">
                    <div class="mb-4">
                        <input 
                            type="text" 
                            placeholder="Cari produk..." 
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        />
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 shadow cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="text-center">
                                <div class="bg-gray-200 dark:bg-gray-600 h-16 rounded mb-2 flex items-center justify-center">
                                    <span class="text-gray-500 dark:text-gray-400">Es Teh</span>
                                </div>
                                <h4 class="font-semibold text-gray-800 dark:text-white">Es Teh</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-300">Rp 5,000</p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 shadow cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="text-center">
                                <div class="bg-gray-200 dark:bg-gray-600 h-16 rounded mb-2 flex items-center justify-center">
                                    <span class="text-gray-500 dark:text-gray-400">Kopi</span>
                                </div>
                                <h4 class="font-semibold text-gray-800 dark:text-white">Kopi</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-300">Rp 8,000</p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 shadow cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="text-center">
                                <div class="bg-gray-200 dark:bg-gray-600 h-16 rounded mb-2 flex items-center justify-center">
                                    <span class="text-gray-500 dark:text-gray-400">Kacang</span>
                                </div>
                                <h4 class="font-semibold text-gray-800 dark:text-white">Kacang Goreng</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-300">Rp 10,000</p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 shadow cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="text-center">
                                <div class="bg-gray-200 dark:bg-gray-600 h-16 rounded mb-2 flex items-center justify-center">
                                    <span class="text-gray-500 dark:text-gray-400">Rokok</span>
                                </div>
                                <h4 class="font-semibold text-gray-800 dark:text-white">Rokok</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-300">Rp 15,000</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Selected Items -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">Item Terpilih</h3>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="space-y-2">
                                <div class="flex justify-between items-center border-b pb-2">
                                    <span>Es Teh</span>
                                    <div class="flex items-center gap-2">
                                        <button class="w-6 h-6 bg-gray-300 dark:bg-gray-600 rounded text-sm">-</button>
                                        <span>1</span>
                                        <button class="w-6 h-6 bg-gray-300 dark:bg-gray-600 rounded text-sm">+</button>
                                        <span class="ml-2">Rp 5,000</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span>Subtotal:</span>
                                    <span>Rp 5,000</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>