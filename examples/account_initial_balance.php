<?php

// Bootstrap the Laravel application
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Account;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// This is a simple example script that demonstrates how to create an account with an initial balance

// Assuming you have a user
$user = User::first();

if (!$user) {
    echo "No users found. Please create a user first.\n";
    exit(1);
}

// Example 1: Create an account with initial balance using the setInitialBalance method
// First, get the currency
$usdCurrency = Currency::where('code', 'USD')->first();

if (!$usdCurrency) {
    echo "USD currency not found. Please run the CurrencySeeder first.\n";
    exit(1);
}

$savingsAccount = new Account([
    'name' => 'My Savings',
    'type' => 'savings',
    'currency_id' => $usdCurrency->id,
    'description' => 'My personal savings account',
    'user_id' => $user->id,
]);

// Set the initial balance and save
$savingsAccount->setInitialBalance(5000.00)->save();

echo "Created savings account with initial balance: {$usdCurrency->symbol}" . $savingsAccount->balance . "\n";

// Example 2: Create an account with initial balance by setting the balance directly
// Let's use a different currency for this example
$eurCurrency = Currency::where('code', 'EUR')->first();

if (!$eurCurrency) {
    echo "EUR currency not found. Using USD instead.\n";
    $eurCurrency = $usdCurrency;
}

$checkingAccount = new Account([
    'name' => 'My Checking',
    'type' => 'checking',
    'balance' => 2500.75, // Set initial balance directly
    'currency_id' => $eurCurrency->id,
    'description' => 'My personal checking account',
    'user_id' => $user->id,
]);

$checkingAccount->save();

echo "Created checking account with initial balance: {$eurCurrency->symbol}" . $checkingAccount->balance . "\n";

// List all accounts with their balances
echo "\nAll accounts:\n";
$accounts = Account::with('currency')->get();
foreach ($accounts as $account) {
    echo "- {$account->name}: {$account->currency->symbol}{$account->balance} ({$account->currency->code})\n";
}
