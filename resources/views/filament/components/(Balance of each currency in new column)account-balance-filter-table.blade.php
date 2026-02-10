<div style="overflow-x:auto; border:1px solid #e5e7eb; border-radius:0.5rem; background:#fff; margin-top:10px; margin-bottom:20px;">
    <table style="width:100%; border-collapse:collapse; min-width:700px;">
        <thead style="background-color:#f9fafb; border-bottom:1px solid #e5e7eb;">
            <tr>
                <th style="padding:10px 15px; text-align:left; font-size:12px; font-weight:bold; color:#6b7280;">#</th>
                <th style="padding:10px 15px; text-align:left; font-size:12px; font-weight:bold; color:#6b7280;">Account</th>
                <th style="padding:10px 15px; text-align:left; font-size:12px; font-weight:bold; color:#6b7280;">Reference No.</th>
                @foreach($currencies as $currency)
                    <th style="padding:10px 15px; text-align:right; font-size:12px; font-weight:bold; color:#6b7280;">{{ $currency->currency_name }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($unifiedBalances as $index => $item)
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:10px 15px; text-align:left; font-size:13px; color:#9ca3af;">
                        {{ ($unifiedBalances->currentPage() - 1) * $unifiedBalances->perPage() + $index + 1 }}
                    </td>
                    <td style="padding:10px 15px; text-align:left; font-size:13px; font-weight:bold; color:#111827;">{{ $item['account_name'] }}</td>
                    <td style="padding:10px 15px; text-align:left; font-family:monospace; font-size:13px;">{{ $item['reference_no'] }}</td>
                    @foreach($currencies as $currency)
                        <td style="padding:10px 15px; text-align:right; font-family:monospace; font-size:13px; color: {{ $item[$currency->currency_name] >= 0 ? '#16a34a' : '#dc2626' }};">
                            {{ number_format($item[$currency->currency_name] ?? 0, 2) }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 3 + count($currencies) }}" style="text-align:center; padding:15px; color:#6b7280;">
                        No records found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination Controls --}}
    @if($unifiedBalances->hasPages())
        <div style="padding: 15px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            {{-- Per Page Selector --}}
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 13px; color: #6b7280;">Show</span>
                <select wire:model.live="perPage" style="padding: 4px 8px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span style="font-size: 13px; color: #6b7280;">entries</span>
            </div>

            {{-- Page Info --}}
            <div style="font-size: 13px; color: #6b7280;">
                Showing {{ $unifiedBalances->firstItem() }} to {{ $unifiedBalances->lastItem() }} of {{ $unifiedBalances->total() }} entries
            </div>

            {{-- Navigation Buttons --}}
            <div style="display: flex; gap: 4px;">
                {{-- Previous --}}
                <button 
                    type="button"
                    wire:click="previousPage" 
                    @disabled($unifiedBalances->onFirstPage())
                    style="padding: 6px 12px; border: 1px solid #d1d5db; border-radius: 4px; background: {{ $unifiedBalances->onFirstPage() ? '#f3f4f6' : '#fff' }}; color: {{ $unifiedBalances->onFirstPage() ? '#9ca3af' : '#374151' }}; cursor: {{ $unifiedBalances->onFirstPage() ? 'not-allowed' : 'pointer' }}; font-size: 13px;"
                >
                    Previous
                </button>

                {{-- Page Numbers --}}
                @foreach(range(1, $unifiedBalances->lastPage()) as $pageNum)
                    <button 
                        type="button"
                        wire:click="gotoPage({{ $pageNum }})"
                        @disabled($pageNum == $unifiedBalances->currentPage())
                        style="padding: 6px 12px; border: 1px solid {{ $pageNum == $unifiedBalances->currentPage() ? '#3b82f6' : '#d1d5db' }}; border-radius: 4px; background: {{ $pageNum == $unifiedBalances->currentPage() ? '#3b82f6' : '#fff' }}; color: {{ $pageNum == $unifiedBalances->currentPage() ? '#fff' : '#374151' }}; cursor: pointer; font-size: 13px; min-width: 36px;"
                    >
                        {{ $pageNum }}
                    </button>
                @endforeach

                {{-- Next --}}
                <button 
                    type="button"
                    wire:click="nextPage" 
                    @disabled(!$unifiedBalances->hasMorePages())
                    style="padding: 6px 12px; border: 1px solid #d1d5db; border-radius: 4px; background: {{ !$unifiedBalances->hasMorePages() ? '#f3f4f6' : '#fff' }}; color: {{ !$unifiedBalances->hasMorePages() ? '#9ca3af' : '#374151' }}; cursor: {{ !$unifiedBalances->hasMorePages() ? 'not-allowed' : 'pointer' }}; font-size: 13px;"
                >
                    Next
                </button>
            </div>
        </div>
    @endif
</div>