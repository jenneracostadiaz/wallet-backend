<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('transactions/index', [
            'transactions' => auth()->user()->transactions()
                ->with(['account.currency', 'category', 'toAccount.currency'])
                ->orderBy('date', 'desc')
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('transactions/create', [
            'accounts' => auth()->user()->accounts()->with('currency')->get(),
            'categories' => auth()->user()->categories()->get(),
            'types' => [
                [
                    'value' => 'income',
                    'label' => 'Income',
                ],
                [
                    'value' => 'expense',
                    'label' => 'Expense',
                ],
                [
                    'value' => 'transfer',
                    'label' => 'Transfer',
                ],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request)
    {
        $validated = $request->validated();
        $account = auth()->user()->accounts()->findOrFail($validated['account_id']);

        if ($validated['type'] === 'transfer' && $validated['to_account_id']) {
            // Verificar que la cuenta destino también pertenece al usuario
            $toAccount = auth()->user()->accounts()->findOrFail($validated['to_account_id']);
        }

        if ($validated['category_id']) {
            // Verificar que la categoría pertenece al usuario y es del tipo correcto
            $category = auth()->user()->categories()
                ->where('id', $validated['category_id'])
                ->where('type', $validated['type'])
                ->firstOrFail();
        }

        DB::transaction(function () use ($validated, $account) {
            $validated['user_id'] = auth()->id();

            // Crear la transacción
            $transaction = auth()->user()->transactions()->create($validated);

            // Actualizar saldos de cuentas
            $this->updateAccountBalances($transaction);
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        // Verificar que la transacción pertenece al usuario
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        return inertia('transactions/edit', [
            'transaction' => $transaction->load(['account.currency', 'category', 'toAccount.currency']),
            'accounts' => auth()->user()->accounts()->with('currency')->get(),
            'categories' => auth()->user()->categories()->get(),
            'types' => [
                [
                    'value' => 'income',
                    'label' => 'Income',
                ],
                [
                    'value' => 'expense',
                    'label' => 'Expense',
                ],
                [
                    'value' => 'transfer',
                    'label' => 'Transfer',
                ],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $request, Transaction $transaction)
    {

        // Verificar que la cuenta pertenece al usuario
        $account = auth()->user()->accounts()->findOrFail($validated['account_id']);

        if ($validated['type'] === 'transfer' && $validated['to_account_id']) {
            // Verificar que la cuenta destino también pertenece al usuario
            $toAccount = auth()->user()->accounts()->findOrFail($validated['to_account_id']);
        }

        if ($validated['category_id']) {
            // Verificar que la categoría pertenece al usuario y es del tipo correcto
            $category = auth()->user()->categories()
                ->where('id', $validated['category_id'])
                ->where('type', $validated['type'])
                ->firstOrFail();
        }

        DB::transaction(function () use ($validated, $transaction) {
            // Revertir los cambios de saldo anteriores
            $this->revertAccountBalances($transaction);

            // Actualizar la transacción
            $transaction->update($validated);

            // Aplicar los nuevos cambios de saldo
            $this->updateAccountBalances($transaction->fresh());
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {

        DB::transaction(function () use ($transaction) {
            // Revertir los cambios de saldo
            $this->revertAccountBalances($transaction);

            // Eliminar la transacción
            $transaction->delete();
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully!');
    }

    /**
     * Update account balances based on transaction
     */
    private function updateAccountBalances(Transaction $transaction)
    {
        $account = $transaction->account;

        switch ($transaction->type) {
            case 'income':
                $account->increment('balance', $transaction->amount);
                break;

            case 'expense':
                $account->decrement('balance', $transaction->amount);
                break;

            case 'transfer':
                if ($transaction->toAccount) {
                    // Restar del origen
                    $account->decrement('balance', $transaction->amount);
                    // Sumar al destino
                    $transaction->toAccount->increment('balance', $transaction->amount);
                }
                break;
        }
    }

    /**
     * Revert account balances based on transaction
     */
    private function revertAccountBalances(Transaction $transaction)
    {
        $account = $transaction->account;

        switch ($transaction->type) {
            case 'income':
                $account->decrement('balance', $transaction->amount);
                break;

            case 'expense':
                $account->increment('balance', $transaction->amount);
                break;

            case 'transfer':
                if ($transaction->toAccount) {
                    // Revertir: sumar al origen
                    $account->increment('balance', $transaction->amount);
                    // Revertir: restar del destino
                    $transaction->toAccount->decrement('balance', $transaction->amount);
                }
                break;
        }
    }
}
