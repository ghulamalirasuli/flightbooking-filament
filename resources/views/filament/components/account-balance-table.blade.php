@props(['accountUid'])

@php
    $account = \App\Models\Accounts::where('uid', $accountUid)->first();
    $unifiedBalances = collect();

    if ($account) {
        $allCurrencies = \App\Models\Currency::all();
        $ledgerData = \App\Models\Account_ledger::where('account', $account->uid)
            ->select('currency', 'status', \Illuminate\Support\Facades\DB::raw('SUM(credit) - SUM(debit) as balance'))
            ->groupBy('currency', 'status')
            ->get();

        $unifiedBalances = $allCurrencies->map(function ($currency) use ($ledgerData) {
            return [
                'id' => $currency->id,
                'name' => $currency->currency_name,
                'code' => $currency->currency_code,
                'confirmed' => $ledgerData->where('currency', $currency->id)->where('status', 'Confirmed')->first()?->balance ?? 0,
                'pending' => $ledgerData->where('currency', $currency->id)->where('status', 'Pending')->first()?->balance ?? 0,
            ];
        });
    }
@endphp

@if($account && $unifiedBalances->isNotEmpty())
    <div style="border: 1px solid #e5e7eb; border-radius: 0.75rem; overflow: hidden; background: white; margin-top: 10px; margin-bottom: 20px;">
        <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            <thead style="background-color: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                <tr>
                    <th style="width: 50px; padding: 12px 24px; text-align: left; font-size: 12px; font-weight: bold; color: #6b7280; text-transform: uppercase;">#</th>
                    <th style="padding: 10px 20px; text-align: left; font-size: 11px; font-weight: bold; color: #6b7280; text-transform: uppercase;">Currency</th>
                    <th style="padding: 10px 20px; text-align: left; font-size: 11px; font-weight: bold; color: #6b7280; text-transform: uppercase;">View</th>
                    <th style="padding: 10px 20px; text-align: left; font-size: 11px; font-weight: bold; color: #6b7280; text-transform: uppercase;">Confirmed</th>
                    <th style="padding: 10px 20px; text-align: left; font-size: 11px; font-weight: bold; color: #6b7280; text-transform: uppercase;">Pending</th>
                </tr>
            </thead>
            <tbody style="background-color: white;">
                @foreach($unifiedBalances  as $index => $item)

                    <tr style="border-bottom: 1px solid #f3f4f6;">
                         <td style="padding: 12px 24px; text-align: left; font-size: 14px; color: #9ca3af;">
                        {{ $index + 1 }}.
                    </td>
                        <td style="padding: 10px 20px; text-align: left; font-size: 13px; font-weight: bold; color: #111827;">
                            {{ $item['name'] }}
                        </td>
                        <td style="padding: 12px 24px; text-align: left; font-family: monospace; font-size: 14px; font-weight: bold;">
                            <a target="_blank" href="{{ \App\Filament\Resources\AccountLedgers\AccountLedgerResource::getUrl('view', ['record' => $account->id]) }}?tableFilters[currency][value]={{ $item['id'] }}">
                                <x-filament::icon
                                    icon="heroicon-o-eye"
                                    class="h-4 w-4 text-gray-400 group-hover:text-primary-500 inline"
                                />
                            </a>

                        </td>
                        <td style="padding: 10px 20px; text-align: left; font-family: monospace; font-size: 13px; font-weight: bold; color: {{ $item['confirmed'] >= 0 ? '#16a34a' : '#dc2626' }};">
                          <a target="_blank" href="{{ route('account_ledger.print', ['ownerId' => $account->uid]) }}?filters[currency][value]={{ $item['id'] }}&filters[status][value]=Confirmed">
                            {{ number_format($item['confirmed'], 2) }} {{ $item['code'] }}
                          </a>
                        </td>
                        <td style="padding: 10px 20px; text-align: left; font-family: monospace; font-size: 13px; font-weight: bold; color: #d97706;">
                            
                        <a target="_blank" href="{{ route('account_ledger.print', ['ownerId' => $account->uid]) }}?filters[currency][value]={{ $item['id'] }}&filters[status][value]=Pending">
                      
                            {{ number_format($item['pending'], 2) }} {{ $item['code'] }}
                        </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif