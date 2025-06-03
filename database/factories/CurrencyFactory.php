<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Currency::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->currencyCode,
            'name' => $this->faker->word,
            'symbol' => $this->faker->randomElement(['$', '€', '£', '¥']),
            'decimal_places' => 2,
            'is_active' => true,
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
