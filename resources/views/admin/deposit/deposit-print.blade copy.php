<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Deposit - {{ $record->reference_no }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; padding: 20px; color: #333; }
        .receipt-box { max-width: 400px; margin: auto; border: 1px solid #eee; padding: 15px; }
        .header { text-align: center; border-bottom: 1px dashed #000; margin-bottom: 10px; }
        .row { display: flex; justify-content: space-between; margin: 5px 0; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; }
        
        /* Hide everything except the receipt area during print */
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .receipt-box { border: none; width: 100%; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Click to Print</button>
        <p><small>If the print dialog didn't open, click the button above.</small></p>
    </div>

    <div class="receipt-box">
        <div class="header">
            <h2>RECEIPT</h2>
            <p>{{ $record->branch?->branch_name }}</p>
        </div>

        <div class="row">
            <span>Reference:</span>
            <strong>{{ $record->reference_no }}</strong>
        </div>
        <div class="row">
            <span>Date:</span>
            <span>{{ $record->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span>Account:</span>
            <span>{{ $record->account?->account_name }}</span>
        </div>

        <hr style="border: 0; border-top: 1px dashed #000;">

        <div class="row">
            <span>Type:</span>
            <span>{{ $record->entry_type }}</span>
        </div>
        <div class="row">
            <span style="font-size: 1.2em; font-weight: bold;">Amount:</span>
            <span style="font-size: 1.2em; font-weight: bold;">
                {{ $record->currency?->currency_name }} {{ number_format($record->amount_from, 2) }}
            </span>
        </div>

        <div class="footer">
            <p>Cashier: {{ $record->user?->name }}</p>
            <p>Thank you!</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
            // Optional: Close the tab after printing
            // window.onafterprint = function() { window.close(); };
        }
    </script>
</body>
</html>