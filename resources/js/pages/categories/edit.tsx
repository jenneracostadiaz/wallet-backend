import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import { Category } from '@/types/categories';
import { Head, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import CategoryForm from '@/pages/categories/components/category-form';

interface EditProp {
    category: Category;
    categories: Category[];
    types: { type: string; name: string }[];
}

export default function Edit({category, categories, types}: EditProp) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Categories',
            href: '/categories',
        },
        {
            title: 'Edit',
            href: `/categories/${category.id}/edit`,
        },
    ];

    const { data, setData, put, processing, errors, recentlySuccessful } = useForm({
        name: category.name,
        type: category.type,
        icon: category.icon,
        color: category.color,
        parent_id: category.parent_id,
    });

    const submit = (e: { preventDefault: () => void }) => {
        e.preventDefault();
        put(
            route('categories.update', category.id),
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Category: ${category.name}`} />
            <div className="px-4 py-6">
                <Heading title={`Edit Category: ${category.name}`} description="Update your category details." />

                <CategoryForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    recentlySuccessful={recentlySuccessful}
                    onSubmit={submit}
                    categories={categories}
                    types={types}
                    submitLabel="Update"
                />
            </div>
        </AppLayout>
    )
}
