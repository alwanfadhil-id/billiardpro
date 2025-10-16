# Pentingnya Unit Testing dalam Debugging Bug Kompleks

## 📖 Pendahuluan

Unit testing bukan hanya tentang memastikan bahwa kode kita bekerja sesuai ekspektasi. Dalam proyek nyata seperti BilliardPro, **unit testing terbukti menjadi alat yang sangat efektif untuk menemukan dan memperbaiki bug yang kompleks dan tersembunyi**.

Dokumen ini menjelaskan bagaimana proses unit testing membantu mengidentifikasi dan memperbaiki bug `duration_minutes = 0` yang sebenarnya disebabkan oleh masalah di `PaymentProcess::mount()`.

## 🔍 Kasus Nyata: Bug `duration_minutes = 0`

### Masalah Awal
`duration_minutes` tetap `0` dalam `PaymentProcess::processPayment` meskipun seharusnya dihitung ulang sebagai fallback.

### Hipotesis Awal
Tim pengembang berasumsi bahwa masalahnya ada di perhitungan `duration_minutes` itu sendiri dalam `TableGrid::markAsAvailable` atau `PaymentProcess::processPayment`.

### Proses Debugging dengan Unit Testing

#### Langkah 1: Membuat Test untuk Validasi API
```php
// tests/Feature/ApiTransactionsControllerTest.php
public function test_store_fails_with_future_started_at()
{
    // Test apakah API menolak started_at di masa depan
    // HASIL: Test LULUS - Validasi API berfungsi
}
```

**Kesimpulan**: Validasi API sudah benar. Masalah bukan di sini.

#### Langkah 2: Membuat Test untuk Alur Utama
```php
// tests/Feature/LivewireTableGridTest.php
public function test_mark_as_available_calculates_duration_correctly()
{
    // Test apakah markAsAvailable menghitung duration_minutes dengan benar
    // HASIL: Test GAGAL - duration_minutes tetap 0
}
```

**Kesimpulan**: Ada masalah dalam perhitungan `duration_minutes` di `markAsAvailable`.

#### Langkah 3: Menambahkan Logging untuk Investigasi
```php
// app/Livewire/Dashboard/TableGrid.php
Log::info('TableGrid markAsAvailable: Updating transaction', [
    'transaction_id' => $ongoingTransaction->id,
    'raw_duration' => $rawDuration, // -1.298...
    'calculated_duration' => $duration, // 0
]);
```

**Kesimpulan**: `rawDuration` negatif karena `started_at` di masa depan. Ini mengarah ke pemahaman bahwa `diffInMinutes` bermasalah.

#### Langkah 4: Menerapkan `abs()` sebagai Workaround
```php
$rawDuration = abs(now()->diffInMinutes($ongoingTransaction->started_at));
```

**Kesimpulan**: Ini memperbaiki perhitungan `duration_minutes` di `markAsAvailable`, tetapi **test untuk `PaymentProcess` masih gagal**.

#### Langkah 5: Membuat Test untuk PaymentProcess
```php
// tests/Feature/LivewirePaymentProcessTest.php
public function test_process_payment_applies_duration_fallback()
{
    // Test apakah processPayment menghitung ulang duration_minutes jika 0
    // HASIL: Test GAGAL - duration_minutes tetap 0
}
```

**Kesimpulan**: `processPayment` tidak mengeksekusi blok fallback. Ada sesuatu yang menyebabkan validasi pembayaran gagal.

#### Langkah 6: Menambahkan Logging Lebih Detail
```php
// app/Livewire/Transactions/PaymentProcess.php
Log::info('PaymentProcess processPayment: Checking amount received', [
    'amount_received' => $this->amountReceived, // 15000
    'transaction_total_old' => $this->transaction->total, // 60000.00
    'is_amount_less_than_total' => $this->amountReceived < $this->transaction->total, // TRUE
]);
```

**Kesimpulan**: `transaction->total` adalah `60000.00`, bukan `0`. Ini menjelaskan mengapa validasi pembayaran gagal.

#### Langkah 7: Mencari Penyebab `transaction->total = 60000.00`
Investigasi lebih lanjut menemukan bahwa `PaymentProcess::mount()` memanggil `updateTransactionTotal()` yang menghitung ulang `total` berdasarkan `started_at` dan `now()`.

#### Langkah 8: Memperbaiki `PaymentProcess::mount()`
```php
// Sebelum
$this->updateTransactionTotal();

// Sesudah
if ($this->transaction->status === 'completed' || $this->transaction->ended_at) {
    $this->updateTransactionTotal();
}
```

