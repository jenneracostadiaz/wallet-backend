<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('transactions/index', [
            'transactions' => auth()->user()->transactions()->with(['account', 'category'])->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('transactions/create', [
            'accounts' => auth()->user()->accounts()->get(),
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
            'type' => 'required|string|in:income,expense,transfer',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required_unless:type,transfer|exists:categories,id|nullable',
            'to_account_id' => 'required_if:type,transfer|exists:accounts,id|nullable',
        ]);

        $validated['user_id'] = auth()->id();

        auth()->user()->transactions()->create($validated);

        return redirect()->route('transactions.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = auth()->user()->transactions()->with(['account', 'category', 'toAccount'])->findOrFail($id);

        return inertia('transactions/show', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $transaction = auth()->user()->transactions()->findOrFail($id);

        return inertia('transactions/edit', [
            'transaction' => $transaction,
            'accounts' => auth()->user()->accounts()->get(),
            'categories' => auth()->user()->categories()->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $transaction = auth()->user()->transactions()->findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
            'type' => 'required|string|in:income,expense,transfer',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required_unless:type,transfer|exists:categories,id|nullable',
            'to_account_id' => 'required_if:type,transfer|exists:accounts,id|nullable',
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transaction = auth()->user()->transactions()->findOrFail($id);
        $transaction->delete();

        return redirect()->route('transactions.index');
    }
}
