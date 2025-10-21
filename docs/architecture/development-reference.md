Berikut adalah **file referensi lengkap** yang dirancang khusus sebagai panduan bagi **AI (seperti saya)** dan **pengembang manusia** untuk membangun dan mengembangkan sistem billing billiard berbasis Laravel. File ini berisi spesifikasi teknis, struktur, aturan, dan contoh — semua dalam format yang mudah dipahami oleh AI maupun developer.

---

# 📄 **REFERENSI PENGEMBANGAN: SISTEM BILLING BILLIARD (BILLIARDPRO)**  
**Versi**: 2.0  
**Framework**: Laravel 11  
**Tujuan**: Panduan teknis & fungsional untuk AI dan developer

---

## 🧩 1. TUJUAN SISTEM
Sistem ini adalah aplikasi web untuk mengelola **billing meja billiard** secara otomatis, mencakup:
- Pelacakan penggunaan meja per jam
- Penambahan item tambahan (minuman/snack)
- Manajemen stok produk
- Perhitungan biaya otomatis (durasi dibulatkan ke atas per jam)
- Pembayaran tunai & pencetakan struk
- Laporan harian, bulanan, dan tahunan
- Antarmuka operator yang intuitif dan visual

> ✅ **Fokus utama**: **Kemudahan penggunaan oleh kasir**, **akurasi perhitungan**, dan **tampilan modern**.

---

## 🗂️ 2. STRUKTUR PROYEK

```
billiardpro/
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Table.php
│   │   ├── Product.php
│   │   ├── Transaction.php
│   │   ├── TransactionItem.php
│   │   ├── Booking.php  // Ditambahkan untuk fitur booking
│   │   └── InventoryTransaction.php
│   └── Livewire/
│       ├── Actions/
│       ├── Bookings/  // Ditambahkan untuk fitur booking
│       │   ├── BookingList.php
│       │   └── CreateBooking.php
│       ├── Dashboard/
│       │   └── TableGrid.php
│       ├── Forms/
│       ├── Layout/
│       ├── Products/
│       ├── Reports/
│       │   ├── DailyReport.php
│       │   ├── MonthlyReport.php
│       │   └── YearlyReport.php
│       ├── Settings/
│       ├── Tables/
│       ├── Transactions/
│       │   ├── AddItems.php
│       │   ├── PaymentProcess.php
│       │   ├── ReceiptPrint.php
│       │   └── StartSession.php
│       └── Users/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   └── livewire/
│   └── css/
│       └── app.css (Tailwind + DaisyUI)
├── routes/
│   └── web.php
├── public/
│   └── receipts/ (opsional: simpan PDF struk)
└── docs/
    └── architecture/
    └── business/
    └── features/
    └── development/
    └── operations/
    └── api/
```

---

## 🗃️ 3. STRUKTUR DATABASE (SCHEMA LENGKAP)

### Tabel: `users`
| Kolom | Tipe | Constraint | Keterangan |
|------|------|-----------|-----------|
| id | bigint | PK | - |
| name | string(255) | - | Nama lengkap |
| email | string(255) | unique | Untuk login |
| password | string(255) | - | Hashed |
| role | enum | `admin`, `cashier` | Role akses |
| created_at | timestamp | - | - |

### Tabel: `tables`
| Kolom | Tipe | Constraint | Keterangan |
|------|------|-----------|-----------|
| id | bigint | PK | - |
| name | string(100) | unique | Contoh: "Meja 1", "VIP A" |
| type | string | `biasa`, `premium`, `vip` | Jenis meja (dari migrasi 2025_10_14_155857) |
| hourly_rate | decimal(10,2) | > 0 | Harga per jam |
| status | enum | `available`, `occupied`, `maintenance`, `reserved` | Default: `available` (dari fitur booking) |
| created_at | timestamp | - | - |

### Tabel: `bookings` (baru - dari fitur booking)
| Kolom | Tipe | Constraint | Keterangan |
|------|------|-----------|-----------|
| id | bigint | PK | - |
| table_id | bigint | FK → tables.id | Meja yang dibooking |
| customer_name | string(255) | - | Nama pelanggan |
| customer_phone | string(20) | nullable | Nomor telepon pelanggan |
| booking_date | date | - | Tanggal booking |
| start_time | time | - | Jam mulai booking |
| end_time | time | - | Jam selesai booking |
| status | enum | `confirmed`, `cancelled`, `completed`, `no_show` | Default: `confirmed` |
| notes | text | nullable | Catatan tambahan |
| created_at | timestamp | - | - |
| updated_at | timestamp | - | - |

