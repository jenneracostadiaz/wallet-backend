// Account type definitions
const ACCOUNT_TYPES = [
    {
        type: 'checking',
        name: 'Checking',
        color: 'bg-teal-400',
        borderColor: 'border-teal-400/30',
    },
    {
        type: 'savings',
        name: 'Savings',
        color: 'bg-blue-400',
        borderColor: 'border-blue-400/30',
    },
    {
        type: 'credit_card',
        name: 'Credit Card',
        color: 'bg-red-400',
        borderColor: 'border-red-400/30',
    },
    {
        type: 'cash',
        name: 'Cash',
        color: 'bg-yellow-400',
        borderColor: 'border-yellow-400/30',
    },
];

/**
 * Helper function to find account type information
 */
const getAccountTypeInfo = (type: string) => {
    return ACCOUNT_TYPES.find(account => account.type === type) || null;
};

/**
 * Get the border color for an account type
 */
export const getAccountTypeColor = (type: string) => {
    const accountInfo = getAccountTypeInfo(type);
    return accountInfo ? accountInfo.borderColor : '';
};

/**
 * Get the display name for an account type
 */
export const getAccountTypeName = (type: string) => {
    const accountInfo = getAccountTypeInfo(type);
    return accountInfo ? accountInfo.name : '';
};

