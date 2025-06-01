const accountTypes = ['checking', 'savings', 'credit_card', 'cash'];
const accountColors = ['bg-teal-400', 'bg-blue-400', 'bg-red-400', 'bg-yellow-400'];
const accountBorderColors = ['border-teal-400/30', 'border-blue-400/30', 'border-red-400/30', 'border-yellow-400/30'];
const accountNames = ['Checking', 'Savings', 'Credit Card', 'Cash'];

const getAccountTypes = accountTypes.map((accountType, index) => {
    return {
        accountType,
        color: accountColors[index],
        borderColor: accountBorderColors[index],
        name: accountNames[index],
    };
});

export const useAccountTypeColor = (type) => {
    const account = getAccountTypes.find(acc => acc.accountType === type);
    return account ? account.borderColor : null;
}

export const useAccountTypeName = (type) => {
    const account = getAccountTypes.find(acc => acc.accountType === type);
    return account ? account.name : null;
}
