<x-admin::layouts>
    <x-slot:title>
        @lang('tochka-payment::app.admin.test-order.index.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.tochka-payment.test-order.store')"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('tochka-payment::app.admin.test-order.index.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Submit Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('tochka-payment::app.admin.test-order.index.create-btn')
                </button>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="mt-4 rounded-lg bg-green-50 p-4 text-green-800 dark:bg-green-900/20 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mt-4 rounded-lg bg-red-50 p-4 text-red-800 dark:bg-red-900/20 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        <!-- Payment Result -->
        @if (session('payment'))
            @php
                $payment = session('payment');
            @endphp
            <div class="mt-4 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                <p class="mb-2 font-semibold text-blue-800 dark:text-blue-200">
                    @lang('tochka-payment::app.admin.test-order.index.payment-created')
                </p>
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    <strong>@lang('tochka-payment::app.admin.test-order.index.payment-url'):</strong>
                    <a href="{{ $payment->payment_url }}" target="_blank" class="underline">
                        {{ $payment->payment_url }}
                    </a>
                </p>
                @if ($payment->order_id)
                    <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                        <strong>@lang('tochka-payment::app.admin.test-order.index.order-id'):</strong> {{ $payment->order_id }}
                    </p>
                @endif
            </div>
        @endif

        <!-- Form Fields -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Section -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                <!-- General Information -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('tochka-payment::app.admin.test-order.index.general')
                    </p>

                    <!-- Company ID -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('tochka-payment::app.admin.test-order.index.company')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="company_id"
                            rules="required"
                            :value="old('company_id')"
                            label="@lang('tochka-payment::app.admin.test-order.index.company')"
                        >
                            <option value="">@lang('tochka-payment::app.admin.test-order.index.select-company')</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="company_id" />
                    </x-admin::form.control-group>

                    <!-- User Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('tochka-payment::app.admin.test-order.index.name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="name"
                            rules="required|string|max:255"
                            :value="old('name')"
                            label="@lang('tochka-payment::app.admin.test-order.index.name')"
                            placeholder="@lang('tochka-payment::app.admin.test-order.index.name-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    <!-- Email -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('tochka-payment::app.admin.test-order.index.email')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="email"
                            name="email"
                            rules="required|email|max:255"
                            :value="old('email')"
                            label="@lang('tochka-payment::app.admin.test-order.index.email')"
                            placeholder="@lang('tochka-payment::app.admin.test-order.index.email-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="email" />
                    </x-admin::form.control-group>

                    <!-- Phone -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('tochka-payment::app.admin.test-order.index.phone')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="phone"
                            rules="required|string|max:20"
                            :value="old('phone')"
                            label="@lang('tochka-payment::app.admin.test-order.index.phone')"
                            placeholder="@lang('tochka-payment::app.admin.test-order.index.phone-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="phone" />
                    </x-admin::form.control-group>

                    <!-- Purpose -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('tochka-payment::app.admin.test-order.index.purpose')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="purpose"
                            rules="required|string|max:255"
                            :value="old('purpose')"
                            label="@lang('tochka-payment::app.admin.test-order.index.purpose')"
                            placeholder="@lang('tochka-payment::app.admin.test-order.index.purpose-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="purpose" />
                    </x-admin::form.control-group>

                    <!-- Amount -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('tochka-payment::app.admin.test-order.index.amount')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="amount"
                            rules="required|numeric|min:1"
                            :value="old('amount')"
                            label="@lang('tochka-payment::app.admin.test-order.index.amount')"
                            placeholder="@lang('tochka-payment::app.admin.test-order.index.amount-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="amount" />
                    </x-admin::form.control-group>

                    <!-- External Order ID (Optional) -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.test-order.index.external-order-id')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="external_order_id"
                            rules="nullable|string|max:255"
                            :value="old('external_order_id')"
                            label="@lang('tochka-payment::app.admin.test-order.index.external-order-id')"
                            placeholder="@lang('tochka-payment::app.admin.test-order.index.external-order-id-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="external_order_id" />
                    </x-admin::form.control-group>
                </div>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
