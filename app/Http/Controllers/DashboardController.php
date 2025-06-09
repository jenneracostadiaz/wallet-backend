<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $accounts = $user->accounts()->with('currency')->get();
        // Agrupar balances por moneda
        $totalsByCurrency = $accounts->groupBy('currency_id')->map(function($accounts) {
            $currency = $accounts->first()->currency;
            $total = $accounts->sum('balance');
            return [
                'currency' => $currency->code,
                'currency_symbol' => $currency->symbol,
                'total' => $total,
            ];
        })->values();
        // Listado de cuentas con balance y moneda
        $accountsList = $accounts->map(function($account) {
            return [
                'name' => $account->name,
                'balance' => $account->balance,
                'currency' => $account->currency->code,
                'currency_symbol' => $account->currency->symbol,
            ];
        });
        // Resumen mensual básico
        $currentMonth = now()->format('Y-m');
        $monthlySummary = $user->transactions()
            ->whereRaw("strftime('%Y-%m', date) = ?", [$currentMonth])
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type');
        // Últimas transacciones (máximo 5)
        $latestTransactions = $user->transactions()
            ->with(['account.currency', 'category'])
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get()
            ->map(function($t) {
                return [
                    'id' => $t->id,
                    'date' => $t->date,
                    'amount' => $t->amount,
                    'type' => $t->type,
                    'account' => $t->account ? $t->account->name : null,
                    'currency_symbol' => $t->account && $t->account->currency ? $t->account->currency->symbol : '',
                    'category' => $t->category ? $t->category->name : null,
                    'description' => $t->description,
                ];
            });
        return Inertia::render('dashboard', [
            'totalsByCurrency' => $totalsByCurrency,
            'accounts' => $accountsList,
            'monthlySummary' => $monthlySummary,
            'latestTransactions' => $latestTransactions,
        ]);
    }
}
