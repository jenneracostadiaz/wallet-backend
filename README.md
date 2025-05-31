# Wallet v3

A personal finance system that helps you track your accounts, transactions, and categories.

## Features

- Multiple account management
- Transaction tracking
- Category management
- User authentication

## Account Management

### Setting Initial Balance

When creating a new account, you can set an initial balance in two ways:

#### Method 1: Using the `setInitialBalance` method

```php
$account = new Account([
    'name' => 'My Savings',
    'type' => 'savings',
    'currency' => 'USD',
    'user_id' => $user->id,
]);

// Set the initial balance and save
$account->setInitialBalance(5000.00)->save();
```

#### Method 2: Setting the balance directly

```php
$account = new Account([
    'name' => 'My Checking',
    'type' => 'checking',
    'balance' => 2500.75, // Set initial balance directly
    'currency' => 'USD',
    'user_id' => $user->id,
]);

$account->save();
```

## Database Seeders

The application includes seeders to populate the database with test data:

### Available Seeders

- **UserSeeder**: Creates a default test user and 5 random users
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
- 5 income categories and 12 expense categories for each user
- 4 accounts for each user with different initial balances
- Various transactions including monthly salary, random expenses, and transfers between accounts

## Examples

Check the `examples` directory for sample code demonstrating how to use the system:

- `examples/account_initial_balance.php`: Shows how to create accounts with initial balances

## Testing

Run the tests to ensure everything is working correctly:

```bash
php artisan test
```
