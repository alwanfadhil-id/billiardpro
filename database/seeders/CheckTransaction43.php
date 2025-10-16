<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;

class CheckTransaction43 extends Seeder
{
    public function run()
    {
        $transaction = Transaction::find(43);
        if ($transaction) {
            $this->command->info("ID: {$transaction->id}");
            $this->command->info("Status: {$transaction->status}");
            $this->command->info("Started At: {$transaction->started_at}");
            $this->command->info("Ended At: " . ($transaction->ended_at ? $transaction->ended_at : 'NULL'));
            $this->command->info("Duration Minutes: {$transaction->duration_minutes}");
            $this->command->info("---");

            // Cek apakah ended_at ada
            if ($transaction->ended_at) {
                $rawDuration = $transaction->started_at->diffInMinutes($transaction->ended_at);
                $intDuration = intval($rawDuration);
                $this->command->info("Raw Duration (calc from DB times): {$rawDuration} minutes");
                $this->command->info("Int Duration (intval): {$intDuration} minutes");
            }
        } else {
            $this->command->error("Transaction ID 43 not found.");
        }
    }
}