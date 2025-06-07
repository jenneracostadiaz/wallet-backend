import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import CreateCategorySection from '@/pages/categories/components/create-category-section';
import CategoriesList from '@/pages/categories/components/categories-list';
import { Category } from '@/types/categories';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Categories',
        href: '/categories',
    },
];

interface CategoryProps {
    categories: Category[];
}

export default function Index({categories}:CategoryProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Categories" />
            <div className="px-4 py-6">
                <Heading title="Categories" description="Manage your categories, create new ones, and more." />

                <CreateCategorySection />

                <CategoriesList categories={categories} />
            </div>
        </AppLayout>
    )
}