### Tabel: `products`
| Kolom | Tipe | Constraint | Keterangan |
|------|------|-----------|-----------|
| id | bigint | PK | - |
| name | string(255) | - | "Es Teh", "Kacang Goreng" |
| price | decimal(10,2) | > 0 | Harga satuan |
| category | string(100) | nullable | Opsional |
| stock_quantity | integer | >= 0 | Jumlah stok produk (dari migrasi 2025_01_01_000001) |
| min_stock_level | integer | >= 0 | Level stok minimum (dari migrasi 2025_01_01_000001) |
| created_at | timestamp | - | - |

### Tabel: `transactions`
| Kolom | Tipe | Constraint | Keterangan |
|------|------|-----------|-----------|
| id | bigint | PK | - |
| table_id | bigint | FK → tables.id | Meja yang dipakai |
| user_id | bigint | FK → users.id | Kasir yang input |
| started_at | timestamp | - | Waktu mulai sesi |
| ended_at | timestamp | nullable | Waktu selesai |
| duration_minutes | int | ≥ 0 | Durasi akhir (arsip) |
| total | decimal(10,2) | ≥ 0 | Total bayar |
| payment_method | string(50) | - | "cash", "qris", dll |
| cash_received | decimal(10,2) | nullable | Jika tunai |
| change_amount | decimal(10,2) | nullable | Kembalian |
| status | enum | `ongoing`, `completed`, `cancelled` | Default: `ongoing` |
| created_at | timestamp | - | - |

### Tabel: `transaction_items`
| Kolom | Tipe | Constraint | Keterangan |
|------|------|-----------|-----------|
| id | bigint | PK | - |
| transaction_id | bigint | FK → transactions.id | - |
| product_id | bigint | FK → products.id | - |
| quantity | int | ≥ 1 | Jumlah item |
| price_per_item | decimal(10,2) | ≥ 0 | Snapshot harga saat transaksi |
| total_price | decimal(10,2) | ≥ 0 | = quantity × price_per_item |

### Tabel: `inventory_transactions` (tambahan)
| Kolom | Tipe | Constraint | Keterangan |
|------|------|-----------|-----------|
| id | bigint | PK | - |
| product_id | bigint | FK → products.id | Produk yang terlibat |
| user_id | bigint | FK → users.id | User yang melakukan perubahan |
| type | enum | `in`, `out` | Jenis perubahan stok |
| quantity | int | > 0 | Jumlah perubahan |
| description | text | nullable | Deskripsi perubahan |
| created_at | timestamp | - | - |

---

## 🔗 4. RELASI MODEL (ELOQUENT)

```php
// User.php
public function transactions() {
    return $this->hasMany(Transaction::class);
}

// Table.php
public function transactions() {
    return $this->hasMany(Transaction::class);
}
public function bookings() {  // Ditambahkan untuk fitur booking
    return $this->hasMany(Booking::class);
}
// Cek ketersediaan untuk booking
public function isAvailableForBooking(string $date, string $startTime, string $endTime, ?int $excludeBookingId = null): bool
{
    $conflictingBooking = $this->bookings()
        ->confirmed()
        ->forDate($date)
        ->where(function ($query) use ($startTime, $endTime) {
            $query->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q) use ($startTime, $endTime) {
                      $q->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                  });
        });
    
    if ($excludeBookingId) {
        $conflictingBooking->where('id', '!=', $excludeBookingId);
    }
    
    return $conflictingBooking->doesntExist();
}
// Cek apakah meja bisa dibooking (available & tidak maintenance)
public function canBeBooked(): bool
{
    return in_array($this->status, ['available', 'reserved']);
}

// Booking.php (model baru untuk fitur booking)
class Booking extends Model
{
    protected $fillable = [
        'table_id',
        'customer_name', 
        'customer_phone',
        'booking_date',
        'start_time',
        'end_time', 
        'status',
        'notes'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Relasi ke meja
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    // Scope helpers
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }
    
    public function scopeForDate($query, $date)
    {
        return $query->where('booking_date', $date);
    }
    
    public function scopeUpcoming($query)
    {
        return $query->where('booking_date', '>=', now()->format('Y-m-d'))
                    ->where('status', 'confirmed')
                    ->orderBy('booking_date')
                    ->orderBy('start_time');
    }

    // Business logic methods
    public function isActive(): bool
    {
        return $this->status === 'confirmed';
    }
    
    public function isOverdue(): bool
    {
        if (!$this->isActive()) return false;
        
        $bookingDateTime = \Carbon\Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->start_time);
        return now()->greaterThan($bookingDateTime->addMinutes(15));
    }
    
    public function markAsNoShow(): void
    {
        $this->update(['status' => 'no_show']);
        $this->table->update(['status' => 'available']);
    }
    
    public function completeBooking(): void
    {
        $this->update(['status' => 'completed']);
    }
    
    public function cancelBooking(): void
    {
        $this->update(['status' => 'cancelled']);
        $this->table->update(['status' => 'available']);
    }
}

// Transaction.php
public function table() {
    return $this->belongsTo(Table::class);
}
public function user() {
    return $this->belongsTo(User::class);
}
public function items() {
    return $this->hasMany(TransactionItem::class);
}
public function handleStockReduction() { // Fungsi tambahan
    // Reduce stock for each item in the transaction
    foreach ($this->items as $item) {
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

// TransactionItem.php
public function transaction() {
    return $this->belongsTo(Transaction::class);
}
public function product() {
    return $this->belongsTo(Product::class);
}

// Product.php
public function transactionItems() {
    return $this->hasMany(TransactionItem::class);
}
public function reduceStock($quantity) { // Fungsi tambahan
    if ($this->stock_quantity < $quantity) {
        throw new \InvalidArgumentException('Insufficient stock for product: ' . $this->name);
    }
    
    $this->update([
        'stock_quantity' => $this->stock_quantity - $quantity
    ]);
}
public function increaseStock($quantity) { // Fungsi tambahan
    $this->update([
        'stock_quantity' => $this->stock_quantity + $quantity
    ]);
}

// InventoryTransaction.php (tambahan)
public function product() {
    return $this->belongsTo(Product::class);
}
public function user() {
    return $this->belongsTo(User::class);
}
```

