import { Currency } from '@/types/accounts';

interface AccountBalanceProps {
    balance: number;
    currency: Currency;
}

export default function AccountBalance({ balance, currency }: AccountBalanceProps) {
    return <p className="text-muted-foreground text-sm">{currency.symbol} {balance}</p>;
}
