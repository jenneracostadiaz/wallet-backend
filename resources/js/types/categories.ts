export type Category = {
    id: number;
    name: string;
    type: 'income' | 'expense' | 'transfer';
    color: string;
    icon: string;
    parent_id: number | null;
    user_id: number;
    order: number;
}
