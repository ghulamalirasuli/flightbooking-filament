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
    @if($format != 'pdf')
    <div class="no-print" style="background: #fff3cd; padding: 10px; margin-bottom: 20px; text-align: center; border: 1px solid #ffeeba;">
        <strong>Print Preview</strong> - Click <button onclick="window.print()">Print</button> or <button onclick="window.close()">Close</button>
    </div>
    @endif

  <div class="header">
    <div class="row mr-4 mb-3">
        <div class="col-sm-6 col-md-4 text-start">
            <h5>Account: {{ $account->account_name }} </h5>
            <h6> Date: {{ now()->format('d-M-Y H:i') }}</h6>
            <h6> Status: {{ $status }}</h6>
            {{-- ADDED: Date Range in Header --}}
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

@php
    $groupedRecords = $records->groupBy(fn($record) => $record->currencyInfo?->currency_name ?? 'Unknown');
@endphp

@foreach($groupedRecords as $currencyName => $items)
    @php
        $firstItem = $items->first();
        $currencyId = $firstItem->currency ?? null;
        $currencyCode = $firstItem->currencyInfo?->currency_code ?? 'N/A';
        
        // Start running balance with Opening Balance (records before from_date)
        $openingBal = ($fromDate && $currencyId) ? ($openingBalances[$currencyId] ?? 0) : 0;
        $runningBalance = $openingBal;
        
        // Get the absolute total from the database for this currency
        $absoluteTotal = $currencyId ? ($grandTotalBalances[$currencyId] ?? 0) : 0;
        
        // Calculate Movement (only the records in this date range)
        $periodDebit = $items->sum('debit');
        $periodCredit = $items->sum('credit');
        $movement = $periodCredit - $periodDebit;
    @endphp

    <div style="margin-bottom: 40px;">
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
                {{-- Opening Balance Row (Only if a date filter is applied) --}}
                @if($fromDate)
                <tr style="background-color: #fcfcfc; font-style: italic;">
                    <td>-</td>
                    <td>{{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }}</td>
                    <td colspan="3"><strong>Opening Balance Brought Forward</strong></td>
                    <td>-</td>
                    <td>-</td>
                    <td style="font-weight: bold;">{{ number_format($openingBal, 2) }}</td>
                    <td>({{ $currencyCode }}) {{ $openingBal < 0 ? 'Dr' : 'Cr' }}</td>
                </tr>
                @endif

                @foreach($items as $index => $record)
                    @php $runningBalance += ($record->credit - $record->debit); @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->date_confirm)->format('d/m/Y') }}</td>
                        <td>{{ $record->reference_no }}</td>
                        <td>{{ $record->description }}</td>
                        <td>{{ $record->service?->title ?? ''}}</td>
                        <td>{{ number_format($record->debit, 2) }}</td>
                        <td>{{ number_format($record->credit, 2) }}</td>
                        <td style="font-weight: bold; color: {{ $runningBalance >= 0 ? 'green' : 'red' }}">
                            {{ number_format($runningBalance, 2) }}
                        </td>
                        <td>
                            ({{ $currencyCode }})
                            <span style="color: {{ $runningBalance < 0 ? 'red' : 'green' }}; font-weight: bold;">
                                {{ $runningBalance < 0 ? 'Dr' : 'Cr' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                  <tr>
        <td colspan="9"></td>
    </tr>
  
    {{-- 1. Period Movement Row (Only sums what is visible in the current table) --}}
    <tr style="background: #fff9e6; font-weight: bold;">
        <td colspan="5" class="text-right">PERIOD TOTALS (MOVEMENT)

             @if($fromDate || $toDate)
                 Period: 
                    {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d/m/Y') : 'Beginning' }} 
                    to 
                    {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d/m/Y') : 'Today' }}
            @endif

        </td>
        <td>{{ number_format($periodDebit, 2) }}</td>
        <td>{{ number_format($periodCredit, 2) }}</td>

        <td >
            <span style="color: {{ $movement < 0 ? 'red' : 'green' }}">
                {{ number_format($movement, 2) }} 
            </span>
            
        </td>
        <td >
              ({{ $currencyCode }})
            <span style="font-weight: bold;">
                {{ $movement < 0 ? 'Dr' : 'Cr' }}
            </span>

        </td>
    </tr>
    {{-- 2. Final Account Balance (The absolute sum from database for this account/status) --}}
    <tr style="background: #f9f9f9; font-weight: bold; border-top: 2px solid #444;">
        <td colspan="5" class="text-right">GRAND TOTAL ACCOUNT BALANCE ({{ $currencyName }})</td>
        {{-- These are empty because the Grand Total includes records not shown in this specific table --}}
        <td>{{ number_format($items->sum('debit'), 2) }}</td>
        <td>{{ number_format($items->sum('credit'), 2) }}</td>
        <td style="font-size: 1.1em; color: {{ $absoluteTotal < 0 ? 'red' : 'green' }}">
            {{ number_format($absoluteTotal, 2) }}
        </td>
        <td>
            ({{ $currencyCode }})
            <span style="font-weight: bold;">
                {{ $absoluteTotal < 0 ? 'Dr' : 'Cr' }}
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