<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print To - {{ $record->reference_no }}</title>
    
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
        }

        .header-section {
            border-bottom: 3px solid #198754;
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
            color: #198754;
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
            color: #198754;
            font-weight: 800;
            font-size: 24px;
            letter-spacing: 1px;
        }

        .voucher-title .ref-no {
            background: #d1e7dd;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            color: #146c43;
            font-weight: 600;
            display: inline-block;
            margin-top: 5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        .info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #198754;
            padding: 15px;
            border-radius: 6px;
            width: 100%;
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
            background: #198754;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            margin-left: 5px;
        }

        .table-section {
            margin-bottom: 25px;
        }

        .section-header {
            background: linear-gradient(135deg, #198754 0%, #146c43 100%);
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

        .section-header.blue {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
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

        .text-end {
            text-align: right !important;
        }

        .amount {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #198754;
        }

        .total-row {
            background-color: #d1e7dd !important;
            font-weight: 700;
        }

        .total-row td {
            border-top: 2px solid #198754;
            color: #146c43;
            font-size: 12px;
        }

        .ledger-table .credit {
            color: #dc3545;
        }

        .ledger-table .debit {
            color: #198754;
        }

        .signature-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
            page-break-inside: avoid;
            clear: both;
        }

        .signature-grid {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 30px;
            width: 100%;
        }

        .signature-box {
            text-align: center;
            flex: 1;
            min-width: 0;
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
            background: #198754;
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
            background: #146c43;
        }

        .btn-close {
            background: transparent;
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 10px 20px;
            border-radius: 6px;
            margin-left: 10px;
            cursor: pointer;
        }

        @media print {
            body {
                background: white;
                font-size: 10px;
            }
            
            .no-print {
                display: none !important;
            }
            
            .print-container {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }
            
            .header-section {
                border-bottom-width: 2px;
            }
            
            .signature-grid {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                page-break-inside: avoid;
            }
            
            .signature-box {
                page-break-inside: avoid;
            }
        }

        @media (max-width: 768px) {
            .signature-grid {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="no-print print-controls">
        <button onclick="handlePrint()" class="btn-print">
            <i class="bi bi-printer-fill"></i> Print To
        </button>
        <button onclick="window.close()" class="btn-close">
            <i class="bi bi-x-lg"></i> Close
        </button>
    </div>

    <div class="print-container">
        
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
                            <div style="width: 60px; height: 60px; background: #198754; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
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
                    <h4>Receipt Voucher</h4>
                    <div class="ref-no">
                        <i class="bi bi-upc-scan"></i> {{ $record->reference_no }}
                    </div>
                    <div style="margin-top: 8px; font-size: 10px; color: #6c757d;">
                        Date: {{ now()->format('M d, Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <div class="info-label">To Account (Receiving Account)</div>
                <div class="info-value">
                    {{ $record->accountTo?->account_name_with_category_and_branch ?? 'N/A' }}
                    <span class="currency-badge">{{ $record->currencyTo?->currency_code ?? '' }}</span>
                </div>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="table-section">
                <div class="section-header blue">
                    <i class="bi bi-file-text"></i> Transaction Details
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">ID</th>
                            <th width="20%">Processed By</th>
                            <th width="35%">Details</th>
                            <th width="15%">Service</th>
                            <th width="15%" class="text-end">Received Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $index => $trans)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><span class="badge bg-light text-dark">#{{ $trans->id }}</span></td>
                                <td>
                                    <div style="font-weight: 600;">{{ $trans->user?->name ?? 'System' }}</div>
                                    <small style="color: #6c757d;">{{ $trans->created_at?->format('M d, Y H:i') }}</small>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #212529;">{{ $trans->fullname ?? 'N/A' }}</div>
                                    <small style="color: #6c757d;">{{ $trans->description ?? 'No description' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        {{ $trans->service?->title ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="amount">{{ number_format($trans->sold_price ?? 0, 2) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 30px; color: #6c757d;">
                                    <i class="bi bi-inbox" style="font-size: 24px; display: block; margin-bottom: 10px;"></i>
                                    No transactions found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($transactions->count() > 0)
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="5" class="text-end">Total Received Amount:</td>
                                <td class="text-end">
                                    <span class="amount" style="font-size: 14px;">{{ number_format($totalSoldPrice, 2) }}</span>
                                    <small style="display: block; color: #146c43; font-weight: 600;">{{ $record->currencyTo?->currency_code ?? '' }}</small>
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <div class="table-section">
                <div class="section-header">
                    <i class="bi bi-journal-text"></i> Account Ledger - Receipts
                </div>
                <table class="data-table ledger-table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Date</th>
                            <th width="35%">Description</th>
                            <th width="15%" class="text-end">Debit (In)</th>
                            <th width="15%" class="text-end">Credit (Out)</th>
                            <th width="15%" class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $runningBalance = 0;
                        @endphp
                        
                        @forelse($toAccountLedger as $index => $ledger)
                            @php 
                                // For receiving account: Debit increases balance, Credit decreases
                                $runningBalance += ($ledger->debit ?? 0) - ($ledger->credit ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $ledger->date_update ?? '-' }}</td>
                                <td>{{ $ledger->description ?? 'No description' }}</td>
                                <td class="text-end debit" style="color: #198754; font-weight: 600;">
                                    {{ number_format($ledger->debit, 2) }}
                                </td>
                                <td class="text-end credit" style="color: #dc3545;">
                                    @if($ledger->credit > 0)
                                        {{ number_format($ledger->credit, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end" style="font-weight: 600; font-family: 'Courier New', monospace; color: #212529;">
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
                    </tbody>
                    @if($toAccountLedger->count() > 0)
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="3" class="text-end"><strong>Account Summary:</strong></td>
                                <td class="text-end" style="color: #198754;">{{ number_format($totalDebit, 2) }}</td>
                                <td class="text-end" style="color: #dc3545;">{{ number_format($totalCredit, 2) }}</td>
                                <td class="text-end">
                                    <strong style="color: #146c43; font-size: 13px;">{{ number_format($balance, 2) }}</strong>
                                    <small style="display: block; color: #198754;">{{ $record->currencyTo?->currency_code ?? '' }}</small>
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        @if($record->to_remarks)
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-left: 4px solid #198754; background: #f8f9fa; border: 1px solid #d1e7dd;">
            <h6 class="alert-heading" style="color: #146c43; font-weight: 700; font-size: 11px; text-transform: uppercase; margin-bottom: 8px;">
                <i class="bi bi-chat-left-text"></i> Remarks
            </h6>
            <p style="margin: 0; color: #333; font-size: 11px;">{{ $record->to_remarks }}</p>
        </div>
        @endif

        <div class="signature-section">
            <div class="signature-grid">
                <div class="signature-box">
                    <div class="signature-line">Received By</div>
                    <div class="signature-subtitle">{{ $record->accountTo?->account_name ?? 'Account Holder' }}</div>
                    <div class="signature-subtitle">{{ now()->format('M d, Y') }}</div>
                </div>
                
                <div class="signature-box">
                    <div class="signature-line">Authorized Signature</div>
                    <div class="signature-subtitle">I confirm receipt of above amount</div>
                    <div class="signature-subtitle">Date: _____________</div>
                </div>
                
                <div class="signature-box">
                    <div class="signature-line">Processed By</div>
                    <div class="signature-subtitle">{{ $record->user?->name ?? 'System User' }}</div>
                    <div class="signature-subtitle">Manager / Supervisor</div>
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
            document.querySelector('.no-print').style.display = 'none';
            setTimeout(() => {
                window.print();
                setTimeout(() => {
                    document.querySelector('.no-print').style.display = 'block';
                }, 100);
            }, 100);
        }
    </script>
</body>
</html>