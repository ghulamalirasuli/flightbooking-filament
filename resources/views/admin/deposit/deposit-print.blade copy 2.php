<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Deposits</title>
    <style>
        /* A4 Setup */
        @page {
            size: A4;
            margin: 20mm;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }
        .page-wrapper {
            page-break-after: always; /* Each record starts on a new A4 page */
        }
        .container {
            width: 100%;
        }
        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo img {
            max-height: 80px;
        }
        .company-info {
            text-align: right;
        }
        .company-info h1 { margin: 0; color: #333; font-size: 24px; }
        .company-info p { margin: 2px 0; color: #666; font-size: 14px; }

        /* Content Section */
        .voucher-title {
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 40px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
        }
        .detail-item { font-size: 16px; margin-bottom: 10px; }
        .label { font-weight: bold; color: #555; width: 120px; display: inline-block; }

        /* Table Section */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 50px;
        }
        .data-table th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }
        .data-table td {
            border: 1px solid #dee2e6;
            padding: 12px;
        }
        .amount-row {
            font-size: 18px;
            font-weight: bold;
            background-color: #fdfdfe;
        }

        /* Footer/Signatures */
        .signature-section {
            margin-top: 100px;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            border-top: 1px solid #333;
            width: 200px;
            text-align: center;
            padding-top: 10px;
            font-size: 14px;
        }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="background: #333; padding: 10px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor:pointer;">Confirm Print</button>
    </div>

    @foreach($records as $record)
    <div class="page-wrapper">
        <div class="container">
         <div class="header">
            <div class="logo">
                @if($settings['logo'])
                    <img src="{{ $settings['logo'] }}" alt="Branch Logo">
                @else
                    <h2 style="margin:0;">{{ $settings['name'] }}</h2>
                @endif
            </div>
            <div class="company-info">
                <h1>{{ $settings['name'] }}</h1>
                <p>Email: {{ $settings['email'] }}</p>
                <p>Phone: {{ $settings['phone'] }}</p>
            </div>
        </div>

            <h2 class="voucher-title">Deposit Voucher</h2>

            <div class="details-grid">
                <div class="detail-item"><span class="label">Reference:</span> {{ $record->reference_no }}</div>
                <div class="detail-item" style="text-align: right;"><span class="label">Date:</span> {{ $record->created_at->format('d M Y, H:i') }}</div>
                <div class="detail-item"><span class="label">Account:</span> {{ $record->account?->account_name_with_category_and_branch ?? 'N/A' }}</div>
                <div class="detail-item" style="text-align: right;"><span class="label">Status:</span> {{ $record->status }}</div>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Type</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="height: 100px; vertical-align: top;">{{ $record->description ?? 'No description provided.' }}</td>
                        <td>{{ $record->entry_type }}</td>
                        <td style="text-align: right;" class="amount-row">
                            {{ $record->currency?->currency_code }} {{ number_format($record->amount_from, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="signature-section">
                <div class="sig-box">Issued By: {{ $record->user?->name }}</div>
                <div class="sig-box">Customer Signature</div>
                <div class="sig-box">Authorized Manager</div>
            </div>
        </div>
    </div>
    @endforeach

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>