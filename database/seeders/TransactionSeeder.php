<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $this->createTransactionsForUser($user);
        }
    }

    /**
     * Create transactions for a user.
     */
    private function createTransactionsForUser(User $user): void
    {
        $accounts = Account::query()->where('user_id', $user->id)->get();
        $incomeCategories = Category::query()->where('user_id', $user->id)
            ->where('type', 'income')
            ->get();
        $expenseCategories = Category::query()->where('user_id', $user->id)
            ->where('type', 'expense')
            ->get();

        // Skip if the user doesn't have accounts or categories
        if ($accounts->isEmpty() || $incomeCategories->isEmpty() || $expenseCategories->isEmpty()) {
            return;
        }

        // Create income transactions
        $this->createIncomeTransactions($user, $accounts, $incomeCategories);

        // Create expense transactions
        $this->createExpenseTransactions($user, $accounts, $expenseCategories);

        // Create transfer transactions
        $this->createTransferTransactions($user, $accounts);
    }

    /**
     * Create income transactions for a user.
     */
    private function createIncomeTransactions(User $user, $accounts, $incomeCategories): void
    {
        // Get a checking or savings account for income
        $account = $accounts->firstWhere('type', 'checking')
            ?? $accounts->firstWhere('type', 'savings')
            ?? $accounts->first();

        // Create a salary income for the past 3 months
        for ($i = 0; $i < 3; $i++) {
            $date = Carbon::now()->subMonths($i)->startOfMonth()->addDays(rand(1, 5));

            Transaction::query()->create([
                'amount' => rand(3000, 5000),
                'description' => 'Monthly Salary',
                'date' => $date,
                'type' => 'income',
                'account_id' => $account->id,
                'category_id' => $incomeCategories->firstWhere('name', 'Salary')->id,
                'user_id' => $user->id,
            ]);
        }

        // Create some random additional income
        for ($i = 0; $i < 5; $i++) {
            $date = Carbon::now()->subDays(rand(1, 90));
            $category = $incomeCategories->random();

            Transaction::query()->create([
                'amount' => rand(50, 1000),
                'description' => 'Additional income - ' . $category->name,
                'date' => $date,
                'type' => 'income',
                'account_id' => $account->id,
                'category_id' => $category->id,
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Create expense transactions for a user.
     */
    private function createExpenseTransactions(User $user, $accounts, $expenseCategories): void
    {
        // Create expenses for each account
        foreach ($accounts as $account) {
            // Skip accounts with a negative balance
            if ($account->balance < 0) {
                continue;
            }

            // Create 10-20 random expenses for each account
            $numExpenses = rand(10, 20);

            for ($i = 0; $i < $numExpenses; $i++) {
                $date = Carbon::now()->subDays(rand(1, 90));
                $category = $expenseCategories->random();
                $amount = rand(5, 200);

                Transaction::query()->create([
                    'amount' => $amount,
                    'description' => $category->name . ' expense',
                    'date' => $date,
                    'type' => 'expense',
                    'account_id' => $account->id,
                    'category_id' => $category->id,
                    'user_id' => $user->id,
                ]);
            }
        }
    }

    /**
     * Create transfer transactions for a user.
     */
    private function createTransferTransactions(User $user, $accounts): void
    {
        // Skip if the user doesn't have at least 2 accounts
        if ($accounts->count() < 2) {
            return;
        }

        // Create 3-5 random transfers between accounts
        $numTransfers = rand(3, 5);

        for ($i = 0; $i < $numTransfers; $i++) {
            $fromAccount = $accounts->random();
            $toAccount = $accounts->except($fromAccount->id)->random();
            $date = Carbon::now()->subDays(rand(1, 90));
            $amount = rand(50, 500);

            Transaction::query()->create([
                'amount' => $amount,
                'description' => 'Transfer from ' . $fromAccount->name . ' to ' . $toAccount->name,
                'date' => $date,
                'type' => 'transfer',
                'account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'user_id' => $user->id,
            ]);
        }
    }
}
