# Manajemen Produk dalam BilliardPro

## 📋 Gambaran Umum

Manajemen produk adalah fitur dalam sistem BilliardPro yang mengelola produk tambahan seperti minuman dan snack yang dapat ditambahkan ke transaksi billing.

## 🔄 Alur Manajemen Produk

### 1. Penambahan Produk Baru
```
Admin mengakses halaman produk → Input nama, harga, kategori, stok awal → 
Sistem menyimpan produk ke database → Produk siap untuk ditambahkan ke transaksi
```

### 2. Penggunaan Produk dalam Transaksi
```
Kasir menambahkan produk ke transaksi → Validasi ketersediaan stok → 
Simpan ke transaction_items → Sementara kurangi stok → 
Saat pembayaran selesai, stok secara permanen berkurang
```

### 3. Update Stok
- Penambahan stok saat restock
- Pengurangan stok saat item terjual
- Catatan historis semua perubahan

## 🛠️ Komponen Terlibat

### 1. Product Model
```php
// app/Models/Product.php
protected $fillable = [
    'name', 'price', 'category', 'stock_quantity', 'min_stock_level'
];

public function reduceStock($quantity) {
    // Validasi dan pengurangan stok
}

public function increaseStock($quantity) {
    // Penambahan stok
}

public function isLowStock() {
    // Cek apakah stok rendah
}
```

### 2. AddItems Component
- Menampilkan daftar produk
- Menangani penambahan produk ke transaksi
- Validasi ketersediaan stok

### 3. TransactionItem Model
- Menyimpan item dalam transaksi
- Menyimpan harga per item sebagai snapshot

## 🧮 Struktur Data Produk

### Kolom dalam Tabel Products
- `id` - Primary key
- `name` - Nama produk
- `price` - Harga satuan
- `category` - Kategori produk (opsional)
- `stock_quantity` - Jumlah stok saat ini
- `min_stock_level` - Level minimum stok sebelum notifikasi
- `created_at`, `updated_at` - Timestamp

### Kolom dalam Tabel TransactionItems
- `id` - Primary key
- `transaction_id` - Referensi ke transaksi
- `product_id` - Referensi ke produk
- `quantity` - Jumlah item
- `price_per_item` - Harga satuan saat transaksi
- `total_price` - Jumlah total (quantity × price_per_item)

### Kolom dalam Tabel InventoryTransactions
- `id` - Primary key
- `product_id` - Referensi ke produk
- `user_id` - User yang melakukan perubahan
- `type` - Jenis perubahan (in/out)
- `quantity` - Jumlah perubahan
- `description` - Deskripsi perubahan
- `created_at` - Timestamp

## 🛡️ Validasi dan Keamanan

### 1. Validasi Input Produk
- Nama produk tidak boleh kosong
- Harga harus > 0
- Stok harus >= 0
- Level minimum stok harus >= 0

### 2. Validasi Ketersediaan Stok
- Saat menambahkan item ke transaksi
- Saat menyelesaikan pembayaran
- Mencegah penjualan produk yang stoknya tidak mencukupi

### 3. Hak Akses
- Hanya admin yang bisa menambah/mengedit/hapus produk
- Kasir hanya bisa melihat dan menggunakan produk dalam transaksi

## 📊 Fitur Manajemen

### 1. CRUD Produk
- Create: Tambah produk baru
- Read: Tampilkan daftar produk
- Update: Edit informasi produk
- Delete: Hapus produk (dengan validasi tidak digunakan dalam transaksi aktif)

### 2. Manajemen Stok
- Tampilkan jumlah stok saat ini
- Tampilkan level minimum stok
- Notifikasi stok rendah
- Catatan historis perubahan stok

### 3. Pencarian dan Filter
- Cari produk berdasarkan nama
- Filter berdasarkan kategori
- Filter produk dengan stok rendah

## 🧪 Unit Testing

