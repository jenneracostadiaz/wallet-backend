<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionService
{
    /**
     * @throws Throwable
     */
    public function create(User $user, array $data): Transaction
    {
        $transaction = null;

        DB::transaction(function () use ($user, $data, &$transaction) {
            $transaction = $user->transactions()->create($data);
            $this->updateAccountBalances($transaction);
        });

        return $transaction;
    }

    /**
     * @throws Throwable
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        DB::transaction(function () use ($transaction, $data) {
            $this->revertAccountBalances($transaction);
            $transaction->update($data);
            $this->updateAccountBalances($transaction);
        });

        return $transaction;
    }

    /**
     * @throws Throwable
     */
    public function delete(Transaction $transaction): float
    {
        $balance = null;

        DB::transaction(function () use ($transaction, &$balance) {
            $this->revertAccountBalances($transaction);
            $balance = $transaction->account->balance;
            $transaction->delete();
        });

        return $balance;
    }

    private function updateAccountBalances(Transaction $transaction): void
    {
        $account = $transaction->account;
        $transactionType = TransactionType::from($transaction->type);

        match ($transactionType) {
            TransactionType::Income => $account->increment('balance', $transaction->amount),
            TransactionType::Expense => $account->decrement('balance', $transaction->amount),
            TransactionType::Transfer => $this->handleTransferUpdate($transaction),
        };
    }

    private function revertAccountBalances(Transaction $transaction): void
    {
        $account = $transaction->account;
        $transactionType = TransactionType::from($transaction->type);

        match ($transactionType) {
            TransactionType::Income => $account->decrement('balance', $transaction->amount),
            TransactionType::Expense => $account->increment('balance', $transaction->amount),
            TransactionType::Transfer => $this->handleTransferRevert($transaction),
        };
    }

    private function handleTransferUpdate(Transaction $transaction): void
    {
        if ($transaction->toAccount) {
            $transaction->account->decrement('balance', $transaction->amount);
            $transaction->toAccount->increment('balance', $transaction->amount);
        }
    }

    private function handleTransferRevert(Transaction $transaction): void
    {
        if ($transaction->toAccount) {
            $transaction->account->increment('balance', $transaction->amount);
            $transaction->toAccount->decrement('balance', $transaction->amount);
        }
    }
}
