<div>
    <div class="max-w-6xl mx-auto p-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Daftar Produk</h2>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Tambah Produk
                </button>
            </div>
            
            <!-- Search and Filter -->
            <div class="mb-6 flex flex-col md:flex-row gap-4">
                <input 
                    type="text" 
                    placeholder="Cari produk..." 
                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                />
                <select class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">Semua Kategori</option>
                    <option value="minuman">Minuman</option>
                    <option value="makanan">Makanan Ringan</option>
                </select>
            </div>
            
            <!-- Products Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 shadow">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold text-gray-800 dark:text-white">Es Teh</h3>
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Rp 5.000</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">Minuman segar</p>
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Minuman</span>
                        <span>Stok: 50</span>
                    </div>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 shadow">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold text-gray-800 dark:text-white">Kopi Susu</h3>
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Rp 8.000</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">Kopi nikmat hangat</p>
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Minuman</span>
                        <span>Stok: 30</span>
                    </div>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 shadow">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold text-gray-800 dark:text-white">Kacang Goreng</h3>
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Rp 10.000</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">Camilan renyah</p>
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Makanan</span>
                        <span>Stok: 20</span>
                    </div>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 shadow">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold text-gray-800 dark:text-white">Rokok</h3>
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Rp 15.000</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">Stok barang</p>
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Lain-lain</span>
                        <span>Stok: 15</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>