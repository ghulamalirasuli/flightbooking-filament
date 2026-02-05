{{-- Full Width Layout: Collapsible Cards per Account, Grouped by Currency --}}
<div style="width: 100%; display: flex; flex-direction: column; gap: 24px;">

    @forelse($currencyGroups as $group)
        @php
            $currency = $group['currency'];
            $currencyCode = $currency?->currency_code ?? 'Unknown';
            $currencyName = $currency?->currency_name ?? 'Unknown Currency';
            $currencyId = $group['currency_id'];
            
            // Calculate currency totals
            $totalCredit = collect($group['accounts'])->sum('total_credit');
            $totalDebit = collect($group['accounts'])->sum('total_debit');
        @endphp

        {{-- Currency Section Header --}}
        <div style="margin-top: 8px; margin-bottom: -8px;">
            <div style="display: flex; align-items: center; gap: 12px; padding-bottom: 8px; border-bottom: 2px solid #2563eb;">
                <h3 style="margin: 0; font-size: 18px; font-weight: bold; color: #1e293b;">
                    {{ $currencyName }} 
                    <span style="color: #64748b; font-weight: 500;">({{ $currencyCode }})</span>
                </h3>
                <span style="background: #dbeafe; color: #1e40af; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                    {{ count($group['accounts']) }} Account(s)
                </span>
                <div style="margin-left: auto; font-family: monospace; font-size: 13px; color: #64748b;">
                    Total Debit: <strong style="color: #dc2626;">-{{ number_format($totalDebit, 2) }}</strong> | 
                    Total Credit: <strong style="color: #059669;">{{ number_format($totalCredit, 2) }}</strong> 
                </div>
            </div>
        </div>

        {{-- Account Cards for this Currency --}}
        @foreach($group['accounts'] as $accountGroup)
            @php
                $account = $accountGroup['account'];
                $ledgers = $accountGroup['ledgers'];
                $accountUid = $accountGroup['uid'];
                $accountTotalCredit = $accountGroup['total_credit'];
                $accountTotalDebit = $accountGroup['total_debit'];
                $netAmount = $accountTotalCredit - $accountTotalDebit;
                $hasCredit = $accountTotalCredit > 0;
                $hasDebit = $accountTotalDebit > 0;
            @endphp
            
            <details style="width: 100%; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);" open>
                <summary style="cursor: pointer; padding: 16px 20px; background: linear-gradient(135deg, #ffffff, #f8fafc); border-bottom: 1px solid #e2e8f0; list-style: none; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s;">
                    
                    {{-- Left: Account Info --}}
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <div style="font-weight: 700; color: #1e293b; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                            {{ $account->account_name_with_category_and_branch ?? $account->account_name ?? 'Unknown Account' }}
                            @if($netAmount > 0)
                                <span style="color: #059669; font-size: 11px; background: #f0fdf4; padding: 2px 6px; border-radius: 4px; border: 1px solid #bbf7d0;">Net Receiver</span>
                            @elseif($netAmount < 0)
                                <span style="color: #dc2626; font-size: 11px; background: #fef2f2; padding: 2px 6px; border-radius: 4px; border: 1px solid #fecaca;">Net Payer</span>
                            @else
                                <span style="color: #6b7280; font-size: 11px; background: #f3f4f6; padding: 2px 6px; border-radius: 4px;">Balanced</span>
                            @endif
                        </div>
                        <div style="font-size: 12px; color: #64748b; display: flex; gap: 16px;">
                            <span>Currency: <strong>{{ $currencyCode }}</strong></span>
                            <span>Transactions: <strong>{{ count($ledgers) }}</strong></span>
                        </div>
                    </div>

                    {{-- Center: Quick Stats --}}
                    <div style="display: flex; gap: 24px; font-family: monospace; font-size: 13px;">
                        <div style="text-align: center;">
                            <div style="font-size: 10px; color: #dc2626; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Debit</div>
                            <div style="font-weight: bold; color: #dc2626;">
                                {{ $accountTotalDebit > 0 ? '-' : '' }}{{ number_format($accountTotalDebit, 2) }}
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 10px; color: #059669; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Credit</div>
                            <div style="font-weight: bold; color: #059669;">{{ number_format($accountTotalCredit, 2) }}</div>
                        </div>
                        <div style="text-align: center; padding-left: 24px; border-left: 1px solid #e2e8f0;">
                            <div style="font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Net</div>
                            <!-- FIX: Added abs() here to prevent double minus -->
                            <div style="font-weight: bold; color: {{ $netAmount >= 0 ? '#059669' : '#dc2626' }};">
                                {{ $netAmount >= 0 ? '' : '-' }}{{ number_format(abs($netAmount), 2) }}
                            </div>
                        </div>
                    </div>

                    {{-- Right: Expand Icon --}}
                    <div style="color: #94a3b8; margin-left: 16px;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transition: transform 0.2s;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </summary>
                
                {{-- Collapsible Content --}}
                <div style="padding: 20px; background: #ffffff;">
                    
                    {{-- Ledgers Table --}}
                    <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 16px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                            <thead>
                                <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                    <th style="padding: 10px 12px; text-align: left; color: #475569; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">#</th>
                                    <th style="padding: 10px 12px; text-align: left; color: #475569; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Insert</th>
                                    <th style="padding: 10px 12px; text-align: left; color: #475569; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Description</th>
                                    <th style="padding: 10px 12px; text-align: right; color: #475569; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; width: 120px;">Debit</th>
                                    <th style="padding: 10px 12px; text-align: right; color: #475569; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; width: 120px;">Credit</th>
                                    <th style="padding: 10px 12px; text-align: right; color: #475569; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; width: 120px;">Balance</th>
                                    <th style="padding: 10px 12px; text-align: center; color: #475569; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; width: 100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $runningBalance = 0;
                                @endphp
                                @foreach($ledgers as $key => $ledger)
                                    @php
                                        $runningBalance += ($ledger->credit ?? 0) - ($ledger->debit ?? 0);
                                    @endphp
                                    <tr style="border-bottom: 1px solid #f1f5f9; background-color: {{ $loop->even ? '#ffffff' : '#fafafa' }};">
                                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">
                                            {{ ++$key }}
                                        </td>
                                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">
                                            {{ $ledger->date_update ? \Carbon\Carbon::parse($ledger->date_update)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">
                                            {{ $ledger->description ?? '-' }}
                                        </td>
                                        <td style="padding: 10px 12px; text-align: right; font-family: monospace; font-weight: bold; color: #dc2626; font-size: 13px;">
                                            @if($ledger->debit > 0)
                                                -{{ number_format($ledger->debit, 2) }}
                                            @else
                                                0.00
                                            @endif
                                        </td>
                                        <td style="padding: 10px 12px; text-align: right; font-family: monospace; font-weight: bold; color: #059669; font-size: 13px;">
                                            @if($ledger->credit > 0)
                                                {{ number_format($ledger->credit, 2) }}
                                            @else
                                                0.00
                                            @endif
                                        </td>
                                        <td style="padding: 10px 12px; text-align: right; font-family: monospace; font-weight: bold; font-size: 13px; color: {{ $runningBalance >= 0 ? '#059669' : '#dc2626' }};">
                                            {{ $runningBalance >= 0 ? '' : '-' }}{{ number_format(abs($runningBalance), 2) }}
                                        </td>
                                        <td style="padding: 10px 12px; text-align: center;">
                                            @if($ledger->status === 'Confirmed')
                                                <span style="display: inline-flex; align-items: center; gap: 4px; color: #059669; font-weight: 600; font-size: 11px;">
                                                    <span style="width: 6px; height: 6px; background: #059669; border-radius: 50%;"></span>
                                                    Confirmed
                                                </span>
                                            @elseif($ledger->status === 'Cancelled')
                                                <span style="display: inline-flex; align-items: center; gap: 4px; color: #dc2626; font-weight: 600; font-size: 11px;">
                                                    <span style="width: 6px; height: 6px; background: #dc2626; border-radius: 50%;"></span>
                                                    Cancelled
                                                </span>
                                            @else
                                                <span style="display: inline-flex; align-items: center; gap: 4px; color: #d97706; font-weight: 600; font-size: 11px;">
                                                    <span style="width: 6px; height: 6px; background: #d97706; border-radius: 50%;"></span>
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Card Footer with Print Actions --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                        <div style="font-size: 12px; color: #64748b;">
                            Account ID: <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 11px;">{{ $accountUid }}</code>
                        </div>
                        
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('transactions.print_from', ['reference_no' => $referenceNo, 'account_id' => $accountUid, 'currency_id' => $currencyId]) }}" 
                               target="_blank" 
                               style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: white; color: #4b5563; border: 1px solid #d1d5db; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 500; text-decoration: none; transition: all 0.2s;"
                               onmouseover="this.style.background='#f9fafb'" 
                               onmouseout="this.style.background='white'">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Print 
                            </a>
                        </div>
                    </div>
                </div>
            </details>
        @endforeach

    @empty
        <div style="padding: 40px; text-align: center; color: #6b7280; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb; font-style: italic;">
            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 12px; color: #cbd5e1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            No account ledgers found for this transaction batch.
        </div>
    @endforelse

</div>

<style>
    details > summary::-webkit-details-marker {
        display: none;
    }
    details[open] summary svg {
        transform: rotate(180deg);
    }
    details summary:hover {
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0) !important;
    }
</style>