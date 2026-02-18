<x-admin::layouts>
    <x-slot:title>
        Настройки Альфа-Банк
    </x-slot>

    <v-alfabank-payment-settings>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                Настройки Альфа-Банк
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
                    <!-- Base Settings -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Основные настройки</h3>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Включить метод оплаты
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="switch"
                                name="active"
                                v-model="active"
                                :label="'Включить метод оплаты'"
                            />
                            <x-admin::form.control-group.error control-name="active" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Название
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="title"
                                v-model="title"
                                :label="'Название'"
                                :placeholder="'Альфа-Банк'"
                            />
                            <x-admin::form.control-group.error control-name="title" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Описание
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="textarea"
                                name="description"
                                v-model="description"
                                :label="'Описание'"
                                :placeholder="'Оплата картой через Альфа-Банк'"
                            />
                            <x-admin::form.control-group.error control-name="description" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Логин API (Login-API)
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="merchant"
                                v-model="merchant"
                                :label="'Логин API'"
                            />
                            <x-admin::form.control-group.error control-name="merchant" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Пароль
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="password"
                                v-model="password"
                                :label="'Пароль'"
                                autocomplete="new-password"
                            />
                            <x-admin::form.control-group.error control-name="password" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Токен (альтернатива логину/паролю)
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="token"
                                v-model="token"
                                :label="'Токен'"

                            />
                            <x-admin::form.control-group.error control-name="token" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Тестовый режим
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="switch"
                                name="test_mode"
                                v-model="test_mode"
                                :label="'Тестовый режим'"
                            />
                            <x-admin::form.control-group.error control-name="test_mode" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Тип платежей
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="select"
                                name="stage_mode"
                                v-model="stage_mode"
                                :label="'Тип платежей'"
                            >
                                <option value="one-stage">Однофазные платежи</option>
                                <option value="two-stage">Двухфазные платежи</option>
                            </x-admin::form.control-group.control>
                            <x-admin::form.control-group.error control-name="stage_mode" />
                        </x-admin::form.control-group>
                    </div>

                    <!-- Order Settings -->
                    <div class="mb-6 border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Настройки заказа</h3>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Статус заказа после оплаты
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="select"
                                name="order_status_paid"
                                v-model="order_status_paid"
                                :label="'Статус заказа после оплаты'"
                            >
                                <option value="processing">Обработка</option>
                                <option value="completed">Завершен</option>
                            </x-admin::form.control-group.control>
                            <x-admin::form.control-group.error control-name="order_status_paid" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                URL успешной оплаты
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="success_url"
                                v-model="success_url"
                                :label="'URL успешной оплаты'"
                                :placeholder="'Оставьте пустым для использования по умолчанию'"
                            />
                            <x-admin::form.control-group.error control-name="success_url" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                URL неуспешной оплаты
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="fail_url"
                                v-model="fail_url"
                                :label="'URL неуспешной оплаты'"
                                :placeholder="'Оставьте пустым для использования по умолчанию'"
                            />
                            <x-admin::form.control-group.error control-name="fail_url" />
                        </x-admin::form.control-group>
                    </div>

                    <!-- Cart Data Settings -->
                    <div class="mb-6 border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Настройки отправки данных корзины</h3>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Отправлять данные корзины
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="switch"
                                name="send_order"
                                v-model="send_order"
                                :label="'Отправлять данные корзины'"
                            />
                            <x-admin::form.control-group.error control-name="send_order" />
                        </x-admin::form.control-group>

                        <template v-if="send_order">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    Система налогообложения
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="select"
                                    name="tax_system"
                                    v-model="tax_system"
                                    :label="'Система налогообложения'"
                                >
                                    <option value="0">Общая</option>
                                    <option value="1">Упрощенная, доход</option>
                                    <option value="2">Упрощенная, доход минус расходы</option>
                                    <option value="3">Единый налог на вмененный доход</option>
                                    <option value="4">Единый сельскохозяйственный налог</option>
                                    <option value="5">Патентная система налогообложения</option>
                                </x-admin::form.control-group.control>
                                <x-admin::form.control-group.error control-name="tax_system" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    НДС по умолчанию
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="select"
                                    name="tax_type"
                                    v-model="tax_type"
                                    :label="'НДС по умолчанию'"
                                >
                                    <option value="0">Без НДС</option>
                                    <option value="1">НДС 0%</option>
                                    <option value="2">НДС 10%</option>
                                    <option value="3">НДС 18%</option>
                                    <option value="6">НДС 20%</option>
                                    <option value="14">НДС 22%</option>
                                </x-admin::form.control-group.control>
                                <x-admin::form.control-group.error control-name="tax_type" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    Формат фискального документа
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="select"
                                    name="version_ffd"
                                    v-model="version_ffd"
                                    :label="'Формат фискального документа'"
                                >
                                    <option value="v1_05">v1.05</option>
                                    <option value="v1_2">v1.2</option>
                                </x-admin::form.control-group.control>
                                <x-admin::form.control-group.error control-name="version_ffd" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    Тип оплаты (для товаров)
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="select"
                                    name="payment_method_type"
                                    v-model="payment_method_type"
                                    :label="'Тип оплаты'"
                                >
                                    <option value="1">Полная предоплата</option>
                                    <option value="2">Частичная предоплата</option>
                                    <option value="3">Аванс</option>
                                    <option value="4">Полная оплата</option>
                                    <option value="5">Частичная оплата с дальнейшим кредитом</option>
                                    <option value="6">Без оплаты с дальнейшим кредитом</option>
                                    <option value="7">Оплата в кредит</option>
                                </x-admin::form.control-group.control>
                                <x-admin::form.control-group.error control-name="payment_method_type" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    Тип товаров и услуг
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="select"
                                    name="payment_object_type"
                                    v-model="payment_object_type"
                                    :label="'Тип товаров и услуг'"
                                >
                                    <option value="1">Товар</option>
                                    <option value="2">Подакцизный товар</option>
                                    <option value="3">Работа</option>
                                    <option value="4">Услуга</option>
                                    <option value="5">Ставка в азартной игре</option>
                                    <option value="7">Лотерейный билет</option>
                                    <option value="9">Предоставление результатов интеллектуальной деятельности</option>
                                    <option value="10">Платеж</option>
                                    <option value="11">Агентское вознаграждение</option>
                                    <option value="12">Составной предмет расчета</option>
                                    <option value="13">Иной предмет расчета</option>
                                </x-admin::form.control-group.control>
                                <x-admin::form.control-group.error control-name="payment_object_type" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    Тип оплаты для доставки
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="select"
                                    name="payment_object_type_delivery"
                                    v-model="payment_object_type_delivery"
                                    :label="'Тип оплаты для доставки'"
                                >
                                    <option value="1">Полная предоплата</option>
                                    <option value="2">Частичная предоплата</option>
                                    <option value="3">Аванс</option>
                                    <option value="4">Полная оплата</option>
                                    <option value="5">Частичная оплата с дальнейшим кредитом</option>
                                    <option value="6">Без оплаты с дальнейшим кредитом</option>
                                    <option value="7">Оплата в кредит</option>
                                </x-admin::form.control-group.control>
                                <x-admin::form.control-group.error control-name="payment_object_type_delivery" />
                            </x-admin::form.control-group>
                        </template>
                    </div>

                    <!-- Saved Cards Settings -->
                    <div class="mb-6 border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Оплата сохраненными картами</h3>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Включить оплату сохраненными картами
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="switch"
                                name="saved_cards_payment_enable"
                                v-model="saved_cards_payment_enable"
                                :label="'Включить оплату сохраненными картами'"
                            />
                            <x-admin::form.control-group.error control-name="saved_cards_payment_enable" />
                        </x-admin::form.control-group>
                    </div>

                    <!-- Miscellaneous Settings -->
                    <div class="mb-6 border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Прочее</h3>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Минимальная сумма заказа
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="number"
                                name="min_order_total"
                                step="0.01"
                                min="0"
                                v-model="min_order_total"
                                :label="'Минимальная сумма заказа'"
                            />
                            <x-admin::form.control-group.error control-name="min_order_total" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Максимальная сумма заказа
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="number"
                                name="max_order_total"
                                step="0.01"
                                min="0"
                                v-model="max_order_total"
                                :label="'Максимальная сумма заказа'"
                            />
                            <x-admin::form.control-group.error control-name="max_order_total" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Тип callback уведомлений
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="select"
                                name="callback_type"
                                v-model="callback_type"
                                :label="'Тип callback уведомлений'"
                            >
                                <option value="STATIC">Статический</option>
                                <option value="DYNAMIC">Динамический</option>
                            </x-admin::form.control-group.control>
                            <x-admin::form.control-group.error control-name="callback_type" />
                        </x-admin::form.control-group>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-x-2.5">
                        <button
                            type="submit"
                            class="primary-button"
                            :disabled="isLoading"
                        >
                            <span v-if="isLoading" class="flex items-center gap-2">
                                <svg
                                    class="h-5 w-5 animate-spin"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Сохранение...
                            </span>
                            <span v-else>Сохранить</span>
                        </button>
                    </div>
                </div>
            </form>
        </x-admin::form>
    </v-alfabank-payment-settings>

    @push('scripts')
        <script type="text/x-template" id="v-alfabank-payment-settings-template">
            <div>
                <slot></slot>
            </div>
        </script>

        <script type="module">
            app.component('v-alfabank-payment-settings', {
                template: '#v-alfabank-payment-settings-template',

                data() {
                    return {
                        active: @json($settings['active'] ?? '0') === '1',
                        title: @json($settings['title'] ?? 'Альфа-Банк'),
                        description: @json($settings['description'] ?? 'Оплата картой через Альфа-Банк'),
                        merchant: @json($settings['merchant'] ?? ''),
                        password: @json($settings['password'] ?? ''),
                        token: @json($settings['token'] ?? ''),
                        test_mode: @json($settings['test_mode'] ?? '1') === '1',
                        stage_mode: @json($settings['stage_mode'] ?? 'one-stage'),
                        order_status_paid: @json($settings['order_status_paid'] ?? 'processing'),
                        success_url: @json($settings['success_url'] ?? ''),
                        fail_url: @json($settings['fail_url'] ?? ''),
                        send_order: @json($settings['send_order'] ?? '0') === '1',
                        tax_system: @json($settings['tax_system'] ?? '0'),
                        tax_type: @json($settings['tax_type'] ?? '0'),
                        version_ffd: @json($settings['version_ffd'] ?? 'v1_05'),
                        payment_method_type: @json($settings['payment_method_type'] ?? '4'),
                        payment_object_type: @json($settings['payment_object_type'] ?? '1'),
                        payment_object_type_delivery: @json($settings['payment_object_type_delivery'] ?? '1'),
                        saved_cards_payment_enable: @json($settings['saved_cards_payment_enable'] ?? '0') === '1',
                        min_order_total: @json($settings['min_order_total'] ?? ''),
                        max_order_total: @json($settings['max_order_total'] ?? ''),
                        callback_type: @json($settings['callback_type'] ?? 'STATIC'),
                        isLoading: false,
                    };
                },

                methods: {
                    updateSettings(params, { setErrors }) {
                        this.isLoading = true;

                        let formData = new FormData(this.$refs.settingsForm);
                        formData.append('_method', 'put');

                        // Convert boolean values
                        formData.set('active', this.active ? '1' : '0');
                        formData.set('test_mode', this.test_mode ? '1' : '0');
                        formData.set('send_order', this.send_order ? '1' : '0');
                        formData.set('saved_cards_payment_enable', this.saved_cards_payment_enable ? '1' : '0');

                        this.$axios.post("{{ route('admin.alfabank.settings.update') }}", formData)
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
                                    this.$emitter.emit('add-flash', { type: 'error', message: (error.response && error.response.data.message) || 'Произошла ошибка' });
                                }
                            });
                    },
                }
            });
        </script>
    @endpush
</x-admin::layouts>
