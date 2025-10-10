<?php

namespace Tests\Unit;

use App\Models\Table;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test calculateTotal method with 1 minute duration (should round up to 1 hour)
     */
    public function test_calculate_total_with_1_minute_duration()
    {
        // Create a user, table with hourly rate
        $user = User::factory()->create();
        $table = Table::factory()->create([
            'hourly_rate' => 10000,
        ]);

        // Create a transaction with 1 minute duration
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'table_id' => $table->id,
            'started_at' => Carbon::now()->subMinute(), // 1 minute ago
            'ended_at' => Carbon::now(),
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        // Calculate total should round up to 1 hour (10000)
        $this->assertEquals(10000, $transaction->calculateTotal());
    }

    /**
     * Test calculateTotal method with 61 minutes duration (should round up to 2 hours)
     */
    public function test_calculate_total_with_61_minutes_duration()
    {
        // Create a user, table with hourly rate
        $user = User::factory()->create();
        $table = Table::factory()->create([
            'hourly_rate' => 10000,
        ]);

        // Create a transaction with 61 minutes duration
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'table_id' => $table->id,
            'started_at' => Carbon::now()->subMinutes(61),
            'ended_at' => Carbon::now(),
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        // Calculate total should round up to 2 hours (20000)
        $this->assertEquals(20000, $transaction->calculateTotal());
    }

    /**
     * Test calculateTotal method with additional items
     */
    public function test_calculate_total_with_additional_items()
    {
        // Create a user, table with hourly rate
        $user = User::factory()->create();
        $table = Table::factory()->create([
            'hourly_rate' => 10000,
        ]);

        // Create a product for additional items
        $product = Product::factory()->create([
            'price' => 5000,
        ]);

        // Create a transaction with 90 minutes duration (should round up to 2 hours)
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'table_id' => $table->id,
            'started_at' => Carbon::now()->subMinutes(90),
            'ended_at' => Carbon::now(),
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        // Add a transaction item
        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_item' => 5000,
            'total_price' => 5000,
        ]);

        // Calculate total: 2 hours (20000) + 1 item (5000) = 25000
        $this->assertEquals(25000, $transaction->calculateTotal());
    }

    /**
     * Test calculateTotal method with ongoing transaction (no ended_at)
     */
    public function test_calculate_total_with_ongoing_transaction()
    {
        // Create a user, table with hourly rate
        $user = User::factory()->create();
        $table = Table::factory()->create([
            'hourly_rate' => 10000,
        ]);

        // Create an ongoing transaction (no ended_at)
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'table_id' => $table->id,
            'started_at' => Carbon::now()->subMinutes(150), // 2.5 hours ago, should round to 3 hours
            'ended_at' => null, // Ongoing
            'payment_method' => 'cash',
            'status' => 'ongoing',
        ]);

        // Calculate total: should round up to 3 hours (30000)
        // Note: Since ended_at is null, we use current time
        $expectedHours = ceil(150 / 60); // ceil(2.5) = 3
        $expectedTotal = $table->hourly_rate * $expectedHours;
        
        $this->assertEquals($expectedTotal, $transaction->calculateTotal());
    }

    /**
     * Test calculateTotal method with multiple additional items
     */
    public function test_calculate_total_with_multiple_additional_items()
    {
        // Create a user, table with hourly rate
        $user = User::factory()->create();
        $table = Table::factory()->create([
            'hourly_rate' => 10000,
        ]);

        // Create products for additional items
        $product1 = Product::factory()->create([
            'price' => 5000,
        ]);

        $product2 = Product::factory()->create([
            'price' => 7000,
        ]);

        // Create a transaction with 120 minutes duration (should round up to 2 hours)
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'table_id' => $table->id,
            'started_at' => Carbon::now()->subMinutes(120),
            'ended_at' => Carbon::now(),
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        // Add multiple transaction items
        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price_per_item' => 5000,
            'total_price' => 10000,
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price_per_item' => 7000,
            'total_price' => 7000,
        ]);

        // Calculate total: 2 hours (20000) + 2 items (10000 + 7000) = 37000
        $this->assertEquals(37000, $transaction->calculateTotal());
    }
}