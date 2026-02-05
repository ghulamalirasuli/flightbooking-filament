{{-- Account Ledgers Side-by-Side Layout --}}
<div style="width: 100%; display: table; table-layout: fixed; border-spacing: 20px 0; margin: 20px 0;">
    
    {{-- FROM ACCOUNT COLUMN --}}
    <div style="display: table-cell; width: 50%; vertical-align: top; padding-right: 10px;">
        <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); height: 100%; display: flex; flex-direction: column;">
            
            {{-- Header: Account Name & Currency --}}
            <div style="background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; padding: 16px 20px; border-bottom: 2px solid #1e40af;">
                <h3 style="margin: 0; font-size: 14px; font-weight: bold; line-height: 1.4;">
                    From Account:<br>
                    <span style="font-size: 13px; opacity: 0.95; font-weight: 600;">
                        {{ $fromAccount?->account_name_with_category_and_branch ?? 'N/A' }} 
                        <span style="font-weight: normal; opacity: 0.8;">({{ $fromAccount?->currency?->currency_code ?? 'N/A' }})</span>
                    </span>
                </h3>
            </div>
            
            {{-- Ledger Table --}}
            <div style="padding: 15px; flex: 1;">
                @if($fromLedgers->count() > 0)
                    <div style="overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 6px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                            <thead>
                                <tr style="background: linear-gradient(135deg, #fecaca, #fca5a5);">
                                    <th style="padding: 10px 8px; text-align: left; color: #374151; font-weight: bold; border-bottom: 2px solid #f87171; white-space: nowrap;">Description</th>
                                    <th style="padding: 10px 8px; text-align: right; color: #374151; font-weight: bold; border-bottom: 2px solid #f87171; width: 80px;">Debit</th>
                                    <th style="padding: 10px 8px; text-align: right; color: #374151; font-weight: bold; border-bottom: 2px solid #f87171; width: 80px;">Credit</th>
                                    <th style="padding: 10px 8px; text-align: right; color: #374151; font-weight: bold; border-bottom: 2px solid #f87171; width: 80px;">Balance</th>
                                    <th style="padding: 10px 8px; text-align: left; color: #374151; font-weight: bold; border-bottom: 2px solid #f87171; padding-left: 15px; width: 100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $fromTotal = 0; @endphp
                                @foreach($fromLedgers as $ledger)
                                    @php 
                                        $debit = $ledger->debit ?? 0;
                                        $credit = $ledger->credit ?? 0;
                                        $balance = $credit - $debit;
                                        $fromTotal += $balance;
                                        $currency = $ledger->currencyInfo?->currency_code ?? $fromAccount?->currency?->currency_code ?? '';
                                        $rowBg = $loop->iteration % 2 === 0 ? '#f9fafb' : '#ffffff';
                                    @endphp
                                    <tr style="background-color: {{ $rowBg }}; border-bottom: 1px solid #f3f4f6;">
                                        <td style="padding: 8px; font-weight: 500; color: #111827; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ \Illuminate\Support\Str::limit($ledger->description ?? '-', 25) }}
                                        </td>
                                        <td style="padding: 8px; text-align: right; font-family: monospace; color: #6b7280;">{{ number_format($debit, 2) }}</td>
                                        <td style="padding: 8px; text-align: right; font-family: monospace; color: #6b7280;">{{ number_format($credit, 2) }}</td>
                                        <td style="padding: 8px; text-align: right; font-family: monospace; font-weight: bold; color: {{ $balance >= 0 ? '#059669' : '#dc2626' }};">
                                            {{ number_format($balance, 2) }}
                                        </td>
                                        <td style="padding: 8px; padding-left: 15px; font-size: 11px; color: #6b7280; line-height: 1.4;">
                                            <div>- {{ $ledger->status ?? 'Pending' }}</div>
                                            <div style="color: #9ca3af; font-size: 10px;">{{ $ledger->date_update ? \Carbon\Carbon::parse($ledger->date_update)->format('Y-m-d') : '-' }}</div>
                                            @if($ledger->status === 'Confirmed')
                                                <div style="color: #16a34a; font-weight: 600; font-size: 10px; margin-top: 2px;">✓ Confirmed</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #f3f4f6; font-weight: bold; border-top: 2px solid #d1d5db;">
                                    <td style="padding: 12px 8px; color: #111827;">Total Amount</td>
                                    <td colspan="2"></td>
                                    <td style="padding: 12px 8px; text-align: right; font-family: monospace; color: #2563eb; font-size: 13px;">
                                        {{ number_format($fromTotal, 2) }} <span style="font-size: 10px; color: #6b7280; font-weight: normal;">{{ $fromAccount?->currency?->currency_code ?? '' }}</span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div style="padding: 40px; text-align: center; color: #9ca3af; background: #f9fafb; border-radius: 6px; border: 2px dashed #e5e7eb; font-style: italic;">
                        No ledger entries found for this account
                    </div>
                @endif
            </div>

            {{-- Print Footer --}}
            <div style="padding: 12px 15px; background: #f9fafb; border-top: 1px solid #e5e7eb; text-align: right;">
                <button type="button" onclick="window.print()" style="DISPLAY: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print
                </button>
            </div>
        </div>
    </div>

    {{-- TO ACCOUNT COLUMN --}}
    <div style="display: table-cell; width: 50%; vertical-align: top; padding-left: 10px;">
        <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); height: 100%; display: flex; flex-direction: column;">
            
            {{-- Header: Account Name & Currency --}}
            <div style="background: linear-gradient(135deg, #374151, #1f2937); color: white; padding: 16px 20px; border-bottom: 2px solid #111827;">
                <h3 style="margin: 0; font-size: 14px; font-weight: bold; line-height: 1.4;">
                    To Account:<br>
                    <span style="font-size: 13px; opacity: 0.95; font-weight: 600;">
                        {{ $toAccount?->account_name_with_category_and_branch ?? 'N/A' }} 
                        <span style="font-weight: normal; opacity: 0.8;">({{ $toAccount?->currency?->currency_code ?? 'N/A' }})</span>
                    </span>
                </h3>
            </div>
            
            {{-- Ledger Table --}}
            <div style="padding: 15px; flex: 1;">
                @if($toLedgers->count() > 0)
                    <div style="overflow-x: auto; border: 1px solid #e5e7eb; border-radius: 6px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                            <thead>
                                <tr style="background: linear-gradient(135deg, #fecaca, #fca5a5);">
                                    <th style="padding: 10px 8px; text-align: left; color: #374151; font-weight: bold; border-bottom: 2px solid #f87171; white-space: nowrap;">Description</th>
                                    <th style="padding: 10px 8px; text-align: right; color: #374151; font-weight: bold; border-bottom: 2px solid #f87171; width: 80px;">Debit</th>
                                    <th style="padding: 10px 8px; text-align: right; color: #374151; font-weight: bold; border-bottom: 2px solid #f87171; width: 80px;">Credit</th>
                                    <th style="padding: 10px 8px; text-align: right; color: #374151; font-weight: bold; border-bottom: 2px solid #f87171; width: 80px;">Balance</th>
                                    <th style="padding: 10px 8px; text-align: left; color: #374151; font-weight: bold; border-bottom: 2px solid #f87171; padding-left: 15px; width: 100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $toTotal = 0; @endphp
                                @foreach($toLedgers as $ledger)
                                    @php 
                                        $debit = $ledger->debit ?? 0;
                                        $credit = $ledger->credit ?? 0;
                                        $balance = $debit - $credit; // To account: Debit - Credit
                                        $toTotal += $balance;
                                        $currency = $ledger->currencyInfo?->currency_code ?? $toAccount?->currency?->currency_code ?? '';
                                        $rowBg = $loop->iteration % 2 === 0 ? '#f9fafb' : '#ffffff';
                                    @endphp
                                    <tr style="background-color: {{ $rowBg }}; border-bottom: 1px solid #f3f4f6;">
                                        <td style="padding: 8px; font-weight: 500; color: #111827; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ \Illuminate\Support\Str::limit($ledger->description ?? '-', 25) }}
                                        </td>
                                        <td style="padding: 8px; text-align: right; font-family: monospace; color: #6b7280;">{{ number_format($debit, 2) }}</td>
                                        <td style="padding: 8px; text-align: right; font-family: monospace; color: #6b7280;">{{ number_format($credit, 2) }}</td>
                                        <td style="padding: 8px; text-align: right; font-family: monospace; font-weight: bold; color: {{ $balance >= 0 ? '#059669' : '#dc2626' }};">
                                            {{ number_format($balance, 2) }}
                                        </td>
                                        <td style="padding: 8px; padding-left: 15px; font-size: 11px; color: #6b7280; line-height: 1.4;">
                                            <div>- {{ $ledger->status ?? 'Pending' }}</div>
                                            <div style="color: #9ca3af; font-size: 10px;">{{ $ledger->date_update ? \Carbon\Carbon::parse($ledger->date_update)->format('Y-m-d') : '-' }}</div>
                                            @if($ledger->status === 'Confirmed')
                                                <div style="color: #16a34a; font-weight: 600; font-size: 10px; margin-top: 2px;">✓ Confirmed</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #f3f4f6; font-weight: bold; border-top: 2px solid #d1d5db;">
                                    <td style="padding: 12px 8px; color: #111827;">Total Amount</td>
                                    <td colspan="2"></td>
                                    <td style="padding: 12px 8px; text-align: right; font-family: monospace; color: #1f2937; font-size: 13px;">
                                        {{ number_format($toTotal, 2) }} <span style="font-size: 10px; color: #6b7280; font-weight: normal;">{{ $toAccount?->currency?->currency_code ?? '' }}</span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div style="padding: 40px; text-align: center; color: #9ca3af; background: #f9fafb; border-radius: 6px; border: 2px dashed #e5e7eb; font-style: italic;">
                        No ledger entries found for this account
                    </div>
                @endif
            </div>

            {{-- Print Footer --}}
            <div style="padding: 12px 15px; background: #f9fafb; border-top: 1px solid #e5e7eb; text-align: right;">
                <button type="button" onclick="window.print()" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: #374151; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.background='#111827'" onmouseout="this.style.background='#374151'">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print
                </button>
            </div>
        </div>
    </div>

</div>