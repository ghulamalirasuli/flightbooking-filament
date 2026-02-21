<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-primary-50">
            <tr>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-primary-900">Currency</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-primary-900">Rate</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-success-900">Income</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-danger-900">Expense</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-primary-900">Net Balance</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-primary-900">Net in {{ $defaultCurrency?->currency_name ?? 'Default' }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($balances as $row)
                <tr>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                        {{ $row['currency_name'] }} ({{ $row['currency_code'] }})
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $row['rate'] }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-success-600">{{ number_format($row['income']) }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-danger-600">{{ number_format($row['expense']) }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-right font-medium {{ $row['net'] >= 0 ? 'text-primary-600' : 'text-warning-600' }}">
                        {{ number_format($row['net']) }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-primary-600">
                        {{ number_format($row['net_in_default'], 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-3 py-4 text-sm text-center text-gray-500">No balance data available.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-gray-50 font-semibold">
            <tr>
                <td colspan="3" class="px-3 py-4 text-sm text-gray-900">Total NET Balance:</td>
                <td colspan="3" class="px-3 py-4 text-sm text-right text-primary-700">
                    {{ number_format($totalInDefault, 2) }} {{ $defaultCurrency?->currency_code ?? '' }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>