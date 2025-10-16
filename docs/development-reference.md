Berikut adalah **file referensi lengkap** yang dirancang khusus sebagai panduan bagi **AI (seperti saya)** dan **pengembang manusia** untuk membangun dan mengembangkan sistem billing billiard berbasis Laravel. File ini berisi spesifikasi teknis, struktur, aturan, dan contoh — semua dalam format yang mudah dipahami oleh AI maupun developer.

---

# 📄 **REFERENSI PENGEMBANGAN: SISTEM BILLING BILLIARD (BILLIARDPRO)**  
**Versi**: 1.0  
**Framework**: Laravel 11  
**Tujuan**: Panduan teknis & fungsional untuk AI dan developer

---

## 🧩 1. TUJUAN SISTEM
Sistem ini adalah aplikasi web untuk mengelola **billing meja billiard** secara otomatis, mencakup:
- Pelacakan penggunaan meja per jam
- Penambahan item tambahan (minuman/snack)
- Perhitungan biaya otomatis (durasi dibulatkan ke atas per jam)
- Pembayaran tunai & pencetakan struk
- Laporan harian
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
│   │   └── TransactionItem.php
│   └── Livewire/
│       ├── Dashboard/
│       │   └── TableGrid.php
│       ├── Tables/
│       │   └── TableForm.php
│       ├── Transactions/
│       │   ├── StartSession.php
│       │   ├── AddItems.php
│       │   └── PaymentProcess.php
│       └── Reports/
│           └── DailyReport.php
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
    ├── BRD.md
    └── ERD.md
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
| hourly_rate | decimal(10,2) | > 0 | Harga per jam |
| status | enum | `available`, `occupied`, `maintenance` | Default: `available` |
| created_at | timestamp | - | - |

### Tabel: `products`
| Kolom | Tipe | Constraint | Keterangan |
|------|------|-----------|-----------|
| id | bigint | PK | - |
| name | string(255) | - | "Es Teh", "Kacang Goreng" |
| price | decimal(10,2) | > 0 | Harga satuan |
| category | string(100) | nullable | Opsional |
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
  $minutes = $ended_at->diffInMinutes($started_at);
  $hours = ceil($minutes / 60); // dibulatkan ke atas
  $tableCost = $hourly_rate * $hours;
  ```

### Alur Transaksi:
1. Kasir klik meja dengan status `available`
2. Sistem ubah status meja → `occupied`
3. Buat record `Transaction` dengan `status = ongoing`, `started_at = now()`
4. Saat selesai:
   - Hitung durasi & total
   - Tambah item (jika ada)
   - Input pembayaran
   - Simpan `ended_at`, `duration_minutes`, `total`
   - Ubah status meja → `available`
   - Ubah status transaksi → `completed`

### Validasi:
- Tidak boleh mulai sesi di meja `occupied` atau `maintenance`
- Harga produk & meja harus > 0
- Jumlah item ≥ 1

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

### Testing:
- Minimal: Uji alur end-to-end (mulai → bayar → laporan)
- Gunakan `php artisan make:test` untuk fitur kritis

---

## 📦 8. DEPENDENSI YANG DIGUNAKAN

```json
// package.json (frontend)
{
  "devDependencies": {
    "alpinejs": "^3.13.0",
    "autoprefixer": "^10.4.19",
    "daisyui": "^4.10.1",
    "laravel-vite-plugin": "^1.0",
    "postcss": "^8.4.38",
    "tailwindcss": "^3.4.3",
    "vite": "^5.0"
  }
}
```

```bash
# composer.json (backend)
- laravel/framework: ^11.0
- livewire/livewire: ^3.0
- (opsional) mike42/escpos-php: untuk printer thermal
```

---

## 🌐 9. CONTOH ROUTE & HALAMAN

```php
// routes/web.php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [TableGrid::class, 'render'])->name('dashboard');
    Route::get('/tables/manage', TableForm::class)->name('tables.manage');
    Route::get('/transactions/report', DailyReport::class)->name('report.daily');
});
```

Halaman utama:
- `/dashboard` → tampilan grid meja (untuk kasir)
- `/tables/manage` → CRUD meja (admin only)
- `/transactions/report` → laporan harian (admin)

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

## 📎 Lampiran: Template Dokumen

### `docs/BRD.md`
```md
# Business Requirement Document

- Nama Tempat: [Isi]
- Jumlah Meja: [Isi]
- Tarif: [Isi]
- Layanan Tambahan: [Daftar]
- Metode Bayar: [Tunai/QRIS]
- Printer: [Thermal/A4]
```

### `docs/ERD.md`
> Gunakan dbdiagram.io dengan skema di bagian 3.

---

## ✅ PENUTUP

File ini adalah **satu-satunya sumber kebenaran (single source of truth)** untuk:
- AI yang membantu generate kode
- Developer yang menulis/maintain sistem
- Tester yang membuat skenario uji

Setiap fitur baru **harus merujuk ke dokumen ini**.

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

**Pelajaran Penting**:
- Selalu gunakan `abs()` saat melakukan perhitungan selisih waktu untuk menangani bug `diffInMinutes`.
- Hindari pemanggilan fungsi update prematur dalam lifecycle komponen Livewire.
- Validasi waktu input dengan aturan `before_or_equal:now` untuk mencegah data waktu di masa depan.
- Gunakan unit test untuk skenario edge case seperti `duration_minutes = 0`.

Untuk detail lengkap perbaikan bug ini, lihat:
- `docs/bugfix-duration-minutes-zero.md`
- `docs/debugging-session-duration-minutes-zero.md`
- `docs/unit-testing-debugging-value.md`
- `docs/developer-guide-duration-minutes.md`
- `docs/executive-summary-duration-minutes-fix.md`

---
