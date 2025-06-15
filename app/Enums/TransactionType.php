<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';

    case Transfer = 'transfer';

    public static function labels(): array
    {
        return [
            self::Income->value => 'Income',
            self::Expense->value => 'Expense',
            self::Transfer->value => 'Transfer',
        ];
    }

    public function icon(): string
    {
        return match ($this) {
            self::Income => '💵',
            self::Expense => '💸',
            self::Transfer => '🔄',
        };
    }
}
