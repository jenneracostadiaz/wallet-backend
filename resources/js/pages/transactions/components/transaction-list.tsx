import { Transaction } from '@/types/transactions';
import TransactionItem from './transaction-item';

interface TransactionListProps {
    transactions: Transaction[];
}

export default function TransactionList({ transactions }: TransactionListProps) {
    return (
        <ul className="grid gap-4 grid-cols-1">
            {transactions.map((transaction: Transaction) => (
                <TransactionItem key={transaction.id} transaction={transaction} />
            ))}
        </ul>
    );
}
