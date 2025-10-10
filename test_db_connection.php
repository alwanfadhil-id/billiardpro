<?php
// File untuk tes koneksi database secara langsung
$host = '127.0.0.1';
$dbname = 'billiardpro';
$username = 'root';
$password = ''; // Sesuaikan jika ada password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Koneksi ke database berhasil!\n";
    echo "Database: $dbname\n";
    echo "Host: $host\n";
    echo "User: $username\n";
} catch (PDOException $e) {
    echo "❌ Koneksi ke database gagal: " . $e->getMessage() . "\n";
    echo "Silakan pastikan:\n";
    echo "1. MySQL server sedang berjalan\n";
    echo "2. Database '$dbname' sudah dibuat\n";
    echo "3. User '$username' memiliki akses ke database tersebut\n";
    echo "4. Password untuk user tersebut benar\n";
}