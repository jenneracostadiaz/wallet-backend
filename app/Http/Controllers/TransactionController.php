<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // Definir tipos de transacción como constantes
    private const TRANSACTION_TYPES = [
        ['value' => 'income', 'label' => 'Income'],
        ['value' => 'expense', 'label' => 'Expense'],
        ['value' => 'transfer', 'label' => 'Transfer'],
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->transactions()
            ->with(['account.currency', 'category', 'toAccount.currency'])
            ->orderBy('date', 'desc');

        // Filtros
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->input('date_to'));
        }

        return inertia('transactions/index', [
            'transactions' => $query->get(),
            'categories' => auth()->user()->categories()->get(),
            'filters' => [
                'category_id' => $request->input('category_id'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ],
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
            'types' => self::TRANSACTION_TYPES,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request)
    {
        $validated = $request->validated();
        $this->validateTransactionPrerequisites($validated);

        DB::transaction(function () use ($validated) {
            $validated['user_id'] = auth()->id();
            $transaction = auth()->user()->transactions()->create($validated);
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
        $this->authorizeTransaction($transaction);

        return inertia('transactions/edit', [
            'transaction' => $transaction->load(['account.currency', 'category', 'toAccount.currency']),
            'accounts' => auth()->user()->accounts()->with('currency')->get(),
            'categories' => auth()->user()->categories()->get(),
            'types' => self::TRANSACTION_TYPES,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $request, Transaction $transaction)
    {
        $this->authorizeTransaction($transaction); // Authorize first

        $validated = $request->validated();
        $this->validateTransactionPrerequisites($validated);

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
        $this->authorizeTransaction($transaction); // Authorize first

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

    private function findUserAccount($accountId)
    {
        return auth()->user()->accounts()->findOrFail($accountId);
    }

    private function validateCategory($categoryId, $type)
    {
        auth()->user()->categories()
            ->where('id', $categoryId)
            ->where('type', $type)
            ->firstOrFail();
    }

    private function authorizeTransaction(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }
    }

    /**
     * Validate common transaction prerequisites.
     */
    private function validateTransactionPrerequisites(array $validatedData): void
    {
        $this->findUserAccount($validatedData['account_id']);

        if ($validatedData['type'] === 'transfer') {
            $this->findUserAccount($validatedData['to_account_id']);
        }

        if ($validatedData['category_id']) {
            $this->validateCategory($validatedData['category_id'], $validatedData['type']);
        }
    }
}
