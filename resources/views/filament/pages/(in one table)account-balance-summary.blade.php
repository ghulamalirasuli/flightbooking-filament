<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    @if(filled($this->data['selectedAccount'] ?? null))
        <x-filament::section class="mt-6">
            <x-slot name="heading">
                <span class="text-gray-950 dark:text-white font-extrabold uppercase tracking-tight">Account Balance Summary</span>
            </x-slot>

            <div class="fi-ta-ctn border border-gray-200 dark:border-white/10 rounded-xl overflow-hidden shadow-sm">
                <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 dark:divide-white/5">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            {{-- All headers now forced to text-left --}}
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest w-16">#</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Currency</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Confirmed</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Pending</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5 bg-white dark:bg-gray-900">
                        @foreach($this->unifiedBalances as $index => $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                {{-- All data cells forced to text-left to align with headers --}}
                                <td class="px-6 py-4 text-left text-sm text-gray-400 font-medium">
                                    {{ $index + 1 }}.
                                </td>
                                <td class="px-6 py-4 text-left">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $item['name'] }}</span>
                                    <span class="text-[10px] text-gray-500 font-bold uppercase ml-1">{{ $item['code'] }}</span>
                                </td>
                                <td @class([
                                    'px-6 py-4 text-left font-mono text-sm font-bold',
                                    'text-success-600' => $item['confirmed'] > 0,
                                    'text-danger-600' => $item['confirmed'] < 0,
                                    'text-gray-400' => $item['confirmed'] == 0,
                                ])>
                                    {{ number_format($item['confirmed'], 2) }}
                                </td>
                                <td @class([
                                    'px-6 py-4 text-left font-mono text-sm font-bold',
                                    'text-warning-600' => $item['pending'] != 0,
                                    'text-gray-400' => $item['pending'] == 0,
                                ])>
                                    {{ number_format($item['pending'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>