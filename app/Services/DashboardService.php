<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Transaction;
use Carbon\Carbon;

readonly class DashboardService
{
    public function __construct(private int $userId) {}

    public function getCurrentTotalBalance(): array
    {
        $accounts = Account::query()->where('user_id', $this->userId)
            ->with('currency')
            ->get()
            ->groupBy('currency.code');

        // If no accounts, return empty
        if ($accounts->isEmpty()) {
            return [
                'total_balance' => 0,
                'balances_by_currency' => [],
                'accounts_summary' => $this->getAccountsSummary(),
            ];
        }

        $balances = [];
        $totalInPen = 0;

        foreach ($accounts as $accountsGroup) {
            $currency = $accountsGroup->first()->currency;
            $total = $accountsGroup->sum('balance');

            // Convert to PEN using exchange rate
            $totalInPen += $total * $currency->exchange_rate_to_pen;

            $balances[] = [
                'currency' => [
                    'code' => $currency->code,
                    'symbol' => $currency->symbol,
                    'name' => $currency->name,
                ],
                'total' => number_format($total, $currency->decimal_places),
                'total_in_pen' => number_format($total * $currency->exchange_rate_to_pen, 2),
                'exchange_rate' => $currency->exchange_rate_to_pen,
                'accounts_count' => $accountsGroup->count(),
            ];
        }

        // Get PEN currency for displaying total, or create it if it doesn't exist
        $penCurrency = Currency::query()->firstOrCreate(
            ['code' => 'PEN'],
            [
                'name' => 'Peruvian Sol',
                'symbol' => 'S/',
                'decimal_places' => 2,
                'exchange_rate_to_pen' => 1.0000,
            ]
        );

        return [
            'total_balance' => [
                'currency' => [
                    'code' => $penCurrency->code,
                    'symbol' => $penCurrency->symbol,
                    'name' => $penCurrency->name,
                ],
                'total' => number_format($totalInPen, 2),
            ],
            'balances_by_currency' => $balances,
            'accounts_summary' => $this->getAccountsSummary(),
        ];
    }

    public function getMonthlyBasicSummary(?string $month = null): array
    {
        $date = $month ? Carbon::parse($month) : Carbon::now();
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $transactions = Transaction::forUser($this->userId)
            ->betweenDates($startOfMonth, $endOfMonth)
            ->withRelations()
            ->get();

        // Convert all amounts to PEN using exchange rates
        $income = $transactions->where('type', 'income')->sum(function ($transaction) {
            return $transaction->amount * $transaction->account->currency->exchange_rate_to_pen;
        });

        $expenses = $transactions->where('type', 'expense')->sum(function ($transaction) {
            return $transaction->amount * $transaction->account->currency->exchange_rate_to_pen;
        });

        $transfers = $transactions->where('type', 'transfer')->sum(function ($transaction) {
            return $transaction->amount * $transaction->account->currency->exchange_rate_to_pen;
        });

        // Group expenses by category and convert to PEN
        $expensesByCategory = $transactions
            ->where('type', 'expense')
            ->groupBy('category.name')
            ->map(function ($items, $categoryName) {
                $totalInPen = $items->sum(function ($transaction) {
                    return $transaction->amount * $transaction->account->currency->exchange_rate_to_pen;
                });

                return [
                    'category' => $categoryName ?: 'Sin categoría',
                    'amount' => $totalInPen,
                    'count' => $items->count(),
                    'percentage' => 0,
                ];
            })
            ->sortByDesc('amount')
            ->values();

        if ($expenses > 0) {
            $expensesByCategory = $expensesByCategory->map(function ($item) use ($expenses) {
                $item['percentage'] = round(($item['amount'] / $expenses) * 100, 1);

                return $item;
            });
        }

        // Use PEN as the primary currency for summary
        $penCurrency = Currency::query()->firstOrCreate(
            ['code' => 'PEN'],
            [
                'name' => 'Peruvian Sol',
                'symbol' => 'S/',
                'decimal_places' => 2,
                'exchange_rate_to_pen' => 1.0000,
            ]
        );

        $currencyArr = [
            'code' => $penCurrency->code,
            'symbol' => $penCurrency->symbol,
            'name' => $penCurrency->name,
            'decimal_places' => $penCurrency->decimal_places,
        ];

        // Format transactions for the month
        $formattedTransactions = $transactions->map(function ($transaction) {
            $amountInPen = $transaction->amount * $transaction->account->currency->exchange_rate_to_pen;

            return [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'amount_in_pen' => number_format($amountInPen, 2),
                'date' => $transaction->date->toDateTimeString(),
                'description' => $transaction->description,
                'category' => [
                    'id' => $transaction->category->id,
                    'name' => $transaction->category->name,
                    'type' => $transaction->category->type,
                    'icon' => $transaction->category->icon,
                ],
                'account' => [
                    'id' => $transaction->account->id,
                    'name' => $transaction->account->name,
                    'type' => $transaction->account->type,
                    'color' => $transaction->account->color,
                ],
                'currency' => [
                    'code' => $transaction->account->currency->code,
                    'symbol' => $transaction->account->currency->symbol,
                ],
                'to_account' => $transaction->toAccount ? [
                    'id' => $transaction->toAccount->id,
                    'name' => $transaction->toAccount->name,
                    'type' => $transaction->toAccount->type,
                    'color' => $transaction->toAccount->color,
                ] : null,
            ];
        })->sortByDesc('date')->values();

        return [
            'period' => [
                'month' => $date->format('Y-m'),
                'month_name' => $date->format('F Y'),
                'start_date' => $startOfMonth->format('Y-m-d'),
                'end_date' => $endOfMonth->format('Y-m-d'),
            ],
            'currency' => $currencyArr,
            'summary' => [
                'total_income' => number_format($income, 2),
                'total_expenses' => number_format($expenses, 2),
                'total_transfers' => number_format($transfers, 2),
                'net_income' => number_format($income - $expenses, 2),
                'transactions_count' => $transactions->count(),
            ],
            'transactions' => $formattedTransactions,
            'expenses_by_category' => $expensesByCategory->take(10)->map(function ($item) {
                $item['amount'] = number_format($item['amount'], 2);
                return $item;
            }),
            'daily_balance' => $this->getDailyBalanceForMonth($startOfMonth, $endOfMonth),
        ];
    }

    public function getMonthlyComparison(): array
    {
        $currentMonth = Carbon::now();
        $previousMonth = $currentMonth->copy()->subMonth();

        $currentSummary = $this->getMonthlyBasicSummary($currentMonth->format('Y-m'));
        $previousSummary = $this->getMonthlyBasicSummary($previousMonth->format('Y-m'));

        $incomeOld = (float) str_replace(',', '', $previousSummary['summary']['total_income']);
        $incomeNew = (float) str_replace(',', '', $currentSummary['summary']['total_income']);
        $expenseOld = (float) str_replace(',', '', $previousSummary['summary']['total_expenses']);
        $expenseNew = (float) str_replace(',', '', $currentSummary['summary']['total_expenses']);
        $netOld = (float) str_replace(',', '', $previousSummary['summary']['net_income']);
        $netNew = (float) str_replace(',', '', $currentSummary['summary']['net_income']);

        return [
            'current_month' => $currentSummary,
            'previous_month' => $previousSummary,
            'comparison' => [
                'income_change' => $this->calculatePercentageChange(
                    $incomeOld,
                    $incomeNew
                ),
                'expense_change' => $this->calculatePercentageChange(
                    $expenseOld,
                    $expenseNew
                ),
                'net_income_change' => $this->calculatePercentageChange(
                    $netOld,
                    $netNew
                ),
            ],
        ];
    }

    public function getDashboardData(): array
    {
        return [
            'balance' => $this->getCurrentTotalBalance(),
            'monthly_summary' => $this->getMonthlyBasicSummary(),
            'monthly_comparison' => $this->getMonthlyComparison(),
            'quick_stats' => $this->getQuickStats(),
        ];
    }

    private function getAccountsSummary(): array
    {
        return Account::query()->where('user_id', $this->userId)
            ->with('currency')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'type' => $item->type,
                    'balance' => number_format($item->balance, $item->currency->decimal_places),
                    'description' => $item->description,
                    'color' => $item->color,
                    'currency' => [
                        'code' => $item->currency->code,
                        'symbol' => $item->currency->symbol,
                        'name' => $item->currency->name,
                    ],
                ];
            })
            ->toArray();
    }

    private function getDailyBalanceForMonth(Carbon $start, Carbon $end): array
    {
        // Load transactions with currency relationships for conversion
        $transactions = Transaction::forUser($this->userId)
            ->betweenDates($start, $end)
            ->with(['account.currency'])
            ->get();

        // Group by date and convert to PEN
        $dailyTransactions = $transactions->groupBy(function ($transaction) {
            return $transaction->date->format('Y-m-d');
        })->map(function ($dayTransactions) {
            $income = $dayTransactions->where('type', 'income')->sum(function ($transaction) {
                return $transaction->amount * $transaction->account->currency->exchange_rate_to_pen;
            });

            $expenses = $dayTransactions->where('type', 'expense')->sum(function ($transaction) {
                return $transaction->amount * $transaction->account->currency->exchange_rate_to_pen;
            });

            return [
                'daily_income' => $income,
                'daily_expenses' => $expenses,
            ];
        });

        $dailyBalance = [];
        $currentDate = $start->copy();

        while ($currentDate <= $end) {
            $dateStr = $currentDate->format('Y-m-d');
            $transaction = $dailyTransactions->get($dateStr);

            $dailyBalance[] = [
                'date' => $dateStr,
                'income' => $transaction['daily_income'] ?? 0,
                'expenses' => $transaction['daily_expenses'] ?? 0,
                'net' => ($transaction['daily_income'] ?? 0) - ($transaction['daily_expenses'] ?? 0),
            ];

            $currentDate->addDay();
        }

        return $dailyBalance;
    }

    private function calculatePercentageChange(float $old, float $new): array
    {
        if ($old == 0) {
            return [
                'percentage' => $new > 0 ? 100 : 0,
                'direction' => $new > 0 ? 'up' : 'neutral',
                'amount_change' => $new,
            ];
        }

        $percentage = (($new - $old) / abs($old)) * 100;

        return [
            'percentage' => round($percentage, 1),
            'direction' => $percentage > 0 ? 'up' : ($percentage < 0 ? 'down' : 'neutral'),
            'amount_change' => $new - $old,
        ];
    }

    public function getQuickStats(): array
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $thisWeek = Carbon::now()->startOfWeek();
        $today = Carbon::today();

        return [
            'transactions_this_month' => Transaction::forUser($this->userId)
                ->where('date', '>=', $thisMonth)
                ->count(),
            'transactions_this_week' => Transaction::forUser($this->userId)
                ->where('date', '>=', $thisWeek)
                ->count(),
            'transactions_today' => Transaction::forUser($this->userId)
                ->whereDate('date', $today)
                ->count(),
            'total_accounts' => Account::query()->where('user_id', $this->userId)->count(),
            'total_categories' => Category::query()->where('user_id', $this->userId)->count(),
        ];
    }
}
