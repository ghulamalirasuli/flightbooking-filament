<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ledger_{{ $account->account_name }}_{{ now()->format('Ymd') }}</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f4f4f4; border: 1px solid #ccc; padding: 6px; text-transform: uppercase; }
        td { border: 1px solid #ccc; padding: 6px; }
        .text-right { text-align: right; }
        .footer { margin-top: 20px; font-style: italic; font-size: 10px; }
        
        /* The CSS that hides elements during print */
        @media print {
            .no-print { display: none; }
            @page { margin: 1cm; }
        }

        @media print {
    .currency-section {
        page-break-after: always;
    }
    .currency-section:last-child {
        page-break-after: auto;
    }
}
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="background: #fff3cd; padding: 10px; margin-bottom: 20px; text-align: center; border: 1px solid #ffeeba;">
        <strong>Print Preview</strong> - Click <button onclick="window.print()">Print</button> or <button onclick="window.close()">Close</button>
    </div>

    <div class="header">
        <div class="row mr-4 mb-3">
            <div class="col-sm-6 col-md-4 text-start">
                {{-- @if($companyInfo && $companyInfo->logo_path)
                    <img src="{{ asset('storage/' . $companyInfo->logo_path) }}" alt="Company Logo" style="max-height: 60px;">
                @endif --}}
            </div>
            <div class="col-sm-6 col-md-4 text-start">
                <h5>Account: {{ $account->account_name }} </h5>
                <h6 > Date: {{ now()->format('d-M-Y H:i') }}</h6>
                <h6> Status: {{ $status }}</h6>
                @if($fromDate || $toDate)
        <h6> Period: 
            {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d/m/Y') : 'Beginning' }} 
            to 
            {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d/m/Y') : 'Today' }}
        </h6>
    @endif
            </div>
        </div>
        
    </div>

{{-- @php
    // Group records by their currency name to prevent mixing totals
    $groupedRecords = $records->groupBy(fn($record) => $record->currencyInfo?->currency_name ?? 'Unknown');
@endphp --}}

@php
    // Group by currency ID to match the opening balances array keys
    $groupedRecords = $records->groupBy('currency');
@endphp

@foreach($groupedRecords as $currencyId => $items)
    @php
        $currencyName = $items->first()->currencyInfo?->currency_name ?? 'Unknown';
        $currencyCode = $items->first()->currencyInfo?->currency_code ?? 'N/A';
        
        // Initialize running balance with the Opening Balance for this currency
        $openingBal = $openingBalances[$currencyId] ?? 0;
        $runningBalance = $openingBal;
    @endphp

@foreach($groupedRecords as $currencyName => $items)
    <div style="margin-bottom: 40px;">
        {{-- <h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px;">
            Currency: {{ $currencyName }}
        </h3> --}}
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th>Service</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php $runningBalance = 0; @endphp
                @foreach($items as $index => $record)
                    @php $runningBalance += ($record->credit - $record->debit); @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->date_confirm)->format('d/m/Y') }}</td>
                        <td>{{ $record->reference_no }}</td>
                        <td>{{ $record->description }}</td>
                        <td>{{  $record->service?->title ?? ''}}</td>
                        <td>{{ number_format($record->debit, 2) }}</td>
                        <td>{{ number_format($record->credit, 2) }}</td>
                        <td style="font-weight: bold; color: {{ $runningBalance >= 0 ? 'green' : 'red' }}">
                            {{ number_format($runningBalance, 2) }}
                        </td>
                        <td>
                            ({{ $record->currencyInfo?->currency_code ?? 'N/A' }})
                            <span style="color: {{ $runningBalance < 0 ? 'red' : 'green' }}; font-weight: bold;">
                                {{ $runningBalance < 0 ? 'Dr' : 'Cr' }}
                            </span>
                            {{-- @if($runningBalance < 0)
                                <span style="color: red; font-weight: bold;">Dr</span>
                            @else
                                <span style="color: green; font-weight: bold;">Cr</span>

                            @endif --}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f9f9f9; font-weight: bold;">
                    <td colspan="5" class="text-right">TOTALS ({{ $currencyName }})</td>
                    <td>{{ number_format($items->sum('debit'), 2) }}</td>
                    <td>{{ number_format($items->sum('credit'), 2) }}</td>
                    <td>{{ number_format($runningBalance, 2) }}</td>
                     <td>
                            ({{ $currencyName }})
                            <span style="color: {{ $runningBalance < 0 ? 'red' : 'green' }}; font-weight: bold;">
                                {{ $runningBalance < 0 ? 'Dr' : 'Cr' }}
                            </span>
                        </td>
                </tr>
            </tfoot>
        </table>
    </div>
@endforeach

    <div class="footer">
        Printed by: {{ auth()->user()->name }} | System: {{ config('app.name') }}
    </div>
</body>
</html>