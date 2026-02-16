<x-admin::layouts>
    <x-slot:title>
        {{ trans('tochka-payment::app.admin.payment-history.show.title', ['id' => $payment->id]) }}
    </x-slot:title>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.tochka-payment.payment-history.index') }}"
                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-sort-left rtl:icon-sort-right"
                title="@lang('tochka-payment::app.admin.payment-history.index.view')"
            ></a>
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                {{ trans('tochka-payment::app.admin.payment-history.show.title', ['id' => $payment->id]) }}
            </p>
        </div>
        @if ($payment->payment_url)
            <a
                href="{{ $payment->payment_url }}"
                target="_blank"
                rel="noopener"
                class="primary-button inline-flex"
            >
                @lang('tochka-payment::app.admin.payment-history.show.go_to_payment')
            </a>
        @endif
    </div>

    <div class="mt-4 flex flex-col gap-4">
        <!-- Payment info -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('tochka-payment::app.admin.payment-history.show.payment-info')
            </p>
            <dl class="grid gap-3 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">@lang('tochka-payment::app.admin.payment-history.show.payment-id')</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->id }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">@lang('tochka-payment::app.admin.payment-history.show.order-id')</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->order_id ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">@lang('tochka-payment::app.admin.payment-history.show.external-order-id')</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->external_order_id ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">@lang('tochka-payment::app.admin.payment-history.show.amount')</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ number_format((float) $payment->amount, 2) }} ₽</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">@lang('tochka-payment::app.admin.payment-history.show.status')</dt>
                    <dd class="mt-1">
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
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">@lang('tochka-payment::app.admin.payment-history.show.created-at')</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->created_at?->format('Y-m-d H:i:s') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">@lang('tochka-payment::app.admin.payment-history.show.updated-at')</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->updated_at?->format('Y-m-d H:i:s') ?? '—' }}</dd>
                </div>
                @if ($payment->payment_url)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">@lang('tochka-payment::app.admin.payment-history.show.payment-url')</dt>
                        <dd class="mt-1 text-sm">
                            <a href="{{ $payment->payment_url }}" target="_blank" rel="noopener" class="text-blue-600 underline hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                {{ Str::limit($payment->payment_url, 60) }}
                            </a>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- Client info -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                @lang('tochka-payment::app.admin.payment-history.show.client-info')
            </p>
            <dl class="grid gap-3 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">@lang('tochka-payment::app.admin.payment-history.show.client-name')</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->client_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">@lang('tochka-payment::app.admin.payment-history.show.client-email')</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->client_email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">@lang('tochka-payment::app.admin.payment-history.show.client-phone')</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->client_phone ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        @if ($payment->request_data || $payment->response_data || $payment->webhook_data)
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('tochka-payment::app.admin.payment-history.show.webhook-info')
                </p>
                <div class="space-y-4">
                    @if ($payment->request_data)
                        <div>
                            <dt class="mb-1 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">@lang('tochka-payment::app.admin.payment-history.show.request-data')</dt>
                            <pre class="max-h-48 overflow-auto rounded border border-gray-200 bg-gray-50 p-3 text-xs dark:border-gray-700 dark:bg-gray-800">{{ json_encode($payment->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                    @if ($payment->response_data)
                        <div>
                            <dt class="mb-1 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">@lang('tochka-payment::app.admin.payment-history.show.callback-data')</dt>
                            <pre class="max-h-48 overflow-auto rounded border border-gray-200 bg-gray-50 p-3 text-xs dark:border-gray-700 dark:bg-gray-800">{{ json_encode($payment->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                    @if ($payment->webhook_data)
                        <div>
                            <dt class="mb-1 text-xs font-medium uppercase text-gray-500 dark:text-gray-400">@lang('tochka-payment::app.admin.payment-history.show.webhook-response')</dt>
                            <pre class="max-h-48 overflow-auto rounded border border-gray-200 bg-gray-50 p-3 text-xs dark:border-gray-700 dark:bg-gray-800">{{ json_encode($payment->webhook_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-admin::layouts>
