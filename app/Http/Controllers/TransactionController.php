<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Throwable;

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

    /**
     * @throws Throwable
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = null;

        DB::transaction(function () use ($request, &$transaction) {

            $transaction = auth()->user()->transactions()->create([
                ...$request->validated(),
            ]);

            $this->updateAccountBalances($transaction);
        });

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $this->authorize('update', $transaction);

        DB::transaction(function () use ($request, $transaction) {
            $this->revertAccountBalances($transaction);
            $transaction->update($request->validated());
            $this->updateAccountBalances($transaction);
        });

        return (new TransactionResource($transaction->load(['account.currency', 'category'])))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }

    private function updateAccountBalances(Transaction $transaction): void
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
                    $account->decrement('balance', $transaction->amount);
                    $transaction->toAccount->increment('balance', $transaction->amount);
                }
                break;
        }
    }

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
                    $account->increment('balance', $transaction->amount);
                    $transaction->toAccount->decrement('balance', $transaction->amount);
                }
                break;
        }
    }
}
