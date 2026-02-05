<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print From - {{ $record->reference_no }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .print-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 15mm;
            /* Removed fixed height to allow content to flow naturally */
        }

        /* Header Styles */
        .header-section {
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-container img {
            max-height: 60px;
            width: auto;
            object-fit: contain;
            border-radius: 4px;
        }

        .company-info h4 {
            margin: 0;
            color: #0d6efd;
            font-weight: 700;
            font-size: 18px;
        }

        .company-info p {
            margin: 2px 0;
            font-size: 10px;
            color: #6c757d;
        }

        .voucher-title {
            text-align: right;
        }

        .voucher-title h4 {
            margin: 0;
            color: #0d6efd;
            font-weight: 800;
            font-size: 24px;
            letter-spacing: 1px;
        }

        .voucher-title .ref-no {
            background: #e7f1ff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            color: #0d6efd;
            font-weight: 600;
            display: inline-block;
            margin-top: 5px;
        }

        /* Transaction Info Cards - NOW FULL WIDTH */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr; /* Single column full width */
            gap: 15px;
            margin-bottom: 25px;
        }

        .info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #0d6efd;
            padding: 15px;
            border-radius: 6px;
            width: 100%;
        }

        .info-card.to-account {
            border-left-color: #198754;
        }

        .info-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: 700;
            color: #212529;
            font-size: 14px;
        }

        .currency-badge {
            display: inline-block;
            background: #0d6efd;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            margin-left: 5px;
        }

        /* Table Styles */
        .table-section {
            margin-bottom: 25px;
        }

        .section-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            padding: 10px 15px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 6px 6px 0 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-header i {
            font-size: 14px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            border: 1px solid #dee2e6;
            border-top: none;
        }

        .data-table thead {
            background-color: #f8f9fa;
        }

        .data-table th {
            padding: 10px;
            text-align: left;
            font-weight: 700;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }

        .data-table td {
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .data-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .text-end {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .amount {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #198754;
        }

        .total-row {
            background-color: #e7f1ff !important;
            font-weight: 700;
        }

        .total-row td {
            border-top: 2px solid #0d6efd;
            color: #0b5ed7;
            font-size: 12px;
        }

        /* Ledger Table Specific */
        .ledger-table .credit {
            color: #198754;
        }

        .ledger-table .debit {
            color: #dc3545;
        }

        /* Signature Section - FLEX LAYOUT FOR SIDE BY SIDE */
        .signature-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
            page-break-inside: avoid;
            clear: both; /* Ensure it clears any floated content */
        }

        .signature-grid {
            display: flex; /* Changed from grid to flex for better control */
            flex-direction: row; /* Ensure horizontal layout */
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 30px;
            width: 100%;
        }

        .signature-box {
            text-align: center;
            flex: 1; /* Equal width for all three */
            min-width: 0; /* Prevent overflow issues */
        }

        .signature-line {
            border-top: 1px solid #495057;
            margin-top: 40px;
            padding-top: 8px;
            font-size: 11px;
            font-weight: 600;
            color: #212529;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .signature-subtitle {
            font-size: 9px;
            color: #6c757d;
            margin-top: 4px;
            word-wrap: break-word;
        }

        /* Footer */
        .print-footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            font-size: 8px;
            color: #6c757d;
            flex-wrap: wrap;
            gap: 10px;
        }

        /* Print Controls */
        .print-controls {
            background: #212529;
            padding: 15px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .btn-print {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: background 0.2s;
        }

        .btn-print:hover {
            background: #0b5ed7;
        }

        .btn-close {
            background: transparent;
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 10px 20px;
            border-radius: 6px;
            margin-left: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-close:hover {
            background: rgba(255,255,255,0.1);
        }

        /* Print Media */
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }
            
            body {
                background: white;
                font-size: 10px;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            .print-container {
                max-width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                /* Removed min-height constraint */
            }
            
            .header-section {
                border-bottom-width: 2px;
            }
            
            /* KEEP SIGNATURES SIDE BY SIDE IN PRINT */
            .signature-grid {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            
            .signature-box {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            
            /* Ensure signature section stays at bottom but not fixed */
            .signature-section {
                margin-top: 40px;
                page-break-before: auto; /* Don't force new page */
                page-break-inside: avoid;
            }
            
            .data-table {
                font-size: 10px;
            }
            
            .info-card {
                page-break-inside: avoid;
            }
            
            .table-section {
                page-break-inside: avoid;
            }
        }

        /* Responsive - Stack on mobile */
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .signature-grid {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Print Controls -->
    <div class="no-print print-controls">
        <button onclick="handlePrint()" class="btn-print">
            <i class="bi bi-printer-fill"></i> Print 
        </button>
        <button onclick="window.close()" class="btn-close">
            <i class="bi bi-x-lg"></i> Close
        </button>
    </div>

    <div class="print-container">
        
        <!-- Header -->
        <div class="header-section">
            <div class="row align-items-center">
                <div class="col-6">
                    <div class="logo-container">
                        @if($settings['logo'] && $settings['logo'] !== asset('images/logo.png'))
                            <img src="{{ $settings['logo'] }}" alt="Company Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display: none; width: 60px; height: 60px; background: #e9ecef; border-radius: 8px; align-items: center; justify-content: center; color: #6c757d; font-size: 24px;">
                                <i class="bi bi-building"></i>
                            </div>
                        @else
                            <div style="width: 60px; height: 60px; background: #0d6efd; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
                                {{ substr($settings['name'], 0, 1) }}
                            </div>
                        @endif
                        <div class="company-info">
                            <h4>{{ $settings['name'] }}</h4>
                            <p><i class="bi bi-envelope"></i> {{ $settings['email'] }}</p>
                            <p><i class="bi bi-whatsapp"></i> {{ $settings['phone'] }}</p>
                            @if($settings['address'])
                                <p><i class="bi bi-geo-alt"></i> {{ Str::limit($settings['address'], 50) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-6 voucher-title">
                    <h4>Transaction Ledger</h4>
                    <div class="ref-no">
                        <i class="bi bi-upc-scan"></i> {{ $record->reference_no }}
                    </div>
                    <div style="margin-top: 8px; font-size: 10px; color: #6c757d;">
                        Date: {{ now()->format('M d, Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Info - FULL WIDTH -->
    
<div class="info-grid">
    <div class="info-card">
        <div class="info-label">Account Details</div>
        <div class="info-value">
            {{-- Use $account passed from controller, NOT $record->accountFrom --}}
            {{ $account->account_name_with_category_and_branch ?? $account->account_name ?? 'N/A' }}
            <span class="currency-badge">{{ $currency->currency_code ?? '' }}</span>
        </div>
        <div style="font-size: 11px; color: #6b7280; margin-top: 4px;">
            Account ID: {{ $account->uid }}
        </div>
    </div>
</div>
        <div class="content-wrapper">
            <!-- Transactions Table -->
            <div class="table-section">
    <div class="section-header">
        <i class="bi bi-file-text"></i> Transaction Details (All Related)
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                {{-- <th width="10%">Type</th> --}}
                 <th width="10%">TID</th>
                <th width="20%">Processed By</th>
                <th width="25%">Details</th>
                {{-- <th width="20%">Related Account</th> --}}
                <th width="10%">Service</th>
                <th width="10%" class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $trans)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    {{-- <td>
                        @if($trans->transaction_type === 'Outgoing')
                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-arrow-up-circle"></i> Out
                            </span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-arrow-down-circle"></i> In
                            </span>
                        @endif
                    </td> --}}
                    <td>{{ $trans->id}}</td>
                    <td>
                        <div style="font-weight: 600;">{{ $trans->user?->name ?? 'System' }}</div>
                        <small style="color: #6c757d;">{{ $trans->created_at?->format('M d, Y H:i') }}</small>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #212529;">{{ $trans->fullname ?? 'N/A' }}</div>
                        <small style="color: #6c757d;">{{ $trans->description ?? 'No description' }}</small>
                    </td>
                    {{-- <td>
                        <small style="color: #495057;">{{ $trans->related_account }}</small>
                    </td> --}}
                    <td>
                        <span class="badge bg-primary bg-opacity-10 text-primary">
                            {{ $trans->service?->title ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <span class="amount {{ $trans->transaction_type === 'Outgoing' ? 'text-success' : 'text-danger' }}">
                            {{ $trans->transaction_type === 'Outgoing' ? '' : '-' }}{{ number_format($trans->amount ?? 0, 2) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 30px; color: #6c757d;">
                        <i class="bi bi-inbox" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                        No transactions found for this account
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($transactions->count() > 0)
            <tfoot>
                {{-- <tr class="total-row">
                    <td colspan="6" class="text-end">Total Outgoing:</td>
                    <td class="text-end text-danger">
                        -{{ number_format($totalOutgoing, 2) }}
                    </td>
                </tr> --}}
                {{-- <tr class="total-row" style="background-color: #f8f9fa;">
                    <td colspan="6" class="text-end">Total Incoming:</td>
                    <td class="text-end text-success">
                        +{{ number_format($totalIncoming, 2) }}
                    </td>
                </tr> --}}
                <tr class="total-row" style="background-color: #e7f1ff;">
                    <td colspan="5" class="text-end"><strong>Net Movement:</strong></td>
                    <td class="text-end">
                        <strong>{{ number_format($totalOutgoing - $totalIncoming, 2) }}</strong>
                        <small style="display: block; color: #0b5ed7; font-weight: 600;">{{ $currency->currency_code ?? '' }}</small>
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

            <!-- Ledger Table -->
            <div class="table-section">
                <div class="section-header" style="background: linear-gradient(135deg, #198754 0%, #146c43 100%);">
                    <i class="bi bi-journal-text"></i> Account Ledger - Customer Deposit
                </div>
                <table class="data-table ledger-table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Date</th>
                            <th width="35%">Description</th>
                            <th width="15%" class="text-end">Debit</th>
                            <th width="15%" class="text-end">Credit</th>
                             <th width="15%" class="text-end">Balance</th>
                        </tr>
                    </thead>
                   <tbody>
    @php 
        $runningBalance = 0; 
        // If you want to show opening balance first, set it here: $runningBalance = $openingBalance ?? 0;
    @endphp
    
    @forelse($fromAccountLedger as $index => $ledger)
        @php 
            // Add credit, subtract debit for running total
            $runningBalance += ($ledger->credit ?? 0) - ($ledger->debit ?? 0);
        @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $ledger->date_update ?? '-' }}</td>
            <td>{{ $ledger->description ?? 'No description' }}</td>
            <td class="text-end debit">
                    {{ number_format($ledger->debit, 2) }}
            </td>
            <td class="text-end credit">
                    {{ number_format($ledger->credit, 2) }}
            </td>
            <td class="text-end balance" style="font-weight: 600; font-family: 'Courier New', monospace;">
                {{ number_format($runningBalance, 2) }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="text-center" style="padding: 30px; color: #6c757d;">
                <i class="bi bi-journal-x" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                No ledger entries found
            </td>
        </tr>
    @endforelse
</tbody>                @if($fromAccountLedger->count() > 0)
    <tfoot>
        <tr class="total-row" style="background-color: #d1e7dd !important;">
            <td colspan="3" class="text-end"><strong>Balance Summary:</strong></td>
            <td class="text-end text-danger">{{ number_format($totalDebit, 2) }}</td>
            <td class="text-end text-success">{{ number_format($totalCredit, 2) }}</td>
            <td class="text-center">
                <!-- Fixed: Dynamic color and explicit minus sign handling -->
                <strong style="color: {{ $balance >= 0 ? '#198754' : '#dc2626' }};">
                    @if($balance < 0)
                        -{{ number_format(abs($balance), 2) }}
                    @else
                        {{ number_format($balance, 2) }}
                    @endif
                </strong>
                <small style="display: block; color: {{ $balance >= 0 ? '#146c43' : '#dc2626' }};">
                    {{ $currency->currency_code ?? '' }}
                </small>
            </td>
        </tr>
    </tfoot>
@endif
                </table>
            </div>
        </div>
    @php
      $displayedServices = [];
  @endphp
  
          @foreach ($transactions as $rule)
      @foreach ($services as $ser)
          @if ($rule->service_type == $ser->id && !in_array($ser->id, $displayedServices))
          
           <div class="card border-dark mb-2">
    <div class="card-header text-dark">
        <h5 class="mb-0">{{ $ser->title }} Terms and Condition</h5>
    </div>
    <div class="card-body">
        <p class="card-text mb-0">{!! nl2br(strip_tags(str_replace(['</p>', '</li>'], "\n", $rule->service_content))) !!}</p>
    </div>
</div>

              @php
                  $displayedServices[] = $ser->id;
              @endphp
              @break
          @endif
      @endforeach
  @endforeach

           <div class="card border-dark">
    <div class="card-header text-dark">
        <h5 class="mb-0">Remarks</h5>
    </div>
    <div class="card-body">
        <p class="card-text mb-0">{{ $record->from_remarks }}</p>
    </div>
</div>


        <!-- Signature Section - SIDE BY SIDE -->
        <div class="signature-section">
            <div class="signature-grid">
                <div class="signature-box">
                    <div class="signature-line">Prepared By</div>
                    <div class="signature-subtitle">{{ $record->user?->name ?? 'System User' }}</div>
                    <div class="signature-subtitle">{{ now()->format('M d, Y') }}</div>
                </div>
                
                <div class="signature-box">
                    <div class="signature-line">Customer Signature</div>
                    <div class="signature-subtitle">I confirm the above transactions</div>
                    <div class="signature-subtitle">Date: _____________</div>
                </div>
                
                <div class="signature-box">
                    <div class="signature-line">Authorized Approval</div>
                    <div class="signature-subtitle">Manager / Supervisor</div>
                    <div class="signature-subtitle">Stamp & Sign</div>
                </div>
            </div>

            <div class="print-footer">
                <div>
                    <i class="bi bi-printer"></i> Printed on: {{ now()->format('d/m/Y H:i:s') }} 
                    @if(auth()->check())
                        | User: {{ auth()->user()->name }} (ID: {{ auth()->user()->id }})
                    @endif
                </div>
                <div>
                    <i class="bi bi-shield-check"></i> System Generated - {{ config('app.name') }}
                </div>
            </div>
        </div>

    </div>

    <script>
        function handlePrint() {
            // Hide print buttons before printing
            document.querySelector('.no-print').style.display = 'none';
            
            // Wait a moment for UI to update then print
            setTimeout(() => {
                window.print();
                
                // Show buttons again after print dialog closes
                setTimeout(() => {
                    document.querySelector('.no-print').style.display = 'block';
                }, 100);
            }, 100);
        }
    </script>
</body>
</html>