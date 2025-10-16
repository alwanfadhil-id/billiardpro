# Panduan Developer: Penanganan Duration Minutes dalam BilliardPro

## 📋 Gambaran Umum

Dokumen ini menjelaskan bagaimana sistem menangani perhitungan `duration_minutes` dalam transaksi billing dan hal-hal penting yang perlu diperhatikan oleh developer.

## 🧮 Logika Perhitungan Durasi

### Rumus Dasar
```
duration_minutes = ended_at - started_at
total_hours = ceil(duration_minutes / 60)
table_cost = hourly_rate * total_hours
```

### Implementasi dalam Kode
```php
// Dalam model Transaction atau komponen terkait
$minutes = $ended_at->diffInMinutes($started_at);
$hours = ceil($minutes / 60);
$tableCost = $hourly_rate * $hours;
```

## ⚠️ Masalah yang Sering Terjadi

### 1. started_at di Masa Depan
**Masalah**: `started_at` > `now()` saat `markAsAvailable` dipanggil
**Gejala**: `diffInMinutes()` mengembalikan nilai negatif
**Dampak**: `duration_minutes = 0`

**Solusi**:
```php
// app/Livewire/Dashboard/TableGrid.php
$rawDuration = abs(now()->diffInMinutes($ongoingTransaction->started_at));
$duration = max(0, intval($rawDuration));
```

### 2. Validasi started_at
**Masalah**: Input pengguna atau API dapat mengatur `started_at` ke masa depan
**Solusi**: Validasi API
```php
// app/Http/Controllers/Api/TransactionsController.php
'started_at' => 'required|date|before_or_equal:now',
'started_at' => 'sometimes|required|date|before_or_equal:now',
```

## 🔄 Alur Perhitungan Durasi

### 1. Saat Sesi Dimulai (`startSession`)
```php
// app/Livewire/Dashboard/TableGrid.php
Transaction::create([
    'started_at' => now(),
    'status' => 'ongoing',
    'total' => 0,
]);
```

### 2. Saat Sesi Diselesaikan (`markAsAvailable`)
```php
// app/Livewire/Dashboard/TableGrid.php
$rawDuration = abs(now()->diffInMinutes($ongoingTransaction->started_at));
$duration = max(0, intval($rawDuration));
$ratePerHour = $table->hourly_rate;
$ratePerMinute = $ratePerHour / 60;
$total = $duration * $ratePerMinute;

$ongoingTransaction->update([
    'ended_at' => now(),
    'duration_minutes' => $duration,
    'total' => $total
]);
```

### 3. Fallback dalam Pembayaran (`processPayment`)
```php
// app/Livewire/Transactions/PaymentProcess.php
if (intval($this->transaction->duration_minutes) === 0) {
    $rawDuration = abs(now()->diffInMinutes($this->transaction->started_at));
    $calculatedDuration = max(0, intval($rawDuration));
    
    // Hitung total baru
    $table = $this->transaction->table;
    if ($table) {
        $ratePerMinute = $table->hourly_rate / 60;
        $calculatedTotal = $calculatedDuration * $ratePerMinute;
        
        $updateData['duration_minutes'] = $calculatedDuration;
        $updateData['total'] = $calculatedTotal;
    }
}
```

## 🛡️ Pertimbangan Keamanan

### 1. Validasi Input
Selalu validasi `started_at` agar tidak di masa depan:
```php
'started_at' => 'required|date|before_or_equal:now',
```

### 2. Pencegahan Nilai Negatif
Gunakan `abs()` saat menghitung selisih waktu:
```php
$rawDuration = abs(now()->diffInMinutes($started_at));
```

## 🧪 Unit Testing

### Test yang Harus Ada
1. **Validasi API** - Memastikan `started_at` tidak di masa depan
2. **Perhitungan Durasi Normal** - Durasi dihitung dengan benar
3. **Fallback Durasi** - `duration_minutes` dihitung ulang jika `0`
4. **Edge Cases** - Durasi sangat pendek, sangat panjang

