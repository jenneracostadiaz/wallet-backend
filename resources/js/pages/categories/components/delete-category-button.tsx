import { router } from '@inertiajs/react';
import { useState } from 'react';
import { Trash2 } from 'lucide-react';

interface DeleteCategoryButtonProps {
    categoryId: number;
}

export default function DeleteCategoryButton({ categoryId }:DeleteCategoryButtonProps){
    const [processing, setProcessing] = useState(false);

    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this category?')) {
            setProcessing(true);
            router.delete(`/categories/${categoryId}`, {
                onFinish: () => setProcessing(false),
            });
        }
    };

    return (
        <button
            className="bg-red-400 dark:bg-red-800 p-1 rounded cursor-pointer"
            onClick={handleDelete}
            disabled={processing}
        >
            <Trash2 size={12} />
        </button>
    );
}
