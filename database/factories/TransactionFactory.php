<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'description' => $this->faker->sentence(),
            'date' => $this->faker->dateTimeThisYear(),
            'type' => $this->faker->randomElement(['income', 'expense']),
            'account_id' => Account::query()->inRandomOrder()->first()->id,
            'category_id' => Category::query()->inRandomOrder()->first()->id,
            'user_id' => User::query()->inRandomOrder()->first()->id,
        ];
    }
}
