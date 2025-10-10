<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Table;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class TransactionFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test complete transaction flow: login cashier → start session on available table → add items → pay → check table status and transaction
     */
    public function test_complete_transaction_flow()
    {
        // Create a cashier user and log in
        $cashier = User::factory()->create([
            'role' => 'cashier'
        ]);

        // Create a table that's available
        $table = Table::factory()->create([
            'status' => 'available',
            'hourly_rate' => 10000
        ]);

        // Create some products for additional items
        $product1 = Product::factory()->create([
            'name' => 'Es Teh',
            'price' => 5000
        ]);

        $product2 = Product::factory()->create([
            'name' => 'Kacang Goreng',
            'price' => 7000
        ]);

        // Login as cashier
        $this->actingAs($cashier);

        // Start a session on the available table
        $response = $this->post('/transactions/start', [
            'table_id' => $table->id,
            'user_id' => $cashier->id
        ]);

        // Check that table status changed to occupied
        $table->refresh();
        $this->assertEquals('occupied', $table->status);

        // Find the ongoing transaction
        $transaction = Transaction::where([
            'table_id' => $table->id,
            'user_id' => $cashier->id,
            'status' => 'ongoing'
        ])->first();

        $this->assertNotNull($transaction);

        // Add items to the transaction
        $response = $this->post('/transactions/'.$transaction->id.'/items', [
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 2,
                    'price_per_item' => $product1->price
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 1,
                    'price_per_item' => $product2->price
                ]
            ]
        ]);

        // Verify that items were added
        $transaction->refresh();
        $this->assertCount(2, $transaction->items);
        
        // Calculate expected total: 1 hour of table time (10000) + 2 x Es Teh (10000) + 1 x Kacang (7000) = 27000
        $expectedTotal = 10000 + (2 * 5000) + 7000;
        $this->assertEquals($expectedTotal, $transaction->items->sum('total_price') + (1 * 10000));

        // Process payment and complete transaction
        $response = $this->put('/transactions/'.$transaction->id.'/complete', [
            'ended_at' => Carbon::now(),
            'payment_method' => 'cash',
            'cash_received' => $expectedTotal,
            'change_amount' => 0
        ]);

        // Verify transaction was completed
        $transaction->refresh();
        $this->assertEquals('completed', $transaction->status);
        $this->assertNotNull($transaction->ended_at);
        $this->assertEquals($expectedTotal, $transaction->total);
        $this->assertEquals('cash', $transaction->payment_method);

        // Check that table status changed back to available
        $table->refresh();
        $this->assertEquals('available', $table->status);

        // Verify the transaction is stored correctly in the database
        $storedTransaction = Transaction::find($transaction->id);
        $this->assertNotNull($storedTransaction);
        $this->assertEquals($expectedTotal, $storedTransaction->total);
        $this->assertEquals('completed', $storedTransaction->status);
    }

    /**
     * Alternative test to ensure the flow works with different scenarios
     */
    public function test_transaction_flow_with_different_durations()
    {
        // Create a cashier user and log in
        $cashier = User::factory()->create([
            'role' => 'cashier'
        ]);
        
        // Create an available table
        $table = Table::factory()->create([
            'status' => 'available',
            'hourly_rate' => 20000
        ]);

        $this->actingAs($cashier);

        // Simulate 90 minutes session (should round to 2 hours)
        $startedAt = Carbon::now()->subMinutes(90);
        $transaction = Transaction::create([
            'table_id' => $table->id,
            'user_id' => $cashier->id,
            'started_at' => $startedAt,
            'status' => 'ongoing'
        ]);

        // Verify table status changed
        $table->refresh();
        $this->assertEquals('occupied', $table->status);

        // Simulate adding an item
        $product = Product::factory()->create([
            'name' => 'Mineral Water',
            'price' => 3000
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_per_item' => $product->price,
            'total_price' => $product->price
        ]);

        // Complete transaction
        $transaction->update([
            'ended_at' => Carbon::now(),
            'duration_minutes' => 90,
            'total' => $transaction->calculateTotal(),
            'payment_method' => 'cash',
            'cash_received' => $transaction->calculateTotal(),
            'change_amount' => 0,
            'status' => 'completed'
        ]);

        // Verify table status is back to available
        $table->refresh();
        $this->assertEquals('available', $table->status);

        // Verify the calculated total: 2 hours (40000) + item (3000) = 43000
        $expectedTotal = 40000 + 3000; // 2 hours at 20000/hour + 1 item at 3000
        $this->assertEquals($expectedTotal, $transaction->total);
    }
}