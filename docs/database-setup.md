# Setup Database untuk BilliardPro

## Konfigurasi MySQL

Untuk menggunakan MySQL sebagai database, pastikan Anda telah:

1. Menginstal dan menjalankan server MySQL
2. Membuat database dengan nama `billiardpro`
3. Membuat user dengan akses ke database tersebut

### Membuat Database dan User MySQL

```sql
-- Login ke MySQL sebagai root
mysql -u root -p

-- Buat database
CREATE DATABASE billiardpro;

-- Buat user dan beri hak akses ke database
CREATE USER 'billiard_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON billiardpro.* TO 'billiard_user'@'localhost';
FLUSH PRIVILEGES;

-- Keluar dari MySQL
EXIT;
```

Kemudian ubah konfigurasi di file `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=billiardpro
DB_USERNAME=billiard_user
DB_PASSWORD=password
```

## Konfigurasi SQLite (untuk development)

Jika Anda ingin menggunakan SQLite (lebih mudah untuk development awal), Anda perlu:

1. Menginstal ekstensi SQLite3 untuk PHP:
   ```bash
   # Ubuntu/Debian
   sudo apt-get install php-sqlite3
   
   # CentOS/RHEL
   sudo yum install php-sqlite3
   
   # Atau sesuaikan dengan versi PHP Anda
   sudo apt-get install php8.3-sqlite3
   ```

2. Set konfigurasi di file `.env`:
   ```
   DB_CONNECTION=sqlite
   # DB_DATABASE akan menggunakan default 'database/database.sqlite'
   ```

## Troubleshooting

### Masalah dengan cache konfigurasi Laravel

Jika Laravel tidak membaca perubahan di file `.env`:

```bash
php artisan config:clear
php artisan cache:clear
rm -f bootstrap/cache/config.php bootstrap/cache/services.php bootstrap/cache/packages.php
php artisan config:cache
```

### Masalah pembacaan .env

Jika konfigurasi .env tidak terbaca dengan benar, coba set environment variable langsung saat menjalankan perintah:

```bash
DB_CONNECTION=mysql DB_DATABASE=billiardpro php artisan config:cache
```

## Catatan Penting

- File `.env` tidak boleh dipush ke repository (sudah di-.gitignore)
- Selalu backup file .env sebelum melakukan perubahan besar
- Untuk deployment production, pastikan menggunakan konfigurasi database yang aman