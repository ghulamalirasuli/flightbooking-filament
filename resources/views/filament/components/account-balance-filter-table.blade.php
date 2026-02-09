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
                    <td style="padding:10px 15px; text-align:left; font-size:13px; color:#9ca3af;">{{ $index + 1 }}</td>
                    <td style="padding:10px 15px; text-align:left; font-size:13px; font-weight:bold; color:#111827;">{{ $item['account_name'] }}</td>
                    <td style="padding:10px 15px; text-align:left; font-family:monospace; font-size:13px;">{{ $item['reference_no'] }}</td>
                    @foreach($currencies as $currency)
                        <td style="padding:10px 15px; text-align:right; font-family:monospace; font-size:13px;">
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
</div>
