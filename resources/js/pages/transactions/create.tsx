import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import TransactionForm from '@/pages/transactions/components/transaction-form';
import { Account } from '@/types/accounts';
import { Category } from '@/types/categories';



const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transactions',
        href: '/transactions',
    },
    {
        title: 'Create',
        href: '/transactions/create',
    },
];

interface CreateProp {
    accounts: Account[];
    categories: Category[];
    types: { value: 'income' | 'expense' | 'transfer'; label: string }[];
}

export default function Create({ accounts, categories, types }: CreateProp) {
    const { data, setData, post, processing, errors, recentlySuccessful } = useForm({
        amount: 0,
        description: '',
        date: '',
        type: types[0]?.value || 'expense',
        account_id: accounts[0]?.id || 1,
        category_id: categories[0]?.id || 1,
        to_account_id: null,
    });

    const submit = (e: { preventDefault: () => void }) => {
        e.preventDefault();
        post(
            route('transactions.store', {
                preserveScroll: true,
            }),
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Transaction" />
            <div className="px-4 py-6">
                <Heading title="Create Transaction" description="Create new transactions easily." />

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
                    submitLabel="Save"
                />
            </div>
        </AppLayout>
    );
}
