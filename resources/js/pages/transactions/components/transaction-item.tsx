import { Transaction } from '@/types/transactions';
import { Pencil } from 'lucide-react';
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
                return 'border-green-500/50';
            case 'expense':
                return 'border-red-500/50';
            case 'transfer':
                return 'border-blue-500/50';
            default:
                return 'border-gray-500/50';
        }
    };

    const formatAmount = (amount: number, type: string) => {
        const formattedAmount = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: transaction.account?.currency?.code || 'USD',
        }).format(amount);

        return type === 'expense' ? `-${formattedAmount}` : formattedAmount;
    };

    return (
        <li className={`flex items-center gap-4 relative border-1 ${getTransactionTypeColor(transaction.type)} rounded-xl py-3 px-4`}>
            <span className="flex-1">
                <h3 className="mb-0.5 text-base font-medium">
                    {transaction.description || `${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)} Transaction`}
                </h3>
                <p className="text-muted-foreground text-sm">
                    {format(new Date(transaction.date), 'PPP')} •
                    {transaction.type === 'transfer'
                        ? ` Transfer from ${transaction.account?.name} to ${transaction.toAccount?.name}`
                        : ` ${transaction.account?.name} ${transaction.category ? `• ${transaction.category.name}` : ''}`
                    }
                </p>
            </span>
            <span className={`font-medium ${transaction.type === 'expense' ? 'text-red-500' : transaction.type === 'income' ? 'text-green-500' : 'text-blue-500'}`}>
                {formatAmount(transaction.amount, transaction.type)}
            </span>
            <span className="flex items-center justify-end gap-2">
                <Link
                    href={route('transactions.edit', transaction.id)}
                    className="bg-teal-400 dark:bg-teal-800 hover:bg-teal-200 p-1 rounded cursor-pointer inline-flex"
                >
                    <Pencil size={12} />
                </Link>
                <DeleteTransactionButton transactionId={transaction.id} />
            </span>
        </li>
    );
}
