# Wallet v3

A personal finance system that helps you track your accounts, transactions, and categories.

## Features

- Multiple account management
- Transaction tracking
- Category management
- User authentication
- Multi-currency support

## Currency Management

The system supports multiple currencies through the Currency model. Each account is associated with a specific currency.

### Available Currencies

The system comes with several pre-configured currencies:

- USD (US Dollar)
- EUR (Euro)
- GBP (British Pound)
- JPY (Japanese Yen)
- CAD (Canadian Dollar)
- AUD (Australian Dollar)
- CHF (Swiss Franc)
- CNY (Chinese Yuan)

You can add more currencies as needed.

## Account Management

### Setting Initial Balance

When creating a new account, you can set an initial balance in two ways:

#### Method 1: Using the `setInitialBalance` method

```php
// Get the currency
$usdCurrency = Currency::where('code', 'USD')->first();

$account = new Account([
    'name' => 'My Savings',
    'type' => 'savings',
    'currency_id' => $usdCurrency->id,
    'user_id' => $user->id,
]);

// Set the initial balance and save
$account->setInitialBalance(5000.00)->save();
```

#### Method 2: Setting the balance directly

```php
// Get the currency
$usdCurrency = Currency::where('code', 'USD')->first();

$account = new Account([
    'name' => 'My Checking',
    'type' => 'checking',
    'balance' => 2500.75, // Set initial balance directly
    'currency_id' => $usdCurrency->id,
    'user_id' => $user->id,
]);

$account->save();
```

## Database Seeders

The application includes seeders to populate the database with test data:

### Available Seeders

- **UserSeeder**: Creates a default test user and 5 random users
- **CurrencySeeder**: Creates common currencies (USD, EUR, GBP, JPY, etc.)
- **CategorySeeder**: Creates default income and expense categories for each user
- **AccountSeeder**: Creates default accounts (Cash, Checking, Savings, Credit Card) for each user
- **TransactionSeeder**: Creates test transactions (income, expenses, transfers) for each user

### Running the Seeders

To run all seeders at once:

```bash
php artisan db:seed
```

To run a specific seeder:

```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=AccountSeeder
php artisan db:seed --class=TransactionSeeder
```

### Test Data Overview

The seeders will create:

- A default user (email: test@example.com, password: password) and 5 random users
- 8 common currencies (USD, EUR, GBP, JPY, CAD, AUD, CHF, CNY)
- 5 income categories and 12 expense categories for each user
- 4 accounts for each user with different initial balances (all in USD by default)
- Various transactions including monthly salary, random expenses, and transfers between accounts

## Examples

Check the `examples` directory for sample code demonstrating how to use the system:

- `examples/account_initial_balance.php`: Shows how to create accounts with initial balances

## Testing

Run the tests to ensure everything is working correctly:

```bash
php artisan test
```
