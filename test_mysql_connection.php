<?php
// File untuk tes koneksi ke server MySQL tanpa menyebut database
$host = '127.0.0.1';
$username = 'root';
$password = ''; // Sesuaikan jika ada password

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Koneksi ke server MySQL berhasil!\n";
    echo "Host: $host\n";
    echo "User: $username\n";
    
    // Coba buat database jika belum ada
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS billiardpro");
        echo "✅ Database 'billiardpro' sudah dibuat atau sudah ada\n";
    } catch (PDOException $e) {
        echo "⚠️  Gagal membuat database: " . $e->getMessage() . "\n";
        echo "Mungkin user tidak punya hak untuk membuat database\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Koneksi ke server MySQL gagal: " . $e->getMessage() . "\n";
    echo "Silakan pastikan:\n";
    echo "1. MySQL server sedang berjalan\n";
    echo "2. User '$username' memiliki akses ke server\n";
    echo "3. Password untuk user tersebut benar\n";
    
    // Coba dengan password jika pengguna memasukkan nanti
    echo "\nJika user memerlukan password, ubah variabel \$password di file ini.\n";
}