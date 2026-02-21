<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-success-50">
            <tr>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-success-900">Currency</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-success-900">Code</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-success-900">Total Income</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($totals as $uid => $total)
                <tr>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">{{ $total['currency_name'] }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $total['currency_code'] }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-right font-medium text-success-600">
                        {{ number_format($total['total']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-3 py-4 text-sm text-center text-gray-500">No income records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>