<?php
// File untuk tes koneksi SQLite
$dbPath = __DIR__ . '/database/database.sqlite';

if (!file_exists($dbPath)) {
    echo "❌ File database SQLite tidak ditemukan: $dbPath\n";
    echo "Silakan pastikan file database ada atau buat terlebih dahulu.\n";
    exit(1);
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Koneksi ke database SQLite berhasil!\n";
    echo "File database: $dbPath\n";
    
    // Coba eksekusi query sederhana
    $result = $pdo->query("SELECT 1 as test")->fetch();
    echo "✅ Query uji berhasil: " . $result['test'] . "\n";
    
} catch (PDOException $e) {
    echo "❌ Koneksi ke database SQLite gagal: " . $e->getMessage() . "\n";
    echo "Kemungkinan besar karena ekstensi SQLite tidak diinstal di PHP.\n";
    echo "Silakan instal ekstensi SQLite untuk PHP:\n";
    echo "  Ubuntu/Debian: sudo apt-get install php-sqlite3\n";
    echo "  CentOS/RHEL: sudo yum install php-sqlite3\n";
    echo "  Atau sesuaikan dengan versi PHP Anda\n";
}