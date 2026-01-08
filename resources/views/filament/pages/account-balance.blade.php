<x-filament-panels::page>

    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    @if(filled($this->data['selectedAccount'] ?? null))
        <x-filament::section class="mt-6">
            <x-slot name="heading">
                <span class="text-gray-950 dark:text-white font-extrabold uppercase tracking-tight">Account Balance Summary</span>
            </x-slot>

          <div style="border: 1px solid #e5e7eb; border-radius: 0.75rem; overflow: hidden; background: white;">
    <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
        <thead style="background-color: #f9fafb; border-bottom: 1px solid #e5e7eb;">
            <tr>
                <th style="width: 50px; padding: 12px 24px; text-align: left; font-size: 12px; font-weight: bold; color: #6b7280; text-transform: uppercase;">#</th>
                <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: bold; color: #6b7280; text-transform: uppercase;">Currency</th>
                <th style="width: 150px; padding: 12px 24px; text-align: left; font-size: 12px; font-weight: bold; color: #6b7280; text-transform: uppercase;">View</th>
                <th style="width: 150px; padding: 12px 24px; text-align: left; font-size: 12px; font-weight: bold; color: #6b7280; text-transform: uppercase;">Confirmed</th>
                <th style="width: 150px; padding: 12px 24px; text-align: left; font-size: 12px; font-weight: bold; color: #6b7280; text-transform: uppercase;">Pending</th>
            </tr>
        </thead>
        <tbody style="background-color: white;">
            @foreach($this->unifiedBalances as $index => $item)
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 12px 24px; text-align: left; font-size: 14px; color: #9ca3af;">
                        {{ $index + 1 }}.
                    </td>
           
         <td class="px-6 py-4 text-left">
    @php
        // Get the account ID for the URL
        $accId = $this->data['selectedAccount'] ?? null;
        $accountuid =  App\Models\Accounts::where('id', $accId)->first();
        // dd($accountuid->uid);
    @endphp
        <span class="text-sm font-bold text-primary-600 group-hover:text-primary-500 underline decoration-dotted">
            {{ $item['name'] }}
        </span>
</td>
<td style="padding: 12px 24px; text-align: left; font-family: monospace; font-size: 14px; font-weight: bold;">
    <a target="_blank" href="{{ \App\Filament\Resources\AccountLedgers\AccountLedgerResource::getUrl('view', ['record' => $accId]) }}?tableFilters[currency][value]={{ $item['id'] }}">
          <x-filament::icon
            icon="heroicon-o-eye"
            class="h-4 w-4 text-gray-400 group-hover:text-primary-500 inline"
        />
    </a>

</td>
                    <td style="padding: 12px 24px; text-align: left; font-family: monospace; font-size: 14px; font-weight: bold; color: {{ $item['confirmed'] >= 0 ? '#16a34a' : '#dc2626' }};">
                       <a target="_blank" href="{{ route('account_ledger.print', ['ownerId' => $accountuid->uid]) }}?filters[currency][value]={{ $item['id'] }}&filters[status][value]=Confirmed">
                            {{ number_format($item['confirmed'], 2) }} {{ $item['code'] }}
                        </a>

                    </td>
                    <td style="padding: 12px 24px; text-align: left; font-family: monospace; font-size: 14px; font-weight: bold; color: #d97706;">
                        <a target="_blank" href="{{ route('account_ledger.print', ['ownerId' => $accountuid->uid]) }}?filters[currency][value]={{ $item['id'] }}&filters[status][value]=Pending">
                            {{ number_format($item['pending'], 2) }} {{ $item['code'] }}
                        </a>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
        </x-filament::section>
    @endif
</x-filament-panels::page>