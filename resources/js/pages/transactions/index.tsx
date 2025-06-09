import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Transaction } from '@/types/transactions';
import CreateTransactionSection from '@/pages/transactions/components/create-transaction-section';
import TransactionList from '@/pages/transactions/components/transaction-list';
import type { Category } from '@/types/categories';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transactions',
        href: '/transactions',
    },
];

interface IndexProps {
    transactions: Transaction[],
    categories: Category[],
    filters: {
        category_id?: number | string,
        date_from?: string,
        date_to?: string,
    },
}

export default function Index({ transactions, categories, filters }: IndexProps) {
    const { data, setData, get } = useForm({
        category_id: filters.category_id || '',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
    });

    const handleFilter = (e: React.FormEvent) => {
        e.preventDefault();
        get('/transactions', { preserveState: true });
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
