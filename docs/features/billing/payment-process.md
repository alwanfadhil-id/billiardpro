# Proses Pembayaran dalam BilliardPro

## 📋 Gambaran Umum

Proses pembayaran adalah tahap akhir dari transaksi dalam sistem BilliardPro yang mencakup validasi, perhitungan total, penerimaan pembayaran, dan penyelesaian transaksi.

## 🔄 Alur Proses Pembayaran

### 1. Pembukaan Proses Pembayaran
```
Kasir mengakses halaman pembayaran → Sistem memuat detail transaksi → 
Sistem memperbarui total berdasarkan durasi dan item → 
Tampilkan rincian tagihan dan metode pembayaran
```

### 2. Input Data Pembayaran
- Pilih metode pembayaran (cash, qris, debit, credit, other)
- Input jumlah yang diterima (jika cash)
- Sistem menghitung kembalian otomatis

### 3. Validasi dan Penyelesaian
```
Validasi jumlah pembayaran ≥ total tagihan → 
Perbarui status transaksi ke completed → 
Perbarui status meja ke available → 
Kurangi stok produk terjual → 
Cetak struk (opsional)
```

## 🛠️ Komponen Terlibat

### 1. PaymentProcess Component
```php
// app/Livewire/Transactions/PaymentProcess.php
public $transactionId;
public $transaction;
public $paymentMethod = 'cash';
public $amountReceived = 0;
public $change = 0;
public $showReceiptModal = false;
```

### 2. Transaction Model
- Menyimpan hasil pembayaran (payment_method, cash_received, change_amount)
- Memperbarui status ke completed
- Menjalankan proses pengurangan stok

### 3. ReceiptPrint Component
- Mencetak struk transaksi (thermal atau browser)

## 🧮 Logika Perhitungan Pembayaran

### 1. Validasi Jumlah Pembayaran
```php
public function processPayment()
{
    // Gunakan total yang akan diperbarui, bukan total lama
    $totalToCompare = $this->transaction->total;
    if (isset($updateData['total'])) {
        $totalToCompare = $updateData['total'];
    }

    if ($this->amountReceived < $totalToCompare) {
        session()->flash('error', 'Jumlah yang diterima kurang dari total tagihan.');
        return;
    }
    
    // Proses lanjutan...
}
```

### 2. Kalkulasi Kembalian
```php
private function calculateChange()
{
    if ($this->transaction) {
        $this->change = max(0, $this->amountReceived - $this->transaction->total);
    }
}
```

## ⚠️ Edge Cases dan Penanganannya

### 1. Duration Minutes = 0
- **Masalah**: `duration_minutes` bisa tetap 0 karena berbagai alasan
- **Solusi**: Fallback calculation dalam `processPayment`
```php
if (intval($this->transaction->duration_minutes) === 0) {
    $rawDuration = abs(now()->diffInMinutes($this->transaction->started_at));
    $calculatedDuration = max(0, intval($rawDuration));
    
    // Hitung total baru berdasarkan duration_minutes yang dihitung ulang
    $table = $this->transaction->table;
    if ($table) {
        $ratePerMinute = $table->hourly_rate / 60;
        $calculatedTotal = $calculatedDuration * $ratePerMinute;

        $updateData['duration_minutes'] = $calculatedDuration;
        $updateData['total'] = $calculatedTotal;
        $fallbackApplied = true;
    }
}
```

### 2. Mount Component Prematur
- **Masalah**: `updateTransactionTotal()` dipanggil saat mount meskipun transaksi belum selesai
- **Solusi**: Kondisional pemanggilan fungsi
```php
// Dalam mount()
if ($this->transaction->status === 'completed' || $this->transaction->ended_at) {
    $this->updateTransactionTotal();
}
```

## 🛡️ Validasi dan Keamanan

### 1. Validasi Pembayaran
- Jumlah yang diterima harus ≥ total tagihan
- Metode pembayaran harus valid (cash, qris, debit, credit, other)
- Validasi dilakukan sebelum transaksi disimpan

### 2. Validasi Status Transaksi
- Tidak bisa memproses pembayaran untuk transaksi yang sudah completed
- Cek status sebelum eksekusi proses

### 3. Validasi Stok Produk
- Pastikan stok cukup sebelum menyelesaikan transaksi
- Validasi dilakukan saat pengurangan stok

## 💳 Metode Pembayaran

### 1. Cash
- Input jumlah yang diterima
- Sistem kalkulasi kembalian
- Validasi jumlah yang diterima ≥ total

