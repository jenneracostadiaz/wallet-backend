import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Transition } from '@headlessui/react';
import { Category } from '@/types/categories';

interface CategoryFormProps {
    data: {
        name: string;
        type: string;
        color: string;
        icon: string;
        parent_id?: number | null;
    }
    setData: (key: string, value: string | number | null) => void;
    errors: Record<string, string>;
    processing: boolean;
    recentlySuccessful: boolean;
    onSubmit: (e: { preventDefault: () => void }) => void;
    submitLabel?: string;
    types: { type: string; name: string }[];
    categories?: Category[];
}

export default function CategoryForm({data, setData, errors, processing, recentlySuccessful, onSubmit, submitLabel, types, categories}: CategoryFormProps) {

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
                    <Label htmlFor="type">Type</Label>
                    <select id="type" tabIndex={1} autoComplete="type" onChange={(e) => setData('type', e.target.value)} value={data.type} disabled={processing} className="border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground dark:bg-background focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 *:data-[slot=select-value]:flex *:data-[slot=select-value]:items-center *:data-[slot=select-value]:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&>span]:line-clamp-1">
                        {types.map((type) => (
                            <option
                                key={type.type}
                                value={type.type}
                            >
                                {type.name}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            <div className="grid gap-6 md:grid-cols-3">
                <div className="grip gap-3">
                    <Label htmlFor="color">Color</Label>
                    <Input
                        id="color"
                        type="color"
                        required
                        tabIndex={1}
                        autoComplete="color"
                        value={data.color}
                        onChange={(e) => setData('color', e.target.value)}
                        disabled={processing}
                    />
                    <InputError message={errors.color} className="mt-2" />
                </div>

                <div className="grid gap-3">
                    <Label htmlFor="icon">Icon</Label>
                    <Input
                        id="icon"
                        type="text"
                        tabIndex={1}
                        autoComplete="icon"
                        value={data.icon}
                        onChange={(e) => setData('icon', e.target.value)}
                        disabled={processing}
                        placeholder="e.g. fa-solid fa-coins"
                    />
                    <InputError message={errors.icon} className="mt-2" />
                </div>

                <div className="grid gap-3">
                    <Label htmlFor="parent_id">Parent Category</Label>
                    <select
                        id="parent_id"
                        tabIndex={1}
                        autoComplete="parent_id"
                        value={data.parent_id ?? ''}
                        onChange={(e) => setData('parent_id', e.target.value ? Number(e.target.value) : null)}
                        disabled={processing}
                        className="border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground dark:bg-background focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 *:data-[slot=select-value]:flex *:data-[slot=select-value]:items-center *:data-[slot=select-value]:gap-2 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&>span]:line-clamp-1"
                    >
                        <option value="">None</option>
                        {categories?.map((category) => (
                            <option key={category.id} value={category.id}>
                                {category.name}
                            </option>
                        ))}
                    </select>
                    <InputError message={errors.parent_id} className="mt-2" />
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
