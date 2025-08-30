<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::with(['accounts', 'categories'])->get();
        foreach ($users as $user) {
            if ($user->accounts->isEmpty() || $user->categories->isEmpty()) {
                continue;
            }
            Transaction::factory()
                ->count(50)
                ->create([
                    'user_id' => $user->id,
                    'account_id' => $user->accounts->random()->id,
                    'category_id' => $user->categories->random()->id,
                ]);
        }
    }
}
