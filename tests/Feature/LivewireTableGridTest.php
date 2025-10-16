<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Table;
use App\Models\Transaction;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class LivewireTableGridTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_session_creates_transaction_with_current_time()
    {
        $user = User::factory()->create(['role' => 'cashier']);
        $table = Table::factory()->create(['status' => 'available']);

        // Simulasikan waktu eksekusi untuk validasi
        $beforeCall = Carbon::now()->subSecond();
        $response = Livewire::actingAs($user)->test(\App\Livewire\Dashboard\TableGrid::class)
            ->call('startSession', $table->id);
        $afterCall = Carbon::now()->addSecond();

        // Pastikan transaksi baru dibuat
        $this->assertDatabaseCount('transactions', 1);
        $transaction = Transaction::first();

        $this->assertNotNull($transaction);
        $this->assertEquals($table->id, $transaction->table_id);
        $this->assertEquals($user->id, $transaction->user_id);
        $this->assertEquals('ongoing', $transaction->status);
        // Validasi started_at berada dalam rentang waktu yang masuk akal
        $this->assertTrue($transaction->started_at->between($beforeCall, $afterCall));

        // Pastikan status meja berubah menjadi occupied
        $table->refresh();
        $this->assertEquals('occupied', $table->status);
    }

    public function test_mark_as_available_calculates_duration_correctly()
    {
        $user = User::factory()->create(['role' => 'cashier']);
        $table = Table::factory()->create(['status' => 'occupied']);
        
        // Buat transaksi ongoing dengan started_at 5 menit yang lalu
        $fiveMinutesAgo = Carbon::now()->subMinutes(5);
        \Log::info('TEST: Creating transaction with started_at', [
            'fiveMinutesAgo' => $fiveMinutesAgo->toISOString(),
            'fiveMinutesAgoFormatted' => $fiveMinutesAgo->format('Y-m-d H:i:s'),
        ]);
        $transaction = Transaction::factory()->create([
            'table_id' => $table->id,
            'user_id' => $user->id,
            'status' => 'ongoing',
            'started_at' => $fiveMinutesAgo,
        ]);

        // Pastikan sebelumnya duration_minutes adalah 0
        $this->assertEquals(0, $transaction->duration_minutes);

        // Panggil markAsAvailable
        $response = Livewire::actingAs($user)->test(\App\Livewire\Dashboard\TableGrid::class)
            ->call('markAsAvailable', $table->id);

        // Refresh transaksi dari database
        $transaction->refresh();

        // Pastikan duration_minutes dihitung sekitar 5 menit (dengan toleransi)
        // Karena kita menggunakan diffInMinutes, hasilnya akan dibulatkan ke bawah (floor)
        // 5 menit = 5 menit
        $this->assertGreaterThanOrEqual(4, $transaction->duration_minutes);
        $this->assertLessThanOrEqual(6, $transaction->duration_minutes);
        $this->assertNotNull($transaction->ended_at);
        // Pastikan status masih ongoing (karena akan redirect ke payment)
        $this->assertEquals('ongoing', $transaction->status);

        // Pastikan redirect ke halaman pembayaran
        $response->assertRedirect(route('transactions.payment', ['transaction' => $transaction->id]));
    }
}