**Kesimpulan**: Menemukan dan memperbaiki bug sebenarnya.

#### Langkah 9: Memperbaiki Validasi dalam `processPayment`
```php
// Gunakan total yang akan diperbarui, bukan total lama
$totalToCompare = $this->transaction->total;
if (isset($updateData['total'])) {
    $totalToCompare = $updateData['total'];
}
```

#### Langkah 10: Memastikan Test Lulus
Setelah semua perbaikan diterapkan:
✅ **SEMUA TEST LULUS**

## 💡 Pelajaran yang Dipelajari

### 1. **Unit Testing Mengarahkan Fokus Debugging**
Daripada menebak-nebak, unit testing memberikan indikasi yang jelas tentang **di mana** masalah terjadi:
- API validation ✅
- `TableGrid::markAsAvailable` ❌
- `PaymentProcess::processPayment` ❌

### 2. **Test Membongkar Kebenaran yang Tersembunyi**
Awalnya, tim berpikir masalah ada di `processPayment`. Tapi test menunjukkan bahwa `markAsAvailable` juga bermasalah, yang membawa ke penemuan bahwa `diffInMinutes` mengembalikan nilai negatif.

### 3. **Test Mencegah Regresi**
Dengan test yang ada, kita bisa yakin bahwa perubahan yang kita buat tidak merusak fungsionalitas lain.

### 4. **Test Memaksa Pemikiran yang Komprehensif**
Menulis test memaksa developer untuk mempertimbangkan berbagai skenario edge-case, termasuk:
- `started_at` di masa depan
- `duration_minutes = 0`
- `total = 0`
- Validasi pembayaran yang gagal

### 5. **Test Mempercepat Proses Debugging**
Tanpa test, proses debugging bisa memakan waktu berjam-jam karena harus:
- Membuat transaksi manual di UI
- Memeriksa database
- Memeriksa log
- Menebak-nebak

Dengan test, semua ini dilakukan secara otomatis dalam hitungan detik.

## 🛠️ Praktik Terbaik yang Dipelajari

### 1. **Tulis Test untuk Skema Alur Utama**
```php
public function test_complete_transaction_flow()
{
    // Test alur lengkap: mulai → tambah item → bayar → laporan
}
```

### 2. **Tulis Test untuk Skema Validasi**
```php
public function test_validation_rules()
{
    // Test setiap aturan validasi penting
    // - started_at tidak boleh di masa depan
    // - hourly_rate > 0
    // - quantity >= 1
}
```

### 3. **Tulis Test untuk Fallback Logic**
```php
public function test_fallback_calculation()
{
    // Test apa yang terjadi jika data awal tidak sempurna
    // - duration_minutes = 0
    // - total = 0
    // - ended_at = null
}
```

### 4. **Gunakan Logging dalam Test untuk Debugging**
```php
Log::info('TEST: Creating transaction with started_at', [
    'fiveMinutesAgo' => $fiveMinutesAgo->toISOString(),
]);
```

### 5. **Mock External Dependencies**
```php
// Jika ada panggilan ke API eksternal atau service, mock mereka
$this->mock(PrinterService::class, function ($mock) {
    $mock->shouldReceive('printReceipt')->once();
});
```

## 📈 Manfaat Jangka Panjang

### 1. **Kepercayaan pada Kode**
Dengan suite test yang komprehensif, developer bisa yakin bahwa perubahan yang mereka buat tidak merusak sistem.

### 2. **Dokumentasi Hidup**
Test berfungsi sebagai dokumentasi bagaimana sistem seharusnya berperilaku dalam berbagai skenario.

### 3. **Pencegahan Bug di Masa Depan**
Setiap bug yang ditemukan dan diperbaiki sebaiknya memiliki test yang sesuai untuk mencegah regresi.

### 4. **Onboarding Developer Baru**
Developer baru bisa membaca test untuk memahami bagaimana sistem bekerja.

## 🏁 Kesimpulan

Proses debugging bug `duration_minutes = 0` ini menunjukkan betapa **pentingnya unit testing** dalam pengembangan perangkat lunak:

1. **Mengarahkan fokus debugging** ke area yang tepat
2. **Membongkar kebenaran yang tersembunyi** yang tidak terlihat dengan mata telanjang
3. **Mempercepat proses debugging** secara signifikan
4. **Mencegah regresi** di masa depan
5. **Menjadi dokumentasi hidup** bagi sistem

Investasi waktu dalam menulis unit test **jauh lebih kecil** dibandingkan waktu yang dihabiskan untuk debugging manual dan memperbaiki bug yang sama berulang kali.

> **"Write tests, not just code. Your future self will thank you."**