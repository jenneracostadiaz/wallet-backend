import { Head } from '@inertiajs/react'
import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Accounts',
        href: '/accounts',
    },
];

export default function index() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Accounts" />
            <div className="px-4 py-6">
                <Heading title="Accounts" description="Manage your bank accounts, create new ones, and more." />
            </div>
        </AppLayout>
    );
}
