import { Transaction } from '@/types/transactions';
import { ArrowRight, Pencil } from 'lucide-react';
import DeleteTransactionButton from './delete-transaction-button';
import { Link } from '@inertiajs/react';
import { format } from 'date-fns';

interface TransactionItemProps {
    transaction: Transaction;
}

export default function TransactionItem({ transaction }: TransactionItemProps) {
    const getTransactionTypeColor = (type: string) => {
        switch (type) {
            case 'income':
                return 'border-green-500/50 bg-green-50/50 dark:bg-green-900/10';
            case 'expense':
                return 'border-red-500/50 bg-red-50/50 dark:bg-red-900/10';
            case 'transfer':
                return 'border-blue-500/50 bg-blue-50/50 dark:bg-blue-900/10';
            default:
                return 'border-gray-500/50 bg-gray-50/50 dark:bg-gray-900/10';
        }
    };

    const formatAmount = (amount: number, type: string, currency: any) => {
        const formattedAmount = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency?.code || 'USD',
            minimumFractionDigits: currency?.decimal_places || 2,
            maximumFractionDigits: currency?.decimal_places || 2,
        }).format(amount);

        return type === 'expense' ? `-${formattedAmount}` : formattedAmount;
    };

    const getAmountTextColor = (type: string) => {
        switch (type) {
            case 'income':
                return 'text-green-600 dark:text-green-400';
            case 'expense':
                return 'text-red-600 dark:text-red-400';
            case 'transfer':
                return 'text-blue-600 dark:text-blue-400';
            default:
                return 'text-gray-600 dark:text-gray-400';
        }
    };

    const getTransactionIcon = (type: string) => {
        switch (type) {
            case 'income':
                return '↗️';
            case 'expense':
                return '↘️';
            case 'transfer':
                return '↔️';
            default:
                return '💰';
        }
    };

    const getCurrencyWarning = (transaction: Transaction) => {
        if (transaction.type === 'transfer' &&
            transaction.account?.currency?.code !== transaction.toAccount?.currency?.code) {
            return (
                <span className="text-xs text-amber-600 dark:text-amber-400 font-medium">
                    ⚠️ Multi-currency
                </span>
            );
        }
        return null;
    };

    return (
        <li className={`flex items-center gap-4 relative border ${getTransactionTypeColor(transaction.type)} rounded-xl py-4 px-4 transition-all hover:shadow-sm`}>
            {/* Transaction Icon */}
            <div className="flex-shrink-0 w-10 h-10 rounded-full bg-white dark:bg-gray-800 border flex items-center justify-center text-lg">
                {transaction.category?.icon || getTransactionIcon(transaction.type)}
            </div>

            {/* Transaction Details */}
            <div className="flex-1 min-w-0">
                <div className="flex items-start justify-between">
                    <div className="min-w-0 flex-1">
                        <h3 className="text-base font-medium text-gray-900 dark:text-gray-100 truncate">
                            {transaction.description || `${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)} Transaction`}
                        </h3>
                        <div className="mt-1 space-y-1">
                            <p className="text-sm text-gray-600 dark:text-gray-400">
                                {format(new Date(transaction.date), 'PPP')}
                            </p>

                            {transaction.type === 'transfer' ? (
                                <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <span className="truncate">
                                        {transaction.account?.name}
                                        <span className="text-xs ml-1">({transaction.account?.currency?.code})</span>
                                    </span>
                                    <ArrowRight size={12} className="flex-shrink-0" />
                                    <span className="truncate">
                                        {transaction.toAccount?.name}
                                        <span className="text-xs ml-1">({transaction.toAccount?.currency?.code})</span>
                                    </span>
                                </div>
                            ) : (
                                <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <span className="truncate">
                                        {transaction.account?.name}
                                        <span className="text-xs ml-1">({transaction.account?.currency?.code})</span>
                                    </span>
                                    {getCurrencyWarning(transaction)}
                                </div>
                            )}
                        </div>

                    </div>

                    <p className={`mt-2 text-lg font-semibold ${getAmountTextColor(transaction.type)}`}>
                        {formatAmount(transaction.amount, transaction.type, transaction.account?.currency)}
                    </p>

                    <div className="flex items-center gap-2">
                        <Link href={route('transactions.edit', transaction.id)} className="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <Pencil size={16} />
                        </Link>
                        <DeleteTransactionButton transactionId={transaction.id} />
                    </div>
                </div>
            </div>
        </li>
    );
}
