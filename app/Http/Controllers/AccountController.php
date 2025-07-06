<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $accounts = auth()->user()
            ->accounts()
            ->with('currency')
            ->get();

        return AccountResource::collection($accounts->load('currency'));
    }

    public function show(Account $account): AccountResource
    {
        $this->authorize('view', $account);

        return new AccountResource($account->load('currency'));
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = auth()->user()->accounts()->create([
            ...$request->validated(),
            'order' => $this->getNextOrder(),
        ]);

        return (new AccountResource($account->load('currency')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateAccountRequest $request, Account $account): AccountResource
    {
        $this->authorize('update', $account);

        $account->update($request->validated());

        return new AccountResource($account->load('currency'));
    }

    public function destroy(Account $account): JsonResponse
    {
        $this->authorize('delete', $account);

        $account->delete();

        return response()->json([
            'message' => 'Account deleted successfully',
        ]);
    }

    private function getNextOrder(): int
    {
        return auth()->user()->accounts()->max('order') + 1;
    }

    public function exportPdf(Account $account)
    {
        $this->authorize('view', $account);

        $transactions = $account->transactions()->with('category')->get();
        $balance = $account->balance;

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pdf.account-summary', compact('account', 'transactions', 'balance'));

        return $pdf->download('account-summary.pdf');
    }

    public function exportCsv(Account $account): StreamedResponse
    {
        $this->authorize('view', $account);

        $transactions = $account->transactions()->with('category')->get();
        $fileName = 'account-summary.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Date', 'Category', 'Type','Currency', 'Amount', 'Description'];

        $callback = function() use($transactions, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->created_at->format('Y-m-d'),
                    $transaction->category->name,
                    $transaction->type,
                    $transaction->account->currency->code,
                    $transaction->amount,
                    $transaction->description
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
