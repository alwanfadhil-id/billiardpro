<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'table_id' => null, // Will be set when creating
            'user_id' => null,  // Will be set when creating
            'started_at' => $this->faker->dateTimeThisMonth,
            'ended_at' => null,
            'duration_minutes' => 0,
            'total' => 0,
            'payment_method' => $this->faker->randomElement(['cash', 'qris', 'debit', 'credit', 'other']),
            'cash_received' => null,
            'change_amount' => null,
            'status' => $this->faker->randomElement(['ongoing', 'completed', 'cancelled']),
        ];
    }
}