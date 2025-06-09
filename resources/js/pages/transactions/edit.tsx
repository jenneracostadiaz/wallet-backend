import type { BreadcrumbItem } from '@/types';
import { Category } from '@/types/categories';
import { Transaction } from '@/types/transactions';
import { Account } from '@/types/accounts';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import TransactionForm from '@/pages/transactions/components/transaction-form';

interface EditProp {
    transaction: Transaction;
    categories: Category[];
    accounts: Account[];
    types: { value: 'income' | 'expense' | 'transfer'; label: string }[];
}

export default function Edit({transaction, categories, accounts, types}: EditProp) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Transactions',
            href: '/transactions',
        },
        {
            title: 'Edit',
            href: `/transactions/${transaction.id}/edit`,
        },
    ];

    const { data, setData, put, processing, errors, recentlySuccessful } = useForm({
        amount: transaction.amount,
        description: transaction.description || '',
        date: transaction.date,
        type: transaction.type,
        account_id: transaction.account_id,
        category_id: transaction.category_id,
        to_account_id: transaction.to_account_id,
    });

    const submit = (e: { preventDefault: () => void }) => {
        e.preventDefault();
        put(
            route('transactions.update', transaction.id),
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Transaction: ${transaction.category?.name}`} />
            <div className="px-4 py-6">
                <Heading title={transaction.type === 'transfer'
                    ? ` Transfer from ${transaction.account?.name} to ${transaction.toAccount?.name}`
                    : ` ${transaction.account?.name} ${transaction.category ? `• ${transaction.category.name}` : ''}`
                } description="Update your transaction details." />

                <TransactionForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    recentlySuccessful={recentlySuccessful}
                    onSubmit={submit}
                    accounts={accounts}
                    categories={categories}
                    types={types}
                    submitLabel="Update"
                />
            </div>
        </AppLayout>
    )
}
