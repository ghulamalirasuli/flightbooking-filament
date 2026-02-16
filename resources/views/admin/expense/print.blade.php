<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef08a; color: #854d0e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 15px; cursor: pointer;">Print Now</button>
        <button onclick="window.close()" style="padding: 10px 15px; cursor: pointer;">Close Tab</button>
    </div>

    <h2>Expenses Report</h2>
    <p><strong>Generated on:</strong> {{ now()->format('M d, Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Reference No</th>
                <th>Inserted By</th>
                <th>Expense Type</th>
                <th>Account</th>
                <th>Entry Type</th>
                <th class="text-right">Amount</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
                <tr>
                    <td>{{ $expense->reference_no }}</td>
                    <td>{{ $expense->user?->name ?? 'N/A' }}<br><small>{{ $expense->created_at?->format('M d, Y H:i') }}</small></td>
                    <td>{{ $expense->expenseType?->type ?? 'N/A' }}</td>
                    <td>{{ $expense->accountExp?->account_name ?? 'N/A' }}</td>
                    <td>{{ $expense->entry_type }}</td>
                    <td class="text-right">
                        {{ number_format($expense->entry_type === 'Credit' ? $expense->credit : $expense->debit, 2) }}
                        <br><small>{{ $expense->currencyExp?->currency_code ?? '' }}</small>
                    </td>
                    <td class="text-center">
                        @php
                            $badgeClass = match($expense->status) {
                                'Confirmed' => 'badge-success',
                                'Pending' => 'badge-warning',
                                'Cancelled' => 'badge-danger',
                                default => ''
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $expense->status }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No expenses found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>