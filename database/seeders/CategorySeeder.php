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
            ['name' => 'Salary', 'color' => '#4CAF50', 'icon' => 'work'],
            ['name' => 'Freelance', 'color' => '#2196F3', 'icon' => 'computer'],
            ['name' => 'Investments', 'color' => '#9C27B0', 'icon' => 'trending_up'],
            ['name' => 'Gifts', 'color' => '#E91E63', 'icon' => 'card_giftcard'],
            ['name' => 'Other Income', 'color' => '#607D8B', 'icon' => 'attach_money'],
        ];

        foreach ($incomeCategories as $category) {
            Category::create([
                'name' => $category['name'],
                'type' => 'income',
                'color' => $category['color'],
                'icon' => $category['icon'],
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Create default expense categories for a user.
     */
    private function createExpenseCategories(User $user): void
    {
        $expenseCategories = [
            ['name' => 'Food & Dining', 'color' => '#FF5722', 'icon' => 'restaurant'],
            ['name' => 'Housing', 'color' => '#795548', 'icon' => 'home'],
            ['name' => 'Transportation', 'color' => '#3F51B5', 'icon' => 'directions_car'],
            ['name' => 'Entertainment', 'color' => '#FF9800', 'icon' => 'movie'],
            ['name' => 'Shopping', 'color' => '#F44336', 'icon' => 'shopping_cart'],
            ['name' => 'Utilities', 'color' => '#009688', 'icon' => 'power'],
            ['name' => 'Healthcare', 'color' => '#8BC34A', 'icon' => 'local_hospital'],
            ['name' => 'Education', 'color' => '#00BCD4', 'icon' => 'school'],
            ['name' => 'Personal Care', 'color' => '#FFEB3B', 'icon' => 'spa'],
            ['name' => 'Travel', 'color' => '#673AB7', 'icon' => 'flight'],
            ['name' => 'Gifts & Donations', 'color' => '#E91E63', 'icon' => 'redeem'],
            ['name' => 'Other Expenses', 'color' => '#9E9E9E', 'icon' => 'more_horiz'],
        ];

        foreach ($expenseCategories as $category) {
            Category::create([
                'name' => $category['name'],
                'type' => 'expense',
                'color' => $category['color'],
                'icon' => $category['icon'],
                'user_id' => $user->id,
            ]);
        }
    }
}
