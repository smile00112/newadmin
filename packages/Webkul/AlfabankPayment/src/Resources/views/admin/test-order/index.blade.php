<x-admin::layouts>
    <x-slot:title>
        Тестовый заказ Альфа-Банк
    </x-slot>

    <v-alfabank-test-order>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                Тестовый заказ Альфа-Банк
            </p>
        </div>

        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
            ref="form"
        >
            <form
                @submit="handleSubmit($event, sendTestOrder)"
                ref="testOrderForm"
            >
                <div class="mt-7 rounded-lg bg-white p-4 shadow dark:bg-gray-900">
                    <!-- Required Fields -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Обязательные поля</h3>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Номер заказа
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="orderNumber"
                                v-model="orderNumber"
                                :label="'Номер заказа'"
                                :placeholder="'TEST_ORDER_001'"
                            />
                            <x-admin::form.control-group.error control-name="orderNumber" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Сумма (в рублях)
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="number"
                                name="amount"
                                step="0.01"
                                min="0.01"
                                v-model="amount"
                                :label="'Сумма'"
                                :placeholder="'100.00'"
                            />
                            <x-admin::form.control-group.error control-name="amount" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                URL возврата (успешная оплата)
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="url"
                                name="returnUrl"
                                v-model="returnUrl"
                                :label="'URL возврата'"
                                :placeholder="'https://example.com/success'"
                            />
                            <x-admin::form.control-group.error control-name="returnUrl" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                URL возврата (неуспешная оплата)
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="url"
                                name="failUrl"
                                v-model="failUrl"
                                :label="'URL возврата (ошибка)'"
                                :placeholder="'https://example.com/fail'"
                            />
                            <x-admin::form.control-group.error control-name="failUrl" />
                        </x-admin::form.control-group>
                    </div>

                    <!-- Optional Fields -->
                    <div class="mb-6 border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Дополнительные поля</h3>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Валюта
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="select"
                                name="currency"
                                v-model="currency"
                                :label="'Валюта'"
                            >
                                <option value="">Не указана</option>
                                <option value="BYN">BYN (933)</option>
                                <option value="RUB">RUB (643)</option>
                                <option value="USD">USD (840)</option>
                                <option value="EUR">EUR (978)</option>
                            </x-admin::form.control-group.control>
                            <x-admin::form.control-group.error control-name="currency" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Email клиента
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="email"
                                name="email"
                                v-model="email"
                                :label="'Email'"
                                :placeholder="'test@example.com'"
                            />
                            <x-admin::form.control-group.error control-name="email" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                ID клиента (clientId)
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="clientId"
                                v-model="clientId"
                                :label="'ID клиента'"
                                :placeholder="'test_client_123'"
                            />
                            <x-admin::form.control-group.error control-name="clientId" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                ID сохраненной карты (bindingId)
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="bindingId"
                                v-model="bindingId"
                                :label="'ID сохраненной карты'"
                                :placeholder="'Оставьте пустым для нового платежа'"
                            />
                            <x-admin::form.control-group.error control-name="bindingId" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Описание заказа
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="description"
                                v-model="description"
                                :label="'Описание'"
                                :placeholder="'Тестовый заказ'"
                            />
                            <x-admin::form.control-group.error control-name="description" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Динамический callback URL
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="url"
                                name="dynamicCallbackUrl"
                                v-model="dynamicCallbackUrl"
                                :label="'Динамический callback URL'"
                                :placeholder="'https://example.com/callback'"
                            />
                            <x-admin::form.control-group.error control-name="dynamicCallbackUrl" />
                        </x-admin::form.control-group>
                    </div>

                    <!-- Response Section -->
                    <div v-if="response" class="mb-6 border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Результат</h3>
                        <div :class="['p-4 rounded', response.success ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20']">
                            <p :class="['font-semibold mb-2', response.success ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200']">
                                @{{ response.message }}
                            </p>
                            <div v-if="response.formUrl" class="mt-3">
                                <a :href="response.formUrl" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                                    Перейти на страницу оплаты
                                </a>
                            </div>
                            <div v-if="response.orderId" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Order ID: @{{ response.orderId }}
                            </div>
                            <details class="mt-3">
                                <summary class="cursor-pointer text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                                    Показать полный ответ
                                </summary>
                                <pre class="mt-2 p-2 bg-gray-100 dark:bg-gray-800 rounded text-xs overflow-auto">@{{ JSON.stringify(response.response, null, 2) }}</pre>
                            </details>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-x-2.5">
                        <x-admin::button
                            button-type="button"
                            class="primary-button"
                            title="Отправить тестовый заказ"
                            ::loading="isLoading"
                            ::disabled="isLoading"
                        />
                    </div>
                </div>
            </form>
        </x-admin::form>
    </v-alfabank-test-order>

    @push('scripts')
        <script type="text/x-template" id="v-alfabank-test-order-template">
            <div>
                <slot></slot>
            </div>
        </script>

        <script type="module">
            app.component('v-alfabank-test-order', {
                template: '#v-alfabank-test-order-template',

                data() {
                    return {
                        orderNumber: 'TEST_' + Date.now(),
                        amount: '100.00',
                        returnUrl: '{{ route("alfabank.payment.return") }}',
                        failUrl: '{{ route("alfabank.payment.return", ["status" => "fail"]) }}',
                        currency: 'BYN',
                        email: 'test@example.com',
                        clientId: 'test_client_' + Date.now(),
                        bindingId: '',
                        description: 'Тестовый заказ',
                        dynamicCallbackUrl: '',
                        isLoading: false,
                        response: null,
                    };
                },

                methods: {
                    sendTestOrder(params, { setErrors }) {
                        this.isLoading = true;
                        this.response = null;

                        let formData = new FormData(this.$refs.testOrderForm);

                        this.$axios.post("{{ route('admin.alfabank.test-order.send') }}", formData)
                            .then((response) => {
                                this.isLoading = false;
                                this.response = response.data;
                                if (response.data.success) {
                                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                } else {
                                    this.$emitter.emit('add-flash', { type: 'error', message: response.data.message });
                                }
                            })
                            .catch(error => {
                                this.isLoading = false;
                                if (error.response && error.response.status == 422) {
                                    setErrors(error.response.data.errors || {});
                                    if (error.response.data.message) {
                                        this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                                    }
                                } else {
                                    const errorMessage = (error.response && error.response.data.message) || 'Произошла ошибка';
                                    this.response = {
                                        success: false,
                                        message: errorMessage,
                                        response: error.response?.data || {}
                                    };
                                    this.$emitter.emit('add-flash', { type: 'error', message: errorMessage });
                                }
                            });
                    },
                }
            });
        </script>
    @endpush
</x-admin::layouts>
