import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Transaction } from '@/types/transactions';
import CreateTransactionSection from '@/pages/transactions/components/create-transaction-section';
import TransactionList from '@/pages/transactions/components/transaction-list';
import type { Category } from '@/types/categories';
import type { Account } from '@/types/accounts';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transactions',
        href: '/transactions',
    },
];

interface IndexProps {
    transactions: Transaction[],
    categories: Category[],
    accounts: Account[],
    filters: {
        category_id?: number | string,
        account_id?: number | string,
        date_from?: string,
        date_to?: string,
        type?: string[] | string,
    },
}

const TRANSACTION_TYPES = [
    { value: 'income', label: 'Income' },
    { value: 'expense', label: 'Expense' },
    { value: 'transfer', label: 'Transfer' },
];

export default function Index({ transactions, categories, accounts, filters }: IndexProps) {
    const { data, setData, get } = useForm({
        category_id: filters.category_id || '',
        account_id: filters.account_id || '',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
        type: Array.isArray(filters.type) ? filters.type : (filters.type ? [filters.type] : []),
    });

    const handleFilter = (e: React.FormEvent) => {
        e.preventDefault();
        get('/transactions', { preserveState: true });
    };

    const handleTypeChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        const selected = Array.from(e.target.selectedOptions).map(opt => opt.value);
        setData('type', selected);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Transactions" />
            <div className="px-4 py-6">
                <Heading title="Transactions" description="View and manage your transactions." />

                {/* Filtros */}
                <form onSubmit={handleFilter} className="mb-6 flex flex-wrap gap-4 items-end">
                    <div>
                        <label className="block text-sm font-medium mb-1">Categoría</label>
                        <select
                            className="border rounded px-2 py-1"
                            value={data.category_id}
                            onChange={e => setData('category_id', e.target.value)}
                        >
                            <option value="">Todas</option>
                            {categories.map(cat => (
                                <option key={cat.id} value={cat.id}>{cat.name}</option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="block text-sm font-medium mb-1">Cuenta</label>
                        <select
                            className="border rounded px-2 py-1"
                            value={data.account_id}
                            onChange={e => setData('account_id', e.target.value)}
                        >
                            <option value="">Todas</option>
                            {accounts.map(acc => (
                                <option key={acc.id} value={acc.id}>{acc.name}</option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="block text-sm font-medium mb-1">Tipo</label>
                        <select
                            multiple
                            className="border rounded px-2 py-1 min-w-[120px]"
                            value={data.type}
                            onChange={handleTypeChange}
                        >
                            {TRANSACTION_TYPES.map(type => (
                                <option key={type.value} value={type.value}>{type.label}</option>
                            ))}
                        </select>
                        <div className="text-xs text-gray-500 mt-1">Ctrl+Click para selección múltiple</div>
                    </div>
                    <div>
                        <label className="block text-sm font-medium mb-1">Desde</label>
                        <input
                            type="date"
                            className="border rounded px-2 py-1"
                            value={data.date_from}
                            onChange={e => setData('date_from', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium mb-1">Hasta</label>
                        <input
                            type="date"
                            className="border rounded px-2 py-1"
                            value={data.date_to}
                            onChange={e => setData('date_to', e.target.value)}
                        />
                    </div>
                    <button type="submit" className="bg-blue-600 text-white px-4 py-2 rounded">Filtrar</button>
                </form>

                <CreateTransactionSection />

                <TransactionList transactions={transactions}  />
            </div>
        </AppLayout>
    );
}
