<x-admin::layouts>
    <x-slot:title>
        @lang('tochka-payment::app.admin.settings.index.title')
    </x-slot>

    <v-tochka-payment-settings>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('tochka-payment::app.admin.settings.index.title')
            </p>
        </div>

        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
            ref="form"
        >
            <form
                @submit="handleSubmit($event, updateSettings)"
                ref="settingsForm"
            >
                <div class="mt-7 rounded-lg bg-white p-4 shadow dark:bg-gray-900">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            @lang('tochka-payment::app.admin.settings.index.description')
                        </p>
                    </div>

                    <!-- Company -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.company')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="company_id"
                            rules=""
                            :value="old('company_id', $settings->company_id ?? auth()->guard('admin')->user()?->company_id ?? '')"
                            v-model="company_id"
                            :label="trans('tochka-payment::app.admin.settings.index.company')"
                        >
                            <option value="">@lang('tochka-payment::app.admin.settings.index.select-company')</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id', $settings->company_id ?? auth()->guard('admin')->user()?->company_id) == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="company_id" />
                    </x-admin::form.control-group>

                    <!-- Client ID -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.client_id')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="client_id"
                            rules=""
                            :value="old('client_id', $settings->client_id ?? '')"
                            v-model="client_id"
                            :label="trans('tochka-payment::app.admin.settings.index.client_id')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.client_id_placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="client_id" />
                    </x-admin::form.control-group>

                    <!-- JWT Token -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.jwt_token')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            name="jwt_token"
                            rules=""
                            :value="old('jwt_token', $settings->jwt_token ?? '')"
                            v-model="jwt_token"
                            :label="trans('tochka-payment::app.admin.settings.index.jwt_token')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.jwt_token_placeholder')"
                            rows="3"
                        />

                        <x-admin::form.control-group.error control-name="jwt_token" />
                    </x-admin::form.control-group>

                    <!-- API Base URL -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.api_base_url')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="api_base_url"
                            rules=""
                            :value="old('api_base_url', $settings->api_base_url ?? '')"
                            v-model="api_base_url"
                            :label="trans('tochka-payment::app.admin.settings.index.api_base_url')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.api_base_url_placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="api_base_url" />
                    </x-admin::form.control-group>

                    <!-- Webhook URL -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.webhook_url')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="webhook_url"
                            rules=""
                            :value="old('webhook_url', $settings->webhook_url ?? '')"
                            v-model="webhook_url"
                            :label="trans('tochka-payment::app.admin.settings.index.webhook_url')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.webhook_url_placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="webhook_url" />

                        <!-- Webhook Subscription Controls -->
                        <div class="mt-3 flex items-center gap-3">
                            <x-admin::button
                                v-if="!webhookSubscribed && !webhookCheckingStatus"
                                type="button"
                                class="primary-button"
                                :title="trans('tochka-payment::app.admin.settings.index.webhook_subscribe')"
                                @click="subscribeToWebhook"
                                ::loading="webhookLoading"
                                ::disabled="webhookLoading"
                            />

                            <x-admin::button
                                v-if="webhookSubscribed && !webhookCheckingStatus"
                                type="button"
                                class="secondary-button"
                                :title="trans('tochka-payment::app.admin.settings.index.webhook_unsubscribe')"
                                @click="unsubscribeFromWebhook"
                                ::loading="webhookLoading"
                                ::disabled="webhookLoading"
                            />

                            <span v-if="webhookCheckingStatus" class="text-sm text-gray-600 dark:text-gray-400">
                                @{{ trans('tochka-payment::app.admin.settings.index.webhook_checking_status') }}
                            </span>

                            <span v-if="!webhookCheckingStatus && webhookSubscribed" class="text-sm text-green-600 dark:text-green-400">
                                @{{ trans('tochka-payment::app.admin.settings.index.webhook_subscribed') }}
                            </span>

                            <span v-if="!webhookCheckingStatus && !webhookSubscribed && webhookStatusChecked" class="text-sm text-gray-600 dark:text-gray-400">
                                @{{ trans('tochka-payment::app.admin.settings.index.webhook_not_subscribed') }}
                            </span>
                        </div>
                    </x-admin::form.control-group>

                    <!-- Customer Code -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.customer_code')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="customer_code"
                            rules=""
                            :value="old('customer_code', $settings->customer_code ?? '')"
                            v-model="customer_code"
                            :label="trans('tochka-payment::app.admin.settings.index.customer_code')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.customer_code_placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="customer_code" />
                    </x-admin::form.control-group>

                    <!-- Merchant ID -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.merchant_id')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="merchant_id"
                            rules=""
                            :value="old('merchant_id', $settings->merchant_id ?? '')"
                            v-model="merchant_id"
                            :label="trans('tochka-payment::app.admin.settings.index.merchant_id')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.merchant_id_placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="merchant_id" />
                    </x-admin::form.control-group>

                    <!-- Payment Mode -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.payment_mode')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="payment_mode"
                            rules=""
                            :value="old('payment_mode', is_array($settings->payment_mode ?? null) ? implode(',', $settings->payment_mode) : ($settings->payment_mode ?? ''))"
                            v-model="payment_mode"
                            :label="trans('tochka-payment::app.admin.settings.index.payment_mode')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.payment_mode_placeholder')"
                        />

                        <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                            @lang('tochka-payment::app.admin.settings.index.payment_mode_help')
                        </p>

                        <x-admin::form.control-group.error control-name="payment_mode" />
                    </x-admin::form.control-group>

                    <!-- Save Card -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.save_card')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="switch"
                            name="save_card"
                            :value="old('save_card', (bool)($settings->save_card ?? false))"
                            v-model="save_card"
                            :label="trans('tochka-payment::app.admin.settings.index.save_card')"
                        />

                        <x-admin::form.control-group.error control-name="save_card" />
                    </x-admin::form.control-group>

                    <!-- Pre Authorization -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.pre_authorization')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="switch"
                            name="pre_authorization"
                            :value="old('pre_authorization', (bool)($settings->pre_authorization ?? false))"
                            v-model="pre_authorization"
                            :label="trans('tochka-payment::app.admin.settings.index.pre_authorization')"
                        />

                        <x-admin::form.control-group.error control-name="pre_authorization" />
                    </x-admin::form.control-group>

                    <!-- TTL -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.ttl')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="ttl"
                            rules=""
                            :value="old('ttl', $settings->ttl ?? 10080)"
                            v-model="ttl"
                            :label="trans('tochka-payment::app.admin.settings.index.ttl')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.ttl_placeholder')"
                        />

                        <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                            @lang('tochka-payment::app.admin.settings.index.ttl_help')
                        </p>

                        <x-admin::form.control-group.error control-name="ttl" />
                    </x-admin::form.control-group>

                    <!-- Min Amount -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.min_amount')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="min_amount"
                            rules=""
                            :value="old('min_amount', $settings->min_amount ?? 1.00)"
                            v-model="min_amount"
                            :label="trans('tochka-payment::app.admin.settings.index.min_amount')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.min_amount_placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="min_amount" />
                    </x-admin::form.control-group>

                    <!-- Is Active -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.is_active')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="switch"
                            name="is_active"
                            :value="old('is_active', (bool)($settings->is_active ?? false))"
                            v-model="is_active"
                            :label="trans('tochka-payment::app.admin.settings.index.is_active')"
                        />
                        <x-admin::form.control-group.error control-name="is_active" />
                    </x-admin::form.control-group>

                    <!-- Telegram Notifications Section -->
                    <div class="mt-8 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                            @lang('tochka-payment::app.admin.settings.index.telegram_notifications')
                        </h3>
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                            @lang('tochka-payment::app.admin.settings.index.telegram_notifications_description')
                        </p>

                        <!-- Telegram Bot Token -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('tochka-payment::app.admin.settings.index.telegram_bot_token')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="telegram_bot_token"
                                rules=""
                                :value="old('telegram_bot_token', $settings->telegram_bot_token ?? '')"
                                v-model="telegram_bot_token"
                                :label="trans('tochka-payment::app.admin.settings.index.telegram_bot_token')"
                                :placeholder="trans('tochka-payment::app.admin.settings.index.telegram_bot_token_placeholder')"
                            />

                            <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                                @lang('tochka-payment::app.admin.settings.index.telegram_bot_token_help')
                            </p>

                            <x-admin::form.control-group.error control-name="telegram_bot_token" />
                        </x-admin::form.control-group>

                        <!-- Telegram Chat ID -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('tochka-payment::app.admin.settings.index.telegram_chat_id')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="telegram_chat_id"
                                rules=""
                                :value="old('telegram_chat_id', $settings->telegram_chat_id ?? '')"
                                v-model="telegram_chat_id"
                                :label="trans('tochka-payment::app.admin.settings.index.telegram_chat_id')"
                                :placeholder="trans('tochka-payment::app.admin.settings.index.telegram_chat_id_placeholder')"
                            />

                            <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                                @lang('tochka-payment::app.admin.settings.index.telegram_chat_id_help')
                            </p>

                            <x-admin::form.control-group.error control-name="telegram_chat_id" />
                        </x-admin::form.control-group>
                    </div>

                    <!-- Save Button -->
                    <div class="mt-6 flex items-center justify-end gap-x-2.5">
                        <x-admin::button
                            button-type="button"
                            class="primary-button"
                            :title="trans('tochka-payment::app.admin.settings.index.save-btn')"
                            ::loading="isLoading"
                            ::disabled="isLoading"
                        />
                    </div>
                </div>
            </form>
        </x-admin::form>
    </v-tochka-payment-settings>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-tochka-payment-settings-template"
        >
            <div>
                <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        @lang('tochka-payment::app.admin.settings.index.title')
                    </p>
                </div>

                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                    ref="form"
                >
                    <form
                        @submit="handleSubmit($event, updateSettings)"
                        ref="settingsForm"
                    >
                        <div class="mt-7 rounded-lg bg-white p-4 shadow dark:bg-gray-900">
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    @lang('tochka-payment::app.admin.settings.index.description')
                                </p>
                            </div>

                            <!-- Company -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.company')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="company_id"
                                    rules=""
                                    v-model="company_id"
                                    :label="trans('tochka-payment::app.admin.settings.index.company')"
                                    ::disabled="loadingSettings"
                                >
                                    <option value="">@lang('tochka-payment::app.admin.settings.index.select-company')</option>
                                    <option
                                        v-for="company in companies"
                                        :key="company.id"
                                        :value="company.id"
                                    >@{{ company.name }}</option>
                                </x-admin::form.control-group.control>

                                <p v-if="loadingSettings" class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                                    @lang('tochka-payment::app.admin.settings.index.loading-settings')
                                </p>

                                <x-admin::form.control-group.error control-name="company_id" />
                            </x-admin::form.control-group>

                            <!-- Client ID -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.client_id')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="client_id"
                                    rules=""
                                    v-model="client_id"
                                    :label="trans('tochka-payment::app.admin.settings.index.client_id')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.client_id_placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="client_id" />
                            </x-admin::form.control-group>

                            <!-- JWT Token -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.jwt_token')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="jwt_token"
                                    rules=""
                                    v-model="jwt_token"
                                    :label="trans('tochka-payment::app.admin.settings.index.jwt_token')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.jwt_token_placeholder')"
                                    rows="3"
                                />

                                <x-admin::form.control-group.error control-name="jwt_token" />
                            </x-admin::form.control-group>

                            <!-- API Base URL -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.api_base_url')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="api_base_url"
                                    rules=""
                                    v-model="api_base_url"
                                    :label="trans('tochka-payment::app.admin.settings.index.api_base_url')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.api_base_url_placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="api_base_url" />
                            </x-admin::form.control-group>

                            <!-- Webhook URL -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.webhook_url')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="webhook_url"
                                    rules=""
                                    v-model="webhook_url"
                                    :label="trans('tochka-payment::app.admin.settings.index.webhook_url')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.webhook_url_placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="webhook_url" />

                                <!-- Webhook Subscription Controls -->
                                <div class="mt-3 flex items-center gap-3">
                                    <x-admin::button
                                        v-if="!webhookSubscribed && !webhookCheckingStatus"
                                        type="button"
                                        class="primary-button"
                                        :title="trans('tochka-payment::app.admin.settings.index.webhook_subscribe')"
                                        @click="subscribeToWebhook"
                                        ::loading="webhookLoading"
                                        ::disabled="webhookLoading"
                                    />

                                    <x-admin::button
                                        v-if="webhookSubscribed && !webhookCheckingStatus"
                                        type="button"
                                        class="secondary-button"
                                        :title="trans('tochka-payment::app.admin.settings.index.webhook_unsubscribe')"
                                        @click="unsubscribeFromWebhook"
                                        ::loading="webhookLoading"
                                        ::disabled="webhookLoading"
                                    />

                                    <span v-if="webhookCheckingStatus" class="text-sm text-gray-600 dark:text-gray-400">
                                        @lang('tochka-payment::app.admin.settings.index.webhook_checking_status')
                                    </span>

                                    <span v-if="!webhookCheckingStatus && webhookSubscribed" class="text-sm text-green-600 dark:text-green-400">
                                        @lang('tochka-payment::app.admin.settings.index.webhook_subscribed')
                                    </span>

                                    <span v-if="!webhookCheckingStatus && !webhookSubscribed && webhookStatusChecked" class="text-sm text-gray-600 dark:text-gray-400">
                                        @lang('tochka-payment::app.admin.settings.index.webhook_not_subscribed')
                                    </span>
                                </div>
                            </x-admin::form.control-group>

                            <!-- Customer Code -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.customer_code')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="customer_code"
                                    rules=""
                                    v-model="customer_code"
                                    :label="trans('tochka-payment::app.admin.settings.index.customer_code')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.customer_code_placeholder')"
                                />
                                <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                                    @lang('tochka-payment::app.admin.settings.index.customer_code_help')
                                </p>
                                <x-admin::form.control-group.error control-name="customer_code" />
                            </x-admin::form.control-group>

                            <!-- Merchant ID -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.merchant_id')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="merchant_id"
                                    rules=""
                                    v-model="merchant_id"
                                    :label="trans('tochka-payment::app.admin.settings.index.merchant_id')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.merchant_id_placeholder')"
                                />
                                <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                                    @lang('tochka-payment::app.admin.settings.index.merchant_id_help')
                                </p>
                                <x-admin::form.control-group.error control-name="merchant_id" />
                            </x-admin::form.control-group>

                            <!-- Payment Mode -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.payment_mode')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="payment_mode"
                                    rules=""
                                    v-model="payment_mode"
                                    :label="trans('tochka-payment::app.admin.settings.index.payment_mode')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.payment_mode_placeholder')"
                                />

                                <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                                    @lang('tochka-payment::app.admin.settings.index.payment_mode_help')
                                </p>

                                <x-admin::form.control-group.error control-name="payment_mode" />
                            </x-admin::form.control-group>

                            <!-- Save Card -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.save_card')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="checkbox"
{{--                                    type="switch"--}}
                                    name="save_card"
                                    v-model="save_card"
                                    :label="trans('tochka-payment::app.admin.settings.index.save_card')"
                                />

                                <x-admin::form.control-group.error control-name="save_card" />
                            </x-admin::form.control-group>

                            <!-- Pre Authorization -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.pre_authorization')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
{{--                                    type="switch"--}}
                                    type="checkbox"
                                    name="pre_authorization"
                                    v-model="pre_authorization"
                                    :label="trans('tochka-payment::app.admin.settings.index.pre_authorization')"
                                />

                                <x-admin::form.control-group.error control-name="pre_authorization" />
                            </x-admin::form.control-group>

                            <!-- TTL -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.ttl')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="ttl"
                                    rules=""
                                    v-model="ttl"
                                    :label="trans('tochka-payment::app.admin.settings.index.ttl')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.ttl_placeholder')"
                                />

                                <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                                    @lang('tochka-payment::app.admin.settings.index.ttl_help')
                                </p>

                                <x-admin::form.control-group.error control-name="ttl" />
                            </x-admin::form.control-group>

                            <!-- Min Amount -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.min_amount')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="min_amount"
                                    rules=""
                                    v-model="min_amount"
                                    :label="trans('tochka-payment::app.admin.settings.index.min_amount')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.min_amount_placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="min_amount" />
                            </x-admin::form.control-group>

                            <!-- Is Active -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.is_active')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
{{--                                    type="switch"--}}
                                    type="checkbox"
                                    name="is_active"
                                    v-model="is_active"
                                    :label="trans('tochka-payment::app.admin.settings.index.is_active')"
                                />

                                <x-admin::form.control-group.error control-name="is_active" />
                            </x-admin::form.control-group>

                            <!-- Telegram Notifications Section -->
                            <div class="mt-8 border-t border-gray-200 pt-6 dark:border-gray-700">
                                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                                    @lang('tochka-payment::app.admin.settings.index.telegram_notifications')
                                </h3>
                                <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                                    @lang('tochka-payment::app.admin.settings.index.telegram_notifications_description')
                                </p>

                                <!-- Telegram Bot Token -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('tochka-payment::app.admin.settings.index.telegram_bot_token')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="telegram_bot_token"
                                        rules=""
                                        v-model="telegram_bot_token"
                                        :label="trans('tochka-payment::app.admin.settings.index.telegram_bot_token')"
                                        :placeholder="trans('tochka-payment::app.admin.settings.index.telegram_bot_token_placeholder')"
                                    />

                                    <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                                        @lang('tochka-payment::app.admin.settings.index.telegram_bot_token_help')
                                    </p>

                                    <x-admin::form.control-group.error control-name="telegram_bot_token" />
                                </x-admin::form.control-group>

                                <!-- Telegram Chat ID -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('tochka-payment::app.admin.settings.index.telegram_chat_id')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="telegram_chat_id"
                                        rules=""
                                        v-model="telegram_chat_id"
                                        :label="trans('tochka-payment::app.admin.settings.index.telegram_chat_id')"
                                        :placeholder="trans('tochka-payment::app.admin.settings.index.telegram_chat_id_placeholder')"
                                    />

                                    <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                                        @lang('tochka-payment::app.admin.settings.index.telegram_chat_id_help')
                                    </p>

                                    <x-admin::form.control-group.error control-name="telegram_chat_id" />
                                </x-admin::form.control-group>
                            </div>

                            <!-- Save Button -->
                            <div class="mt-6 flex items-center justify-end gap-x-2.5">
                                <x-admin::button
                                    button-type="button"
                                    class="primary-button"
                                    :title="trans('tochka-payment::app.admin.settings.index.save-btn')"
                                    ::loading="isLoading"
                                    ::disabled="isLoading"
                                />
                            </div>
                        </div>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-tochka-payment-settings', {
                template: '#v-tochka-payment-settings-template',

                data() {
                    return {
                        companies: @json($companies),
                        company_id: @json($settings->company_id ?? auth()->guard('admin')->user()?->company_id ?? ''),
                        client_id: @json($settings->client_id ?? ''),
                        jwt_token: @json($settings->jwt_token ?? ''),
                        api_base_url: @json($settings->api_base_url ?? ''),
                        webhook_url: @json($settings->webhook_url ?? url(route('api.tochka-payment.webhook.handle', [], false))),
                        customer_code: @json($settings->customer_code ?? ''),
                        merchant_id: @json($settings->merchant_id ?? ''),
                        payment_mode: @json(is_array($settings->payment_mode ?? null) ? implode(',', $settings->payment_mode) : ($settings->payment_mode ?? '')),
                        save_card: @json((bool)($settings->save_card ?? false)),
                        pre_authorization: @json((bool)($settings->pre_authorization ?? false)),
                        ttl: @json($settings->ttl ?? 10080),
                        min_amount: @json($settings->min_amount ?? 1.00),
                        is_active: @json((bool)($settings->is_active ?? false)),
                        telegram_bot_token: @json($settings->telegram_bot_token ?? ''),
                        telegram_chat_id: @json($settings->telegram_chat_id ?? ''),

                        isLoading: false,
                        loadingSettings: false,
                        byCompanySettingsUrl: "{{ route('admin.tochka-payment.settings.by-company', ['companyId' => 'COMPANY_ID']) }}",
                        webhookSubscribed: false,
                        webhookCheckingStatus: false,
                        webhookStatusChecked: false,
                        webhookLoading: false,
                        webhookSubscribeUrl: "{{ route('admin.tochka-payment.settings.webhook.subscribe') }}",
                        webhookUnsubscribeUrl: "{{ route('admin.tochka-payment.settings.webhook.unsubscribe') }}",
                        webhookStatusUrl: "{{ route('admin.tochka-payment.settings.webhook.status') }}",
                    };
                },


                mounted() {
                    // Check webhook status on component mount
                    if (this.company_id) {
                        this.checkWebhookStatus();
                    }
                },

                watch: {
                    company_id(newVal) {
                        if (!newVal || this.companies.length <= 1) return;
                        this.loadSettingsForCompany(newVal);
                        // Check webhook status when company changes
                        if (newVal) {
                            this.checkWebhookStatus();
                        }
                    },
                },

                methods: {
                    loadSettingsForCompany(companyId) {
                        const url = this.byCompanySettingsUrl.replace('COMPANY_ID', companyId);
                        this.loadingSettings = true;
                        this.$axios.get(url)
                            .then((response) => {
                                const d = response.data;
                                this.client_id = d.client_id ?? '';
                                this.jwt_token = d.jwt_token ?? '';
                                this.api_base_url = d.api_base_url ?? '';
                                this.webhook_url = d.webhook_url || "{{ url(route('api.tochka-payment.webhook.handle', [], false)) }}";
                                this.customer_code = d.customer_code ?? '';
                                this.merchant_id = d.merchant_id ?? '';
                                this.payment_mode = d.payment_mode ?? '';
                                this.save_card = !!d.save_card;
                                this.pre_authorization = !!d.pre_authorization;
                                this.ttl = d.ttl ?? 10080;
                                this.min_amount = d.min_amount ?? 1.00;
                                this.is_active = !!d.is_active;
                                this.telegram_bot_token = d.telegram_bot_token ?? '';
                                this.telegram_chat_id = d.telegram_chat_id ?? '';
                                
                                // Check webhook status after loading settings
                                this.checkWebhookStatus();
                            })
                            .catch(() => {
                                this.$emitter.emit('add-flash', { type: 'error', message: 'Не удалось загрузить настройки компании' });
                            })
                            .finally(() => {
                                this.loadingSettings = false;
                            });
                    },

                    updateSettings(params, { resetForm, setErrors }) {
                        this.isLoading = true;

                        let formData = new FormData(this.$refs.settingsForm);

                        this.$axios.post("{{ route('admin.tochka-payment.settings.store') }}", formData)
                            .then((response) => {
                                this.isLoading = false;

                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                            })
                            .catch(error => {
                                this.isLoading = false;

                                if (error.response.status == 422) {
                                    setErrors(error.response.data.errors);

                                    if (error.response.data.message) {
                                        this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                                    }
                                } else {
                                    this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message || 'An error occurred' });
                                }
                            });
                    },

                    checkWebhookStatus() {
                        if (!this.company_id) {
                            return;
                        }

                        this.webhookCheckingStatus = true;
                        this.webhookStatusChecked = false;

                        this.$axios.get(this.webhookStatusUrl, {
                            params: {
                                company_id: this.company_id
                            }
                        })
                            .then((response) => {
                                if (response.data.success) {
                                    this.webhookSubscribed = response.data.subscribed;
                                    this.webhookStatusChecked = true;
                                }
                            })
                            .catch(() => {
                                this.webhookSubscribed = false;
                                this.webhookStatusChecked = true;
                            })
                            .finally(() => {
                                this.webhookCheckingStatus = false;
                            });
                    },

                    subscribeToWebhook() {
                        if (!this.company_id) {
                            this.$emitter.emit('add-flash', { 
                                type: 'error', 
                                message: this.trans('tochka-payment::app.admin.settings.index.company_required') 
                            });
                            return;
                        }

                        this.webhookLoading = true;

                        this.$axios.post(this.webhookSubscribeUrl, {
                            company_id: this.company_id,
                            webhook_url: this.webhook_url || null
                        })
                            .then((response) => {
                                if (response.data.success) {
                                    this.$emitter.emit('add-flash', { 
                                        type: 'success', 
                                        message: response.data.message || this.trans('tochka-payment::app.admin.settings.index.webhook_subscribe_success')
                                    });
                                    this.webhookSubscribed = true;
                                    this.webhookStatusChecked = true;
                                } else {
                                    this.$emitter.emit('add-flash', { 
                                        type: 'error', 
                                        message: response.data.message || this.trans('tochka-payment::app.admin.settings.index.webhook_subscribe_error')
                                    });
                                }
                            })
                            .catch((error) => {
                                const message = error.response?.data?.message || this.trans('tochka-payment::app.admin.settings.index.webhook_subscribe_error');
                                this.$emitter.emit('add-flash', { type: 'error', message: message });
                            })
                            .finally(() => {
                                this.webhookLoading = false;
                            });
                    },

                    unsubscribeFromWebhook() {
                        if (!this.company_id) {
                            this.$emitter.emit('add-flash', { 
                                type: 'error', 
                                message: this.trans('tochka-payment::app.admin.settings.index.company_required') 
                            });
                            return;
                        }

                        this.webhookLoading = true;

                        this.$axios.post(this.webhookUnsubscribeUrl, {
                            company_id: this.company_id
                        })
                            .then((response) => {
                                if (response.data.success) {
                                    this.$emitter.emit('add-flash', { 
                                        type: 'success', 
                                        message: response.data.message || this.trans('tochka-payment::app.admin.settings.index.webhook_unsubscribe_success')
                                    });
                                    this.webhookSubscribed = false;
                                    this.webhookStatusChecked = true;
                                } else {
                                    this.$emitter.emit('add-flash', { 
                                        type: 'error', 
                                        message: response.data.message || this.trans('tochka-payment::app.admin.settings.index.webhook_unsubscribe_error')
                                    });
                                }
                            })
                            .catch((error) => {
                                const message = error.response?.data?.message || this.trans('tochka-payment::app.admin.settings.index.webhook_unsubscribe_error');
                                this.$emitter.emit('add-flash', { type: 'error', message: message });
                            })
                            .finally(() => {
                                this.webhookLoading = false;
                            });
                    },
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
