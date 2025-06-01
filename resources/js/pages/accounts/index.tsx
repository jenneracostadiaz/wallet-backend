import { Head } from '@inertiajs/react'
import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import { Account } from '@/types/accounts';
import HeadingSmall from '@/components/heading-small';
import { Pencil, Trash2 } from 'lucide-react';
import { useAccountTypeColor, useAccountTypeName } from '@/hooks/use-account-types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Accounts',
        href: '/accounts',
    },
];

interface AccountProps {
    accounts: Account[];
}

export default function index({accounts}: AccountProps) {

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Accounts" />
            <div className="px-4 py-6">
                <Heading title="Accounts" description="Manage your bank accounts, create new ones, and more." />

                <ul className="grid gap-4 grid-cols-1 xl:grid-cols-2 2xl:grid-cols-3">
                    {accounts.map((account: Account) => (
                        <li key={account.id} className={`flex items-center gap-4 relative border-3 ${useAccountTypeColor(account.type)} rounded-xl py-3 px-4`}>
                            <span className="flex-1"><HeadingSmall title={account.name} smallTitle={useAccountTypeName(account.type)} description={account.description} /></span>
                            <span className="">
                                <p className="text-muted-foreground text-sm">{account.balance}</p>
                            </span>
                            <span className="flex items-center justify-end gap-2">
                                <button className="bg-teal-400 dark:bg-teal-800 hover:bg-teal-200 p-1 rounded cursor-pointer"><Pencil size={12} /></button>
                                <button className="bg-red-400 dark:bg-red-800 p-1 rounded cursor-pointer"><Trash2 size={12} /></button>
                            </span>
                        </li>
                    ))}
                </ul>

            </div>
        </AppLayout>
    );
}
