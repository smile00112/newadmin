<x-admin::layouts>
    <x-slot:title>
        @lang('external-payments::app.admin.systems.edit.title')
    </x-slot:title>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('external-payments::app.admin.systems.edit.title'): {{ $system->name }}
        </p>
        <a href="{{ route('admin.external-payments.systems.index') }}" class="secondary-button">
            @lang('external-payments::app.admin.systems.create.cancel')
        </a>
    </div>

    <form action="{{ route('admin.external-payments.systems.update', $system->id) }}" method="post" class="mt-6">
        @csrf
        @method('PUT')
        <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-900">
            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('external-payments::app.admin.systems.create.name')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control
                    type="text"
                    name="name"
                    :value="old('name', $system->name)"
                    rules="required"
                    :label="trans('external-payments::app.admin.systems.create.name')"
                />
                <x-admin::form.control-group.error control-name="name" />
            </x-admin::form.control-group>

            @if($isSuperAdmin && $companies->isNotEmpty())
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label>
                        @lang('external-payments::app.admin.systems.create.company')
                    </x-admin::form.control-group.label>
                    <x-admin::form.control-group.control
                        type="select"
                        name="company_id"
                        :value="old('company_id', $system->company_id)"
                        :label="trans('external-payments::app.admin.systems.create.company')"
                    >
                        <option value="">@lang('external-payments::app.admin.systems.create.select-company')</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id', $system->company_id) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </x-admin::form.control-group.control>
                    <x-admin::form.control-group.error control-name="company_id" />
                </x-admin::form.control-group>
            @endif

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('external-payments::app.admin.systems.create.api_token')
                </x-admin::form.control-group.label>
                <input
                    type="text"
                    name="api_token"
                    value="{{ old('api_token', $system->api_token) }}"
                    class="flex min-h-[39px] w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-800 shadow-sm transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    placeholder="Leave empty to keep current"
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('external-payments::app.admin.systems.edit.api_token_help')</p>
                <div class="mt-2">
                    <form action="{{ route('admin.external-payments.systems.generate-token', $system->id) }}" method="post" class="inline">
                        @csrf
                        <button type="submit" class="secondary-button text-sm">
                            @lang('external-payments::app.admin.systems.edit.generate_token')
                        </button>
                    </form>
                </div>
                <x-admin::form.control-group.error control-name="api_token" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('external-payments::app.admin.systems.create.webhook_url')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control
                    type="text"
                    name="webhook_url"
                    :value="old('webhook_url', $system->webhook_url ?? '')"
                    :label="trans('external-payments::app.admin.systems.create.webhook_url')"
                />
                <x-admin::form.control-group.error control-name="webhook_url" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('external-payments::app.admin.systems.create.woocommerce_site_url')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control
                    type="text"
                    name="woocommerce_site_url"
                    :value="old('woocommerce_site_url', $system->woocommerce_site_url ?? '')"
                    :label="trans('external-payments::app.admin.systems.create.woocommerce_site_url')"
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('external-payments::app.admin.systems.create.woocommerce_site_url_help')</p>
                <x-admin::form.control-group.error control-name="woocommerce_site_url" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('external-payments::app.admin.systems.create.woocommerce_consumer_key')
                </x-admin::form.control-group.label>
                <x-admin::form.control-group.control
                    type="text"
                    name="woocommerce_consumer_key"
                    :value="old('woocommerce_consumer_key', $system->woocommerce_consumer_key ?? '')"
                    :label="trans('external-payments::app.admin.systems.create.woocommerce_consumer_key')"
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('external-payments::app.admin.systems.create.woocommerce_consumer_key_help')</p>
                <x-admin::form.control-group.error control-name="woocommerce_consumer_key" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('external-payments::app.admin.systems.create.woocommerce_consumer_secret')
                </x-admin::form.control-group.label>
                <input
                    type="text"
                    name="woocommerce_consumer_secret"
                    value=""
                    class="flex min-h-[39px] w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-800 shadow-sm transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    placeholder="@lang('external-payments::app.admin.systems.edit.woocommerce_consumer_secret_placeholder')"
                />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('external-payments::app.admin.systems.create.woocommerce_consumer_secret_help')</p>
                <x-admin::form.control-group.error control-name="woocommerce_consumer_secret" />
            </x-admin::form.control-group>

            <x-admin::form.control-group>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0" />
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $system->is_active) ? 'checked' : '' }} class="rounded border-gray-300" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">@lang('external-payments::app.admin.systems.create.is_active')</span>
                </label>
            </x-admin::form.control-group>

            @php
                $allowedKeys = $system->paymentProviders->pluck('payment_provider')->all();
                $defaultKey = $system->default_provider ?: ($allowedKeys[0] ?? null);
            @endphp
            <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">@lang('external-payments::app.admin.systems.create.payment_providers')</p>
                <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">@lang('external-payments::app.admin.systems.create.payment_providers_help')</p>
                <div class="space-y-2">
                    @foreach ($providers as $key)
                        @php
                            $label = $providerConfig[$key]['name'] ?? $key;
                        @endphp
                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                name="payment_providers[]"
                                value="{{ $key }}"
                                {{ in_array($key, old('payment_providers', $allowedKeys)) ? 'checked' : '' }}
                                class="rounded border-gray-300"
                            />
                            <span class="text-sm text-gray-800 dark:text-gray-200">{{ $label }}</span>
                            <input
                                type="radio"
                                name="default_provider"
                                value="{{ $key }}"
                                {{ old('default_provider', $defaultKey) === $key ? 'checked' : '' }}
                                class="rounded-full border-gray-300"
                            />
                            <span class="text-xs text-gray-500">@lang('external-payments::app.admin.systems.create.default')</span>
                        </label>
                    @endforeach
                </div>
                @error('payment_providers')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                @error('default_provider')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex gap-2">
                <x-admin::button type="submit" class="primary-button" :title="trans('external-payments::app.admin.systems.edit.save')" />
                <a href="{{ route('admin.external-payments.systems.index') }}" class="secondary-button">@lang('external-payments::app.admin.systems.create.cancel')</a>
            </div>
        </div>
    </form>
</x-admin::layouts>
