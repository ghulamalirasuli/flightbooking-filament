<!DOCTYPE html>
<html>
<head>
    <title>Deposit Voucher - {{ $record->reference_no }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .details { margin-top: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DEPOSIT VOUCHER</h1>
        <p>Branch: {{ $record->branch?->branch_name }}</p>
    </div>

    <div class="details">
        <p><strong>Reference:</strong> {{ $record->reference_no }}</p>
        <p><strong>Date:</strong> {{ $record->created_at->format('M d, Y H:i') }}</p>
        <p><strong>Status:</strong> {{ $record->status }}</p>
        <p><strong>Account:</strong> {{ $record->account?->account_name }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Currency</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $record->description ?? 'No Description' }}</td>
                <td>{{ $record->currency?->currency_name }}</td>
                <td>{{ number_format($record->debit, 2) }}</td>
                <td>{{ number_format($record->credit, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 50px;">
        <p>Prepared By: {{ $record->user?->name }}</p>
        <p>Verified By: ____________________</p>
    </div>
</body>
</html>