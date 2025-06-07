import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Transition } from '@headlessui/react';
import { Account } from '@/types/accounts';
import { Category } from '@/types/categories';
import { useEffect } from 'react';

interface TransactionFormProps {
    data: {
        amount: number;
        description: string | null;
        date: string;
        type: string;
        account_id: number;
        category_id: number | null;
        to_account_id: number | null;
    };
    setData: (key: string, value: string | number | null) => void;
    errors: Record<string, string>;
    processing: boolean;
    recentlySuccessful: boolean;
    onSubmit: (e: { preventDefault: () => void }) => void;
    accounts: Account[];
    categories: Category[];
    types: { value: 'income' | 'expense' | 'transfer'; label: string }[];
    submitLabel?: string;
}

export default function TransactionForm({
    data,
    setData,
    errors,
    processing,
    recentlySuccessful,
    onSubmit,
    accounts,
    categories,
    types,
    submitLabel = 'Save'
}: TransactionFormProps) {
    // Set default date to today if not provided
    useEffect(() => {
        if (!data.date) {
            setData('date', new Date().toISOString().split('T')[0]);
        }
    }, []);

    return (
        <form onSubmit={onSubmit} className="flex flex-col gap-6">
            <div className="grid gap-6 md:grid-cols-2">
                <div className="grid gap-3">
                    <Label htmlFor="amount">Amount</Label>
                    <Input
                        id="amount"
                        type="number"
                        step="0.01"
                        required
                        autoFocus
                        tabIndex={1}
                        value={data.amount}
                        onChange={(e) => setData('amount', Number(e.target.value))}
                        disabled={processing}
                        placeholder="0.00"
                    />
                    <InputError message={errors.amount} className="mt-2" />
                </div>
                <div className="grid gap-3">
                    <Label htmlFor="description">Description</Label>
                    <Input
                        id="description"
                        type="text"
                        tabIndex={1}
                        value={data.description || ''}
                        onChange={(e) => setData('description', e.target.value)}
                        disabled={processing}
                        placeholder="Description"
                    />
                    <InputError message={errors.description} className="mt-2" />
                </div>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
                <div className="grid gap-3">
                    <Label htmlFor="date">Date</Label>
                    <Input
                        id="date"
                        type="date"
                        required
                        tabIndex={1}
                        value={data.date ? new Date(data.date).toISOString().split('T')[0] : ''}
                        onChange={(e) => setData('date', e.target.value)}
                        disabled={processing}
                    />
                    <InputError message={errors.date} className="mt-2" />
                </div>

                <div className="grid gap-3">
                    <Label htmlFor="type">Type</Label>
                    <select
                        id="type"
                        required
                        tabIndex={1}
                        value={data.type}
                        onChange={(e) => {
                            const newType = e.target.value;
                            setData('type', newType);
                            // Reset category_id if type is transfer
                            if (newType === 'transfer') {
                                setData('category_id', null);
                            }
                            // Reset to_account_id if type is not transfer
                            if (newType !== 'transfer') {
                                setData('to_account_id', null);
                            }
                        }}
                        disabled={processing}
                        className="border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground dark:bg-background focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 *:data-[slot=select-value]:flex *:data-[slot=select-value]:items-center *:data-[slot=select-value]:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&>span]:line-clamp-1"
                    >
                        {types.map((type, index) => (
                            <option key={type.value} value={type.value}>
                                {type.label}
                            </option>
                        ))}
                    </select>
                    <InputError message={errors.type} className="mt-2" />
                </div>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
                <div className="grid gap-3">
                    <Label htmlFor="account_id">Account</Label>
                    <select
                        id="account_id"
                        required
                        tabIndex={1}
                        value={data.account_id}
                        onChange={(e) => setData('account_id', Number(e.target.value))}
                        disabled={processing}
                        className="border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground dark:bg-background focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 *:data-[slot=select-value]:flex *:data-[slot=select-value]:items-center *:data-[slot=select-value]:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&>span]:line-clamp-1"
                    >
                        {accounts.map((account) => (
                            <option key={account.id} value={account.id}>
                                {account.name}
                            </option>
                        ))}
                    </select>
                    <InputError message={errors.account_id} className="mt-2" />
                </div>
                {data.type === 'transfer' ? (
                    <div className="grid gap-3">
                        <Label htmlFor="to_account_id">To Account</Label>
                        <select
                            id="to_account_id"
                            required
                            tabIndex={1}
                            value={data.to_account_id || ''}
                            onChange={(e) => setData('to_account_id', Number(e.target.value))}
                            disabled={processing}
                            className="border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground dark:bg-background focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 *:data-[slot=select-value]:flex *:data-[slot=select-value]:items-center *:data-[slot=select-value]:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&>span]:line-clamp-1"
                        >
                            <option value="">Select destination account</option>
                            {accounts
                                .filter(account => account.id !== data.account_id)
                                .map((account) => (
                                    <option key={account.id} value={account.id}>
                                        {account.name}
                                    </option>
                                ))}
                        </select>
                        <InputError message={errors.to_account_id} className="mt-2" />
                    </div>
                ) : (
                    <div className="grid gap-3">
                        <Label htmlFor="category_id">Category</Label>
                        <select
                            id="category_id"
                            required
                            tabIndex={1}
                            value={data.category_id || ''}
                            onChange={(e) => setData('category_id', e.target.value ? Number(e.target.value) : null)}
                            disabled={processing}
                            className="border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground dark:bg-background focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 *:data-[slot=select-value]:flex *:data-[slot=select-value]:items-center *:data-[slot=select-value]:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&>span]:line-clamp-1"
                        >
                            <option value="">Select category</option>
                            {categories.map((category) => (
                                <option key={category.id} value={category.id}>
                                    {category.name}
                                </option>
                            ))}
                        </select>
                        <InputError message={errors.category_id} className="mt-2" />
                    </div>
                )}
            </div>

            <div className="flex items-center gap-4">
                <Button disabled={processing}>{submitLabel}</Button>
                <Transition
                    show={recentlySuccessful}
                    enter="transition ease-in-out"
                    enterFrom="opacity-0"
                    leave="transition ease-in-out"
                    leaveTo="opacity-0"
                >
                    <p className="text-sm text-neutral-600">Saved</p>
                </Transition>
            </div>
        </form>
    );
}