---

## 🎨 5. ATURAN UI/UX

### Prinsip Desain:
- **Visual-first**: Status meja ditampilkan dalam **grid warna besar**
- **Minimal input**: Gunakan klik, bukan ketik
- **Responsif**: Bisa dipakai di tablet (min-width: 768px)
- **Konsistensi**: Gunakan komponen DaisyUI

### Palet Warna Status Meja:
| Status | Warna Background | Teks |
|-------|------------------|------|
| `available` | `bg-green-500` | Putih |
| `occupied` | `bg-red-500` | Putih |
| `maintenance` | `bg-gray-500` | Putih |
| `reserved` | `bg-yellow-500` | Putih | (baru - dari fitur booking)

### Komponen UI yang Digunakan:
- **Tombol**: `<button class="btn btn-primary">`
- **Modal**: DaisyUI modal + Livewire
- **Notifikasi**: Alpine.js + Tailwind toast
- **Grid**: `grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4`

### Font & Ukuran:
- Font: Inter (default Tailwind)
- Ukuran teks meja: `text-xl` atau `text-2xl`
- Padding: `p-4` pada card meja

---

## ⚙️ 6. LOGIKA BISNIS PENTING

### Perhitungan Durasi:
- Durasi dihitung dari `started_at` ke `now()` (saat transaksi ongoing)
- Saat selesai:  
  ```php
  // Gunakan abs() untuk menghindari nilai negatif dari diffInMinutes
  $minutes = abs($ended_at->diffInMinutes($started_at));
  $hours = ceil($minutes / 60); // dibulatkan ke atas
  $tableCost = $hourly_rate * $hours;
  ```
- Penanganan bug `duration_minutes = 0`: Jika `duration_minutes` masih 0 saat pembayaran, sistem akan menghitung ulang sebagai fallback

### Alur Transaksi:
1. Kasir klik meja dengan status `available`
2. Sistem ubah status meja → `occupied`
3. Buat record `Transaction` dengan `status = ongoing`, `started_at = now()`
4. Selama sesi berlangsung:
   - Tambah item tambahan (minuman/snack) → otomatis mengurangi stok
5. Saat selesai:
   - Hitung durasi & total (dengan fallback jika `duration_minutes = 0`)
   - Input pembayaran
   - Simpan `ended_at`, `duration_minutes`, `total`
   - Ubah status meja → `available`
   - Ubah status transaksi → `completed`
   - Kurangi stok produk yang dibeli
   - Buat record `inventory_transactions` untuk pelacakan stok

### Alur Booking (fitur baru):
1. Kasir buka halaman booking dan pilih tanggal serta jam
2. Sistem tampilkan meja yang tersedia untuk tanggal dan jam tersebut
3. Kasir pilih meja, masukkan info pelanggan, dan buat booking
4. Sistem ubah status meja → `reserved` dan buat record `Booking`
5. Pada waktu booking tiba, kasir bisa lakukan "Check-in":
   - Sistem ubah status booking → `completed`
   - Sistem buat transaksi baru dengan meja tsb
   - Sistem ubah status meja → `occupied`
