<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Create income categories
            $this->createIncomeCategories($user);

            // Create expense categories
            $this->createExpenseCategories($user);
        }
    }

    /**
     * Create default income categories for a user.
     */
    private function createIncomeCategories(User $user): void
    {
        $incomeCategories = [
            ['name' => 'Salary', 'color' => '#4CAF50', 'icon' => 'work', 'order' => 1],
            ['name' => 'Freelance', 'color' => '#2196F3', 'icon' => 'computer', 'order' => 2],
            ['name' => 'Investments', 'color' => '#9C27B0', 'icon' => 'trending_up', 'order' => 3],
            ['name' => 'Gifts', 'color' => '#E91E63', 'icon' => 'card_giftCard', 'order' => 4],
            ['name' => 'Other Income', 'color' => '#607D8B', 'icon' => 'attach_money', 'order' => 5],
        ];

        foreach ($incomeCategories as $category) {
            Category::query()->create([
                'name' => $category['name'],
                'type' => 'income',
                'color' => $category['color'],
                'icon' => $category['icon'],
                'user_id' => $user->id,
                'order' => $category['order'],
            ]);
        }
    }

    /**
     * Create default expense categories for a user.
     */
    private function createExpenseCategories(User $user): void
    {
        $expenseCategories = [
            ['name' => 'Food & Dining', 'color' => '#FF5722', 'icon' => 'restaurant', 'order' => 1],
            ['name' => 'Housing', 'color' => '#795548', 'icon' => 'home', 'order' => 2],
            ['name' => 'Transportation', 'color' => '#3F51B5', 'icon' => 'directions_car', 'order' => 3],
            ['name' => 'Entertainment', 'color' => '#FF9800', 'icon' => 'movie', 'order' => 4],
            ['name' => 'Shopping', 'color' => '#F44336', 'icon' => 'shopping_cart', 'order' => 5],
            ['name' => 'Utilities', 'color' => '#009688', 'icon' => 'power', 'order' => 6],
            ['name' => 'Healthcare', 'color' => '#8BC34A', 'icon' => 'local_hospital', 'order' => 7],
            ['name' => 'Education', 'color' => '#00BCD4', 'icon' => 'school', 'order' => 8],
            ['name' => 'Personal Care', 'color' => '#FFEB3B', 'icon' => 'spa', 'order' => 9],
            ['name' => 'Travel', 'color' => '#673AB7', 'icon' => 'flight', 'order' => 10],
            ['name' => 'Gifts & Donations', 'color' => '#E91E63', 'icon' => 'redeem', 'order' => 11],
            ['name' => 'Other Expenses', 'color' => '#9E9E9E', 'icon' => 'more_horiz', 'order' => 12],
        ];

        foreach ($expenseCategories as $category) {
            Category::query()->create([
                'name' => $category['name'],
                'type' => 'expense',
                'color' => $category['color'],
                'icon' => $category['icon'],
                'user_id' => $user->id,
                'order' => $category['order'],
            ]);
        }
    }
}
