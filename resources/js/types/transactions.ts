import { Account } from './accounts';
import { Category } from './categories';

export interface Transaction {
    id: number;
    amount: number;
    description: string | null;
    date: string;
    type: 'income' | 'expense' | 'transfer';
    account_id: number;
    category_id: number | null;
    to_account_id: number | null;
    user_id: number;
    created_at: string;
    updated_at: string;
    account?: Account;
    category?: Category;
    toAccount?: Account;
}