### 2. QRIS
- Input jumlah yang diterima sebagai referensi
- Penyelesaian manual setelah pembayaran selesai

### 3. Debit/Kredit
- Input jumlah yang diterima sebagai referensi
- Penyelesaian manual setelah pembayaran selesai

### 4. Lainnya
- Metode lain yang bisa ditentukan

## 🖨️ Fungsi Cetak Struk

### 1. Thermal Printer
- Integrasi dengan `mike42/escpos-php`
- Konfigurasi IP dan port printer
- Fallback ke browser jika printer tidak tersedia

### 2. Browser Print
- Generator PDF menggunakan `barryvdh/laravel-dompdf`
- Tampilan struk yang responsif

### 3. Format Struk
```
BILLIARDPRO
[Alamat dan kontak]
-------------------------
Receipt No: [ID Transaksi]
Date: [Tanggal]  Time: [Waktu]
Cashier: [Nama kasir]
Table: [Nama meja]
-------------------------
Start Time: [Waktu mulai]
End Time: [Waktu selesai]
Duration (Rounded): [Durasi jam]
-------------------------
[Item tambahan jika ada]
Nama Produk x Jumlah
Harga: Rp [Total per item]
-------------------------
Table Rental ([jam] × Rp [tarif])
Rp [Total tarif meja]
-------------------------
Total: Rp [Total keseluruhan]
Payment: [Metode pembayaran]
Received: Rp [Jumlah diterima]
Change: Rp [Kembalian]
-------------------------
Terima kasih!
Barang yang sudah dibeli tidak dapat ditukar/dikembalikan
```

## 📊 Fitur Pelaporan

### 1. Transaksi Selesai
- Disimpan dengan status completed
- Ditampilkan di laporan harian/bulanan/tahunan

### 2. Detail Transaksi
- Waktu mulai/selesai
- Durasi
- Item tambahan
- Metode pembayaran
- Kasir yang menangani

## 🧪 Unit Testing

### Test Proses Pembayaran
```php
public function test_process_payment_completes_transaction()
{
    $table = Table::factory()->create(['hourly_rate' => 10000]);
    $user = User::factory()->create(['role' => 'cashier']);
    $transaction = Transaction::factory()->create([
        'table_id' => $table->id,
        'user_id' => $user->id,
        'status' => 'ongoing',
        'started_at' => now()->subHour(),
        'total' => 10000 // 1 jam @ 10000/jam
    ]);
    
    $this->actingAs($user);
    
    $response = Livewire::test(PaymentProcess::class, ['transaction' => $transaction->id])
        ->set('amountReceived', 15000)
        ->set('paymentMethod', 'cash')
        ->call('processPayment');
    
    $transaction->refresh();
    
    $this->assertEquals('completed', $transaction->status);
    $this->assertEquals(5000, $transaction->change_amount);
    $this->assertEquals(15000, $transaction->cash_received);
    $this->assertEquals('cash', $transaction->payment_method);
    
    $table->refresh();
    $this->assertEquals('available', $table->status);
}

public function test_process_payment_fails_if_insufficient_amount()
{
    $table = Table::factory()->create(['hourly_rate' => 10000]);
    $user = User::factory()->create(['role' => 'cashier']);
    $transaction = Transaction::factory()->create([
        'table_id' => $table->id,
        'user_id' => $user->id,
        'status' => 'ongoing',
        'total' => 10000
    ]);
    
    $response = Livewire::test(PaymentProcess::class, ['transaction' => $transaction->id])
        ->set('amountReceived', 5000) // Kurang dari total
        ->set('paymentMethod', 'cash')
        ->call('processPayment');
    
    $response->assertHasErrors(['amountReceived']);
}
```

## 🔧 Fungsi Tambahan

### 1. Pembatalan Pembayaran
- Kembali ke halaman penambahan item
- Tidak mengubah status transaksi

### 2. Preview Struk
- Tampilkan detail transaksi sebelum cetak
- Tampilkan dalam modal

### 3. Multi-currency (di masa depan)
- Mendukung berbagai mata uang

## 🔄 Update Terbaru

- Perbaikan bug `duration_minutes = 0` dengan fallback calculation
- Validasi `'before_or_equal:now'` untuk mencegah masalah durasi
- Penggunaan `abs()` dalam perhitungan durasi untuk menghindari nilai negatif
- Penanganan mount component yang tidak memperbarui total prematur
- Integrasi thermal printing dengan fallback browser