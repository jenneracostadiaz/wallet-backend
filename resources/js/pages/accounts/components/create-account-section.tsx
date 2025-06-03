import { Link } from '@inertiajs/react';

export default function CreateAccountSection() {
    return (
        <Link href="/accounts/create"
            className="group w-full flex flex-col items-center justify-center p-4 border-2 border-dashed rounded-lg my-8">
            <h2 className="text-lg font-semibold">Create new Account</h2>
            <p className="mt-4 text-sm group-hover:underline">
                You can add a new account by clicking the button below
            </p>
        </Link>
    )
}
