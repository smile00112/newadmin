<x-admin::layouts>
    <x-slot:title>
        @lang('tochka-payment::app.admin.test-order.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('tochka-payment::app.admin.test-order.index.title')
        </p>
    </div>

    @if (session('success'))
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-5 grid gap-5">
        <!-- Form -->
        <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-900">
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                @lang('tochka-payment::app.admin.test-order.index.description')
            </p>

            <form
                method="POST"
                action="{{ route('admin.tochka-payment.test-order.store') }}"
            >
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-600 dark:text-gray-400" for="amount">
                            @lang('tochka-payment::app.admin.test-order.index.amount')
                        </label>
                        <input
                            type="number"
                            name="amount"
                            id="amount"
                            step="0.01"
                            min="{{ config('tochka-payment.min_amount', 1) }}"
                            value="{{ old('amount', '100') }}"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="{{ trans('tochka-payment::app.admin.test-order.index.amount_placeholder') }}"
                        />
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-600 dark:text-gray-400" for="product_name">
                            @lang('tochka-payment::app.admin.test-order.index.product_name')
                        </label>
                        <input
                            type="text"
                            name="product_name"
                            id="product_name"
                            value="{{ old('product_name','тариф 1') }}"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="{{ trans('tochka-payment::app.admin.test-order.index.product_name_placeholder') }}"

                        />
                        @error('product_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-600 dark:text-gray-400" for="external_order_id">
                            @lang('tochka-payment::app.admin.test-order.index.external_order_id')
                        </label>
                        <input
                            type="text"
                            name="external_order_id"
                            id="external_order_id"
                            value="{{ old('external_order_id','10001') }}"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="{{ trans('tochka-payment::app.admin.test-order.index.external_order_id_placeholder') }}"
                        />
                        @error('external_order_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-600 dark:text-gray-400" for="client_name">
                            @lang('tochka-payment::app.admin.test-order.index.client_name')
                        </label>
                        <input
                            type="text"
                            name="client_name"
                            id="client_name"
                            value="{{ old('client_name','Алексей П.') }}"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="{{ trans('tochka-payment::app.admin.test-order.index.client_name_placeholder') }}"
                            required
                        />
                        @error('client_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-600 dark:text-gray-400" for="client_email">
                            @lang('tochka-payment::app.admin.test-order.index.client_email')
                        </label>
                        <input
                            type="email"
                            name="client_email"
                            id="client_email"
                            value="{{ old('client_email','alex@yandex.ru') }}"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="{{ trans('tochka-payment::app.admin.test-order.index.client_email_placeholder') }}"
                            required
                        />
                        @error('client_email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-600 dark:text-gray-400" for="client_phone">
                            @lang('tochka-payment::app.admin.test-order.index.client_phone')
                        </label>
                        <input
                            type="text"
                            name="client_phone"
                            id="client_phone"
                            value="{{ old('client_phone', '+79026669966') }}"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="{{ trans('tochka-payment::app.admin.test-order.index.client_phone_placeholder') }}"
                            required
                        />
                        @error('client_phone')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <button
                        type="submit"
                        class="primary-button"
                    >
                        @lang('tochka-payment::app.admin.test-order.index.submit')
                    </button>
                </div>
            </form>
        </div>

        <!-- Result block (after successful creation) -->
        @if ($payment ?? null)
            <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                    @lang('tochka-payment::app.admin.test-order.result.title')
                </h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            @lang('tochka-payment::app.admin.test-order.result.amount')
                        </label>
                        <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white">
                            {{ core()->formatPrice($payment->amount) }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            @lang('tochka-payment::app.admin.test-order.result.product_name')
                        </label>
                        <p class="mt-1 text-sm text-gray-800 dark:text-white">
                            {{ $payment->request_data['product_name'] ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            @lang('tochka-payment::app.admin.test-order.result.order_id')
                        </label>
                        <p class="mt-1 text-sm text-gray-800 dark:text-white">{{ $payment->order_id }}</p>
                    </div>
                </div>

                <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                    <h4 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.test-order.result.client_info')
                    </h4>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">@lang('tochka-payment::app.admin.test-order.result.client_name'):</span>
                            <span class="text-sm text-gray-800 dark:text-white">{{ $payment->client_name }}</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">@lang('tochka-payment::app.admin.test-order.result.client_email'):</span>
                            <span class="text-sm text-gray-800 dark:text-white">{{ $payment->client_email }}</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">@lang('tochka-payment::app.admin.test-order.result.client_phone'):</span>
                            <span class="text-sm text-gray-800 dark:text-white">{{ $payment->client_phone }}</span>
                        </div>
                    </div>
                </div>

                @if ($payment->payment_url)
                    <div class="mt-4">
                        <a
                            href="{{ $payment->payment_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="primary-button inline-block"
                        >
                            @lang('tochka-payment::app.admin.test-order.result.go_to_payment')
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-admin::layouts>
