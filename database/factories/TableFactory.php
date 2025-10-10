<?php

namespace Database\Factories;

use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word . ' ' . $this->faker->numberBetween(1, 100),
            'hourly_rate' => $this->faker->numberBetween(10000, 100000),
            'status' => $this->faker->randomElement(['available', 'occupied', 'maintenance']),
        ];
    }
}