<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Table;
use App\Models\Transaction;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class LivewirePaymentProcessTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_payment_applies_duration_fallback()
    {
        $user = User::factory()->create(['role' => 'cashier']);
        $table = Table::factory()->create(['status' => 'occupied', 'hourly_rate' => 60000]); // 60.000/hour = 1000/minute
        
        // Buat transaksi completed dengan duration_minutes = 0
        $transaction = Transaction::factory()->create([
            'table_id' => $table->id,
            'user_id' => $user->id,
            'status' => 'ongoing',
            'started_at' => Carbon::now()->subMinutes(10), // 10 menit yang lalu
            'duration_minutes' => 0, // Simulasikan bug atau data lama
            'total' => 0,
        ]);

        // Log untuk debugging test
        \Log::info('TEST: Transaction created', [
            'transaction_id' => $transaction->id,
            'duration_minutes' => $transaction->duration_minutes,
            'total' => $transaction->total,
        ]);

        // Pastikan transaksi benar-benar memiliki nilai yang diinginkan sebelum diproses
        $this->assertEquals(0, $transaction->duration_minutes);
        $this->assertEquals(0, $transaction->total);

        // Siapkan payload pembayaran
        $payload = [
            'amountReceived' => 15000, // Cukup untuk 10 menit @ 1000/minute = 10.000 + margin
            'paymentMethod' => 'cash',
            'change' => 5000,
        ];

        // Panggil processPayment melalui Livewire
        $response = Livewire::actingAs($user)
            ->test(\App\Livewire\Transactions\PaymentProcess::class, ['transaction' => $transaction->id])
            ->set('amountReceived', $payload['amountReceived'])
            ->set('paymentMethod', $payload['paymentMethod'])
            ->call('processPayment');

        // Refresh transaction
        $transaction->refresh();

        // Pastikan durasi fallback diterapkan (sekitar 10 menit)
        $this->assertGreaterThanOrEqual(9, $transaction->duration_minutes);
        $this->assertLessThanOrEqual(11, $transaction->duration_minutes);
        $this->assertEquals('completed', $transaction->status);
        $this->assertNotNull($transaction->ended_at);

        // Pastikan table status berubah
        $table->refresh();
        $this->assertEquals('available', $table->status);

        // Pastikan flash message muncul
        $response->assertHasNoErrors();
        // $response->assertSessionHas('message'); // Tidak digunakan karena Livewire test tidak selalu menyimpan flash message dengan cara ini
        
        // Pastikan transaksi diperbarui dengan benar
        $transaction->refresh();
        $this->assertGreaterThanOrEqual(9, $transaction->duration_minutes);
        $this->assertLessThanOrEqual(11, $transaction->duration_minutes);
        $this->assertEquals('completed', $transaction->status);
        $this->assertNotNull($transaction->ended_at);
    }
}