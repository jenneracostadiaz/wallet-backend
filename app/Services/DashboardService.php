<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;

readonly class DashboardService
{
    public function __construct(private int $userId) {}



    private function getAccountsSummary(): array
    {
        return Account::query()->where('user_id', $this->userId)
            ->selectRaw('type, COUNT(*) as count, SUM(balance) as total_balance')
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type,
                    'count' => $item->count,
                    'total_balance' => $item->total_balance,
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
