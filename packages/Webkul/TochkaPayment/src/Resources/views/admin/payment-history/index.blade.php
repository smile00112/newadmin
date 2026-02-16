<x-admin::layouts>
    <x-slot:title>
        @lang('tochka-payment::app.admin.payment-history.index.title')
    </x-slot:title>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('tochka-payment::app.admin.payment-history.index.title')
        </p>
    </div>

    @if ($isSuperAdmin && $companies->isNotEmpty())
        <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <form method="get" action="{{ route('admin.tochka-payment.payment-history.index') }}" class="flex items-center gap-4">
                <label for="company_id" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    @lang('tochka-payment::app.admin.settings.index.company')
                </label>
                <select
                    name="company_id"
                    id="company_id"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    onchange="this.form.submit()"
                >
                    @foreach ($companies as $c)
                        <option value="{{ $c->id }}" {{ (int) $companyId === (int) $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.payment-history.index.id')
                    </th>
                    @if ($isSuperAdmin)
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            @lang('tochka-payment::app.admin.settings.index.company')
                        </th>
                    @endif
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.payment-history.index.order_id')
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.payment-history.index.amount')
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.payment-history.index.client')
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.payment-history.index.status')
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.payment-history.index.created_at')
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.payment-history.index.actions')
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                @forelse ($payments as $payment)
                    <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                            {{ $payment->id }}
                        </td>
                        @if ($isSuperAdmin)
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ $payment->company?->name ?? '—' }}
                            </td>
                        @endif
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                            {{ $payment->order_id ?? $payment->external_order_id ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                            {{ number_format((float) $payment->amount, 2) }} ₽
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                            {{ $payment->client_name ?? $payment->client_email ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            @php
                                $statusKey = 'tochka-payment::app.admin.payment-history.status.' . $payment->status;
                                $statusLabel = trans($statusKey) !== $statusKey ? trans($statusKey) : $payment->status;
                            @endphp
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5
                                @if ($payment->status === 'paid') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                @elseif ($payment->status === 'failed' || $payment->status === 'cancelled') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                @else bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                                @endif">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                            {{ $payment->created_at?->format('Y-m-d H:i') ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                            <a
                                href="{{ route('admin.tochka-payment.payment-history.show', $payment->id) }}"
                                class="text-blue-600 transition hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                title="@lang('tochka-payment::app.admin.payment-history.index.view')"
                            >
                                <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-sort-right rtl:icon-sort-left"></span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isSuperAdmin ? 8 : 7 }}" class="px-6 py-12 text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @lang('tochka-payment::app.admin.payment-history.index.empty')
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($payments->hasPages())
        <div class="mt-4">
            {{ $payments->withQueryString()->links() }}
        </div>
    @endif
</x-admin::layouts>
