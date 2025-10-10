<div>
    <div class="max-w-4xl mx-auto p-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Proses Pembayaran</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Transaction Details -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Detail Transaksi</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">Meja:</span>
                            <span class="font-medium">#A01</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">Waktu Mulai:</span>
                            <span class="font-medium">10:00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">Waktu Selesai:</span>
                            <span class="font-medium">12:30</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">Durasi:</span>
                            <span class="font-medium">3 jam</span>
                        </div>
                        <div class="border-t pt-2 mt-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Tarif per Jam:</span>
                                <span class="font-medium">Rp 50,000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Subtotal Meja:</span>
                                <span class="font-medium">Rp 150,000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Item Tambahan:</span>
                                <span class="font-medium">Rp 25,000</span>
                            </div>
                        </div>
                        <div class="border-t pt-2 mt-2 font-bold text-lg">
                            <div class="flex justify-between">
                                <span>Total:</span>
                                <span>Rp 175,000</span>
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
                            <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white">
                                <option value="cash">Tunai</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Diterima</label>
                            <input 
                                type="number" 
                                placeholder="Masukkan jumlah"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-600 text-gray-900 dark:text-white"
                            />
                        </div>
                        
                        <div class="bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg">
                            <div class="flex justify-between">
                                <span class="text-gray-700 dark:text-gray-300">Kembalian:</span>
                                <span class="font-bold">Rp 25,000</span>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex gap-2">
                            <button class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg">
                                Batal
                            </button>
                            <button class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg">
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