<?php

namespace Tests\Unit;

use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use InvalidArgumentException;

class TableValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test saving table with negative hourly_rate should fail
     */
    public function test_table_cannot_be_saved_with_negative_hourly_rate()
    {
        // Expect an InvalidArgumentException when trying to save a table with negative hourly_rate
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Hourly rate must be greater than 0');

        // Try to create a table with negative hourly rate
        $table = new Table();
        $table->name = 'Test Table';
        $table->hourly_rate = -10000; // Negative rate should fail
        $table->status = 'available';
        $table->save();
    }

    /**
     * Test updating table with negative hourly_rate should fail
     */
    public function test_table_cannot_be_updated_with_negative_hourly_rate()
    {
        // Create a valid table first
        $table = Table::factory()->create([
            'name' => 'Valid Table',
            'hourly_rate' => 10000,
            'status' => 'available'
        ]);

        // Expect an InvalidArgumentException when trying to update with negative hourly_rate
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Hourly rate must be greater than 0');

        // Try to update the hourly rate to a negative value
        $table->hourly_rate = -10000;
        $table->save();
    }

    /**
     * Test that zero hourly rate also fails validation
     */
    public function test_table_cannot_be_saved_with_zero_hourly_rate()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Hourly rate must be greater than 0');

        // Try to create a table with zero hourly rate
        $table = new Table();
        $table->name = 'Test Table';
        $table->hourly_rate = 0; // Zero rate should also fail as it's not greater than 0
        $table->status = 'available';
        $table->save();
    }

    /**
     * Test that positive hourly rate passes validation
     */
    public function test_table_can_be_saved_with_positive_hourly_rate()
    {
        // This should not throw an exception
        $table = new Table();
        $table->name = 'Valid Table';
        $table->hourly_rate = 10000; // Positive rate should pass
        $table->status = 'available';
        $table->save();

        // Verify the table was saved with the correct values
        $this->assertDatabaseHas('tables', [
            'name' => 'Valid Table',
            'hourly_rate' => 10000,
            'status' => 'available'
        ]);

        $this->assertEquals(10000, $table->hourly_rate);
    }

    /**
     * Test that validation also works through factory creation
     */
    public function test_table_factory_cannot_create_with_negative_hourly_rate()
    {
        // Test with model creation directly with negative value
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Hourly rate must be greater than 0');

        $table = Table::create([
            'name' => 'Invalid Table',
            'hourly_rate' => -10000,
            'status' => 'available'
        ]);
    }
}