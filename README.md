# BilliardPro - Sistem Billing Billiard

<p align="center">
  <img src="https://laravel.com/assets/img/components/logo-laravel.svg" width="400" alt="Laravel Logo">
</p>

<p align="center">
Sistem billing dan manajemen billiard otomatis berbasis web dengan Laravel 11
</p>

## 📋 Daftar Isi

- [Deskripsi](#deskripsi)
- [Fitur Utama](#fitur-utama)
- [Tabel Konten](#tabel-konten)
- [Prasyarat Sistem](#prasyarat-sistem)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Struktur Database](#struktur-database)
- [Penggunaan](#penggunaan)
- [Testing](#testing)
- [Kontak Pengembang](#kontak-pengembang)
- [Lisensi](#lisensi)

## 📖 Deskripsi

BilliardPro adalah sistem billing dan manajemen billiard berbasis web yang dibangun dengan Laravel 11 dan Livewire. Sistem ini dirancang untuk mengotomatiskan proses pemesanan meja, perhitungan tarif per jam, penjualan item tambahan (minuman/snack), dan pelaporan penjualan harian.

Sistem ini memiliki antarmuka yang intuitif dan visual dengan dukungan mode gelap, serta dirancang untuk digunakan di perangkat tablet selama jam operasional.

## ⭐ Fitur Utama

### Fitur Manajemen Meja
- 📊 Tampilan grid meja dengan status warna real-time (tersedia/habis/dipakai)
- ⏰ Perhitungan durasi otomatis dengan pembulatan ke atas per jam
- 💰 Tarif per jam dapat dikonfigurasi per meja
- 🛠️ Status perawatan untuk meja yang sedang diperbaiki

### Fitur Transaksi
- 💸 Proses pembayaran tunai dengan kalkulasi kembalian otomatis
- 🛒 Penambahan item tambahan (minuman/snack) ke transaksi
- 🧾 Generasi struk otomatis setelah pembayaran
- 🔐 Sistem otorisasi role-based (admin/kasir)

### Fitur Laporan
- 📈 Laporan penjualan harian
- 📊 Statistik penggunaan meja
- 💰 Analisis pendapatan
- 📥 Ekspor data dalam format CSV/PDF

### Fitur Tambahan
- 🌙 Mode gelap untuk kenyamanan visual
- 📱 Responsif dan mendukung penggunaan tablet
- 🔐 Sistem autentikasi aman dengan Laravel Breeze
- 📁 Sistem backup database bawaan

## 🗂️ Struktur Proyek

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

## 🛠️ Struktur Database

### Tabel Utama
- `users`: Manajemen pengguna (admin/kasir)
- `tables`: Informasi meja dan tarif per jam
- `products`: Item tambahan (minuman/snack)
- `transactions`: Detail transaksi billing
- `transaction_items`: Item tambahan dalam transaksi

### Relasi Antar Tabel
- `transactions` → `tables` (membuka-banyak)
- `transactions` → `users` (membuka-banyak)
- `transaction_items` → `transactions` (membuka-banyak)
- `transaction_items` → `products` (membuka-banyak)

## 🧰 Prasyarat Sistem

- PHP >= 8.2
- Composer
- Database (MySQL 5.7+, PostgreSQL, SQLite)
- Node.js & NPM
- Web server (Apache/Nginx)

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd billiardpro
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Konfigurasi Lingkungan
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi Database
Sesuaikan konfigurasi database di file `.env`:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Jalankan Migrasi & Seeder
```bash
php artisan migrate --seed
```

### 6. Build Assets
```bash
npm run build
# atau untuk development
npm run dev
```

### 7. Jalankan Aplikasi
```bash
php artisan serve
```

## ⚙️ Konfigurasi

### Konfigurasi Database
BilliardPro mendukung berbagai jenis database:
- MySQL (default)
- PostgreSQL
- SQLite
- SQL Server

Lihat contoh konfigurasi di `docs/database-config.example`.

### Konfigurasi Thermal Printer (Opsional)
Untuk fitur cetak struk thermal, install dependensi:
```bash
composer require mike42/escpos-php
```

## 👥 Role Pengguna

### Admin
- Akses semua fitur sistem
- Manajemen meja
- Laporan lengkap
- Manajemen produk
- Manajemen pengguna

### Kasir
- Dashboard utama
- Mulai/akhiri sesi meja
- Proses pembayaran
- Tambah item tambahan

## 🧪 Testing

Sistem ini memiliki komprehensif unit dan feature testing:

### Jalankan Testing
```bash
# Unit dan Feature Tests
php artisan test

# Atau dengan verbose output
php artisan test --verbose
```

### Coverage Testing
Sistem mencakup testing untuk:
- Validasi input data
- Alur transaksi lengkap
- Otorisasi role-based
- Fungsi perhitungan biaya
- Manajemen status meja

## 📊 Fitur Penggunaan

### Dashboard
- Tampilan grid meja real-time
- Warna status meja (hijau=tersedia, merah=dipakai, abu=maintenance)
- Durasi dan biaya otomatis terupdate
- Akses cepat ke fitur penting

### Manajemen Meja
- CRUD meja dengan tarif per jam
- Filter dan pencarian meja
- Status meja (tersedia/dipakai/maintenance)

### Proses Transaksi
1. Klik meja tersedia untuk mulai sesi
2. Sistem menghitung durasi dan biaya otomatis
3. Tambah item tambahan (minuman/snack) jika diperlukan
4. Proses pembayaran tunai
5. Cetak struk setelah pembayaran

### Laporan
- Laporan harian real-time
- Chart tren pendapatan
- Ekspor data (CSV/Excel/PDF)
- Filter berdasarkan tanggal

## 📋 Testing Checklist

### Unit Tests
- [x] Method `calculateTotal()` di model Transaction → 1 menit durasi dibulatkan ke 1 jam
- [x] Method `calculateTotal()` di model Transaction → 61 menit durasi dibulatkan ke 2 jam  
- [x] Method `calculateTotal()` di model Transaction → dengan item tambahan
- [x] Validasi hourly_rate tidak boleh negatif → pengujian dengan nilai -10000 gagal sesuai ekspektasi
- [x] Validasi hourly_rate tidak boleh nol → pengujian dengan nilai 0 gagal sesuai ekspektasi
- [x] Validasi hourly_rate positif diterima → pengujian dengan nilai positif berhasil

### Feature Tests - Transaksi
- [x] Alur transaksi lengkap: login kasir → mulai sesi di meja available → tambah item → bayar → cek status meja jadi available dan transaksi tersimpan
- [x] Alur transaksi dengan durasi berbeda (90 menit → 2 jam billing)

### Feature Tests - Keamanan
- [x] Route `/tables/manage` hanya bisa diakses admin → cashier tidak bisa akses (403 Forbidden)
- [x] Route `/tables/manage` hanya bisa diakses admin → unauthenticated user redirect ke login
- [x] Admin bisa akses route `/tables/manage` → akses diperbolehkan
- [x] Admin bisa melakukan tindakan manajemen meja → akses diperbolehkan
- [x] Cashier tidak bisa melakukan tindakan manajemen meja → akses ditolak

## 📞 Kontak Pengembang

**Alwan Fadhil**  
📧 Email: alwanfadhil@hotmail.com  
📱 WhatsApp: +62 822-8858-3033  
🐱 GitHub: [alwanfadhil-id](https://github.com/alwanfadhil-id)

## 🔒 Keamanan

- Validasi input yang ketat
- Otorisasi role-based untuk semua fitur
- Session management Laravel bawaan
- Enkripsi password otomatis
- CSRF protection

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).

## 🤝 Kontribusi

Kontribusi sangat dihargai! Silakan buat issue atau pull request untuk perbaikan dan fitur baru.

## 📞 Dukungan

Untuk bantuan teknis atau pertanyaan lebih lanjut, silakan hubungi pengembang melalui informasi kontak di atas.

---

Dibuat dengan 💙 menggunakan Laravel 11 & Livewire  
Pengembang: Alwan Fadhil