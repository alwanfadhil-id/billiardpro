<?php

namespace Database\Factories;

use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionItemFactory extends Factory
{
    protected $model = TransactionItem::class;

    public function definition()
    {
        return [
            'transaction_id' => null,
            'product_id' => null,
            'quantity' => $this->faker->numberBetween(1, 10),
            'price_per_item' => $this->faker->numberBetween(1000, 50000),
            'total_price' => 0, // Will be calculated based on quantity and price
        ];
    }
}