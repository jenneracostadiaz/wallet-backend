import { Category } from '@/types/categories';
import CategoryItem from '@/pages/categories/components/category-item';

interface CategoriesListProps {
    categories: Category[];
}

export default function CategoriesList({ categories }: CategoriesListProps) {
    const categoryTypes = [
        { type: 'income', categories: categories.filter(c => c.type === 'income') },
        { type: 'expense', categories: categories.filter(c => c.type === 'expense') },
        { type: 'transfer', categories: categories.filter(c => c.type === 'transfer') },
    ];

    return (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {categoryTypes.map(({ type, categories }) => (
                <ul key={type} className="flex flex-col gap-4">
                    {categories.map((category) => (
                        <CategoryItem key={category.id} category={category} />
                    ))}
                </ul>
            ))}
        </div>
    );
}
