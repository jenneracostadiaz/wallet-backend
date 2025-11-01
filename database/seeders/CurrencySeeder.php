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
        $currencies = [
            ['code' => 'PEN', 'name' => 'Peruvian Sol', 'symbol' => 'S/', 'decimal_places' => 2, 'exchange_rate_to_pen' => 1.0000],
            ['code' => 'USD', 'name' => 'United States Dollar', 'symbol' => '$', 'decimal_places' => 2, 'exchange_rate_to_pen' => 3.3800],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2, 'exchange_rate_to_pen' => 3.9100],
            ['code' => 'GBP', 'name' => 'British Pound Sterling', 'symbol' => '£', 'decimal_places' => 2, 'exchange_rate_to_pen' => 4.4500],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'decimal_places' => 0, 'exchange_rate_to_pen' => 0.0220],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'decimal_places' => 2, 'exchange_rate_to_pen' => 2.4200],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'decimal_places' => 2, 'exchange_rate_to_pen' => 2.2100],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'decimal_places' => 2, 'exchange_rate_to_pen' => 4.2000],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'decimal_places' => 2, 'exchange_rate_to_pen' => 0.4800],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'decimal_places' => 2, 'exchange_rate_to_pen' => 0.0380],
        ];

        foreach ($currencies as $currencyData) {
            Currency::updateOrCreate(
                ['code' => $currencyData['code']],
                $currencyData
            );
        }
    }
}
