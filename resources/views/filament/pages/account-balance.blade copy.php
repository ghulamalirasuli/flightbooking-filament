<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    @if($this->data['selectedAccount'] ?? false)
        {{-- Responsive Grid: 1 column on mobile, 2 columns (col-6) on desktop --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            
            {{-- Table 1: Confirmed --}}
            <x-filament::section>
                <x-slot name="heading">
                    <span class="text-success-600 font-extrabold uppercase tracking-tight">Confirmed Balances</span>
                </x-slot>

                <div class="fi-ta-ctn border border-gray-200 dark:border-white/10 rounded-xl overflow-hidden">
                    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 dark:divide-white/5 text-left">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-3 text-sm font-bold text-gray-950 dark:text-white uppercase">Currency</th>
                                <th class="px-4 py-3 text-sm font-bold text-gray-950 dark:text-white text-right uppercase">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                            @foreach($this->accountBalances->where('status', 'Confirmed') as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $item['currency_name'] }} ({{ $item['currency_code'] }})
                                    </td>
                                    <td @class([
                                        'px-4 py-3 text-sm font-bold text-right',
                                        'text-success-600' => $item['balance'] > 0,
                                        'text-danger-600' => $item['balance'] < 0,
                                        'text-gray-500' => $item['balance'] == 0,
                                    ])>
                                        {{ number_format($item['balance'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            {{-- Table 2: Pending --}}
            <x-filament::section>
                <x-slot name="heading">
                    <span class="text-warning-600 font-extrabold uppercase tracking-tight">Pending Balances</span>
                </x-slot>

                <div class="fi-ta-ctn border border-gray-200 dark:border-white/10 rounded-xl overflow-hidden">
                    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 dark:divide-white/5 text-left">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-3 text-sm font-bold text-gray-950 dark:text-white uppercase">Currency</th>
                                <th class="px-4 py-3 text-sm font-bold text-gray-950 dark:text-white text-right uppercase">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                            @foreach($this->accountBalances->where('status', 'Pending') as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $item['currency_name'] }} ({{ $item['currency_code'] }})
                                    </td>
                                    <td @class([
                                        'px-4 py-3 text-sm font-bold text-right',
                                        'text-warning-600' => $item['balance'] != 0,
                                        'text-gray-500' => $item['balance'] == 0,
                                    ])>
                                        {{ number_format($item['balance'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
    @endif
</x-filament-panels::page>