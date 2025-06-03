import { Link } from '@inertiajs/react';

export default function CreateAccountSection() {
    return (
        <div
            className="w-full flex flex-col items-center justify-center p-4 border-2 border-dashed rounded-lg my-8">
            <h2 className="text-lg font-semibold">Create new Account</h2>
            <Link href="/accounts/create" className="mt-4 text-sm hover:underline">
                You can add a new account by clicking the button below
            </Link>
        </div>
    )
}
