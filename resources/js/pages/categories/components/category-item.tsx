import { Category } from '@/types/categories';

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
            <span>{category.name}</span>
        </li>
    );
}
