<div style="overflow-x:auto; border:1px solid #e5e7eb; border-radius:0.5rem; background:#fff; margin-top:10px; margin-bottom:20px;">

<table style="
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
">

    {{-- FORCE COLUMN ALIGNMENT - Widened columns --}}
    <colgroup>
        <col style="width:50px;">
        <col style="width:220px;">
        <col style="width:280px;">
        <col style="width:120px;">
        <col style="width:140px;">
        <col style="width:180px;">
        <col style="width:220px;">
    </colgroup>

    <thead style="background:#f8fafc; border-bottom:1px solid #e5e7eb;">
        <tr>
            <th style="padding:6px 10px; font-size:11px; color:#6b7280; text-align:left;">#</th>
            <th style="padding:6px 10px; font-size:11px; color:#6b7280; text-align:left;">Account</th>
            <th style="padding:6px 10px; font-size:11px; color:#6b7280; text-align:left;">Reference</th>
            <th style="padding:6px 10px; font-size:11px; color:#6b7280; text-align:center;">Type</th>
            <th style="padding:6px 10px; font-size:11px; color:#6b7280; text-align:center;">Reminder</th>
            <th style="padding:6px 10px; font-size:11px; color:#6b7280; text-align:center;">Visibility</th>
            <th style="padding:6px 10px; font-size:11px; color:#6b7280; text-align:right;">Date</th>
        </tr>
    </thead>

    <tbody>
    @php $row = ($unifiedTasks->currentPage() - 1) * $unifiedTasks->perPage(); @endphp

    @foreach($unifiedTasks as $item)

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
                        {{ $item['type_label'] }}
                    </span>
                </td>

                <td style="
                    padding:6px 10px;
                    text-align:center;
                    font-size:12px;
                    font-weight:600;
                    color: {{ $item['reminder'] == 'yes' ? '#15803d' : '#b91c1c' }};
                ">
                     {{ $item['reminder_label'] }}
                </td>
                <td style="
                    padding:6px 10px;
                    text-align:center;
                    font-size:11px;
                    font-weight:600;
                    color:#374151;
                    ">
                    {{ $item['visibility_label'] }}
                </td>

 <td style="
    padding:6px 10px;
    text-align:right;
    font-size:12px;
    font-family:monospace;
    font-weight:600;
    {{ $item['reminder'] == 'yes' ? (\Carbon\Carbon::parse($item['date_comment'])->isPast() ? 'color:#b91c1c;' : 'color:#15803d;') : '' }}
">
    @if($item['date_comment'])
        @php
            $date = \Carbon\Carbon::parse($item['date_comment']);
        @endphp
        
        {{ $date->format('Y-m-d H:i') }}<br>
        
        @if($item['reminder'] == 'yes')
            @if($date->isPast())
                <span style="font-size:10px; color:#b91c1c;">({{ $date->diffForHumans() }} passed)</span>
            @else
                <span style="font-size:10px; color:#15803d;">({{ $date->diffForHumans() }} remaining)</span>
            @endif
        @endif
    @else
        -
    @endif
</td>

            </tr>
        @endforeach
    </tbody>

</table>

{{-- Pagination Controls --}}
@if($unifiedTasks->hasPages())
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
        Showing {{ $unifiedTasks->firstItem() }} to {{ $unifiedTasks->lastItem() }} of {{ $unifiedTasks->total() }} entries
    </div>

    <div style="display:flex; gap:4px;">
        <button wire:click="previousPage"
            @disabled($unifiedTasks->onFirstPage())
            style="padding:6px 12px; border:1px solid #d1d5db; border-radius:4px; font-size:13px;">
            Previous
        </button>

        @foreach(range(1, $unifiedTasks->lastPage()) as $pageNum)
            <button wire:click="gotoPage({{ $pageNum }})"
                style="padding:6px 12px; border-radius:4px; font-size:13px;
                background:{{ $pageNum == $unifiedTasks->currentPage() ? '#3b82f6' : '#fff' }};
                color:{{ $pageNum == $unifiedTasks->currentPage() ? '#fff' : '#374151' }};
                border:1px solid {{ $pageNum == $unifiedTasks->currentPage() ? '#3b82f6' : '#d1d5db' }};">
                {{ $pageNum }}
            </button>
        @endforeach

        <button wire:click="nextPage"
            @disabled(!$unifiedTasks->hasMorePages())
            style="padding:6px 12px; border:1px solid #d1d5db; border-radius:4px; font-size:13px;">
            Next
        </button>
    </div>
</div>
@endif

</div>