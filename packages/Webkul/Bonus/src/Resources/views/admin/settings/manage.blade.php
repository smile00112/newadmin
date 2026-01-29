<div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
        @lang('bonus::app.admin.settings.manage.title')
    </p>

    <v-bonus-manage></v-bonus-manage>
</div>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-bonus-manage-template"
    >
        <div class="grid gap-4">
            <!-- Customer Search Section -->
            <div class="grid gap-2.5">
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">
                    @lang('bonus::app.admin.settings.manage.search-customer')
                </p>

                <div class="relative">
                    <input
                        type="text"
                        class="block w-full rounded-lg border bg-white py-1.5 leading-6 text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 ltr:pl-3 ltr:pr-10 rtl:pl-10 rtl:pr-3"
                        :placeholder="translations.searchPlaceholder"
                        v-model.lazy="searchTerm"
                        v-debounce="500"
                        @input="searchCustomers"
                    />

                    <template v-if="isSearching">
                        <img
                            class="absolute top-2.5 h-5 w-5 animate-spin ltr:right-3 rtl:left-3"
                            src="{{ bagisto_asset('images/spinner.svg') }}"
                        />
                    </template>

                    <template v-else>
                        <span class="icon-search pointer-events-none absolute top-1.5 flex items-center text-2xl ltr:right-3 rtl:left-3"></span>
                    </template>
                </div>

                <!-- Search Results -->
                <div
                    class="max-h-60 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-800"
                    v-if="searchResults.length > 0"
                >
                    <div
                        class="grid cursor-pointer gap-1.5 border-b border-gray-200 p-3 last:border-b-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                        v-for="customer in searchResults"
                        @click="selectCustomer(customer)"
                    >
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">
                            @{{ customer.first_name }} @{{ customer.last_name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            @{{ customer.email }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Selected Customer Info -->
            <div
                class="grid gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950"
                v-if="selectedCustomer"
            >
                <div class="grid gap-2">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('bonus::app.admin.settings.manage.customer-info')
                    </p>

                    <div class="grid gap-1.5">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            <span class="font-semibold">@lang('bonus::app.admin.settings.manage.name'):</span>
                            @{{ selectedCustomer.name }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            <span class="font-semibold">@lang('bonus::app.admin.settings.manage.email'):</span>
                            @{{ selectedCustomer.email }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            <span class="font-semibold">@lang('bonus::app.admin.settings.manage.available-balance'):</span>
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                @{{ selectedCustomer.available_balance }}
                            </span>
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            <span class="font-semibold">@lang('bonus::app.admin.settings.manage.total-balance'):</span>
                            @{{ selectedCustomer.total_balance }}
                        </p>
                    </div>
                </div>

                <!-- Bonus Management Form -->
                <div class="grid gap-4 border-t border-gray-200 pt-4 dark:border-gray-800">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('bonus::app.admin.settings.manage.manage-bonuses')
                    </p>

                    <div class="grid gap-4">
                        <!-- Operation Type -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('bonus::app.admin.settings.manage.operation-type')
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="select"
                                name="operation_type"
                                v-model="operationType"
                            >
                                <option
                                    v-for="option in operationTypeOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    @{{ option.title }}
                                </option>
                            </x-admin::form.control-group.control>
                        </x-admin::form.control-group>

                        <!-- Amount -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('bonus::app.admin.settings.manage.amount')
                            </x-admin::form.control-group.label>
                            <v-field
                                name="amount"
                                v-slot="{ field }"
                            >
                                <input
                                    type="text"
                                    name="amount"
                                    v-model="amount"
                                    v-bind="field"
                                    :placeholder="translations.amountPlaceholder"
                                    class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                />
                            </v-field>
                        </x-admin::form.control-group>

                        <!-- Description -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('bonus::app.admin.settings.manage.description')
                            </x-admin::form.control-group.label>
                            <v-field
                                name="description"
                                v-slot="{ field }"
                            >
                                <textarea
                                    name="description"
                                    v-model="description"
                                    v-bind="field"
                                    :placeholder="translations.descriptionPlaceholder"
                                    rows="3"
                                    class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                ></textarea>
                            </v-field>
                        </x-admin::form.control-group>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button
                                type="button"
                                class="primary-button"
                                @click="processOperation"
                                :disabled="isProcessing || !amount || parseFloat(amount) <= 0"
                            >
                                <template v-if="isProcessing">
                                    <img
                                        class="h-5 w-5 animate-spin"
                                        src="{{ bagisto_asset('images/spinner.svg') }}"
                                    />
                                </template>
                                <template v-else>
                                    <span v-if="operationType === 'add'">
                                        @lang('bonus::app.admin.settings.manage.add-bonus')
                                    </span>
                                    <span v-else>
                                        @lang('bonus::app.admin.settings.manage.deduct-bonus')
                                    </span>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Recent History -->
                <div class="grid gap-2 border-t border-gray-200 pt-4 dark:border-gray-800" v-if="recentHistory.length > 0">
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('bonus::app.admin.settings.manage.recent-history')
                    </p>

                    <div class="max-h-60 overflow-y-auto">
                        <div
                            class="grid gap-1 border-b border-gray-200 py-2 last:border-b-0 dark:border-gray-800"
                            v-for="item in recentHistory"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold" :class="item.amount >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                    @{{ item.amount >= 0 ? '+' : '' }}@{{ item.amount }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    @{{ item.created_at }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                @{{ item.description }}
                            </p>
                            <p class="text-xs text-gray-500">
                                @lang('bonus::app.admin.settings.manage.balance-after'): @{{ item.balance_after }}
                                <template v-if="item.expires_at">
                                    | @lang('bonus::app.admin.settings.manage.expires-at'): @{{ item.expires_at }}
                                </template>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                v-if="!selectedCustomer && searchTerm.length <= 1"
            >
                <img
                    src="{{ bagisto_asset('images/empty-placeholders/customers.svg') }}"
                    class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                />
                <div class="flex flex-col items-center gap-1.5">
                    <p class="text-base font-semibold text-gray-400">
                        @lang('bonus::app.admin.settings.manage.empty-title')
                    </p>
                    <p class="text-sm text-gray-400">
                        @lang('bonus::app.admin.settings.manage.empty-info')
                    </p>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-bonus-manage', {
            template: '#v-bonus-manage-template',

            data() {
                return {
                    searchTerm: '',
                    searchResults: [],
                    isSearching: false,
                    selectedCustomer: null,
                    recentHistory: [],
                    operationType: 'add',
                    amount: '',
                    description: '',
                    isProcessing: false,
                    operationTypeOptions: [
                        { value: 'add', title: '{{ trans("bonus::app.admin.settings.manage.add-bonus") }}' },
                        { value: 'deduct', title: '{{ trans("bonus::app.admin.settings.manage.deduct-bonus") }}' }
                    ],
                }
            },

            computed: {
                translations() {
                    return {
                        searchPlaceholder: '{{ trans("bonus::app.admin.settings.manage.search-placeholder") }}',
                        amountPlaceholder: '{{ trans("bonus::app.admin.settings.manage.amount-placeholder") }}',
                        descriptionPlaceholder: '{{ trans("bonus::app.admin.settings.manage.description-placeholder") }}',
                    };
                },
            },

            methods: {
                searchCustomers() {
                    if (this.searchTerm.length <= 1) {
                        this.searchResults = [];
                        return;
                    }

                    this.isSearching = true;

                    let self = this;

                    this.$axios.get("{{ route('admin.bonus.manage.search-customer') }}", {
                            params: {
                                query: this.searchTerm,
                            }
                        })
                        .then(function(response) {
                            self.isSearching = false;
                            self.searchResults = response.data.data || [];
                        })
                        .catch(function(error) {
                            self.isSearching = false;
                            self.searchResults = [];
                        });
                },

                selectCustomer(customer) {
                    this.selectedCustomer = null;
                    this.searchTerm = '';
                    this.searchResults = [];
                    this.recentHistory = [];
                    this.amount = '';
                    this.description = '';

                    let self = this;

                    this.$axios.get(`{{ route('admin.bonus.manage.customer-info', ['id' => ':id']) }}`.replace(':id', customer.id))
                        .then(function(response) {
                            if (response.data.success) {
                                self.selectedCustomer = response.data.customer;
                                self.recentHistory = response.data.recent_history || [];
                            } else {
                                self.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: response.data.message || 'Ошибка загрузки данных пользователя'
                                });
                            }
                        })
                        .catch(function(error) {
                            self.$emitter.emit('add-flash', {
                                type: 'error',
                                message: 'Ошибка загрузки данных пользователя'
                            });
                        });
                },

                processOperation() {
                    if (!this.selectedCustomer || !this.amount || parseFloat(this.amount) <= 0) {
                        return;
                    }

                    this.isProcessing = true;

                    const route = this.operationType === 'add'
                        ? "{{ route('admin.bonus.manage.add-bonus') }}"
                        : "{{ route('admin.bonus.manage.deduct-bonus') }}";

                    const data = {
                        customer_id: this.selectedCustomer.id,
                        amount: parseFloat(this.amount),
                        description: this.description || null,
                    };

                    let self = this;

                    this.$axios.post(route, data)
                        .then(function(response) {
                            self.isProcessing = false;

                            if (response.data.success) {
                                self.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.message
                                });

                                // Update customer balance
                                self.selectedCustomer.available_balance = response.data.balance;
                                self.selectedCustomer.total_balance = response.data.balance;

                                // Reload customer info to get updated history
                                self.selectCustomer({ id: self.selectedCustomer.id });

                                // Reset form
                                self.amount = '';
                                self.description = '';
                            } else {
                                self.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: response.data.message || 'Ошибка выполнения операции'
                                });
                            }
                        })
                        .catch(function(error) {
                            self.isProcessing = false;

                            const message = error.response?.data?.message || 'Ошибка выполнения операции';
                            self.$emitter.emit('add-flash', {
                                type: 'error',
                                message: message
                            });
                        });
                },
            },
        });
    </script>
@endPushOnce
