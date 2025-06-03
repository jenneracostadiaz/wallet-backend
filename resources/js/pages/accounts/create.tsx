import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/input-error';
import type { Currency } from '@/types/accounts';
import { Button } from '@/components/ui/button';
import { Transition } from '@headlessui/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Accounts',
        href: '/accounts',
    },{
        title: 'Create',
        href: '/accounts/create',
    },
];

interface CreateProp {
    currencies: Currency[];
    types: { type: string; name: string }[];
}

export default function Create({currencies, types}: CreateProp) {
    const { data, setData, post, processing, errors, recentlySuccessful } = useForm({
        name: '',
        description: '',
        balance: 0,
        currency: currencies[0]?.id || 1,
        type: types[0]?.type || 'checking',
    });

    const submit = (e: { preventDefault: () => void }) => {
        e.preventDefault();
        post(
            route('accounts.store', {
                preserveScroll: true,
            }),
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Accounts" />
            <div className="px-4 py-6">
                <Heading title="Create Accounts" description="Create new accounts, and more." />

                <form onSubmit={submit} className="flex flex-col gap-6">
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
                                required
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
                        <Button disabled={processing}>Save</Button>
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
            </div>
        </AppLayout>
    );
}
