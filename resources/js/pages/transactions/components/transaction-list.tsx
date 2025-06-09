import { Transaction } from '@/types/transactions';
import TransactionItem from './transaction-item';

interface TransactionListProps {
    transactions: Transaction[];
}

export default function TransactionList({ transactions }: TransactionListProps) {
    return (
        <ul className="grid grid-cols-1 gap-4">
            {transactions.map((transaction: Transaction) => (
                <TransactionItem key={transaction.id} transaction={transaction} />
            ))}
        </ul>
    );
}
