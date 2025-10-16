<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class FixDurationMinutesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder identifies transactions where duration_minutes is 0
     * but started_at and ended_at are present (and valid), then recalculates
     * and updates the duration_minutes field based on the time difference.
     */
    public function run()
    {
        // Ambil transaksi yang duration_minutes = 0, memiliki started_at dan ended_at
        // Mencakup completed, cancelled, dan juga ongoing yang mungkin memiliki ended_at (misalnya setelah klik 'Meja Tersedia' sebelum bayar)
        $transactionsToFix = Transaction::where('duration_minutes', 0)
            ->whereNotNull('started_at')
            ->whereNotNull('ended_at') // Harus ada ended_at untuk menghitung durasi
            ->get();

        $totalFixed = 0;

        foreach ($transactionsToFix as $transaction) {
            // Hitung durasi berdasarkan started_at dan ended_at
            $calculatedDuration = $transaction->started_at->diffInMinutes($transaction->ended_at);

            // Pastikan durasi tidak minus (walaupun seharusnya tidak bisa karena ended_at >= started_at untuk transaksi valid)
            $correctDuration = max(0, $calculatedDuration);

            // Update record di database
            $transaction->update(['duration_minutes' => $correctDuration]);

            $totalFixed++;
            $this->command->info("Fixed Transaction ID: {$transaction->id}, Started: {$transaction->started_at}, Ended: {$transaction->ended_at}, Duration: {$correctDuration}");
        }

        $this->command->info("Selesai. Jumlah transaksi yang diperbaiki: $totalFixed");
    }
}