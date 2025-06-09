export default function CreateTransactionSection() {
    return (
        <a href="/transactions/create"
            className="group w-full flex flex-col items-center justify-center p-4 border-2 border-dashed rounded-lg my-8">
            <h2 className="text-lg font-semibold">Create new Transaction</h2>
            <p className="mt-4 text-sm group-hover:underline">
                You can add a new transaction by clicking here
            </p>
        </a>
    )
}
