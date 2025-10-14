<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateTableTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update all existing tables to have a default type of 'biasa'
        \App\Models\Table::whereNull('type')->orWhere('type', '')->update(['type' => 'biasa']);
    }
}
