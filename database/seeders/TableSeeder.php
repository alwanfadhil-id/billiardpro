<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run()
    {
        $tables = [
            ['name' => '1', 'hourly_rate' => 50000, 'status' => 'available'],
            ['name' => '2', 'hourly_rate' => 50000, 'status' => 'available'],
            ['name' => '3', 'hourly_rate' => 75000, 'status' => 'available'],
            ['name' => '4', 'hourly_rate' => 75000, 'status' => 'available'],
            ['name' => '5', 'hourly_rate' => 50000, 'status' => 'available'],
            ['name' => '6', 'hourly_rate' => 50000, 'status' => 'available'],
            ['name' => '7', 'hourly_rate' => 100000, 'status' => 'available'],
            ['name' => '8', 'hourly_rate' => 50000, 'status' => 'available'],
        ];

        foreach ($tables as $tableData) {
            Table::create($tableData);
        }
    }
}