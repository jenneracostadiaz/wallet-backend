<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'type' => $this->faker->randomElement(AccountType::cases()),
            'balance' => $this->faker->randomFloat(2, 0, 10000),
            'currency_id' => Currency::query()->inRandomOrder()->first()->id,
            'user_id' => User::query()->inRandomOrder()->first()->id,
            'description' => $this->faker->sentence,
            'color' => $this->faker->hexColor,
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
