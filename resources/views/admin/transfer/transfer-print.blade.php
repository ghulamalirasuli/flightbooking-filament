<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher - {{ $record->reference_no }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        @page {
            size: A4;
            margin: 5mm; 
        }
        
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            font-size: 11px;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
        }

        .print-container {
            width: 210mm;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
        }

        .voucher-copy {
            background: white;
            padding: 10mm 15mm;
            position: relative;
            height: 143mm; 
            overflow: hidden;
        }

        .cut-line {
            border-top: 2px dashed #ccc;
            position: relative;
            text-align: center;
            margin: 0;
        }
        .cut-line span {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0 10px;
            font-size: 10px;
            color: #999;
        }

        .logo-container img {
            max-height: 45px;
            width: auto;
        }

        .header-accent {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .transaction-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-left: 4px solid #0d6efd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .data-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 600;
        }

        .data-value {
            font-weight: 700;
            color: #212529;
            font-size: 12px;
        }

        .sig-line {
            border-top: 1px solid #495057;
            padding-top: 3px;
            margin-top: 25px;
            font-size: 10px;
            font-weight: 600;
        }

        /* REMOVED: .copy-indicator styling */

        @media print {
            body { background: white; }
            .no-print { display: none !important; }
            .print-container { width: 100%; margin: 0; }
            .voucher-copy { box-shadow: none; }
        }
    </style>
</head>
<body>

    <div class="no-print bg-dark py-2 shadow-sm sticky-top">
        <div class="container text-center">
            <button onclick="window.print()" class="btn btn-primary btn-sm px-4 fw-bold">
                <i class="bi bi-printer-fill me-2"></i> PRINT COPY
            </button>
            <button onclick="window.close()" class="btn btn-outline-light btn-sm ms-2">Close Tab</button>
        </div>
    </div>

    <div class="print-container">
        
            <div class="voucher-copy">
                {{-- REMOVED: <div class="copy-indicator"> --}}

                <div class="row header-accent align-items-center">
                    <div class="col-7 logo-container">
                        @if($settings['logo'])
                            <img src="{{ $settings['logo'] }}" alt="Logo">
                        @else
                            <h5 class="fw-bold text-primary m-0">{{ $settings['name'] }}</h5>
                        @endif
                    </div>
                    <div class="col-5 text-end pe-5">
                        <p class="fw-bold mb-0 text-uppercase" style="font-size: 9px;">{{ $settings['name'] }}</p>
                        <p class="mb-0 text-muted" style="font-size: 8px;"><i class="bi bi-envelope text-primary"></i> {{ $settings['email'] }} | <i class="bi bi-whatsapp text-success"></i> {{ $settings['phone'] }}</p>
                    </div>
                </div>

                <div class="row mb-2 align-items-center">
                    <div class="col-4 border-end">
                        <span class="data-label d-block">Voucher Reference</span>
                        <span class="data-value text-uppercase">{{ $record->reference_no }}</span>
                    </div>
                    <div class="col-4 text-center">
                        <h6 class="fw-bold text-primary m-0" style="letter-spacing: 1px; text-transform: uppercase;">Transfer Voucher</h6>
                    </div>
                    <div class="col-4 text-end border-start">
                        <span class="data-label d-block">Date & Time</span>
                        <span class="data-value" style="font-size: 11px;">{{ $record->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-1 mb-2" style="font-size: 10px;">
                    <i class="bi bi-info-circle-fill me-1"></i> Account Details
                </h6>
                <div class="transaction-card shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-7">
                            <span class="data-label d-block">From</span>
                            <h6 class="fw-bold mb-0" style="font-size: 13px;">
                                {{ $record->accountFrom?->account_name_with_category_and_branch ?? 'Exchange' }}
                            </h6>
                        </div>
                        <div class="col-5 border-start ps-3">
                            <div>
                                <span class="data-label d-block">{{ $ledger->pay_status ?? 'Cash' }} Amount</span>
                                <span class="fw-bold text-primary" style="font-size: 14px;">
                                    {{ $record->mtcurrency?->currency_code }} {{ number_format($record->amount, 2) }}
                                    <small class="text-dark"> Creditted</small>
                                </span>
                            </div>
                        </div>
                    </div>
                           
                </div>


                <div class="transaction-card shadow-sm">
                       <div class="row align-items-center">
                        <div class="col-7">
                            <span class="data-label d-block">To</span>
                            <h6 class="fw-bold mb-0" style="font-size: 13px;">
                                {{ $record->accountTo?->account_name_with_category_and_branch ?? 'Exchange' }}
                            </h6>
                        </div>
                        <div class="col-5 border-start ps-3">
                            <div>
                                <span class="data-label d-block"> {{ $ledger->pay_status ?? 'Cash' }} Amount</span>
                                <span class="fw-bold text-primary" style="font-size: 14px;">
                                    {{ $record->mtcurrency?->currency_code }} {{ number_format($record->amount, 2) }}
                                    <small class="text-dark">   Debitted </small>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <h6 class="fw-bold text-primary border-bottom pb-1 mb-1" style="font-size: 10px;">
                        <i class="bi bi-pencil-square me-1"></i> Narration
                    </h6>
                    <div class="p-2 bg-light border rounded text-secondary" style="min-height: 35px; font-style: italic; font-size: 9px;">
                        {{ $record->description ?: 'No additional narration provided.' }}
                    </div>
                </div>

                <div class="row text-center mt-3">
                    <div class="col-4">
                        <div class="sig-line">Issued By</div>
                        <p class="small text-muted mb-0">{{ $record->user?->name }}</p>
                    </div>
                    <div class="col-4">
                        <div class="sig-line">Customer Signature</div>
                        <p class="small text-muted mb-0">Verified</p>
                    </div>
                    <div class="col-4">
                        <div class="sig-line">Authorized Manager</div>
                        <p class="small text-muted mb-0">Stamp & Sign</p>
                    </div>
                </div>

                <div class="mt-3 pt-1 border-top text-center text-muted" style="font-size: 7px;">
                    Printed on: {{ now()->format('d/m/Y H:i:s') }} | System ID: {{ auth()->user()->id }}
                </div>
            </div>

    </div>
</body>
</html>