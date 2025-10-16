# Perhitungan Durasi dalam Sistem Billing BilliardPro

## 📋 Gambaran Umum

Dokumen ini menjelaskan bagaimana sistem menangani perhitungan `duration_minutes` dalam transaksi billing dan berbagai pertimbangan penting yang perlu diperhatikan oleh developer.

## 🧮 Logika Perhitungan Durasi

### Rumus Dasar
```
duration_minutes = |ended_at - started_at|  (menggunakan fungsi abs() untuk mencegah nilai negatif)
total_hours = ceil(duration_minutes / 60)
table_cost = hourly_rate * total_hours
```

### Implementasi dalam Kode
```php
// Dalam model Transaction atau komponen terkait
$minutes = abs($ended_at->diffInMinutes($started_at)); // Gunakan abs() untuk mencegah nilai negatif
$hours = ceil($minutes / 60);
$tableCost = $hourly_rate * $hours;
```

## ⚠️ Masalah yang Sering Terjadi dan Solusi

### 1. started_at di Masa Depan
**Masalah**: `started_at` > `now()` saat `markAsAvailable` dipanggil, menyebabkan `diffInMinutes()` mengembalikan nilai negatif
**Gejala**: `duration_minutes = 0`
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
// app/Livewire/Dashboard/TableGrid.php atau komponen lain
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

### 3. Validasi Mount Component
Jangan panggil `updateTransactionTotal()` dalam `mount()` jika transaksi belum selesai:
```php
// app/Livewire/Transactions/PaymentProcess.php
// Sebelum
$this->updateTransactionTotal();

// Sesudah
if ($this->transaction->status === 'completed' || $this->transaction->ended_at) {
    $this->updateTransactionTotal();
}
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
Log::info('PaymentProcess processPayment: Updating transaction', [
    'transaction_id' => $this->transaction->id,
    'raw_duration_for_fallback' => $rawDuration ?? null,
    'calculated_duration_for_fallback' => $calculatedDuration ?? null,
    'ended_at_for_update' => now(),
]);
```

### 2. Debug Durasi Sebelum dan Sesudah Update
```php
Log::info('PaymentProcess processPayment: Transaction updated', [
    'transaction_id' => $this->transaction->id,
    'duration_minutes_after_update' => $this->transaction->fresh()->duration_minutes,
    'ended_at_after_update' => $this->transaction->fresh()->ended_at,
]);
```

## 🔄 Update Terbaru

**Tanggal Update**: 15-16 Oktober 2025

### Penanganan `duration_minutes` dan `total`

Dalam proses debugging, ditemukan bahwa `duration_minutes` bisa bernilai `0` karena beberapa alasan:

1. **`started_at` di masa depan**: Jika `started_at` transaksi berada di masa depan relatif terhadap `now()`, maka `diffInMinutes()` bisa mengembalikan nilai negatif.

2. **Pemanggilan `updateTransactionTotal()` prematur**: Dalam `PaymentProcess::mount()`, pemanggilan fungsi `updateTransactionTotal()` secara tidak sengaja bisa mengupdate `transaction->total` berdasarkan waktu saat ini, bukan berdasarkan `duration_minutes` yang seharusnya.

**Solusi yang Diterapkan**:
- Validasi `'before_or_equal:now'` ditambahkan di API untuk mencegah `started_at` di masa depan.
- Fungsi `abs()` digunakan dalam perhitungan `diffInMinutes` sebagai workaround.
- `PaymentProcess::mount()` dimodifikasi agar tidak memanggil `updateTransactionTotal()` untuk transaksi yang belum selesai.
- Validasi pembayaran dalam `processPayment()` dimodifikasi untuk menggunakan `total` yang akan diperbarui.

**Pelajaran Penting**:
- Selalu gunakan `abs()` saat melakukan perhitungan selisih waktu untuk menangani bug `diffInMinutes`.
- Hindari pemanggilan fungsi update prematur dalam lifecycle komponen Livewire.
- Validasi waktu input dengan aturan `before_or_equal:now` untuk mencegah data waktu di masa depan.
- Gunakan unit test untuk skenario edge case seperti `duration_minutes = 0`.