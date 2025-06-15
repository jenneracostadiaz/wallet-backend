<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $transactionService) {}

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
        $transaction = $this->transactionService->create(
            auth()->user(),
            $request->validated()
        );

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

        $updatedTransaction = $this->transactionService->update(
            $transaction,
            $request->validated()
        );

        return (new TransactionResource($updatedTransaction->load(['account.currency', 'category'])))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * @throws Throwable
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        $this->authorize('delete', $transaction);

        $balance = $this->transactionService->delete($transaction);

        return response()->json([
            'message' => 'Transaction deleted successfully',
            'balance' => $balance,
        ]);
    }
}
