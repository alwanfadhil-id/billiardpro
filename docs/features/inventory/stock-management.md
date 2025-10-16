# Manajemen Stok Produk dalam BilliardPro

## 📋 Gambaran Umum

Dokumen ini menjelaskan bagaimana sistem menangani manajemen stok produk dalam transaksi dan pelacakan perubahan stok secara keseluruhan.

## 🧮 Struktur Tabel

### Tabel Produk
- `stock_quantity`: Jumlah stok saat ini
- `min_stock_level`: Level minimum stok sebelum muncul notifikasi

### Tabel Inventory Transactions
- `product_id`: Produk yang terlibat
- `user_id`: User yang melakukan perubahan
- `type`: `in` untuk penambahan stok, `out` untuk pengurangan stok
- `quantity`: Jumlah perubahan
- `description`: Deskripsi perubahan

## 🔄 Alur Pengurangan Stok

### 1. Saat Pembayaran Selesai
```php
// Dalam model Transaction
public function handleStockReduction()
{
    // Reduce stock for each item in the transaction
    foreach ($this->items as $item) {
        // Reduce the product stock
        $item->product->reduceStock($item->quantity);
        
        // Create an inventory transaction record
        InventoryTransaction::create([
            "product_id" => $item->product_id,
            "user_id" => $this->user_id,
            "type" => "out",
            "quantity" => $item->quantity,
            "description" => "Sold in transaction #" . $this->id,
        ]);
    }
}
```

### 2. Validasi Stok Sebelum Transaksi
```php
// Dalam model Product
public function reduceStock($quantity)
{
    if ($this->stock_quantity < $quantity) {
        throw new \InvalidArgumentException('Insufficient stock for product: ' . $this->name);
    }
    
    $this->update([
        'stock_quantity' => $this->stock_quantity - $quantity
    ]);
}
```

## 🛡️ Validasi dan Proteksi

### 1. Validasi Ketersediaan Stok
Sebelum item ditambahkan ke transaksi, sistem akan memvalidasi apakah stok mencukupi:
```php
// Dalam AddItems component
public function addItem()
{
    $product = Product::find($this->selectedProduct);
    
    if ($product->stock_quantity < $this->quantity) {
        session()->flash('error', 'Insufficient stock for ' . $product->name);
        return;
    }
    
    // Proses penambahan item
}
```

### 2. Validasi saat Pembayaran
Stok juga divalidasi kembali saat pembayaran untuk mencegah konflik simultan:
```php
// Dalam transaksi saat pembayaran
// Stock reduction hanya terjadi saat status transaksi menjadi completed
```

## 📊 Fitur Pelaporan Stok

### 1. Notifikasi Stok Rendah
```php
// Dalam model Product
public function isLowStock()
{
    return $this->stock_quantity <= $this->min_stock_level;
}
```

### 2. Riwayat Perubahan Stok
Setiap perubahan stok dicatat dalam tabel `inventory_transactions`:
- Penambahan stok (misalnya saat restock)
- Pengurangan stok (saat item terjual dalam transaksi)
- Deskripsi perubahan untuk audit trail

## 👤 Hak Akses dan Keamanan

### 1. Pembatasan Akses
- Hanya user dengan role tertentu yang bisa mengelola stok
- Semua perubahan stok tercatat dengan user yang melakukan perubahan

### 2. Audit Trail
Setiap perubahan stok dicatat dalam `inventory_transactions` dengan:
- ID produk
- ID user yang melakukan perubahan
- Jenis perubahan (masuk/keluar)
- Jumlah perubahan
- Tanggal dan waktu perubahan
- Deskripsi perubahan

## 🔄 Fungsi-fungsi Penting

### 1. Penambahan Stok
```php
// Dalam model Product
public function increaseStock($quantity)
{
    $this->update([
        'stock_quantity' => $this->stock_quantity + $quantity
    ]);
}
```

### 2. Pengurangan Stok
```php
// Dalam model Product
public function reduceStock($quantity)
{
    if ($this->stock_quantity < $quantity) {
        throw new \InvalidArgumentException('Insufficient stock for product: ' . $this->name);
    }
    
    $this->update([
        'stock_quantity' => $this->stock_quantity - $quantity
    ]);
}
```

## 🧪 Unit Testing

### Test yang Harus Ada
1. **Validasi Stok**: Memastikan item tidak bisa ditambahkan jika stok tidak mencukupi
2. **Pengurangan Stok**: Memastikan stok berkurang setelah transaksi selesai
3. **Catatan Transaksi Stok**: Memastikan `inventory_transactions` dibuat dengan benar
4. **Notifikasi Stok Rendah**: Memastikan fungsi `isLowStock()` berfungsi dengan benar

### Contoh Test
```php
public function test_stock_reduces_after_transaction_completion()
{
    $product = Product::factory()->create([
        'stock_quantity' => 10,
        'min_stock_level' => 2
    ]);
    
    $transaction = Transaction::factory()->create([
        'status' => 'completed'
    ]);
    
    $transactionItem = TransactionItem::create([
        'transaction_id' => $transaction->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'price_per_item' => 10000,
        'total_price' => 30000
    ]);
    
    // Proses pengurangan stok
    $transaction->handleStockReduction();
    
    $product->refresh();
    $this->assertEquals(7, $product->stock_quantity);
    
    // Periksa bahwa inventory transaction dibuat
    $this->assertDatabaseHas('inventory_transactions', [
        'product_id' => $product->id,
        'type' => 'out',
        'quantity' => 3,
        'description' => 'Sold in transaction #' . $transaction->id
    ]);
}
```

## 🚨 Penanganan Error

### 1. Stok Tidak Mencukupi
Sistem akan menampilkan pesan error yang jelas kepada user saat stok tidak mencukupi.

### 2. Rollback Transaksi
Jika terjadi error saat pengurangan stok, seluruh transaksi seharusnya dirollback untuk menjaga konsistensi data.

## 🔄 Update Terbaru

Dokumentasi ini mencerminkan fitur manajemen stok yang ditambahkan setelah versi awal sistem, termasuk:
- Kolom `stock_quantity` dan `min_stock_level` pada tabel `products`
- Tabel `inventory_transactions` untuk pelacakan perubahan stok
- Fungsi `reduceStock()` dan `increaseStock()` pada model `Product`
- Fungsi `handleStockReduction()` pada model `Transaction`