### Contoh Test
```php
public function test_process_payment_applies_duration_fallback()
{
    $transaction = Transaction::factory()->create([
        'duration_minutes' => 0,
        'total' => 0,
        'status' => 'ongoing'
    ]);

    // Lakukan pembayaran
    $response = Livewire::test(PaymentProcess::class, ['transaction' => $transaction->id])
        ->set('amountReceived', 15000)
        ->set('paymentMethod', 'cash')
        ->call('processPayment');

    // Pastikan durasi fallback diterapkan
    $transaction->refresh();
    $this->assertGreaterThan(0, $transaction->duration_minutes);
    $this->assertEquals('completed', $transaction->status);
}
```

## 🐛 Debugging Tips

### 1. Logging Informasi Penting
```php
Log::info('Calculating duration', [
    'transaction_id' => $transaction->id,
    'started_at' => $transaction->started_at,
    'ended_at' => now(),
    'raw_duration' => $rawDuration,
    'calculated_duration' => $duration
]);
```

### 2. Memeriksa Nilai Sebelum dan Sesudah Update
```php
Log::info('Transaction before update', [
    'duration_minutes' => $transaction->duration_minutes,
    'total' => $transaction->total
]);

$transaction->update($updateData);

Log::info('Transaction after update', [
    'duration_minutes' => $transaction->fresh()->duration_minutes,
    'total' => $transaction->fresh()->total
]);
```

## ⚠️ Perhatian Khusus

### 1. Lifecycle Komponen Livewire
Hindari pemanggilan fungsi update prematur dalam `mount()`:
```php
// ❌ JANGAN LAKUKAN INI SELALU
public function mount($transaction)
{
    $this->transaction = Transaction::find($transaction);
    $this->updateTransactionTotal(); // <- Bisa menyebabkan bug
}

// ✅ LAKUKAN INI SECARA SELEKTIF
public function mount($transaction)
{
    $this->transaction = Transaction::find($transaction);
    if ($this->transaction->status === 'completed' || $this->transaction->ended_at) {
        $this->updateTransactionTotal();
    }
}
```

### 2. Validasi dalam Pembayaran
Pastikan menggunakan `total` yang akan diperbarui:
```php
// ❌ JANGAN GUNAKAN TOTAL LAMA UNTUK VALIDASI
if ($amountReceived < $transaction->total) {
    // ...
}

// ✅ GUNAKAN TOTAL YANG AKAN DIPERBARUI
$totalToCompare = $transaction->total;
if (isset($updateData['total'])) {
    $totalToCompare = $updateData['total'];
}

if ($amountReceived < $totalToCompare) {
    // ...
}
```

## 📚 Referensi

### File Terkait
- `app/Models/Transaction.php` - Model transaksi
- `app/Livewire/Dashboard/TableGrid.php` - Komponen dashboard
- `app/Livewire/Transactions/PaymentProcess.php` - Komponen pembayaran
- `app/Http/Controllers/Api/TransactionsController.php` - Kontroler API

### Dokumentasi Terkait
- `docs/bugfix-duration-minutes-zero.md` - Dokumentasi lengkap bug fix
- `docs/debugging-session-duration-minutes-zero.md` - Catatan debugging session
- `docs/unit-testing-debugging-value.md` - Nilai unit testing dalam debugging

## 🆘 Troubleshooting

### Gejala: `duration_minutes` tetap 0
**Cek**:
1. Apakah `started_at` di masa depan?
2. Apakah `diffInMinutes()` mengembalikan nilai negatif?
3. Apakah `PaymentProcess::mount()` memanggil `updateTransactionTotal()` secara prematur?
4. Apakah validasi pembayaran menggunakan `total` lama?

### Gejala: Validasi pembayaran gagal
**Cek**:
1. Apakah `transaction->total` sudah diperbarui sebelum validasi?
2. Apakah `totalToCompare` menggunakan nilai yang benar?

### Gejala: Total tidak sesuai
**Cek**:
1. Apakah `hourly_rate` benar?
2. Apakah pembulatan `ceil()` diterapkan dengan benar?
3. Apakah `duration_minutes` dihitung dengan benar?