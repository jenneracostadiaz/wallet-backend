import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Transition } from '@headlessui/react';
import { Account } from '@/types/accounts';
import { Category } from '@/types/categories';
import { useEffect, useMemo } from 'react';

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

    useEffect(() => {
        if (!data.account_id && accounts.length > 0) {
            setData('account_id', accounts[0].id);
        }
    }, [accounts]);

    const filteredCategories = useMemo(() => {
        if (data.type === 'transfer') {
            return [];
        }
        return categories.filter(category => category.type === data.type);
    }, [categories, data.type]);

    // Reset category when type changes
    useEffect(() => {
        if (data.type === 'transfer') {
            setData('category_id', null);
        } else if (filteredCategories.length > 0 && !filteredCategories.find(cat => cat.id === data.category_id)) {
            setData('category_id', filteredCategories[0].id);
        }
    }, [data.type, filteredCategories]);

    // Reset to_account_id when type is not transfer
    useEffect(() => {
        if (data.type !== 'transfer') {
            setData('to_account_id', null);
        }
    }, [data.type]);

    // Get selected account for currency display
    const selectedAccount = accounts.find(account => account.id === data.account_id);
    const selectedToAccount = accounts.find(account => account.id === data.to_account_id);

    // Format currency for display
    const formatCurrency = (amount: number, account?: Account) => {
        if (!account?.currency) return `$${amount.toFixed(2)}`;

        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: account.currency.code,
            minimumFractionDigits: account.currency.decimal_places || 2,
            maximumFractionDigits: account.currency.decimal_places || 2,
        }).format(amount);
    };

    return (
        <form onSubmit={onSubmit} className="flex flex-col gap-6">
            <div className="grid gap-6 md:grid-cols-2">
                <div className="grid gap-3">
                    <Label htmlFor="amount">Amount {selectedAccount?.currency && `(${selectedAccount.currency.code})`}</Label>
                    <div className="relative">
                        <Input
                            id="amount"
                            type="number"
                            step={selectedAccount?.currency ? `0.${'0'.repeat((selectedAccount.currency.decimal_places || 2) - 1)}1` : '0.01'}
                            min="0.01"
                            required
                            autoFocus
                            tabIndex={1}
                            value={data.amount || ''}
                            onChange={(e) => setData('amount', Number(e.target.value))}
                            disabled={processing}
                            placeholder="0.00"
                            className="pr-16"
                        />
                        {selectedAccount?.currency && (
                            <div className="absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-500">
                                {selectedAccount.currency.symbol}
                            </div>
                        )}
                    </div>
                    {data.amount > 0 && selectedAccount && (
                        <p className="text-sm text-gray-600">
                            {formatCurrency(data.amount, selectedAccount)}
                        </p>
                    )}
                    <InputError message={errors.amount} className="mt-2" />
                </div>

                <div className="grid gap-3">
                    <Label htmlFor="description">Description</Label>
                    <Input
                        id="description"
                        type="text"
                        tabIndex={2}
                        value={data.description || ''}
                        onChange={(e) => setData('description', e.target.value)}
                        disabled={processing}
                        placeholder="Optional description"
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
                        tabIndex={3}
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
                        tabIndex={4}
                        value={data.type}
                        onChange={(e) => {
                            setData('type', e.target.value);
                        }}
                        disabled={processing}
                        className="border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground dark:bg-background focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 *:data-[slot=select-value]:flex *:data-[slot=select-value]:items-center *:data-[slot=select-value]:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&>span]:line-clamp-1"
                    >
                        {types.map((type) => (
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
                    <Label htmlFor="account_id">
                        {data.type === 'transfer' ? 'From Account' : 'Account'}
                    </Label>
                    <select
                        id="account_id"
                        required
                        tabIndex={5}
                        value={data.account_id || ''}
                        onChange={(e) => setData('account_id', Number(e.target.value))}
                        disabled={processing}
                        className="border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground dark:bg-background focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 *:data-[slot=select-value]:flex *:data-[slot=select-value]:items-center *:data-[slot=select-value]:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&>span]:line-clamp-1"
                    >
                        <option value="">Select account</option>
                        {accounts.map((account) => (
                            <option key={account.id} value={account.id}>
                                {account.name} ({account.currency?.code || 'USD'}) - {formatCurrency(account.balance, account)}
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
                            tabIndex={6}
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
                                        {account.name} ({account.currency?.code || 'USD'}) - {formatCurrency(account.balance, account)}
                                    </option>
                                ))}
                        </select>
                        {selectedAccount && selectedToAccount &&
                            selectedAccount.currency?.code !== selectedToAccount.currency?.code && (
                                <p className="text-sm text-amber-600 flex items-center gap-1">
                                    ⚠️ Different currencies: {selectedAccount.currency?.code} → {selectedToAccount.currency?.code}
                                </p>
                            )}
                        <InputError message={errors.to_account_id} className="mt-2" />
                    </div>
                ) : (
                    <div className="grid gap-3">
                        <Label htmlFor="category_id">Category</Label>
                        <select
                            id="category_id"
                            required
                            tabIndex={6}
                            value={data.category_id || ''}
                            onChange={(e) => setData('category_id', e.target.value ? Number(e.target.value) : null)}
                            disabled={processing}
                            className="border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground dark:bg-background focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 *:data-[slot=select-value]:flex *:data-[slot=select-value]:items-center *:data-[slot=select-value]:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&>span]:line-clamp-1"
                        >
                            <option value="">Select category</option>
                            {filteredCategories.map((category) => (
                                <option key={category.id} value={category.id}>
                                    {category.icon && `${category.icon} `}{category.name}
                                </option>
                            ))}
                        </select>
                        {filteredCategories.length === 0 && data.type !== 'transfer' && (
                            <p className="text-sm text-amber-600">
                                No categories available for {data.type} transactions. Please create some first.
                            </p>
                        )}
                        <InputError message={errors.category_id} className="mt-2" />
                    </div>
                )}
            </div>

            {/* Transaction Summary */}
            {data.amount > 0 && selectedAccount && (
                <div className="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                    <h3 className="font-medium mb-2">Transaction Summary</h3>
                    <div className="space-y-1 text-sm">
                        <div className="flex justify-between">
                            <span>Type:</span>
                            <span className="capitalize font-medium">{data.type}</span>
                        </div>
                        <div className="flex justify-between">
                            <span>Amount:</span>
                            <span className={`font-medium ${
                                data.type === 'expense' ? 'text-red-600' :
                                    data.type === 'income' ? 'text-green-600' :
                                        'text-blue-600'
                            }`}>
                                {data.type === 'expense' ? '-' : ''}
                                {formatCurrency(data.amount, selectedAccount)}
                            </span>
                        </div>
                        {data.type === 'transfer' && selectedToAccount ? (
                            <div className="flex justify-between">
                                <span>Transfer:</span>
                                <span className="font-medium">
                                    {selectedAccount.name} → {selectedToAccount.name}
                                </span>
                            </div>
                        ) : (
                            <div className="flex justify-between">
                                <span>Account:</span>
                                <span className="font-medium">{selectedAccount.name}</span>
                            </div>
                        )}
                    </div>
                </div>
            )}


            <div className="flex items-center gap-4">
                <Button disabled={processing || !data.amount || data.amount <= 0}>
                    {processing ? 'Processing...' : submitLabel}
                </Button>
                <Transition
                    show={recentlySuccessful}
                    enter="transition ease-in-out"
                    enterFrom="opacity-0"
                    leave="transition ease-in-out"
                    leaveTo="opacity-0"
                >
                    <p className="text-sm text-green-600">Saved successfully!</p>
                </Transition>
            </div>
        </form>
    );
}
