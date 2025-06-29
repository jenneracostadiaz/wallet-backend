<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
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

        $balances = [];
        $totalInPrimaryCurrency = 0;

        foreach ($accounts as $accountsGroup) {
            $currency = $accountsGroup->first()->currency;
            $total = $accountsGroup->sum('balance');

            $balances[] = [
                'currency' => [
                    'code' => $currency->code,
                    'symbol' => $currency->symbol,
                    'name' => $currency->name,
                ],
                'total' => number_format($total, $currency->decimal_places),
                'accounts_count' => $accountsGroup->count(),
            ];

            if (empty($totalInPrimaryCurrency)) {
                $totalInPrimaryCurrency = [
                    'currency' => [
                        'code' => $currency->code,
                        'symbol' => $currency->symbol,
                        'name' => $currency->name,
                    ],
                    'total' => number_format($total, $currency->decimal_places),
                ];
            }
        }

        return [
            'total_balance' => $totalInPrimaryCurrency,
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

        $income = $transactions->where('type', 'income')->sum('amount');
        $expenses = $transactions->where('type', 'expense')->sum('amount');
        $transfers = $transactions->where('type', 'transfer')->sum('amount');

        $expensesByCategory = $transactions
            ->where('type', 'expense')
            ->groupBy('category.name')
            ->map(function ($items, $categoryName) {
                return [
                    'category' => $categoryName ?: 'Sin categoría',
                    'amount' => $items->sum('amount'),
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

        // Obtener moneda principal igual que en getCurrentTotalBalance
        $accounts = Account::query()->where('user_id', $this->userId)
            ->with('currency')
            ->get();
        $primaryCurrency = $accounts->first() ? $accounts->first()->currency : null;
        $currencyArr = $primaryCurrency ? [
            'code' => $primaryCurrency->code,
            'symbol' => $primaryCurrency->symbol,
            'name' => $primaryCurrency->name,
            'decimal_places' => $primaryCurrency->decimal_places,
        ] : null;
        $decimals = $primaryCurrency ? $primaryCurrency->decimal_places : 2;

        return [
            'period' => [
                'month' => $date->format('Y-m'),
                'month_name' => $date->format('F Y'),
                'start_date' => $startOfMonth->format('Y-m-d'),
                'end_date' => $endOfMonth->format('Y-m-d'),
            ],
            'currency' => $currencyArr,
            'summary' => [
                'total_income' => number_format($income, $decimals),
                'total_expenses' => number_format($expenses, $decimals),
                'total_transfers' => number_format($transfers, $decimals),
                'net_income' => number_format($income - $expenses, $decimals),
                'transactions_count' => $transactions->count(),
            ],
            'expenses_by_category' => $expensesByCategory->take(10), // Top 10 categorías
            'daily_balance' => $this->getDailyBalanceForMonth($startOfMonth, $endOfMonth),
        ];
    }

    public function getLatestTransactions(int $limit = 10): array
    {
        $transactions = Transaction::forUser($this->userId)
            ->withRelations()
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'date' => $transaction->date->format('Y-m-d H:i:s'),
                'date_human' => $transaction->date->diffForHumans(),
                'type' => $transaction->type,
                'account' => [
                    'name' => $transaction->account->name,
                    'color' => $transaction->account->color,
                    'currency' => $transaction->account->currency->symbol,
                ],
                'category' => $transaction->category ? [
                    'name' => $transaction->category->name,
                    'icon' => $transaction->category->icon,
                    'parent' => $transaction->category->parent ? [
                        'name' => $transaction->category->parent->name,
                        'icon' => $transaction->category->parent->icon,
                    ] : null,
                ] : null,
                'to_account' => $transaction->toAccount ? [
                    'name' => $transaction->toAccount->name,
                ] : null,
            ];
        })->toArray();
    }

    public function getMonthlyComparison(): array
    {
        $currentMonth = Carbon::now();
        $previousMonth = $currentMonth->copy()->subMonth();

        $currentSummary = $this->getMonthlyBasicSummary($currentMonth->format('Y-m'));
        $previousSummary = $this->getMonthlyBasicSummary($previousMonth->format('Y-m'));

        $incomeOld = (float)str_replace(',', '', $previousSummary['summary']['total_income']);
        $incomeNew = (float)str_replace(',', '', $currentSummary['summary']['total_income']);
        $expenseOld = (float)str_replace(',', '', $previousSummary['summary']['total_expenses']);
        $expenseNew = (float)str_replace(',', '', $currentSummary['summary']['total_expenses']);
        $netOld = (float)str_replace(',', '', $previousSummary['summary']['net_income']);
        $netNew = (float)str_replace(',', '', $currentSummary['summary']['net_income']);

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
            'latest_transactions' => $this->getLatestTransactions(),
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
                    'name' => $item->name,
                    'type' => $item->type,
                    'balance' => number_format($item->balance, $item->currency->decimal_places),
                    'description' => $item->description,
                    'color' => $item->color,
                    "currency" => [
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
        $dailyTransactions = Transaction::forUser($this->userId)
            ->betweenDates($start, $end)
            ->selectRaw('DATE(date) as transaction_date,
                        SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as daily_income,
                        SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as daily_expenses')
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get()
            ->keyBy('transaction_date');

        $dailyBalance = [];
        $currentDate = $start->copy();

        while ($currentDate <= $end) {
            $dateStr = $currentDate->format('Y-m-d');
            $transaction = $dailyTransactions->get($dateStr);

            $dailyBalance[] = [
                'date' => $dateStr,
                'income' => $transaction->daily_income ?? 0,
                'expenses' => $transaction->daily_expenses ?? 0,
                'net' => ($transaction->daily_income ?? 0) - ($transaction->daily_expenses ?? 0),
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
