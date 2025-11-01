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

        // Total balance should now be in PEN (base currency)
        expect($result['total_balance'])->toMatchArray([
            'currency' => [
                'code' => 'PEN',
                'symbol' => 'S/',
                'name' => 'Peruvian Sol',
            ],
        ])
            ->and($result['total_balance']['total'])->toBe('1,500.75') // USD has exchange rate of 1.0 by default in factory
            ->and($result['balances_by_currency'])->toHaveCount(1)
            ->and($result['balances_by_currency'][0])
            ->toMatchArray([
                'currency' => [
                    'code' => 'USD',
                    'symbol' => '$',
                    'name' => $this->currency->name,
                ],
                'total' => '1,500.75',
                'total_in_pen' => '1,500.75',
                'exchange_rate' => 1.0,
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
            ->and($result['total_balance'])->toMatchArray([
                'currency' => [
                    'code' => 'PEN',
                    'symbol' => 'S/',
                    'name' => 'Peruvian Sol',
                ],
            ])
            // Total should be 1000 (USD at rate 1.0) + 800 (EUR at rate 1.0) = 1800 PEN
            ->and($result['total_balance']['total'])->toBe('1,800.00');
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

        expect($result['total_balance'])->toMatchArray([
            'currency' => [
                'code' => 'PEN',
                'symbol' => 'S/',
                'name' => 'Peruvian Sol',
            ],
        ])
            ->and($result['total_balance']['total'])->toBe('1,000.00')
            ->and($result['balances_by_currency'])->toHaveCount(1)
            ->and($result['balances_by_currency'][0])
            ->toMatchArray([
                'currency' => [
                    'code' => 'USD',
                    'symbol' => '$',
                    'name' => $this->currency->name,
                ],
                'total' => '1,000.00',
                'total_in_pen' => '1,000.00',
                'exchange_rate' => 1.0,
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
                'amount' => '500.00',
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
                'total_income' => '0.00',
                'total_expenses' => '0.00',
                'total_transfers' => '0.00',
                'net_income' => '0.00',
                'transactions_count' => 0,
            ])
            ->and($result['expenses_by_category'])->toBeEmpty()
            ->and($result['transactions'])->toBeEmpty();
    });

    it('includes all transactions for the month', function () {
        $currentMonth = Carbon::now()->startOfMonth();

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 3000,
            'type' => 'income',
            'date' => $currentMonth->copy()->addDays(5),
            'description' => 'Salary payment',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 500,
            'type' => 'expense',
            'date' => $currentMonth->copy()->addDays(10),
            'description' => 'Groceries',
        ]);

        $result = $this->dashboardService->getMonthlyBasicSummary();

        expect($result['transactions'])->toHaveCount(2)
            ->and($result['transactions'][0])->toHaveKeys([
                'id', 'type', 'amount', 'amount_in_pen', 'date',
                'description', 'category', 'account', 'currency', 'to_account',
            ])
            ->and($result['transactions'][0]['description'])->toBe('Groceries') // Latest first
            ->and($result['transactions'][1]['description'])->toBe('Salary payment')
            ->and($result['transactions'][0]['amount_in_pen'])->toBe('500.00')
            ->and($result['transactions'][0]['currency']['code'])->toBe('USD');
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
            'monthly_comparison',
            'quick_stats',
        ])
            ->and($result['balance'])->toHaveKeys([
                'total_balance',
                'balances_by_currency',
                'accounts_summary',
            ])
            ->and($result['monthly_summary'])->toHaveKeys([
                'period',
                'currency',
                'summary',
                'transactions',
                'expenses_by_category',
                'daily_balance',
            ]);

    });
});
