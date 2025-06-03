import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Accounts',
        href: '/accounts',
    },{
        title: 'Create',
        href: '/accounts/create',
    },
];
export default function Create() {


    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Accounts" />
            <div className="px-4 py-6">
                <Heading title="Create Accounts" description="Create new accounts, and more." />
            </div>
        </AppLayout>
    )
}
