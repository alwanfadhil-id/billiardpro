<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Table;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class ApiTransactionsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_fails_with_future_started_at()
    {
        $user = User::factory()->create();
        $table = Table::factory()->create();

        // Simulasikan waktu di masa depan
        $futureTime = Carbon::now()->addMinutes(10)->toISOString();

        $payload = [
            'table_id' => $table->id,
            'user_id' => $user->id,
            'started_at' => $futureTime,
            'status' => 'ongoing',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/transactions', $payload);

        // Harapkan validasi gagal (status 422) karena started_at di masa depan
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['started_at']);
    }

    public function test_store_succeeds_with_past_started_at()
    {
        $user = User::factory()->create();
        $table = Table::factory()->create();

        // Gunakan waktu di masa lalu
        $pastTime = Carbon::now()->subMinutes(5)->toISOString();

        $payload = [
            'table_id' => $table->id,
            'user_id' => $user->id,
            'started_at' => $pastTime,
            'status' => 'ongoing',
            'payment_method' => 'cash', // Ditambahkan karena validasi di model Transaction
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/transactions', $payload);

        // Harapkan sukses (status 201)
        $response->assertStatus(201);
        $this->assertDatabaseHas('transactions', [
            'table_id' => $table->id,
            'user_id' => $user->id,
            'status' => 'ongoing',
        ]);
    }

    // Test lainnya bisa ditambahkan di sini
}