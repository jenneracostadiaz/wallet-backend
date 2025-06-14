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
            'code' => fake()->randomElement(['USD', 'EUR', 'GBP', 'JPY', 'CNY']),
            'name' => fake()->word(),
            'symbol' => fake()->randomElement(['$', '€', 'S/', '£', '¥']),
            'decimal_places' => 2,
            'is_active' => true,
        ];
    }
}
