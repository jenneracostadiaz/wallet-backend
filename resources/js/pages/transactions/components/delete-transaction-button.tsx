import { router } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';

interface DeleteTransactionButtonProps {
    transactionId: number;
}

export default function DeleteTransactionButton({ transactionId }: DeleteTransactionButtonProps) {
    const [processing, setProcessing] = useState(false);

    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this transaction?')) {
            setProcessing(true);
            router.delete(`/transactions/${transactionId}`, {
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
