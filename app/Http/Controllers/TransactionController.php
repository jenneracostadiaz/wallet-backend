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
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $transactionService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $transactions = Transaction::forUser(auth()->id())
            ->when($request->filled('category_id'), fn ($q) => $q->inCategory($request->input('category_id')))
            ->when($request->filled('account_id'), fn ($q) => $q->fromAccount($request->input('account_id')))
            ->when($request->filled('date_from') || $request->filled('date_to'), fn ($q) => $q->betweenDates($request->input('date_from'), $request->input('date_to')))
            ->when($request->filled('type'), fn ($q) => $q->ofType($request->input('type')))
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->withRelations();

        return TransactionResource::collection($transactions->get());
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

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Transaction::class);

        $transactions = Transaction::forUser(auth()->id())
            ->when($request->filled('category_id'), fn ($q) => $q->inCategory($request->input('category_id')))
            ->when($request->filled('account_id'), fn ($q) => $q->fromAccount($request->input('account_id')))
            ->when($request->filled('date_from') || $request->filled('date_to'), fn ($q) => $q->betweenDates($request->input('date_from'), $request->input('date_to')))
            ->when($request->filled('type'), fn ($q) => $q->ofType($request->input('type')))
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->withRelations();

        $fileName = 'transactions_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Date', 'Currency', 'Amount', 'Type', 'Description', 'Category', 'Account'];

        $callback = function() use($transactions, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $transactions->chunk(1000, function ($chunk) use ($file) {
                foreach ($chunk as $transaction) {
                    fputcsv($file, [
                        $transaction->id,
                        $transaction->date->format('Y-m-d'),
                        $transaction->account->currency->code ?? '',
                        $transaction->amount,
                        $transaction->type,
                        $transaction->description,
                        $transaction->category->name ?? '',
                        $transaction->account->name ?? '',
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
