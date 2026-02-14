<div style="border:1px solid #e5e7eb; border-radius:0.75rem; background:#fff; margin-top:16px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">

<table style="width:100%; border-collapse:collapse;">
    
    <thead style="background:linear-gradient(to bottom, #f8fafc, #f1f5f9); border-bottom:2px solid #e2e8f0;">
        <tr>
            <th style="padding:14px 16px; font-size:12px; font-weight:600; color:#475569; text-align:left; text-transform:uppercase; letter-spacing:0.5px; width:60px;">#</th>
            <th style="padding:14px 16px; font-size:12px; font-weight:600; color:#475569; text-align:left; text-transform:uppercase; letter-spacing:0.5px;">User</th>
            <th style="padding:14px 16px; font-size:12px; font-weight:600; color:#475569; text-align:left; text-transform:uppercase; letter-spacing:0.5px; width:25%;">Reference</th>
            <th style="padding:14px 16px; font-size:12px; font-weight:600; color:#475569; text-align:left; text-transform:uppercase; letter-spacing:0.5px;">Fullname</th>
            <th style="padding:14px 16px; font-size:12px; font-weight:600; color:#475569; text-align:left; text-transform:uppercase; letter-spacing:0.5px; width:20%;">Email</th>
            <th style="padding:14px 16px; font-size:12px; font-weight:600; color:#475569; text-align:left; text-transform:uppercase; letter-spacing:0.5px; width:140px;">Mobile No.</th>
        </tr>
    </thead>

    <tbody>
    @php $row = ($getContacts->currentPage() - 1) * $getContacts->perPage(); @endphp

    @foreach($getContacts as $item)
        <tr style="border-bottom:1px solid #f1f5f9; transition:background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
            
            <td style="padding:14px 16px; font-size:13px; color:#64748b; font-weight:500;">
                {{ ++$row }}
            </td>

         <td style="padding:14px 16px;">
    <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg, #3b82f6, #1d4ed8); display:flex; align-items:center; justify-content:center; color:#fff; font-size:12px; font-weight:600;">
            {{ strtoupper(substr($item['user'], 0, 1)) }}
        </div>
        <div style="display:flex; flex-direction:column;">
            <span style="font-size:14px; font-weight:600; color:#1e293b;">{{ $item['user'] }}</span> ({{ $item['branch'] }})
            <span style="font-size:11px; color:#64748b; margin-top:2px;">{{ $item['created_at'] }}</span>
        </div>
    </div>
</td>
            <td style="padding:14px 16px; font-size:13px; font-family:'SF Mono', Monaco, monospace; color:#475569; letter-spacing:0.3px;">
                {{ $item['reference_no'] }}
            </td>

            <td style="padding:14px 16px;">
                <span style="font-size:14px; font-weight:500; color:#334155;">
                    {{ $item['fullname'] }}
                </span>
            </td>

            <td style="padding:14px 16px; font-size:13px; color:#475569;">
                <a href="mailto:{{ $item['email'] }}" style="color:#2563eb; text-decoration:none; font-weight:500;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                    {{ $item['email'] }}
                </a>
            </td>

            <td style="padding:14px 16px;">
                <span style="display:inline-flex; align-items:center; gap:6px; padding:6px 12px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:20px; font-size:12px; font-weight:600; color:#15803d;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    {{ $item['mobile_number'] }}
                </span>
            </td>
        </tr>
    @endforeach
    </tbody>

</table>

{{-- Pagination Controls --}}
@if($getContacts->hasPages())
<div style="padding:20px 24px; border-top:1px solid #e2e8f0; background:#fafafa; border-radius:0 0 0.75rem 0.75rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">

    <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:14px; color:#64748b;">Show</span>
        <select wire:model.live="perPage" style="padding:8px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; background:#fff; cursor:pointer; outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span style="font-size:14px; color:#64748b;">entries</span>
    </div>

    <div style="font-size:14px; color:#64748b; font-weight:500;">
        Showing <span style="color:#334155; font-weight:600;">{{ $getContacts->firstItem() }}</span> to <span style="color:#334155; font-weight:600;">{{ $getContacts->lastItem() }}</span> of <span style="color:#334155; font-weight:600;">{{ $getContacts->total() }}</span> entries
    </div>

    <div style="display:flex; gap:6px; align-items:center;">
        <button wire:click="previousPage" @disabled($getContacts->onFirstPage()) style="padding:8px 16px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; background:#fff; color:#374151; cursor:pointer; transition:all 0.2s; font-weight:500;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'" {{ $getContacts->onFirstPage() ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }}>
            ← Previous
        </button>

        <div style="display:flex; gap:4px;">
            @php
                $currentPage = $getContacts->currentPage();
                $lastPage = $getContacts->lastPage();
                $range = 2;
                $start = max(1, $currentPage - $range);
                $end = min($lastPage, $currentPage + $range);
            @endphp

            @if($start > 1)
                <button wire:click="gotoPage(1)" style="padding:8px 14px; border-radius:6px; font-size:14px; background:#fff; color:#374151; border:1px solid #d1d5db; cursor:pointer; font-weight:500;">1</button>
                @if($start > 2)
                    <span style="padding:8px 12px; color:#9ca3af;">...</span>
                @endif
            @endif

            @foreach(range($start, $end) as $pageNum)
                <button wire:click="gotoPage({{ $pageNum }})" style="padding:8px 14px; border-radius:6px; font-size:14px; border:1px solid {{ $pageNum == $currentPage ? '#3b82f6' : '#d1d5db' }}; background:{{ $pageNum == $currentPage ? '#3b82f6' : '#fff' }}; color:{{ $pageNum == $currentPage ? '#fff' : '#374151' }}; cursor:pointer; font-weight:500; transition:all 0.2s;" onmouseover="{{ $pageNum != $currentPage ? 'this.style.background=\'#f3f4f6\'' : '' }}" onmouseout="{{ $pageNum != $currentPage ? 'this.style.background=\'#fff\'' : '' }}">
                    {{ $pageNum }}
                </button>
            @endforeach

            @if($end < $lastPage)
                @if($end < $lastPage - 1)
                    <span style="padding:8px 12px; color:#9ca3af;">...</span>
                @endif
                <button wire:click="gotoPage({{ $lastPage }})" style="padding:8px 14px; border-radius:6px; font-size:14px; background:#fff; color:#374151; border:1px solid #d1d5db; cursor:pointer; font-weight:500;">{{ $lastPage }}</button>
            @endif
        </div>

        <button wire:click="nextPage" @disabled(!$getContacts->hasMorePages()) style="padding:8px 16px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; background:#fff; color:#374151; cursor:pointer; transition:all 0.2s; font-weight:500;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'" {{ !$getContacts->hasMorePages() ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }}>
            Next →
        </button>
    </div>
</div>
@endif

</div>