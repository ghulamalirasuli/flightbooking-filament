<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposits</title>
    
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
            height: auto; 
            overflow: hidden;
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

        @media print {
            body { background: white; }
            .no-print { display: none !important; }
            .print-container { width: 100%; margin: 0; }
            .voucher-copy { box-shadow: none; }
        }
    </style>
</head>
<body>
@if($format != 'pdf')
    <div class="no-print bg-dark py-2 shadow-sm sticky-top">
        <div class="container text-center">
            <button onclick="window.print()" class="btn btn-primary btn-sm px-4 fw-bold">
                <i class="bi bi-printer-fill me-2"></i> PRINT BOTH COPIES
            </button>
            <button onclick="window.close()" class="btn btn-outline-light btn-sm ms-2">Close Tab</button>
        </div>
    </div>
@endif
    <div class="print-container">
        
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

                 <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Inserted</th>
                                <th>Reference No.</th>
                                <th>Description</th>
                                <th>Account</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Status</th>
                            </tr>
                        </thead>
    
           
                        <tbody>
                                @foreach($deposits as $deposit)
                            <tr>
                                <td>{{ $loop->index + 1 }}</td>
                                <td>{{ $deposit->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $deposit->reference_no }}</td>
                                <td>{{ $deposit->description }}</td>
                                <td>{{ $deposit->account ? $deposit->account->account_name_with_category_and_branch : 'Exchange' }}</td>
                                <td>{{ $deposit->debit }} <br>{{ $deposit->currency->currency_code ?? '' }}</td>
                                <td>{{ $deposit->credit }}<br> {{ $deposit->currency->currency_code ?? '' }}</td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $deposit->status }}</span>
                                    <br>
                                    <small>{{ $deposit->updated_at->format('M d, Y H:i') }} By {{ $deposit->updated_by->name ?? 'Admin' }}</small>
                                </td>
                            </tr>
        @endforeach

                        </tbody>
                    </table>
    </div>
</body>
</html>