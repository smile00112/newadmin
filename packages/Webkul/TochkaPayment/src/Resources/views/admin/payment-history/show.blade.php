<x-admin::layouts>
    <x-slot:title>
        @lang('tochka-payment::app.admin.payment-history.show.title', ['id' => $payment->id])
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-2.5">
            <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                @lang('tochka-payment::app.admin.payment-history.show.title', ['id' => $payment->id])
            </p>
            <span class="label-{{ $payment->status }} text-sm">
                {{ $payment->status_label }}
            </span>
        </div>

        <a
            href="{{ route('admin.tochka-payment.history.index') }}"
            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
        >
            @lang('admin::app.account.edit.back-btn')
        </a>
    </div>

    <div class="mt-5 grid gap-5">
        <!-- Payment Information -->
        <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                @lang('tochka-payment::app.admin.payment-history.show.payment-info')
            </h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        @lang('tochka-payment::app.admin.payment-history.show.payment-id')
                    </label>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">#{{ $payment->id }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        @lang('tochka-payment::app.admin.payment-history.show.order-id')
                    </label>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $payment->order_id }}</p>
                </div>

                @if ($payment->external_order_id)
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            @lang('tochka-payment::app.admin.payment-history.show.external-order-id')
                        </label>
                        <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $payment->external_order_id }}</p>
                    </div>
                @endif

                @if (!empty($payment->request_data['product_name']))
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            @lang('tochka-payment::app.admin.payment-history.show.product-name')
                        </label>
                        <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $payment->request_data['product_name'] }}</p>
                    </div>
                @endif

                @if ($payment->transaction_id)
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            @lang('tochka-payment::app.admin.payment-history.show.transaction-id')
                        </label>
                        <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $payment->transaction_id }}</p>
                    </div>
                @endif

                <div>
                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        @lang('tochka-payment::app.admin.payment-history.show.amount')
                    </label>
                    <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">
                        {{ core()->formatPrice($payment->amount) }}
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        @lang('tochka-payment::app.admin.payment-history.show.status')
                    </label>
                    <p class="mt-1">
                        <span class="label-{{ $payment->status }} text-sm">
                            {{ $payment->status_label }}
                        </span>
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        @lang('tochka-payment::app.admin.payment-history.show.created-at')
                    </label>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">
                        {{ $payment->created_at->format('d.m.Y H:i:s') }}
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        @lang('tochka-payment::app.admin.payment-history.show.updated-at')
                    </label>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">
                        {{ $payment->updated_at->format('d.m.Y H:i:s') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Client Information -->
        <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                @lang('tochka-payment::app.admin.payment-history.show.client-info')
            </h3>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        @lang('tochka-payment::app.admin.payment-history.show.client-name')
                    </label>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $payment->client_name }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        @lang('tochka-payment::app.admin.payment-history.show.client-email')
                    </label>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $payment->client_email }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        @lang('tochka-payment::app.admin.payment-history.show.client-phone')
                    </label>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $payment->client_phone }}</p>
                </div>
            </div>
        </div>

        <!-- Payment URL -->
        @if ($payment->payment_url)
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                    @lang('tochka-payment::app.admin.payment-history.show.payment-url')
                </h3>
                <a
                    href="{{ $payment->payment_url }}"
                    target="_blank"
                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                >
                    {{ $payment->payment_url }}
                </a>
            </div>
        @endif

        <!-- Webhook Information -->
        @if ($payment->isPaid())
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                    @lang('tochka-payment::app.admin.payment-history.show.webhook-info')
                </h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            @lang('tochka-payment::app.admin.payment-history.show.webhook-sent')
                        </label>
                        <p class="mt-1 text-sm text-gray-800 dark:text-white">
                            {{ $payment->webhook_sent ? __('admin::app.common.yes') : __('admin::app.common.no') }}
                        </p>
                    </div>

                    @if ($payment->webhook_attempts > 0)
                        <div>
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                @lang('tochka-payment::app.admin.payment-history.show.webhook-attempts')
                            </label>
                            <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $payment->webhook_attempts }}</p>
                        </div>
                    @endif

                    @if ($payment->webhook_response)
                        <div class="col-span-2">
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                @lang('tochka-payment::app.admin.payment-history.show.webhook-response')
                            </label>
                            <pre class="mt-1 max-h-40 overflow-auto rounded bg-gray-100 p-2 text-xs text-gray-800 dark:bg-gray-800 dark:text-gray-300">{{ $payment->webhook_response }}</pre>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Request Data -->
        @if ($payment->request_data)
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                    @lang('tochka-payment::app.admin.payment-history.show.request-data')
                </h3>
                <pre class="max-h-60 overflow-auto rounded bg-gray-100 p-2 text-xs text-gray-800 dark:bg-gray-800 dark:text-gray-300">{{ json_encode($payment->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif

        <!-- Callback Data -->
        @if ($payment->callback_data)
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                    @lang('tochka-payment::app.admin.payment-history.show.callback-data')
                </h3>
                <pre class="max-h-60 overflow-auto rounded bg-gray-100 p-2 text-xs text-gray-800 dark:bg-gray-800 dark:text-gray-300">{{ json_encode($payment->callback_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif
    </div>
</x-admin::layouts>
