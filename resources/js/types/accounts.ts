export type Account = {
    id: number,
    name: string,
    description: string,
    balance: number,
    currency_id: number,
    user_id: number,
    type: string,
    order: string,
    created_at: string,
    updated_at: string,
    currency: Currency,
}

export type Currency = {
    id: number,
    code: string,
    name: string,
    symbol: string,
}