6. Jika pelanggan tidak datang dalam 15 menit, sistem ubah status booking → `no_show`
   - Sistem ubah status meja → `available`

### Validasi:
- Tidak boleh mulai sesi di meja `occupied` atau `maintenance`
- Tidak boleh booking di meja `maintenance`
- Harga produk & meja harus > 0
- Jumlah item ≥ 1
- `started_at` tidak boleh di masa depan (`before_or_equal:now`)
- Tidak boleh booking jam yang sudah diambil (cek konflik waktu)
- Maksimal booking 7 hari ke depan
- Durasi booking 1-3 jam

### Manajemen Stok:
- Saat pembayaran selesai, stok produk yang dibeli dikurangi otomatis
- Jika stok produk kurang dari jumlah yang ingin dibeli, transaksi akan gagal
- Setiap perubahan stok dicatat di `inventory_transactions`

---

## 🧪 7. ATURAN PENGEMBANGAN (UNTUK DEVELOPER & AI)

### Naming Convention:
- Model: `Transaction`, `Table` (singular)
- Livewire Component: `StartSession`, `TableGrid` (PascalCase)
- Migration: `create_tables_table.php`, `add_role_to_users_table.php`

### Best Practices:
- Jangan hapus data transaksi → hanya ubah status ke `cancelled`
- Simpan snapshot harga (`price_per_item`) di `transaction_items`
- Gunakan Eloquent, hindari query mentah
- Semua input divalidasi di Livewire
- Gunakan `wire:loading` untuk feedback loading
- Gunakan `abs()` saat menghitung durasi untuk mencegah bug `diffInMinutes`
- Tambahkan logging komprehensif untuk debugging

### Testing:
- Minimal: Uji alur end-to-end (mulai → bayar → laporan)
- Gunakan `php artisan make:test` untuk fitur kritis
- Buat unit test untuk skenario edge case seperti `duration_minutes = 0`

### Background Processing:
- Fitur booking memiliki command untuk otomatis membatalkan booking yang terlambat:
  - `php artisan bookings:cancel-overdue` (akan dijadwalkan per menit)
- Command ini akan menandai booking sebagai `no_show` jika pelanggan tidak datang dalam 15 menit dari waktu booking

---

## 📦 8. DEPENDENSI YANG DIGUNAKAN

```json
// package.json (frontend)
{
  "devDependencies": {
    "@tailwindcss/forms": "^0.5.2",
    "alpinejs": "^3.13.0",
    "autoprefixer": "^10.4.2",
    "axios": "^1.7.4",
    "daisyui": "^5.1.29",
    "laravel-vite-plugin": "^1.2.0",
    "postcss": "^8.4.31",
    "tailwindcss": "^3.1.0",
    "vite": "^6.0.11"
  }
}
```

```bash
# composer.json (backend)
- laravel/framework: ^11.31
- livewire/livewire: ^3.6.4
- livewire/volt: ^1.7.0
- mike42/escpos-php: ^2.0 (untuk printer thermal)
- barryvdh/laravel-dompdf: ^3.1 (untuk PDF)
- doctrine/dbal: ^4.3 (untuk migrasi database)
```

---

## 🌐 9. CONTOH ROUTE & HALAMAN

```php
// routes/web.php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [TableGrid::class, 'render'])->name('dashboard');
    Route::get('/tables/manage', TableForm::class)->name('tables.manage');
    Route::get('/reports', DailyReport::class)->name('report.daily');
    Route::get('/reports/monthly', MonthlyReport::class)->name('report.monthly');
    Route::get('/reports/yearly', YearlyReport::class)->name('report.yearly');
    Route::get('/transactions/add-items/{transaction}', AddItems::class)->name('transactions.add-items');
    Route::get('/transactions/payment/{transaction}', PaymentProcess::class)->name('transactions.payment');
    Route::get('/products', ProductList::class)->name('products.list');
    Route::get('/settings', SettingsForm::class)->name('settings.index');
    Route::get('/users', UserList::class)->name('users.list');
    
    // Booking Routes (fitur baru)
    Route::prefix('bookings')->group(function () {
        Route::get('/', \App\Livewire\Bookings\BookingList::class)->name('bookings.index');
        Route::get('/create', \App\Livewire\Bookings\CreateBooking::class)->name('bookings.create');
    });
});
```

