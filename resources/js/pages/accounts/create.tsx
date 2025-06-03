import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import type { Currency } from '@/types/accounts';
import { AccountForm } from './components';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Accounts',
        href: '/accounts',
    },{
        title: 'Create',
        href: '/accounts/create',
    },
];

interface CreateProp {
    currencies: Currency[];
    types: { type: string; name: string }[];
}

export default function Create({currencies, types}: CreateProp) {
    const { data, setData, post, processing, errors, recentlySuccessful } = useForm({
        name: '',
        description: '',
        balance: 0,
        currency: currencies[0]?.id || 1,
        type: types[0]?.type || 'checking',
    });

    const submit = (e: { preventDefault: () => void }) => {
        e.preventDefault();
        post(
            route('accounts.store', {
                preserveScroll: true,
            }),
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Accounts" />
            <div className="px-4 py-6">
                <Heading title="Create Accounts" description="Create new accounts, and more." />

                <AccountForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    recentlySuccessful={recentlySuccessful}
                    onSubmit={submit}
                    currencies={currencies}
                    types={types}
                    submitLabel="Save"
                />
            </div>
        </AppLayout>
    );
}
