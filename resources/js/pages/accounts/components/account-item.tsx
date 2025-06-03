import { Account } from '@/types/accounts';
import { getAccountTypeColor } from '@/hooks/use-account-types';
import { Pencil } from 'lucide-react';
import DeleteAccountButton from './delete-account-button';
import { Link } from '@inertiajs/react';

interface AccountItemProps {
    account: Account;
}

export default function AccountItem({ account }: AccountItemProps) {
    return (
        <li className={`flex items-center gap-4 relative border-3 ${getAccountTypeColor(account.type)} rounded-xl py-3 px-4`}>
            <span className="flex-1">
                <h3 className="mb-0.5 text-base font-medium">
                    {account.name} {account.type && <span className="text-xs text-muted-foreground">({account.type})</span>}
                </h3>
                {account.description && <p className="text-muted-foreground text-sm">{account.description}</p>}
            </span>
            <span className="">
                <p className="text-muted-foreground text-sm">{account.balance}</p>
            </span>
            <span className="flex items-center justify-end gap-2">
                <Link
                    href={route('accounts.edit', account.id)}
                    className="bg-teal-400 dark:bg-teal-800 hover:bg-teal-200 p-1 rounded cursor-pointer inline-flex"
                >
                    <Pencil size={12} />
                </Link>
                <DeleteAccountButton accountId={account.id} />
            </span>
        </li>
    );
}
