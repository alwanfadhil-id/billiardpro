# Dokumentasi BilliardPro

Selamat datang di folder dokumentasi untuk sistem BilliardPro. Dokumentasi ini terorganisasi dalam beberapa folder berdasarkan topik untuk memudahkan navigasi dan pemeliharaan.

## 📁 Struktur Dokumentasi

### [architecture/](./architecture/)
Dokumentasi arsitektur teknis sistem:
- `development-reference.md` - Referensi pengembangan utama (versi 2.0)
- `ERD.dbml` - Diagram entitas-relasi database
- `system-overview.md` - Gambaran umum arsitektur sistem
- `tech-stack.md` - Teknologi yang digunakan

### [business/](./business/)
Dokumentasi kebutuhan dan aturan bisnis:
- `BRD.md` - Business Requirement Document
- `business-rules.md` - Aturan bisnis spesifik
- `user-roles.md` - Deskripsi peran pengguna

### [features/](./features/)
Dokumentasi spesifik per fitur:
- [billing/](./features/billing/) - Dokumentasi sistem billing
  - `duration-calculation.md` - Perhitungan durasi transaksi
  - `session-management.md` - Manajemen sesi billing
  - `payment-process.md` - Proses pembayaran
- [inventory/](./features/inventory/) - Dokumentasi sistem inventaris
  - `stock-management.md` - Manajemen stok produk
  - `product-management.md` - Manajemen produk
- [reporting/](./features/reporting/) - Dokumentasi sistem pelaporan
  - `daily-report.md` - Laporan harian
  - `monthly-report.md` - Laporan bulanan
  - `yearly-report.md` - Laporan tahunan
- [booking/](./features/booking/) - Dokumentasi sistem booking
  - `booking-system-dev.md` - Rencana pengembangan fitur booking

### [development/](./development/)
Dokumentasi pengembangan dan panduan:
- [bugfixes/](./development/bugfixes/) - Dokumentasi perbaikan bug
  - `bugfix-duration-minutes-zero.md` - Perbaikan bug duration_minutes = 0
  - `debugging-session-duration-minutes-zero.md` - Catatan debugging duration_minutes
  - `unit-testing-debugging-value.md` - Peran unit testing dalam debugging
- [testing/](./development/testing/) - Dokumentasi pengujian
  - `testing-checklist.md` - Checklist pengujian
- [guides/](./development/guides/) - Panduan pengembangan
  - `developer-guide-duration-minutes.md` - Panduan developer untuk duration_minutes
  - `executive-summary-duration-minutes-fix.md` - Ringkasan eksekutif perbaikan duration

### [operations/](./operations/)
Dokumentasi operasional:
- `backup-command.md` - Perintah backup database
- `database-setup.md` - Panduan setup database
- `deployment-guide.md` - Panduan deployment

### [api/](./api/)
Dokumentasi API sistem:
- `api-endpoints.md` - Endpoint-endpoint API

## 🔄 Update Terbaru (Versi 2.0)

Sistem BilliardPro telah berkembang dari versi awal dengan penambahan fitur-fitur penting:

1. **Manajemen Stok Produk** - Pelacakan stok otomatis dengan notifikasi stok rendah
2. **Berbagai Jenis Meja** - Dukungan untuk meja biasa, premium, dan VIP
3. **Laporan Lengkap** - Laporan harian, bulanan, dan tahunan
4. **Perbaikan Durasi** - Penanganan bug `duration_minutes = 0` dengan fallback calculation
5. **Pelacakan Inventaris** - Catatan historis semua perubahan stok

## 🛠️ Panduan Penggunaan

### Untuk Developer Baru
1. Mulai dengan membaca `architecture/development-reference.md` untuk mendapatkan gambaran utuh sistem
2. Pelajari arsitektur database di `architecture/ERD.dbml`
3. Baca `business/BRD.md` untuk memahami kebutuhan bisnis

### Untuk Pemeliharaan Sistem
1. Lihat dokumentasi per fitur di folder `features/` untuk pemahaman mendalam
2. Gunakan dokumentasi `development/bugfixes/` saat menghadapi masalah serupa
3. Ikuti panduan operasional di `operations/` untuk tugas administratif

### Untuk Pengembangan Fitur Baru
1. Patuhi aturan dalam `architecture/development-reference.md`
2. Gunakan format dan pendekatan yang konsisten dengan dokumentasi yang ada
3. Pastikan membuat dokumentasi untuk fitur baru di folder yang sesuai

## 📝 Kontribusi pada Dokumentasi

Jika Anda menemukan informasi yang tidak akurat atau fitur baru yang perlu didokumentasikan:
1. Buat versi dokumentasi baru di folder yang sesuai
2. Pastikan struktur dan format mengikuti dokumentasi lain
3. Perbarui file ini (README.md) jika menambahkan folder atau file baru

---
*Doc version: 2.0*  
*Last updated: Oktober 2025*