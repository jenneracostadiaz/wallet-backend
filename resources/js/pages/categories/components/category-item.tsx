import { Category } from '@/types/categories';
import { Pencil } from 'lucide-react';
import { Link } from '@inertiajs/react';
import DeleteCategoryButton from '@/pages/categories/components/delete-category-button';

interface CategoryItemProps {
    category: Category;
}

export default function CategoryItem({ category }: CategoryItemProps) {
    const typeStyles: Record<string, string> = {
        income: 'bg-green-500 w-5 h-5 flex gap-1 justify-center items-center rounded',
        expense: 'bg-red-500 w-5 h-5 flex gap-1 justify-center items-center rounded',
        transfer: 'bg-blue-500 w-5 h-5 flex gap-1 justify-center items-center rounded',
    };

    const typeIcons: Record<string, string> = {
        income: '↑',
        expense: '↓',
        transfer: '↔',
    };

    return (
        <li className="flex gap-2 text-sm">
            <span className={typeStyles[category.type] ?? 'hidden'}>
                {typeIcons[category.type] ?? null}
            </span>
            <span className="flex-1">{category.name} {category.icon}</span>
            <span className="flex items-center justify-end gap-2">
                <Link
                    href={route('categories.edit', category.id)}
                    className="bg-teal-400 dark:bg-teal-800 hover:bg-teal-200 p-1 rounded cursor-pointer inline-flex"
                >
                    <Pencil size={12} />
                </Link>
                <DeleteCategoryButton categoryId={category.id} />
            </span>
        </li>
    );
}
