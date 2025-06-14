<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::query()->insert([
            ['code' => 'USD', 'name' => 'United States Dollar', 'symbol' => '$', 'decimal_places' => 2, 'is_active' => true],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2, 'is_active' => true],
            ['code' => 'PEN', 'name' => 'Peruvian Sol', 'symbol' => 'S/', 'decimal_places' => 2, 'is_active' => true],
            ['code' => 'GBP', 'name' => 'British Pound Sterling', 'symbol' => '£', 'decimal_places' => 2, 'is_active' => true],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'decimal_places' => 0, 'is_active' => true],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'decimal_places' => 2, 'is_active' => true],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'decimal_places' => 2, 'is_active' => true],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'decimal_places' => 2, 'is_active' => true],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'decimal_places' => 2, 'is_active' => true],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'decimal_places' => 2, 'is_active' => true],
        ]);
    }
}
