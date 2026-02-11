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
                            :value="old('save_card', $settings->save_card ?? false)"
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
                            :value="old('pre_authorization', $settings->pre_authorization ?? false)"
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
                            :value="old('is_active', $settings->is_active ?? false)"
                            v-model="is_active"
                            :label="trans('tochka-payment::app.admin.settings.index.is_active')"
                        />

                        <x-admin::form.control-group.error control-name="is_active" />
                    </x-admin::form.control-group>

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
                            <!-- Form fields will be rendered by blade template above -->
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
                        client_id: @json($settings->client_id ?? ''),
                        jwt_token: @json($settings->jwt_token ?? ''),
                        api_base_url: @json($settings->api_base_url ?? ''),
                        webhook_url: @json($settings->webhook_url ?? ''),
                        customer_code: @json($settings->customer_code ?? ''),
                        merchant_id: @json($settings->merchant_id ?? ''),
                        payment_mode: @json(is_array($settings->payment_mode ?? null) ? implode(',', $settings->payment_mode) : ($settings->payment_mode ?? '')),
                        save_card: @json($settings->save_card ?? false),
                        pre_authorization: @json($settings->pre_authorization ?? false),
                        ttl: @json($settings->ttl ?? 10080),
                        min_amount: @json($settings->min_amount ?? 1.00),
                        is_active: @json($settings->is_active ?? false),

                        isLoading: false,
                    };
                },

                methods: {
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
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
