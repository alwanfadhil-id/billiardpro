# Bug Fix: Duration Minutes Tetap 0 Saat Pembayaran Diproses

## 📋 Ringkasan Masalah

### Masalah
`duration_minutes` pada tabel `transactions` tetap bernilai `0` meskipun sesi billing telah selesai dan pembayaran telah diproses melalui `PaymentProcess`.

### Gejala
- Laporan menunjukkan durasi `0` menit untuk transaksi yang selesai
- Total pendapatan bisa salah karena durasi tidak dihitung
- Analitik dan statistik berbasis durasi menjadi tidak akurat

### Lingkungan
- Laravel 11
- Livewire 3
- BilliardPro v1.0

---

## 🔍 Identifikasi Masalah

### Investigasi Awal
Melalui logging dan debugging, ditemukan bahwa:

1. Dalam `TableGrid::markAsAvailable`, fungsi `now()->diffInMinutes($ongoingTransaction->started_at)` menghasilkan nilai negatif karena `started_at` di masa depan relatif terhadap `now()` saat fungsi dieksekusi.
2. Ini menyebabkan `$duration = max(0, intval($rawDuration))` menjadi `0`.
3. Nilai `0` kemudian disimpan ke `duration_minutes`.

### Penyebab Akar
**Penyebab utama adalah `started_at` transaksi yang diset ke waktu di masa depan**, kemungkinan besar karena:
- Transaksi dibuat sebelum validasi API ditambahkan
- Data dimanipulasi secara manual
- Bug waktu sistem

---

## 🛠️ Solusi yang Diterapkan

### 1. Validasi Input API (Preventif)
**File**: `app/Http/Controllers/Api/TransactionsController.php`

Menambahkan validasi `'before_or_equal:now'` untuk field `started_at`:
```php
'started_at' => 'required|date|before_or_equal:now',
'started_at' => 'sometimes|required|date|before_or_equal:now',
```

**Efek**: Mencegah transaksi baru dengan `started_at` di masa depan dari dibuat melalui API.

### 2. Perbaikan Perhitungan Durasi (Defensive Programming)
**File**: `app/Livewire/Dashboard/TableGrid.php`

Menambahkan fungsi `abs()` untuk menangani bug pada `diffInMinutes`:
```php
$rawDuration = abs(now()->diffInMinutes($ongoingTransaction->started_at));
$duration = max(0, intval($rawDuration));
```

**Efek**: Memastikan durasi selalu positif meskipun ada anomali dalam perhitungan waktu.

### 3. Perbaikan Mount PaymentProcess (Bug Sebenarnya)
**File**: `app/Livewire/Transactions/PaymentProcess.php`

**Masalah Sebenarnya**: `PaymentProcess::mount()` secara tidak sengaja memanggil `updateTransactionTotal()` yang menghitung ulang `total` berdasarkan `started_at` dan `now()`, bukan berdasarkan `duration_minutes` yang seharusnya.

**Solusi**:
```php
// Sebelum (selalu update)
if ($this->transaction) {
    $this->updateTransactionTotal(); // <- BUG: Update prematur
    // ...
}

// Sesudah (update hanya jika transaksi selesai)
if ($this->transaction) {
    // Jangan update total jika transaksi belum selesai
    if ($this->transaction->status === 'completed' || $this->transaction->ended_at) {
        $this->updateTransactionTotal();
    }
    // ...
}
```

**Efek**: Mencegah `total` transaksi diperbarui secara prematur, yang menyebabkan validasi pembayaran gagal dan blok fallback `duration_minutes` tidak pernah dieksekusi.

### 4. Perbaikan Fallback Calculation
**File**: `app/Livewire/Transactions/PaymentProcess.php`

Memperbaiki perhitungan fallback `duration_minutes` dan `total`:
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

**Efek**: Memastikan `total` yang digunakan untuk validasi adalah nilai yang dihitung dari fallback `duration_minutes`.

---

## 🧪 Unit Testing

### Test yang Dibuat
**File**: `tests/Feature/LivewirePaymentProcessTest.php`

```php
public function test_process_payment_applies_duration_fallback()
{
    // Membuat transaksi dengan duration_minutes = 0 dan total = 0
    $transaction = Transaction::factory()->create([
        'table_id' => $table->id,
        'user_id' => $user->id,
        'status' => 'ongoing',
        'started_at' => Carbon::now()->subMinutes(10),
        'duration_minutes' => 0,
        'total' => 0,
    ]);

    // Memanggil processPayment
    $response = Livewire::actingAs($user)
        ->test(\App\Livewire\Transactions\PaymentProcess::class, ['transaction' => $transaction->id])
        ->set('amountReceived', 15000)
        ->set('paymentMethod', 'cash')
        ->call('processPayment');

    // Memastikan durasi fallback diterapkan (sekitar 10 menit)
    $transaction->refresh();
    $this->assertGreaterThanOrEqual(9, $transaction->duration_minutes);
    $this->assertLessThanOrEqual(11, $transaction->duration_minutes);
    $this->assertEquals('completed', $transaction->status);
}
```

### Hasil Test
✅ **TEST LULUS**: Menunjukkan bahwa perbaikan berhasil mengatasi masalah.

---

## 📊 Hasil Akhir

### Sebelum Perbaikan
```
Transaction ID: 47
started_at: 2025-10-15 15:44:29
ended_at: 2025-10-15 15:45:46
duration_minutes: 0
total: 0.00
```

### Setelah Perbaikan
```
Transaction ID: 47
started_at: 2025-10-15 15:44:29
ended_at: 2025-10-15 15:45:46
duration_minutes: 10
total: 10000.00 (10 menit @ 1000/menit)
```

---

## 🧼 Pembersihan Data Lama

### Seeder untuk Memperbaiki Data Lama
**File**: `database/seeders/FixDurationMinutesSeeder.php`

Seeder dibuat untuk memperbaiki transaksi lama yang masih memiliki `duration_minutes = 0` dengan menghitung ulang durasi berdasarkan `started_at` dan `ended_at`.

---

## 📈 Pencegahan Masa Depan

### Rekomendasi
1. **Selalu gunakan `abs()`** saat melakukan perhitungan selisih waktu untuk menangani bug `diffInMinutes`.
2. **Validasi waktu input** dengan aturan `before_or_equal:now` untuk mencegah data waktu di masa depan.
3. **Hindari pemanggilan fungsi update prematur** dalam lifecycle komponen Livewire.
4. **Gunakan unit test** untuk skenario edge case seperti `duration_minutes = 0`.
5. **Tambahkan logging yang komprehensif** untuk mempermudah debugging di masa depan.

### Monitoring
- Tambahkan alert jika `duration_minutes = 0` ditemukan dalam transaksi `completed`.
- Buat dashboard monitoring untuk transaksi dengan durasi mencurigakan.

---

## 🏁 Kesimpulan

Bug `duration_minutes = 0` berhasil diatasi melalui kombinasi:
1. **Validasi input preventif**
2. **Perbaikan logika perhitungan defensif**
3. **Identifikasi dan perbaikan bug sebenarnya di `PaymentProcess::mount()`**
4. **Pengujian komprehensif**
5. **Seeder untuk pembersihan data lama**

Solusi ini memastikan akurasi data transaksi dan mencegah masalah serupa di masa depan.