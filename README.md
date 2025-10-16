# BilliardPro - Sistem Billing Billiard

<p align="center">
  <img src="https://laravel.com/assets/img/components/logo-laravel.svg" width="400" alt="Laravel Logo">
</p>

<p align="center">
Sistem billing dan manajemen billiard otomatis berbasis web dengan Laravel 11
</p>

## 📚 Daftar Isi

- [Dokumentasi Penting](#-dokumentasi-penting)
  - [Dokumentasi Utama](#dokumentasi-utama)
  - [Dokumentasi Teknis Spesifik](#dokumentasi-teknis-spesifik)
  - [Panduan Operasional](#panduan-operasional)
- [Fitur Utama](#-fitur-utama)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
  - [1. Clone Repository](#1-clone-repository)
  - [2. Instal Dependensi](#2-instal-dependensi)
  - [3. Konfigurasi Environment](#3-konfigurasi-environment)
  - [4. Generate Application Key](#4-generate-application-key)
  - [5. Migrasi Database](#5-migrasi-database)
  - [6. Seeding Data Awal](#6-seeding-data-awal)
  - [7. Instalasi Frontend](#7-instalasi-frontend)
  - [8. Menjalankan Server Lokal](#8-menjalankan-server-lokal)
- [Arsitektur Sistem](#-arsitektur-sistem)
  - [Struktur Folder Utama](#struktur-folder-utama)
  - [Komponen Utama](#komponen-utama)
- [Alur Kerja Utama](#-alur-kerja-utama)
  - [1. Manajemen Meja](#1-manajemen-meja)
  - [2. Alur Transaksi](#2-alur-transaksi)
  - [3. Pembayaran](#3-pembayaran)
  - [4. Laporan](#4-laporan)
- [Testing](#-testing)
  - [Menjalankan Unit Test](#menjalankan-unit-test)
  - [Menjalankan Feature Test](#menjalankan-feature-test)
- [Troubleshooting](#-troubleshooting)
  - [Masalah Umum](#masalah-umum)
  - [Masalah Database](#masalah-database)
- [Kontribusi](#-kontribusi)
- [Lisensi](#-lisensi)

## 📚 Dokumentasi Penting

Untuk memahami sistem secara menyeluruh, silakan baca dokumentasi berikut:

### Dokumentasi Utama

- [Business Requirement Document (BRD)](./docs/BRD.md) - Spesifikasi bisnis lengkap
- [Entity Relationship Diagram (ERD)](./docs/ERD.dbml) - Diagram relasi database
- [Development Reference](./docs/development-reference.md) - Referensi teknis utama untuk pengembangan
- [Implementation Analysis](./docs/IMPLEMENTATION_ANALYSIS.md) - Analisis implementasi saat ini
- [Testing Checklist](./docs/testing-checklist.md) - Checklist pengujian sistem

### Dokumentasi Teknis Spesifik

Dokumentasi berikut menjelaskan penanganan bug kompleks `duration_minutes = 0`:

- [Executive Summary](./docs/executive-summary-duration-minutes-fix.md) - Ringkasan eksekutif untuk manajemen
- [Debugging Session](./docs/debugging-session-duration-minutes-zero.md) - Catatan lengkap sesi debugging
- [Bug Fix Details](./docs/bugfix-duration-minutes-zero.md) - Detail teknis perbaikan bug
- [Developer Guide](./docs/developer-guide-duration-minutes.md) - Panduan untuk developer
- [Unit Testing Value](./docs/unit-testing-debugging-value.md) - Nilai unit testing dalam debugging

### Panduan Operasional

- [Database Setup](./docs/database-setup.md) - Panduan setup database
- [Database Configuration Example](./docs/database-config.example) - Contoh konfigurasi database
- [Database Backup Command](./docs/backup-command.md) - Dokumentasi perintah backup database

## 🎯 Fitur Utama

Sistem BilliardPro menyediakan berbagai fitur untuk memudahkan manajemen bisnis billiard:

### Untuk Kasir
- **Dashboard Visual**: Tampilan grid meja dengan status warna (hijau=tersedia, merah=dipakai, abu=maintenance)
- **Manajemen Sesi**: Mulai dan akhiri sesi bermain billiard dengan satu klik
- **Perhitungan Otomatis**: Durasi dan biaya dihitung otomatis berdasarkan tarif per jam
- **Penjualan Item Tambahan**: Tambah minuman/snack ke transaksi yang sedang berjalan
- **Pembayaran Tunai**: Proses pembayaran dengan perhitungan kembalian otomatis
- **Pencetakan Struk**: Cetak struk transaksi (dengan printer thermal)

### Untuk Admin
- **Laporan Harian/Bulanan/Tahunan**: Analisis pendapatan dan statistik bisnis
- **Manajemen Meja**: Tambah, edit, hapus, dan atur status meja
- **Manajemen Produk**: Kelola daftar minuman/snack beserta harganya
- **Manajemen Pengguna**: Tambah dan kelola akun kasir/admin
- **Pengaturan Sistem**: Konfigurasi tarif dan pengaturan lainnya

## ⚙️ Teknologi yang Digunakan

- **Backend**: Laravel 11 dengan PHP 8.2+
- **Frontend**: Livewire 3 + Tailwind CSS + DaisyUI
- **Database**: MySQL/MariaDB atau SQLite
- **Autentikasi**: Laravel Breeze
- **Testing**: PHPUnit + Laravel Dusk (untuk browser test)
- **Printer Thermal**: Library ESC/POS (mike42/escpos-php)

## 🖥️ Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js & NPM
- Database (MySQL 5.7+/MariaDB 10.2+ atau SQLite 3.8.8+)
- Ekstensi PHP: OpenSSL, PDO, Mbstring, Tokenizer, XML, BCMath, Ctype, JSON
- (Opsional) Printer thermal untuk pencetakan struk

## 🚀 Instalasi

Ikuti langkah-langkah berikut untuk menjalankan proyek secara lokal:

### 1. Clone Repository

```bash
git clone https://github.com/username/billiardpro.git
cd billiardpro
```

### 2. Instal Dependensi

```bash
composer install
```

### 3. Konfigurasi Environment

Salin file `.env.example` ke `.env` dan sesuaikan konfigurasi database:

```bash
cp .env.example .env
```

Edit file `.env` dan atur koneksi database:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=billiardpro
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Migrasi Database

```bash
php artisan migrate
```

### 6. Seeding Data Awal

```bash
php artisan db:seed
```

### 7. Instalasi Frontend

```bash
npm install
npm run dev
```

### 8. Menjalankan Server Lokal

```bash
php artisan serve
```

Akses aplikasi di `http://localhost:8000`

## 🏗️ Arsitektur Sistem

### Struktur Folder Utama

```
billiardpro/
├── app/
│   ├── Http/
│   ├── Livewire/
│   ├── Models/
│   └── Services/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   └── js/
├── routes/
├── tests/
└── docs/
```

### Komponen Utama

- **Livewire Components**: Komponen interaktif untuk UI (Dashboard, TableGrid, PaymentProcess, dll)
- **Models**: Representasi data (User, Table, Transaction, Product)
- **Controllers**: API endpoints untuk integrasi
- **Services**: Logika bisnis kompleks (ReportService, ExportService)
- **Database**: Migrations dan seeders untuk struktur dan data awal

## 🔁 Alur Kerja Utama

### 1. Manajemen Meja

1. Kasir melihat dashboard dengan grid meja
2. Meja berwarna hijau (tersedia) bisa diklik untuk memulai sesi
3. Sistem membuat transaksi baru dengan status `ongoing`
4. Meja berubah warna menjadi merah (dipakai)

### 2. Alur Transaksi

1. Transaksi dibuat saat sesi dimulai
2. Kasir bisa menambahkan item tambahan (minuman/snack)
3. Saat sesi selesai, kasir klik "Meja Tersedia"
4. Sistem menghitung durasi dan total biaya
5. Kasir diarahkan ke halaman pembayaran

### 3. Pembayaran

1. Kasir masukkan jumlah uang yang diterima
2. Sistem menghitung kembalian
3. Validasi pembayaran (jumlah cukup/tidak)
4. Jika valid, transaksi diselesaikan
5. Meja kembali ke status tersedia
6. Struk dicetak (jika printer tersedia)

### 4. Laporan

1. Admin akses halaman laporan
2. Pilih periode (harian/bulanan/tahunan)
3. Sistem menampilkan statistik:
   - Total pendapatan
   - Jumlah transaksi
   - Rata-rata durasi
   - Produk terlaris
4. Ekspor data ke CSV/PDF (opsional)

## 🧪 Testing

Sistem dilengkapi dengan suite test untuk memastikan kualitas kode:

### Menjalankan Unit Test

```bash
php artisan test --testsuite=Unit
```

### Menjalankan Feature Test

```bash
php artisan test --testsuite=Feature
```

### Menjalankan Test Tertentu

```bash
# Test untuk bug duration_minutes
php artisan test --filter PaymentProcess

# Test untuk alur transaksi
php artisan test --filter TransactionFlowTest
```

## ❓ Troubleshooting

### Masalah Umum

**Q: Halaman tidak muncul / error blank**
A: Pastikan `npm run dev` dijalankan dan tidak ada error di console browser

**Q: Tidak bisa login**
A: Pastikan seeding data user sudah dilakukan dan menggunakan kredensial yang benar

**Q: Printer tidak merespons**
A: Cek koneksi jaringan ke printer dan konfigurasi IP di `.env`

### Masalah Database

**Q: Migrasi gagal**
A: Pastikan koneksi database benar dan user memiliki hak akses

**Q: Seeding gagal**
A: Cek log error untuk detail spesifik dan pastikan dependensi data terpenuhi

## 🤝 Kontribusi

1. Fork repository ini
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buka Pull Request

## 📄 Lisensi

Proyek ini dilisensikan di bawah lisensi MIT - lihat file [LICENSE.md](LICENSE.md) untuk detailnya.

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