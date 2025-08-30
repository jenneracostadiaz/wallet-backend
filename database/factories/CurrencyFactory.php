<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->randomElement(['PEN', 'USD', 'EUR', 'GBP', 'JPY', 'CNY', 'CAD', 'AUD', 'CHF', 'MXN', 'BRL', 'CLP', 'COP', 'ARS']),
            'name' => $this->faker->word(),
            'symbol' => $this->faker->randomElement(['S/', '$', '€', '£', '¥', 'C$', 'A$', 'CHF', 'R$']),
            'decimal_places' => 2,
        ];
    }
}
