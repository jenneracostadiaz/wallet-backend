import { Head } from '@inertiajs/react'
import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import { Account } from '@/types/accounts';
import HeadingSmall from '@/components/heading-small';
import { Pencil, PiggyBank, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';

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

    // Create color for types: checking, savings, credit card, cash
    const accountTypes = ['checking', 'savings', 'credit_card', 'cash'];
    const accountColors = ['bg-teal-400', 'bg-blue-400', 'bg-red-400', 'bg-yellow-400'];
    const accountIcons = ['credit-card', 'piggy-bank', 'bank-note', 'cash-register'];
    const accountNames = ['Checking', 'Savings', 'Credit Card', 'Cash'];

    const accountTypesWithIcons = accountTypes.map((type, index) => {
        return {
            type,
            icon: accountIcons[index],
            color: accountColors[index],
            name: accountNames[index]
        }
    })

    console.log(accounts);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Accounts" />
            <div className="px-4 py-6">
                <Heading title="Accounts" description="Manage your bank accounts, create new ones, and more." />

                <ul className="grid gap-4">
                    {accounts.map((account: Account) => (
                        <li key={account.id} className="flex gap-2 border border-sidebar-border/70 dark:border-sidebar-border rounded-xl py-3 px-4">
                            <div className="flex-1"><HeadingSmall title={account.name} description={account.description} /></div>
                            <div className="flex-1">
                                <p className="text-muted-foreground text-xs uppercase">
                                    {accountTypesWithIcons.map((type, index) => {
                                        if (account.type === type.type) {
                                            return (
                                                <div key={index} className={`${type.color} text-white text-xs rounded-full px-2 py-1 mr-1 flex items-center gap-1 justify-center`}>
                                                    <PiggyBank />
                                                    {type.name}
                                                </div>
                                            )
                                        }
                                    })}
                                </p>
                            </div>
                            <div className="flex-1">
                                <p className="text-muted-foreground text-sm">{account.balance}</p>
                            </div>
                            <div className="flex-1 flex items-center justify-end gap-3">
                                <Button variant="default" size="xs" className="bg-teal-400 dark:bg-teal-800"><Pencil /></Button>
                                <Button variant="destructive" size="xs"><Trash2 size="5" /></Button>
                            </div>
                        </li>
                    ))}
                </ul>

            </div>
        </AppLayout>
    );
}
