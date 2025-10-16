<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class TestLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('TestLogSeeder executed at: ' . now());
        echo "TestLogSeeder: Info logged.\n"; // Untuk output ke terminal saat dijalankan
    }
}