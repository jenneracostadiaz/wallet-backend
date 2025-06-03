import { Account } from '@/types/accounts';
import AccountItem from './account-item';

interface AccountListProps {
    accounts: Account[];
}

export default function AccountList({ accounts }: AccountListProps) {
    return (
        <ul className="grid gap-4 grid-cols-1 lg:grid-cols-2 2xl:grid-cols-4">
            {accounts.map((account: Account) => (
                <AccountItem key={account.id} account={account} />
            ))}
        </ul>
    );
}
