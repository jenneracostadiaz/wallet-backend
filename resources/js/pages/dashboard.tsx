import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import dayjs from 'dayjs';
import 'dayjs/locale/es';
dayjs.locale('es');

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard() {
    const { totalsByCurrency, accounts, monthlySummary, latestTransactions } = usePage().props as unknown as {
        totalsByCurrency: { currency: string; currency_symbol: string; total: number }[];
        accounts: { name: string; balance: number; currency: string; currency_symbol: string }[];
        monthlySummary: Record<string, number>;
        latestTransactions: {
            id: number;
            date: string;
            amount: number;
            type: string;
            account: string;
            currency_symbol: string;
            category: string;
            description: string;
        }[];
    };
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    {/* Widget de Current total balance por moneda */}
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-xl border flex flex-col items-center justify-center bg-white dark:bg-neutral-900">
                        <div className="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Current total balance</div>
                        <div className="flex flex-col gap-1">
                            {totalsByCurrency.map((item) => (
                                <div key={item.currency} className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {item.currency_symbol}{item.total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} <span className="text-base font-normal text-gray-500">({item.currency})</span>
                                </div>
                            ))}
                        </div>
                    </div>
                    {/* Widget de cuentas individuales */}
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-xl border bg-white dark:bg-neutral-900 flex flex-col p-4">
                        <div className="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Balances por cuenta</div>
                        <ul className="flex flex-col gap-2">
                            {accounts.map((acc) => (
                                <li key={acc.name} className="flex justify-between items-center">
                                    <span className="font-medium text-gray-800 dark:text-gray-100">{acc.name}</span>
                                    <span className="text-blue-700 dark:text-blue-300 font-semibold">{acc.currency_symbol}{acc.balance.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} <span className="text-xs text-gray-500">({acc.currency})</span></span>
                                </li>
                            ))}
                        </ul>
                    </div>
                    {/* Widget de resumen mensual */}
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-xl border bg-white dark:bg-neutral-900 flex flex-col p-4 items-center justify-center">
                        <div className="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Resumen mensual actual</div>
                        <ul className="flex flex-col gap-1">
                            <li className="text-green-600 dark:text-green-400 font-medium">Income: {monthlySummary.income ? monthlySummary.income.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'}</li>
                            <li className="text-red-600 dark:text-red-400 font-medium">Expense: {monthlySummary.expense ? monthlySummary.expense.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'}</li>
                            <li className="text-blue-600 dark:text-blue-400 font-medium">Transfer: {monthlySummary.transfer ? monthlySummary.transfer.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'}</li>
                        </ul>
                    </div>
                </div>
                {/* Filtros para transacciones */}
                <div className="mb-4 flex flex-wrap gap-4 items-end">
                    {/* Aquí puedes agregar más filtros si lo deseas */}
                    {/* Ejemplo: filtro por tipo */}
                    {/* <select ...>...</select> */}
                </div>
                <div className="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border md:min-h-min">
                    <div className="p-6">
                        <div className="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Latest transactions</div>
                        <table className="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead>
                                <tr>
                                    <th className="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    <th className="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase">Cuenta</th>
                                    <th className="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                                    <th className="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                    <th className="px-2 py-1 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                                    <th className="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                {latestTransactions.length === 0 && (
                                    <tr><td colSpan={6} className="text-center py-4 text-gray-400">No hay transacciones recientes.</td></tr>
                                )}
                                {latestTransactions.map(tx => (
                                    <tr key={tx.id} className="hover:bg-gray-50 dark:hover:bg-neutral-800">
                                        <td className="px-2 py-1 whitespace-nowrap">{dayjs(tx.date).format('D MMM YYYY')}</td>
                                        <td className="px-2 py-1 whitespace-nowrap">{tx.account}</td>
                                        <td className="px-2 py-1 whitespace-nowrap">{tx.category}</td>
                                        <td className="px-2 py-1 whitespace-nowrap capitalize">{tx.type}</td>
                                        <td className="px-2 py-1 whitespace-nowrap text-right font-semibold">
                                            <span className={
                                                tx.type === 'income' ? 'text-green-600 dark:text-green-400' :
                                                tx.type === 'expense' ? 'text-red-600 dark:text-red-400' :
                                                'text-blue-600 dark:text-blue-400'
                                            }>
                                                {tx.currency_symbol}{tx.amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                            </span>
                                        </td>
                                        <td className="px-2 py-1 whitespace-nowrap">{tx.description}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
