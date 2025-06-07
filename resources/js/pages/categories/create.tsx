import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';
import CategoryForm from '@/pages/categories/components/category-form';
import { Category } from '@/types/categories';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Categories',
        href: '/categories',
    },
    {
        title: 'Create',
        href: '/categories/create',
    },
];

interface CreateProps {
    categories: Category[];
    types: { type: string; name: string }[];
}

export default function Create({categories, types}:CreateProps ) {

    const { data, setData, post, processing, errors, recentlySuccessful } = useForm({
        name: '',
        color: '#000000',
        icon: '',
        parent_id: 0,
        type: types[0]?.type || 'income',
    })

    const submit = (e: { preventDefault: () => void }) => {
        e.preventDefault();
        post(
            route('categories.store', {
                preserveScroll: true,
            }),
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Categories" />
            <div className="px-4 py-6">
                <Heading title="Create Categories" description="Create new categories, and more." />

                <CategoryForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    recentlySuccessful={recentlySuccessful}
                    onSubmit={submit}
                    submitLabel="Save"
                    types={types}
                    categories={categories}
                />
            </div>
        </AppLayout>
    );
}