Halaman utama:
- `/dashboard` → tampilan grid meja (untuk kasir)
- `/tables/manage` → CRUD meja (admin only)
- `/reports` → laporan harian (admin)
- `/reports/monthly` → laporan bulanan (admin)
- `/reports/yearly` → laporan tahunan (admin)
- `/products` → manajemen produk dan stok
- `/settings` → pengaturan sistem
- `/bookings` → manajemen booking (baru)
- `/bookings/create` → buat booking baru (baru)

---

## 📝 10. CONTOH PROMPT UNTUK AI (SEPerti Saya)

> "Buatkan Livewire component `StartSession` yang:
> - Menerima `tableId`
> - Menampilkan modal konfirmasi
> - Saat dikonfirmasi:
>   - Ubah status meja jadi `occupied`
>   - Buat transaksi baru dengan `started_at = now()`
>   - Redirect ke halaman pembayaran setelah selesai
> - Gunakan Tailwind + DaisyUI
> - Validasi: pastikan meja benar-benar `available`"

---

## 📎 Lampiran: Dokumentasi Terkait

### Dokumentasi Per Fitur
- **Billing**: `docs/features/billing/duration-calculation.md`, `docs/features/billing/session-management.md`, `docs/features/billing/payment-process.md`
- **Inventory**: `docs/features/inventory/stock-management.md`, `docs/features/inventory/product-management.md`
- **Reporting**: `docs/features/reporting/daily-report.md`, `docs/features/reporting/monthly-report.md`, `docs/features/reporting/yearly-report.md`
- **Booking**: `docs/features/booking/booking-system-dev.md`

### Dokumentasi Pengembangan
- **Bug Fixes**: `docs/development/bugfixes/`
- **Testing**: `docs/development/testing/testing-checklist.md`
- **Guides**: `docs/development/guides/`

### Dokumentasi Operasional
- **Setup**: `docs/operations/database-setup.md`
- **Backup**: `docs/operations/backup-command.md`
- **Deployment**: `docs/operations/deployment-guide.md`

---

## ✅ PENUTUP

File ini adalah **satu-satunya sumber kebenaran (single source of truth)** untuk:
- AI yang membantu generate kode
- Developer yang menulis/maintain sistem
- Tester yang membuat skenario uji

Setiap fitur baru **harus merujuk ke dokumen ini** dan dokumen terkait di folder spesifik.

---

## ⚠️ CATATAN PENTING UNTUK PENGEMBANG

### Penanganan `duration_minutes` dan `total`

**Tanggal Update**: 15-16 Oktober 2025

Dalam proses debugging, ditemukan bahwa `duration_minutes` bisa bernilai `0` karena beberapa alasan:

1. **`started_at` di masa depan**: Jika `started_at` transaksi berada di masa depan relatif terhadap `now()`, maka `diffInMinutes()` bisa mengembalikan nilai negatif, yang menyebabkan `duration_minutes = 0` setelah `max(0, intval(...))`.

2. **Pemanggilan `updateTransactionTotal()` prematur**: Dalam `PaymentProcess::mount()`, pemanggilan fungsi `updateTransactionTotal()` secara tidak sengaja bisa mengupdate `transaction->total` berdasarkan waktu saat ini, bukan berdasarkan `duration_minutes` yang seharusnya.

**Solusi yang Diterapkan**:
- Validasi `'before_or_equal:now'` ditambahkan di API untuk mencegah `started_at` di masa depan.
- Fungsi `abs()` digunakan dalam perhitungan `diffInMinutes` sebagai workaround.
- `PaymentProcess::mount()` dimodifikasi agar tidak memanggil `updateTransactionTotal()` untuk transaksi yang belum selesai.
- Validasi pembayaran dalam `processPayment()` dimodifikasi untuk menggunakan `total` yang akan diperbarui.
- Diterapkan fallback untuk menghitung `duration_minutes` jika nilainya masih 0 saat pembayaran.

**Pelajaran Penting**:
- Selalu gunakan `abs()` saat melakukan perhitungan selisih waktu untuk menangani bug `diffInMinutes`.
- Hindari pemanggilan fungsi update prematur dalam lifecycle komponen Livewire.
- Validasi waktu input dengan aturan `before_or_equal:now` untuk mencegah data waktu di masa depan.
- Gunakan unit test untuk skenario edge case seperti `duration_minutes = 0`.

Untuk detail lengkap perbaikan bug ini, lihat:
- `docs/development/bugfixes/bugfix-duration-minutes-zero.md`
- `docs/development/bugfixes/debugging-session-duration-minutes-zero.md`
- `docs/development/guides/developer-guide-duration-minutes.md`
- `docs/development/guides/executive-summary-duration-minutes-fix.md`
- `docs/development/guides/unit-testing-debugging-value.md`

---
