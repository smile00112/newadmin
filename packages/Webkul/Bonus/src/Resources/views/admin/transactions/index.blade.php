<x-admin::layouts>
    <x-slot:title>
        @lang('bonus::app.admin.transactions.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('bonus::app.admin.transactions.title')
        </p>
    </div>

    <div class="mt-7">
        <div class="box-shadow rounded bg-white dark:bg-gray-900">
            <div class="overflow-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <th class="px-4 py-3 text-left">ID</th>
                            <th class="px-4 py-3 text-left">@lang('bonus::app.admin.transactions.customer')</th>
                            <th class="px-4 py-3 text-left">@lang('bonus::app.admin.transactions.order')</th>
                            <th class="px-4 py-3 text-left">@lang('bonus::app.admin.transactions.type')</th>
                            <th class="px-4 py-3 text-left">@lang('bonus::app.admin.transactions.amount')</th>
                            <th class="px-4 py-3 text-left">@lang('bonus::app.admin.transactions.date')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-4 py-3">{{ $transaction->id }}</td>
                                <td class="px-4 py-3">{{ $transaction->customer->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $transaction->order->increment_id ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $transaction->type }}</td>
                                <td class="px-4 py-3">{{ core()->formatPrice($transaction->amount, $transaction->currency_code) }}</td>
                                <td class="px-4 py-3">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-center">@lang('bonus::app.admin.transactions.no-transactions')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-admin::layouts>
