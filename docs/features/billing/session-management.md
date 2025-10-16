# Manajemen Sesi dalam Sistem Billing

## 📋 Gambaran Umum

Manajemen sesi adalah komponen kritis dalam sistem billing BilliardPro yang bertanggung jawab atas pelacakan penggunaan meja dari awal hingga akhir sesi.

## 🔄 Alur Manajemen Sesi

### 1. Memulai Sesi
```
Kasir memilih meja available → Validasi status meja → 
Ubah status meja ke occupied → Buat transaksi baru → 
Simpan started_at dengan waktu sekarang → Tampilkan durasi real-time
```

### 2. Selama Sesi Berlangsung
- Durasi dihitung secara real-time dari started_at ke waktu sekarang
- Biaya sementara dihitung berdasarkan durasi dan hourly_rate
- Kasir dapat menambahkan item tambahan ke transaksi
- Status meja tetap occupied di dashboard

### 3. Mengakhiri Sesi
```
Kasir menyelesaikan sesi → Hitung durasi akhir → 
Simpan ended_at dan duration_minutes → Hitung total akhir → 
Ubah status meja ke available → Proses pembayaran
```

## 🛠️ Komponen Terlibat

### 1. TableGrid Component
- Menampilkan status meja secara visual
- Menangani permulaan dan pengakhiran sesi
- Memperbarui status meja secara real-time

### 2. Transaction Model
- Menyimpan informasi sesi (started_at, ended_at, duration_minutes)
- Menghitung total biaya
- Mengelola status transaksi

### 3. AddItems Component
- Menambahkan item tambahan selama sesi
- Memperbarui total transaksi secara live

## 🧮 Logika Perhitungan Durasi

### Selama Sesi Berlangsung
```php
// Dalam AddItems component untuk menampilkan durasi real-time
public function getDurationForTable($table)
{
    $ongoingTransaction = $table->transactions->first();
    
    if ($ongoingTransaction) {
        $startedAt = $ongoingTransaction->started_at;
        $now = now();
        $minutes = $startedAt->diffInMinutes($now);
        
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;
        
        return [
            'hours' => $hours,
            'minutes' => $remainingMinutes,
            'total_minutes' => $minutes
        ];
    }
    
    return [
        'hours' => 0,
        'minutes' => 0,
        'total_minutes' => 0
    ];
}
```

### Saat Mengakhiri Sesi
```php
// Dalam TableGrid::markAsAvailable
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

## ⚠️ Edge Cases dan Penanganannya

### 1. started_at di Masa Depan
- **Masalah**: Waktu sistem tidak sinkron atau kesalahan input
- **Solusi**: Gunakan fungsi `abs()` untuk mencegah nilai durasi negatif
- **Validasi**: Tambahkan `'before_or_equal:now'` untuk validasi input

### 2. Perhitungan Durasi Zero
- **Masalah**: `duration_minutes` menjadi 0 karena berbagai alasan
- **Solusi**: Gunakan fallback calculation dalam PaymentProcess

### 3. Sesi Terputus
- **Masalah**: Sistem down selama sesi berlangsung
- **Solusi**: Validasi dan penyesuaian manual jika diperlukan

## 🛡️ Validasi dan Keamanan

### 1. Validasi Status Meja
- Tidak bisa mulai sesi di meja occupied atau maintenance
- Validasi dilakukan di level UI dan backend

### 2. Validasi Waktu
- started_at tidak boleh di masa depan
- ended_at tidak boleh sebelum started_at

### 3. Pembatasan Akses
- Hanya kasir yang bisa memulai/mengakhiri sesi
- Validasi role dilakukan sebelum eksekusi

## 📊 Monitoring Sesi

### Sesi Aktif
- Ditampilkan di dashboard dengan durasi real-time
- Bisa difilter dan dicari

### Riwayat Sesi
- Disimpan dalam tabel transactions
- Bisa diakses melalui laporan

## 🧪 Unit Testing

### Test Manajemen Sesi
```php
public function test_start_session_creates_transaction()
{
    $table = Table::factory()->create(['status' => 'available']);
    $user = User::factory()->create(['role' => 'cashier']);
    
    $this->actingAs($user);
    
    $response = $this->post('/api/tables/' . $table->id . '/start-session');
    
    $response->assertStatus(200);
    
    $this->assertDatabaseHas('transactions', [
        'table_id' => $table->id,
        'user_id' => $user->id,
        'status' => 'ongoing'
    ]);
}

public function test_cannot_start_session_on_occupied_table()
{
    $table = Table::factory()->create(['status' => 'occupied']);
    $user = User::factory()->create(['role' => 'cashier']);
    
    $this->actingAs($user);
    
    $response = $this->post('/api/tables/' . $table->id . '/start-session');
    
    $response->assertStatus(422); // Unprocessable Entity
}
```

## 🔧 Fungsi Tambahan

### 1. Mark as Maintenance
- Mengakhiri sesi jika ada dan mengubah status meja

### 2. Manual Duration Adjustment
- (Fitur opsional) Untuk perbaikan manual jika diperlukan

## 🔄 Update Terbaru

- Implementasi fallback calculation untuk mengatasi `duration_minutes = 0`
- Penggunaan `abs()` dalam perhitungan durasi untuk menghindari nilai negatif
- Validasi `'before_or_equal:now'` untuk mencegah waktu di masa depan
- Penanganan kasus edge dalam lifecycle sesi