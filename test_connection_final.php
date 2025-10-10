<?php

// Test koneksi database untuk sistem BilliardPro
require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

try {
    // Cek apakah bisa terhubung ke database
    DB::connection()->getPdo();
    echo "✅ Koneksi ke database berhasil!\n";
    echo "Nama database: " . DB::connection()->getDatabaseName() . "\n";
    echo "Driver database: " . DB::connection()->getDriverName() . "\n";
    
    // Cek apakah tabel migrations sudah ada
    if (DB::getSchemaBuilder()->hasTable('migrations')) {
        echo "✅ Tabel migrations ditemukan\n";
    } else {
        echo "⚠️  Tabel migrations tidak ditemukan\n";
    }
    
    // Tampilkan info tambahan
    $connection = DB::connection();
    echo "Koneksi: " . $connection->getName() . "\n";
    
} catch (\Exception $e) {
    echo "❌ Koneksi ke database gagal: " . $e->getMessage() . "\n";
}