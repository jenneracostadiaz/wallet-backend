<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->currency = Currency::factory()->create([
        'code' => 'USD',
        'symbol' => '$',
        'decimal_places' => 2,
    ]);

    $this->eurCurrency = Currency::factory()->create([
        'code' => 'EUR',
        'symbol' => '€',
        'decimal_places' => 2,
    ]);

    $this->dashboardService = new DashboardService($this->user->id);
});

describe('getCurrentTotalBalance', function () {
    it('returns correct total balance for single currency', function () {
        Account::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $this->currency->id,
            'balance' => 1000.50,
            'type' => 'checking',
        ]);

        Account::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $this->currency->id,
            'balance' => 500.25,
            'type' => 'savings',
        ]);

        $result = $this->dashboardService->getCurrentTotalBalance();

        expect($result['total_balance'])->toBe([
            'currency' => [
                'code' => 'USD',
                'symbol' => '$',
                'name' => $this->currency->name,
            ],
            'total' => '1,500.75',
        ])
            ->and($result['balances_by_currency'])->toHaveCount(1)
            ->and($result['balances_by_currency'][0])
            ->toMatchArray([
                'currency' => [
                    'code' => 'USD',
                    'symbol' => '$',
                    'name' => $this->currency->name,
                ],
                'total' => '1,500.75',
            ]);
    });

    it('returns correct balance for multiple currencies', function () {
        Account::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $this->currency->id,
            'balance' => 1000,
            'type' => 'checking',
        ]);

        Account::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $this->eurCurrency->id,
            'balance' => 800,
            'type' => 'savings',
        ]);

        $result = $this->dashboardService->getCurrentTotalBalance();

        expect($result['balances_by_currency'])->toHaveCount(2)
            ->and($result['total_balance'])->toBe([
                'currency' => [
                    'code' => 'USD',
                    'symbol' => '$',
                    'name' => $this->currency->name,
                ],
                'total' => '1,000.00',
            ]);
    });

    it('excludes other users accounts', function () {
        Account::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $this->currency->id,
            'balance' => 1000,
        ]);

        Account::factory()->create([
            'user_id' => $this->otherUser->id,
            'currency_id' => $this->currency->id,
            'balance' => 5000,
        ]);

        $result = $this->dashboardService->getCurrentTotalBalance();

        expect($result['total_balance'])->toBe([
            'currency' => [
                'code' => 'USD',
                'symbol' => '$',
                'name' => $this->currency->name,
            ],
            'total' => '1,000.00',
        ])
            ->and($result['balances_by_currency'])->toHaveCount(1)
            ->and($result['balances_by_currency'][0])
            ->toMatchArray([
                'currency' => [
                    'code' => 'USD',
                    'symbol' => '$',
                    'name' => $this->currency->name,
                ],
                'total' => '1,000.00',
            ])
            ->and($result['balances_by_currency'][0]['accounts_count'])->toBe(1);
    });

    it('returns empty data when user has no accounts', function () {
        $result = $this->dashboardService->getCurrentTotalBalance();

        expect($result['total_balance'])->toBe(0)
            ->and($result['balances_by_currency'])->toBeEmpty();
    });
});

describe('getMonthlyBasicSummary', function () {
    beforeEach(function () {
        $this->account = Account::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $this->currency->id,
            'balance' => 1000,
        ]);

        $this->incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'name' => 'Salary',
        ]);

        $this->expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'name' => 'Food',
        ]);
    });

    it('calculates monthly summary correctly', function () {
        $currentMonth = Carbon::now()->startOfMonth();

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 3000,
            'type' => 'income',
            'date' => $currentMonth->copy()->addDays(5),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 1000,
            'type' => 'income',
            'date' => $currentMonth->copy()->addDays(15),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 500,
            'type' => 'expense',
            'date' => $currentMonth->copy()->addDays(10),
        ]);

        $result = $this->dashboardService->getMonthlyBasicSummary();

        expect($result['summary'])
            ->toMatchArray([
                'total_income' => '4,000.00',
                'total_expenses' => '500.00',
                'total_transfers' => '0.00',
                'net_income' => '3,500.00',
                'transactions_count' => 3,
            ])
            ->and($result['period']['month'])->toBe($currentMonth->format('Y-m'))
            ->and($result['expenses_by_category'])->toHaveCount(1)
            ->and($result['expenses_by_category'][0])
            ->toMatchArray([
                'category' => 'Food',
                'amount' => 500,
                'count' => 1,
                'percentage' => 100.0,
            ]);

    });

    it('handles specific month parameter', function () {
        $specificMonth = Carbon::create(2024, 6);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 2000,
            'type' => 'income',
            'date' => $specificMonth->copy()->addDays(5),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 1000,
            'type' => 'income',
            'date' => $specificMonth->copy()->addMonth(),
        ]);

        $result = $this->dashboardService->getMonthlyBasicSummary('2024-06');

        expect($result['summary']['total_income'])->toBe('2,000.00')
            ->and($result['summary']['transactions_count'])->toBe(1)
            ->and($result['period']['month'])->toBe('2024-06');
    });

    it('excludes other users transactions', function () {
        $otherAccount = Account::factory()->create([
            'user_id' => $this->otherUser->id,
            'currency_id' => $this->currency->id,
        ]);

        $otherCategory = Category::factory()->create([
            'user_id' => $this->otherUser->id,
            'type' => 'income',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 1000,
            'type' => 'income',
            'date' => Carbon::now(),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->otherUser->id,
            'account_id' => $otherAccount->id,
            'category_id' => $otherCategory->id,
            'amount' => 5000,
            'type' => 'income',
            'date' => Carbon::now(),
        ]);

        $result = $this->dashboardService->getMonthlyBasicSummary();

        expect($result['summary']['total_income'])->toBe('1,000.00')
            ->and($result['summary']['transactions_count'])->toBe(1);
    });

    it('handles month with no transactions', function () {
        $result = $this->dashboardService->getMonthlyBasicSummary();

        expect($result['summary'])
            ->toMatchArray([
                'total_income' => 0,
                'total_expenses' => 0,
                'total_transfers' => 0,
                'net_income' => 0,
                'transactions_count' => 0,
            ])
            ->and($result['expenses_by_category'])->toBeEmpty();

    });
});

