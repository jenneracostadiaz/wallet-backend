import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Transaction } from '@/types/transactions';
import CreateTransactionSection from '@/pages/transactions/components/create-transaction-section';
import TransactionList from '@/pages/transactions/components/transaction-list';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transactions',
        href: '/transactions',
    },
];

interface IndexProps {
    transactions: Transaction[],
}

export default function Index({transactions}: IndexProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Transactions" />
            <div className="px-4 py-6">
                <Heading title="Transactions" description="View and manage your transactions." />

                <CreateTransactionSection />

                <TransactionList transactions={transactions}  />
            </div>
        </AppLayout>
    );
}
