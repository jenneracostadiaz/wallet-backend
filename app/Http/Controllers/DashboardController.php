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
        return Inertia::render('dashboard', [
            'totalsByCurrency' => $totalsByCurrency,
            'accounts' => $accountsList,
        ]);
    }
}

