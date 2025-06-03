import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Transition } from '@headlessui/react';
import type { Currency } from '@/types/accounts';

interface AccountFormProps {
    data: {
        name: string;
        description: string;
        balance: number;
        currency: number;
        type: string;
    };
    setData: (key: string, value: string | number) => void;
    errors: Record<string, string>;
    processing: boolean;
    recentlySuccessful: boolean;
    onSubmit: (e: { preventDefault: () => void }) => void;
    currencies: Currency[];
    types: { type: string; name: string }[];
    submitLabel?: string;
}

export default function AccountForm({
    data,
    setData,
    errors,
    processing,
    recentlySuccessful,
    onSubmit,
    currencies,
    types,
    submitLabel = 'Save'
}: AccountFormProps) {
    return (
        <form onSubmit={onSubmit} className="flex flex-col gap-6">
            <div className="grid gap-6 md:grid-cols-2">
                <div className="grid gap-3">
                    <Label htmlFor="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autoFocus
                        tabIndex={1}
                        autoComplete="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        disabled={processing}
                        placeholder="Full name"
                    />
                    <InputError message={errors.name} className="mt-2" />
                </div>
                <div className="grid gap-3">
                    <Label htmlFor="description">Description</Label>
                    <Input
                        id="description"
                        type="text"
                        tabIndex={1}
                        autoComplete="description"
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        disabled={processing}
                        placeholder="Description"
                    />
                    <InputError message={errors.description} className="mt-2" />
                </div>
            </div>

            <div className="grid gap-6 md:grid-cols-3">
                <div className="grid gap-3">
                    <Label htmlFor="balance">Balance</Label>
                    <Input
                        id="balance"
                        type="number"
                        required
                        tabIndex={1}
                        autoComplete="balance"
                        value={data.balance}
                        onChange={(e) => setData('balance', Number(e.target.value))}
                        disabled={processing}
                    />
                    <InputError message={errors.balance} className="mt-2" />
                </div>
                <div className="grid gap-3">
                    <Label htmlFor="currency">Currency</Label>
                    <select
                        id="currency"
                        required
                        tabIndex={1}
                        autoComplete="currency"
                        value={data.currency}
                        onChange={(e) => setData('currency', Number(e.target.value))}
                        disabled={processing}
                        className="border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground dark:bg-background focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 *:data-[slot=select-value]:flex *:data-[slot=select-value]:items-center *:data-[slot=select-value]:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&>span]:line-clamp-1"
                    >
                        {currencies.map((currency) => (
                            <option key={currency.id} value={currency.id}>
                                {currency.symbol} {currency.name}
                            </option>
                        ))}
                    </select>
                    <InputError message={errors.currency} className="mt-2" />
                </div>
                <div className="grid gap-3">
                    <Label htmlFor="type">Type</Label>
                    <select
                        id="type"
                        required
                        tabIndex={1}
                        autoComplete="type"
                        value={data.type}
                        onChange={(e) => setData('type', e.target.value)}
                        disabled={processing}
                        className="border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground dark:bg-background focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 *:data-[slot=select-value]:flex *:data-[slot=select-value]:items-center *:data-[slot=select-value]:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&>span]:line-clamp-1"
                    >
                        {types.map((type) => (
                            <option key={type.type} value={type.type}>
                                {type.name}
                            </option>
                        ))}
                    </select>
                </div>
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