describe('getLatestTransactions', function () {
    beforeEach(function () {
        $this->account = Account::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $this->currency->id,
            'name' => 'Main Account',
        ]);

        $this->category = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Food',
            'icon' => 'food-icon',
        ]);
    });

    it('returns latest transactions in descending order', function () {
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'amount' => 100,
            'description' => 'Older transaction',
            'date' => Carbon::now()->subDays(2),
            'type' => 'expense',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'amount' => 200,
            'description' => 'Newer transaction',
            'date' => Carbon::now()->subDay(),
            'type' => 'expense',
        ]);

        $result = $this->dashboardService->getLatestTransactions(5);

        expect($result)->toHaveCount(2)
            ->and($result[0]['description'])->toBe('Newer transaction')
            ->and($result[1]['description'])->toBe('Older transaction')
            ->and($result[0]['amount'])->toBe('200.00');
    });

    it('respects the limit parameter', function () {
        Transaction::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'date' => Carbon::now(),
        ]);

        $result = $this->dashboardService->getLatestTransactions(5);

        expect($result)->toHaveCount(5);
    });

    it('includes all required transaction data', function () {
        $toAccount = Account::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $this->currency->id,
            'name' => 'Savings Account',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'to_account_id' => $toAccount->id,
            'category_id' => $this->category->id,
            'amount' => 500,
            'description' => 'Transfer to savings',
            'date' => Carbon::now(),
            'type' => 'transfer',
        ]);

        $result = $this->dashboardService->getLatestTransactions(1);

        expect($result[0])
            ->toHaveKeys([
                'id', 'amount', 'description', 'date',
                'type', 'account', 'category', 'to_account',
            ])
            ->and($result[0]['account']['name'])->toBe('Main Account')
            ->and($result[0]['category']['name'])->toBe('Food')
            ->and($result[0]['to_account']['name'])->toBe('Savings Account');

    });

    it('excludes other users transactions', function () {
        $otherAccount = Account::factory()->create([
            'user_id' => $this->otherUser->id,
            'currency_id' => $this->currency->id,
        ]);

        $otherCategory = Category::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'amount' => 100,
            'date' => Carbon::now(),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->otherUser->id,
            'account_id' => $otherAccount->id,
            'category_id' => $otherCategory->id,
            'amount' => 200,
            'date' => Carbon::now(),
        ]);

        $result = $this->dashboardService->getLatestTransactions();

        expect($result)->toHaveCount(1)
            ->and($result[0]['amount'])->toBe('100.00');
    });

    it('handles user with no transactions', function () {
        $result = $this->dashboardService->getLatestTransactions();

        expect($result)->toBeEmpty();
    });
});

describe('getMonthlyComparison', function () {
    beforeEach(function () {
        $this->account = Account::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $this->currency->id,
        ]);

        $this->category = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income',
        ]);
    });

    it('compares current month with previous month', function () {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = $currentMonth->copy()->subMonth();

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'amount' => 2000,
            'type' => 'income',
            'date' => $previousMonth->copy()->addDays(10),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'amount' => 2500,
            'type' => 'income',
            'date' => $currentMonth->copy()->addDays(10),
        ]);

        $result = $this->dashboardService->getMonthlyComparison();

        expect($result)->toHaveKeys(['current_month', 'previous_month', 'comparison'])
            ->and($result['comparison']['income_change']['percentage'])->toBe(25.0)
            ->and($result['comparison']['income_change']['direction'])->toBe('up')
            ->and($result['comparison']['income_change']['amount_change'])->toBe(500.0);
    });

    it('handles zero values in previous month', function () {
        $currentMonth = Carbon::now()->startOfMonth();

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'amount' => 1000,
            'type' => 'income',
            'date' => $currentMonth->copy()->addDays(10),
        ]);

        $result = $this->dashboardService->getMonthlyComparison();

        expect($result['comparison']['income_change']['percentage'])->toBe(100)
            ->and($result['comparison']['income_change']['direction'])->toBe('up');
    });
});

describe('getDashboardData', function () {
    it('returns complete dashboard data structure', function () {
        Account::factory()->create([
            'user_id' => $this->user->id,
            'currency_id' => $this->currency->id,
            'balance' => 1000,
        ]);

        $result = $this->dashboardService->getDashboardData();

        expect($result)->toHaveKeys([
            'balance',
            'monthly_summary',
            'latest_transactions',
            'monthly_comparison',
            'quick_stats',
        ])
            ->and($result['balance'])->toHaveKeys([
                'total_balance',
                'balances_by_currency',
                'accounts_summary',
            ]);

    });
});
