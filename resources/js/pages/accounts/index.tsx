import { Head } from '@inertiajs/react';
import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import { Account } from '@/types/accounts';
import { AccountList } from './components';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Accounts',
        href: '/accounts',
    },
];

interface AccountProps {
    accounts: Account[];
}

export default function Index({accounts}: AccountProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Accounts" />
            <div className="px-4 py-6">
                <Heading title="Accounts" description="Manage your bank accounts, create new ones, and more." />

                <AccountList accounts={accounts} />

            </div>
        </AppLayout>
    );
}
