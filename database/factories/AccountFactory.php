<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Currency;
use App\Models\User;
use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'type' => AccountType::Checking,
            'balance' => $this->faker->randomFloat(2, 0, 10000),
            'currency_id' => Currency::factory(),
            'description' => $this->faker->sentence,
            'user_id' => User::factory(),
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
