<x-admin::layouts>
    <x-slot:title>
        @lang('tochka-payment::app.admin.payment-history.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="py-3 text-xl font-bold text-gray-800 dark:text-white">
            @lang('tochka-payment::app.admin.payment-history.index.title')
        </p>
    </div>

    <div class="mt-5 rounded-lg bg-white p-4 shadow dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                            @lang('tochka-payment::app.admin.payment-history.index.id')
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                            @lang('tochka-payment::app.admin.payment-history.index.order_id')
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                            @lang('tochka-payment::app.admin.payment-history.index.amount')
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                            @lang('tochka-payment::app.admin.payment-history.index.client')
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                            @lang('tochka-payment::app.admin.payment-history.index.status')
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                            @lang('tochka-payment::app.admin.payment-history.index.created_at')
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">
                            @lang('tochka-payment::app.admin.payment-history.index.actions')
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                #{{ $payment->id }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $payment->order_id }}
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white">
                                {{ core()->formatPrice($payment->amount) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                <div>{{ $payment->client_name }}</div>
                                <div class="text-xs text-gray-500">{{ $payment->client_email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="label-{{ $payment->status }} text-xs">
                                    {{ $payment->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $payment->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <a
                                    href="{{ route('admin.tochka-payment.history.show', $payment->id) }}"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                >
                                    @lang('tochka-payment::app.admin.payment-history.index.view')
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">
                                @lang('tochka-payment::app.admin.payment-history.index.empty')
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    </div>
</x-admin::layouts>
