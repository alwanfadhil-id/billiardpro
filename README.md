# BilliardPro - Sistem Billing Biliar

**BilliardPro** adalah sistem billing otomatis untuk usaha biliar yang dibangun dengan **Laravel 11** dan **Livewire 3**. Sistem ini dirancang untuk mengotomatiskan proses billing, manajemen produk, pelaporan, dan manajemen meja dengan antarmuka yang intuitif dan modern.

## 🚀 Fitur Utama

### Billing & Manajemen Meja
- Dashboard visual untuk monitoring status meja
- Perhitungan durasi otomatis dengan pembulatan ke atas per jam
- Manajemen status meja (available, occupied, maintenance)
- Fitur booking meja (rencana pengembangan)

### Penjualan Produk
- Manajemen produk tambahan (minuman/snack)
- **Manajemen stok otomatis** dengan notifikasi stok rendah
- Penambahan item ke transaksi secara real-time

### Pembayaran & Struk
- Proses pembayaran cash dengan kalkulasi kembalian
- Metode pembayaran lain (QRIS, debit, credit)
- Cetak struk thermal atau browser

### Laporan & Analitik
- **Laporan harian, bulanan, dan tahunan**
- Tren pendapatan dan analitik bisnis
- Fitur ekspor (CSV, Excel, PDF)

## 🛠️ Teknologi yang Digunakan

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Livewire 3, Alpine.js
- **UI Framework**: Tailwind CSS + DaisyUI
- **Database**: MySQL (mendukung PostgreSQL & SQLite)
- **Authentication**: Laravel Breeze
- **API**: Laravel Sanctum

## 📁 Struktur Proyek

```
billiardpro/
├── app/
│   ├── Models/          # Model Eloquent
│   ├── Livewire/        # Komponen Livewire
│   │   ├── Dashboard/   # Dashboard utama
│   │   ├── Transactions/# Proses transaksi
│   │   ├── Reports/     # Laporan
│   │   └── ...
├── database/
│   ├── migrations/      # File migrasi database
│   └── seeders/         # Data awal
├── resources/
│   └── views/           # Template Blade
├── docs/               # Dokumentasi lengkap
└── routes/             # File route
```

## 📚 Dokumentasi

Dokumentasi lengkap sistem tersedia di folder `docs/` dengan struktur organisasi:

- **`docs/architecture/`** - Dokumentasi teknis dan arsitektur
- **`docs/business/`** - Kebutuhan dan aturan bisnis  
- **`docs/features/`** - Dokumentasi spesifik per fitur
- **`docs/development/`** - Panduan dan dokumentasi pengembangan
- **`docs/operations/`** - Dokumentasi operasional
- **`docs/api/`** - Dokumentasi API

Lihat `docs/README.md` untuk navigasi lengkap dokumentasi.

## 🏃‍♂️ Cara Menjalankan

### Persiapan Awal
1. Pastikan PHP 8.2+, Composer, dan database server (MySQL/PostgreSQL/SQLite) sudah terinstal
2. Clone repository (jika belum)
3. Instal dependensi:

```bash
composer install
npm install
```

### Konfigurasi
1. Salin file `.env.example` ke `.env`
2. Generate app key:
```bash
php artisan key:generate
```
3. Konfigurasi database di file `.env`
4. Jalankan migrasi:
```bash
php artisan migrate --seed
```

### Menjalankan Aplikasi
```bash
# Jalankan development server
php artisan serve

# Jalankan build frontend
npm run dev
```

## 🔐 User Role

Sistem memiliki 2 jenis role:
- **Admin**: Akses penuh (manajemen meja, produk, laporan, pengguna)
- **Cashier**: Akses operasional (billing meja, transaksi, laporan harian)

## 🧪 Testing

Jalankan testing untuk memastikan semua fungsi berjalan:
```bash
# Testing unit
php artisan test

# Testing feature
php artisan test --filter=Feature
```

## 🚀 Deployment

1. Konfigurasi server production (PHP 8.2+, database, web server)
2. Upload kode dan instal dependensi
3. Konfigurasi `.env` untuk production
4. Jalankan migrasi production
5. Build asset production:
```bash
npm run build
```

## ⚠️ Penanganan Durasi

Sistem memiliki mekanisme penanganan khusus untuk kasus `duration_minutes = 0`:

- Validasi `'before_or_equal:now'` untuk mencegah `started_at` di masa depan
- Penggunaan fungsi `abs()` untuk mencegah nilai negatif dari `diffInMinutes`
- Fallback calculation dalam `processPayment` jika `duration_minutes` tetap 0
- Perbaikan pada `PaymentProcess::mount()` untuk mencegah pemanggilan `updateTransactionTotal()` prematur

Lihat dokumentasi `docs/features/billing/duration-calculation.md` untuk detail teknis.

## 📈 Fitur Pengembangan Mendatang

- Sistem booking meja online
- Integrasi dengan sistem akuntansi
- Multi-outlet support
- Mobile app (via Laravel Vapor atau Inertia)

## 🤝 Kontribusi

Kontribusi sangat diterima melalui pull requests! Meskipun proyek ini dirilis di bawah lisensi MIT yang bersifat permissive, kami sangat menghargai kontribusi dari komunitas. Jika Anda ingin berkontribusi:

1. Fork repository ini
2. Buat branch fitur Anda (`git checkout -b feature/AwesomeFeature`)
3. Lakukan perubahan dan pastikan menulis test jika relevan
4. Commit perubahan Anda (`git commit -m 'Add some AwesomeFeature'`)
5. Push ke branch Anda (`git push origin feature/AwesomeFeature`)
6. Buka Pull Request

Kontributor dianggap menyetujui bahwa kontribusi mereka akan dilisensikan di bawah MIT, sesuai dengan lisensi utama proyek. Pastikan untuk:
- Menulis test untuk kode baru
- Memperbarui dokumentasi yang relevan
- Mengikuti standar coding proyek

## 📄 Lisensi

Proyek ini dilisensikan di bawah [Lisensi MIT](LICENSE).

## 📞 Kontak Pengembang                                                                                               
**Alwan Fadhil**                                                                                                      │
📧 Email: alwanfadhil@hotmail.com                                                                                     │
📱 WhatsApp: +62 822-8858-3033                                                                                        │
🐱 GitHub: [alwanfadhil-id](https://github.com/alwanfadhil-id)                                                        │
                    

**BilliardPro v2.0**  
*Dikembangkan dengan ❤️ untuk usaha biliar*