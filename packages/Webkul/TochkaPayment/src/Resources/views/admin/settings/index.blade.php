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

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.server_url')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="text"
                            name="server_url"
                            :value="old('server_url', $settings['server_url'] ?? '')"
                            v-model="server_url"
                            :label="trans('tochka-payment::app.admin.settings.index.server_url')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.server_url_placeholder')"
                        />
                        <x-admin::form.control-group.error control-name="server_url" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.login')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="text"
                            name="login"
                            :value="old('login', $settings['login'] ?? '')"
                            v-model="login"
                            :label="trans('tochka-payment::app.admin.settings.index.login')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.login_placeholder')"
                        />
                        <x-admin::form.control-group.error control-name="login" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.secret_key')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="password"
                            name="secret_key"
                            :value="old('secret_key', $settings['secret_key'] ?? '')"
                            v-model="secret_key"
                            :label="trans('tochka-payment::app.admin.settings.index.secret_key')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.secret_key_placeholder')"
                        />
                        <x-admin::form.control-group.error control-name="secret_key" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.webhook_url')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="text"
                            name="webhook_url"
                            :value="old('webhook_url', $settings['webhook_url'] ?? '')"
                            v-model="webhook_url"
                            :label="trans('tochka-payment::app.admin.settings.index.webhook_url')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.webhook_url_placeholder')"
                        />
                        <x-admin::form.control-group.error control-name="webhook_url" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.api_token')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="text"
                            name="api_token"
                            :value="old('api_token', $settings['api_token'] ?? '')"
                            v-model="api_token"
                            :label="trans('tochka-payment::app.admin.settings.index.api_token')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.api_token_placeholder')"
                        />
                        <x-admin::form.control-group.error control-name="api_token" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.min_amount')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="number"
                            name="min_amount"
                            step="0.01"
                            min="0"
                            :value="old('min_amount', $settings['min_amount'] ?? 1)"
                            v-model="min_amount"
                            :label="trans('tochka-payment::app.admin.settings.index.min_amount')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.min_amount_placeholder')"
                        />
                        <x-admin::form.control-group.error control-name="min_amount" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.service_name')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="text"
                            name="service_name"
                            :value="old('service_name', $settings['service_name'] ?? '')"
                            v-model="service_name"
                            :label="trans('tochka-payment::app.admin.settings.index.service_name')"
                            :placeholder="trans('tochka-payment::app.admin.settings.index.service_name_placeholder')"
                        />
                        <x-admin::form.control-group.error control-name="service_name" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('tochka-payment::app.admin.settings.index.lang')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="select"
                            name="lang"
                            :value="old('lang', $settings['lang'] ?? 'ru')"
                            v-model="lang"
                            :label="trans('tochka-payment::app.admin.settings.index.lang')"
                            rules="required"
                        >
                            <option value="ru">@lang('tochka-payment::app.admin.settings.index.lang_ru')</option>
                            <option value="en">@lang('tochka-payment::app.admin.settings.index.lang_en')</option>
                        </x-admin::form.control-group.control>
                        <x-admin::form.control-group.error control-name="lang" />
                    </x-admin::form.control-group>

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

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.server_url')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="server_url"
                                    v-model="server_url"
                                    :label="trans('tochka-payment::app.admin.settings.index.server_url')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.server_url_placeholder')"
                                />
                                <x-admin::form.control-group.error control-name="server_url" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.login')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="login"
                                    v-model="login"
                                    :label="trans('tochka-payment::app.admin.settings.index.login')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.login_placeholder')"
                                />
                                <x-admin::form.control-group.error control-name="login" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.secret_key')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="password"
                                    name="secret_key"
                                    v-model="secret_key"
                                    :label="trans('tochka-payment::app.admin.settings.index.secret_key')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.secret_key_placeholder')"
                                />
                                <x-admin::form.control-group.error control-name="secret_key" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.webhook_url')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="webhook_url"
                                    v-model="webhook_url"
                                    :label="trans('tochka-payment::app.admin.settings.index.webhook_url')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.webhook_url_placeholder')"
                                />
                                <x-admin::form.control-group.error control-name="webhook_url" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.api_token')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="api_token"
                                    v-model="api_token"
                                    :label="trans('tochka-payment::app.admin.settings.index.api_token')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.api_token_placeholder')"
                                />
                                <x-admin::form.control-group.error control-name="api_token" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.min_amount')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="number"
                                    name="min_amount"
                                    step="0.01"
                                    min="0"
                                    v-model="min_amount"
                                    :label="trans('tochka-payment::app.admin.settings.index.min_amount')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.min_amount_placeholder')"
                                />
                                <x-admin::form.control-group.error control-name="min_amount" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.service_name')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="service_name"
                                    v-model="service_name"
                                    :label="trans('tochka-payment::app.admin.settings.index.service_name')"
                                    :placeholder="trans('tochka-payment::app.admin.settings.index.service_name_placeholder')"
                                />
                                <x-admin::form.control-group.error control-name="service_name" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('tochka-payment::app.admin.settings.index.lang')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="select"
                                    name="lang"
                                    v-model="lang"
                                    :label="trans('tochka-payment::app.admin.settings.index.lang')"
                                    rules="required"
                                >
                                    <option value="ru">@lang('tochka-payment::app.admin.settings.index.lang_ru')</option>
                                    <option value="en">@lang('tochka-payment::app.admin.settings.index.lang_en')</option>
                                </x-admin::form.control-group.control>
                                <x-admin::form.control-group.error control-name="lang" />
                            </x-admin::form.control-group>

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
                        server_url: @json($settings['server_url'] ?? ''),
                        login: @json($settings['login'] ?? ''),
                        secret_key: @json($settings['secret_key'] ?? ''),
                        webhook_url: @json($settings['webhook_url'] ?? ''),
                        api_token: @json($settings['api_token'] ?? ''),
                        min_amount: @json($settings['min_amount'] ?? 1),
                        service_name: @json($settings['service_name'] ?? ''),
                        lang: @json($settings['lang'] ?? 'ru'),
                        isLoading: false,
                    };
                },

                methods: {
                    updateSettings(params, { setErrors }) {
                        this.isLoading = true;

                        let formData = new FormData(this.$refs.settingsForm);
                        formData.append('_method', 'put');

                        this.$axios.post("{{ route('admin.tochka-payment.settings.update') }}", formData)
                            .then((response) => {
                                this.isLoading = false;
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                            })
                            .catch(error => {
                                this.isLoading = false;
                                if (error.response && error.response.status == 422) {
                                    setErrors(error.response.data.errors || {});
                                    if (error.response.data.message) {
                                        this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                                    }
                                } else {
                                    this.$emitter.emit('add-flash', { type: 'error', message: (error.response && error.response.data.message) || 'An error occurred' });
                                }
                            });
                    },
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