### Test Manajemen Produk
```php
public function test_admin_can_create_product()
{
    $admin = User::factory()->create(['role' => 'admin']);
    
    $this->actingAs($admin);
    
    $response = $this->post('/api/products', [
        'name' => 'Es Teh Manis',
        'price' => 5000,
        'category' => 'Minuman',
        'stock_quantity' => 50,
        'min_stock_level' => 5
    ]);
    
    $response->assertStatus(201);
    
    $this->assertDatabaseHas('products', [
        'name' => 'Es Teh Manis',
        'price' => 5000,
        'category' => 'Minuman',
        'stock_quantity' => 50,
        'min_stock_level' => 5
    ]);
}

public function test_cannot_add_item_with_insufficient_stock()
{
    $product = Product::factory()->create([
        'stock_quantity' => 2,
        'min_stock_level' => 1
    ]);
    
    $transaction = Transaction::factory()->create(['status' => 'ongoing']);
    
    // Mencoba menambahkan 5 item padahal stok hanya 2
    $response = Livewire::test(AddItems::class, ['transaction' => $transaction->id])
        ->set('selectedProduct', $product->id)
        ->set('quantity', 5)
        ->call('addItem');
    
    $response->assertHasErrors(['quantity']);
}

public function test_stock_reduces_when_transaction_completes()
{
    $product = Product::factory()->create(['stock_quantity' => 10]);
    
    $transaction = Transaction::factory()->create(['status' => 'ongoing']);
    
    // Tambahkan item ke transaksi
    TransactionItem::create([
        'transaction_id' => $transaction->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'price_per_item' => $product->price,
        'total_price' => $product->price * 3
    ]);
    
    // Selesaikan transaksi - ini seharusnya mengurangi stok
    $transaction->update(['status' => 'completed']);
    $transaction->handleStockReduction(); // Fungsi ini mengurangi stok dan membuat catatan
    
    $product->refresh();
    $this->assertEquals(7, $product->stock_quantity);
    
    // Cek bahwa inventory transaction dibuat
    $this->assertDatabaseHas('inventory_transactions', [
        'product_id' => $product->id,
        'type' => 'out',
        'quantity' => 3,
        'description' => 'Sold in transaction #' . $transaction->id
    ]);
}
```

## 🔍 Pencarian dan Filter

### 1. Pencarian Produk
- Pencarian berdasarkan nama produk
- Pencarian berdasarkan kategori
- Pencarian produk dengan stok rendah

### 2. Filter Produk
- Filter berdasarkan kategori
- Filter produk dengan stok rendah
- Urutan berdasarkan nama, harga, atau stok

## 📈 Fitur Pelaporan

### 1. Laporan Produk Terlaris
- Produk dengan penjualan terbanyak
- Berdasarkan jumlah item terjual

### 2. Analisis Stok
- Produk dengan stok rendah
- Produk yang tidak terjual dalam periode tertentu

## 🔄 Proses Pengurangan Stok

### 1. Saat Penambahan ke Transaksi
- Dicek ketersediaan stok
- Stok sementara tidak berkurang

### 2. Saat Pembayaran Selesai
- Stok secara permanen berkurang
- Catatan inventory transaction dibuat
- Validasi dilakukan sekali lagi sebelum pengurangan permanen

## ⚠️ Penanganan Edge Cases

### 1. Stok Menjadi Negatif
- Sistem mencegah pengurangan stok lebih dari ketersediaan
- Validasi dilakukan di level database dan aplikasi

### 2. Konflik Simultan
- Dua kasir mencoba menggunakan stok yang sama bersamaan
- Penanganan dengan database transaction dan locking jika perlu

### 3. Pembatalan Transaksi
- Jika transaksi dibatalkan, stok harus dikembalikan
- Proses pengembalian stok jika diperlukan

## 🔧 Fungsi Tambahan

### 1. Import Produk
- Impor produk dari file CSV
- Massal tambah produk

### 2. Barcode Support (di masa depan)
- Integrasi dengan barcode scanner
- Identifikasi produk melalui barcode

### 3. Satuan Produk (di masa depat)
- Mendukung berbagai satuan (buah, pcs, pack, dll)

## 🔄 Update Terbaru

- Implementasi manajemen stok otomatis
- Catatan historis perubahan stok (inventory_transactions)
- Fungsi `reduceStock()` dan `increaseStock()` di model Product
- Validasi ketersediaan stok sebelum penambahan ke transaksi
- Notifikasi stok rendah dengan fungsi `isLowStock()`
- Proses pengurangan stok otomatis saat transaksi selesai