import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import type { Account, Currency } from '@/types/accounts';
import { AccountForm } from './components';

interface EditProp {
    account: Account;
    currencies: Currency[];
    types: { type: string; name: string }[];
}

export default function Edit({ account, currencies, types }: EditProp) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Accounts',
            href: '/accounts',
        },
        {
            title: 'Edit',
            href: `/accounts/${account.id}/edit`,
        },
    ];

    const { data, setData, put, processing, errors, recentlySuccessful } = useForm({
        name: account.name,
        description: account.description,
        balance: account.balance,
        currency: account.currency_id,
        type: account.type,
    });

    const submit = (e: { preventDefault: () => void }) => {
        e.preventDefault();
        put(
            route('accounts.update', account.id),
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Account: ${account.name}`} />
            <div className="px-4 py-6">
                <Heading title={`Edit Account: ${account.name}`} description="Update your account details." />

                <AccountForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    recentlySuccessful={recentlySuccessful}
                    onSubmit={submit}
                    currencies={currencies}
                    types={types}
                    submitLabel="Update"
                />
            </div>
        </AppLayout>
    );
}
