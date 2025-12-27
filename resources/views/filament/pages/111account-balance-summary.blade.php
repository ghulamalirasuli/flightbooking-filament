<x-filament::page>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">Account Name</th>
                    @foreach(array_unique(array_merge(...array_values($this->getBalances()))) as $currency => $_)
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 dark:text-gray-300">{{ $currency }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($this->getBalances() as $account => $currencies)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $account }}</td>
                        @foreach(array_unique(array_merge(...array_values($this->getBalances()))) as $currency => $_)
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                                {{ $currencies[$currency] ?? 0 }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-filament::page>