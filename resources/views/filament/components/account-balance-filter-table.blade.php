<div style="overflow-x:auto; border:1px solid #e5e7eb; border-radius:0.5rem; background:#fff; margin-top:10px; margin-bottom:20px;">

<table style="
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
">

    {{-- FORCE COLUMN ALIGNMENT --}}
    <colgroup>
        <col style="width:50px;">
        <col style="width:260px;">
        <col style="width:320px;">
        <col style="width:140px;">
        <col style="width:160px;">
    </colgroup>

    <thead style="background:#f8fafc; border-bottom:1px solid #e5e7eb;">
        <tr>
            <th style="padding:6px 10px; font-size:11px; color:#6b7280; text-align:left;">#</th>
            <th style="padding:6px 10px; font-size:11px; color:#6b7280; text-align:left;">Account</th>
            <th style="padding:6px 10px; font-size:11px; color:#6b7280; text-align:left;">Reference</th>
            <th style="padding:6px 10px; font-size:11px; color:#6b7280; text-align:center;">Currency</th>
            <th style="padding:6px 10px; font-size:11px; color:#6b7280; text-align:right;">Amount</th>
            <th style="padding:6px 10px; font-size:11px; color:#6b7280; text-align:right;">Type</th>
        </tr>
    </thead>

    <tbody>
    @php $row = ($unifiedBalances->currentPage() - 1) * $unifiedBalances->perPage(); @endphp

    @foreach($unifiedBalances as $item)
        @foreach($currencies as $currency)
            @php $amount = $item[$currency->currency_name] ?? 0; @endphp
            @continue($amount == 0)

            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:6px 10px; font-size:12px; color:#9ca3af;">
                    {{ ++$row }}
                </td>

                <td style="
                    padding:6px 10px;
                    font-size:12px;
                    font-weight:600;
                    color:#111827;
                    white-space:nowrap;
                    overflow:hidden;
                    text-overflow:ellipsis;
                ">
                    {{ $item['account_name'] }}
                </td>

                <td style="
                    padding:6px 10px;
                    font-size:12px;
                    font-family:monospace;
                    color:#374151;
                    white-space:nowrap;
                    overflow:hidden;
                    text-overflow:ellipsis;
                ">
                    {{ $item['reference_no'] }}
                </td>

                <td style="padding:6px 10px; text-align:center;">
                    <span style="
                        display:inline-block;
                        padding:2px 8px;
                        font-size:11px;
                        border-radius:999px;
                        background:#eef2ff;
                        color:#3730a3;
                    ">
                        {{ $currency->currency_name }}
                    </span>
                </td>

                <td style="
                    padding:6px 10px;
                    text-align:right;
                    font-size:12px;
                    font-family:monospace;
                    font-weight:600;
                    color: {{ $amount >= 0 ? '#15803d' : '#b91c1c' }};
                ">
                    {{ number_format($amount, 2) }}
                </td>
                <td style="
                    padding:6px 10px;
                    text-align:right;
                    font-size:11px;
                    font-weight:600;
                    ">
                    {{ $item['pay_status'] }}
                </td>
            </tr>
        @endforeach
    @endforeach
    </tbody>

</table>

{{-- Pagination Controls --}}
@if($unifiedBalances->hasPages())
<div style="padding:15px; border-top:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">

    <div style="display:flex; align-items:center; gap:8px;">
        <span style="font-size:13px; color:#6b7280;">Show</span>
        <select wire:model.live="perPage" style="padding:4px 8px; border:1px solid #d1d5db; border-radius:4px; font-size:13px;">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span style="font-size:13px; color:#6b7280;">entries</span>
    </div>

    <div style="font-size:13px; color:#6b7280;">
        Showing {{ $unifiedBalances->firstItem() }} to {{ $unifiedBalances->lastItem() }} of {{ $unifiedBalances->total() }} entries
    </div>

    <div style="display:flex; gap:4px;">
        <button wire:click="previousPage"
            @disabled($unifiedBalances->onFirstPage())
            style="padding:6px 12px; border:1px solid #d1d5db; border-radius:4px; font-size:13px;">
            Previous
        </button>

        @foreach(range(1, $unifiedBalances->lastPage()) as $pageNum)
            <button wire:click="gotoPage({{ $pageNum }})"
                style="padding:6px 12px; border-radius:4px; font-size:13px;
                background:{{ $pageNum == $unifiedBalances->currentPage() ? '#3b82f6' : '#fff' }};
                color:{{ $pageNum == $unifiedBalances->currentPage() ? '#fff' : '#374151' }};
                border:1px solid {{ $pageNum == $unifiedBalances->currentPage() ? '#3b82f6' : '#d1d5db' }};">
                {{ $pageNum }}
            </button>
        @endforeach

        <button wire:click="nextPage"
            @disabled(!$unifiedBalances->hasMorePages())
            style="padding:6px 12px; border:1px solid #d1d5db; border-radius:4px; font-size:13px;">
            Next
        </button>
    </div>
</div>
@endif

</div>
