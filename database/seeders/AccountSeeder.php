<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Create default accounts for each user
            $this->createDefaultAccounts($user);
        }
    }

    /**
     * Create default accounts for a user.
     */
    private function createDefaultAccounts(User $user): void
    {
        $accounts = [
            [
                'name' => 'Cash',
                'type' => 'cash',
                'balance' => 500.00,
                'currency' => 'USD',
                'description' => 'Cash on hand',
            ],
            [
                'name' => 'Checking Account',
                'type' => 'checking',
                'balance' => 2500.75,
                'currency' => 'USD',
                'description' => 'Primary checking account',
            ],
            [
                'name' => 'Savings Account',
                'type' => 'savings',
                'balance' => 10000.00,
                'currency' => 'USD',
                'description' => 'Emergency fund and savings',
            ],
            [
                'name' => 'Credit Card',
                'type' => 'credit_card',
                'balance' => -750.50,
                'currency' => 'USD',
                'description' => 'Primary credit card',
            ],
        ];

        foreach ($accounts as $accountData) {
            $account = new Account([
                'name' => $accountData['name'],
                'type' => $accountData['type'],
                'currency' => $accountData['currency'],
                'description' => $accountData['description'],
                'user_id' => $user->id,
            ]);

            // Set initial balance and save
            $account->setInitialBalance($accountData['balance'])->save();
        }
    }
}
