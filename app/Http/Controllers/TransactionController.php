<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransactionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $transactions = auth()->user()
            ->transactions();

        // Filters
        if ($request->filled('category_id')) {
            $transactions->where('category_id', $request
                ->input('category_id'));
        }

        if ($request->filled('account_id')) {
            $transactions->where('account_id', $request
                ->input('account_id'));
        }

        if ($request->filled('date_from')) {
            $transactions->whereDate('date', '>=', $request
                ->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $transactions->whereDate('date', '<=', $request
                ->input('date_to'));
        }

        if ($request->filled('type')) {
            $types = $request->input('type');
            if (! is_array($types)) {
                $types = [$types];
            }
            $transactions->whereIn('type', $types);
        }

        return TransactionResource::collection($transactions->paginate());
    }

    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);

        return new TransactionResource($transaction->load(['account.currency', 'category']));
    }

    public function store(StoreTransactionRequest $request)
    {
        $transaction = auth()->user()->transactions()->create([
            ...$request->validated(),
        ]);

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
