import { Head, router } from '@inertiajs/react';
import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import { Account } from '@/types/accounts';
import { Pencil, Trash2 } from 'lucide-react';
import { getAccountTypeColor } from '@/hooks/use-account-types';
import { useState } from 'react';

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
    const [processing, setProcessing] = useState(false);

    const handleDelete = (id:number) => {
        if (confirm('Are you sure you want to delete this post?')) {
            setProcessing(true);
            router.delete(`/accounts/${id}`, {
                onFinish: () => setProcessing(false),
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Accounts" />
            <div className="px-4 py-6">
                <Heading title="Accounts" description="Manage your bank accounts, create new ones, and more." />

                <ul className="grid gap-4 grid-cols-1 xl:grid-cols-2 2xl:grid-cols-3">
                    {accounts.map((account: Account) => (
                        <li key={account.id} className={`flex items-center gap-4 relative border-3 ${getAccountTypeColor(account.type)} rounded-xl py-3 px-4`}>
                            <span className="flex-1">
                                <h3 className="mb-0.5 text-base font-medium">
                                    {account.name} {account.type && <span className="text-xs text-muted-foreground">({account.type})</span>}
                                </h3>
                                {account.description && <p className="text-muted-foreground text-sm">{account.description}</p>}
                            </span>
                            <span className="">
                                <p className="text-muted-foreground text-sm">{account.balance}</p>
                            </span>
                            <span className="flex items-center justify-end gap-2">
                                <button className="bg-teal-400 dark:bg-teal-800 hover:bg-teal-200 p-1 rounded cursor-pointer"><Pencil size={12} /></button>
                                <button
                                    className="bg-red-400 dark:bg-red-800 p-1 rounded cursor-pointer"
                                    onClick={() => handleDelete(account.id)}
                                    disabled={processing}
                                >
                                    <Trash2 size={12} />
                                </button>
                            </span>
                        </li>
                    ))}
                </ul>

            </div>
        </AppLayout>
    );
}
