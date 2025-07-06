<!DOCTYPE html>
<html>
<head>
    <title>Account Summary</title>
    <style>
        body { font-family: sans-serif; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .balance { font-weight: bold; }
    </style>
</head>
<body>
    <h1>Account Summary: {{ $account->name }}</h1>
    <p><strong>Currency:</strong> {{ $account->currency->name }}</p>
    <p class="balance">Current Balance: {{ $balance }}</p>

    <h2>Transactions</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Type</th>
                <th>Currency</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->created_at->format('Y-m-d') }}</td>
                    <td>{{ $transaction->category->name }}</td>
                    <td>{{ $transaction->type }}</td>
                    <td>{{ $transaction->account->currency->code }}</td>
                    <td>{{ $transaction->amount }}</td>
                    <td>{{ $transaction->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
