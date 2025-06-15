<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'type' => $this->faker->randomElement(['income', 'expense', 'transfer']),
            'icon' => $this->faker->randomElement(['💵', '🏘️', '🎸', '💻', '🏢']),
            'parent_id' => null,
            'user_id' => User::query()->inRandomOrder()->first()->id,
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
