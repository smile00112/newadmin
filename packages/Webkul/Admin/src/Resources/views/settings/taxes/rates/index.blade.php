<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.taxes.rates.index.title')
    </x-slot>

    <v-tax-rates>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.settings.taxes.rates.index.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <x-admin::datagrid.export src="{{ route('admin.settings.taxes.rates.index') }}" />

                @if (bouncer()->hasPermission('settings.taxes.tax_rates.create'))
                    <button type="button" class="primary-button">
                        @lang('admin::app.settings.taxes.rates.index.button-title')
                    </button>
                @endif
            </div>
        </div>

        <!-- DataGrid Shimmer -->
        <x-admin::shimmer.datagrid />
    </v-tax-rates>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-tax-rates-template"
        >
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.taxes.rates.index.title')
                </p>

                <div class="flex items-center gap-x-2.5">
                    <x-admin::datagrid.export src="{{ route('admin.settings.taxes.rates.index') }}" />

                    @if (bouncer()->hasPermission('settings.taxes.tax_rates.create'))
                        <button
                            type="button"
                            class="primary-button"
                            @click="selectedTaxRate=0; resetForm(); $refs.taxRateModal.toggle()"
                        >
                            @lang('admin::app.settings.taxes.rates.index.button-title')
                        </button>
                    @endif
                </div>
            </div>

            <x-admin::datagrid
                :src="route('admin.settings.taxes.rates.index')"
                ref="datagrid"
            >
                <template #body="{
                    isLoading,
                    available,
                    applied,
                    selectAll,
                    sort,
                    performAction
                }">
                    <template v-if="isLoading">
                        <x-admin::shimmer.datagrid.table.body />
                    </template>

                    <template v-else>
                        <div
                            v-for="record in available.records"
                            class="row grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950"
                            :style="`grid-template-columns: repeat(${gridsCount}, minmax(0, 1fr))`"
                        >
                            <p>@{{ record.id }}</p>
                            <p>@{{ record.identifier }}</p>
                            <p>@{{ record.state || '*' }}</p>
                            <p>@{{ record.country }}</p>
                            <p>@{{ record.zip_code }}</p>
                            <p>@{{ record.zip_from }}</p>
                            <p>@{{ record.zip_to }}</p>
                            <p>@{{ record.tax_rate }}</p>

                            <!-- Actions -->
                            <div class="flex justify-end">
                                @if (bouncer()->hasPermission('settings.taxes.tax_rates.edit'))
                                    <a @click="selectedTaxRate=1; editModal(record.actions.find(action => action.index === 'edit')?.url)">
                                        <span
                                            :class="record.actions.find(action => action.index === 'edit')?.icon"
                                            class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                        >
                                        </span>
                                    </a>
                                @endif

                                @if (bouncer()->hasPermission('settings.taxes.tax_rates.delete'))
                                    <a @click="performAction(record.actions.find(action => action.index === 'delete'))">
                                        <span
                                            :class="record.actions.find(action => action.index === 'delete')?.icon"
                                            class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                        >
                                        </span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </template>
                </template>
            </x-admin::datagrid>

            <!-- Tax Rate Create/Edit Modal -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form
                    @submit="handleSubmit($event, updateOrCreate)"
                    ref="taxRateForm"
                >
                    <x-admin::modal ref="taxRateModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                <span v-if="selectedTaxRate">
                                    @lang('admin::app.settings.taxes.rates.edit.title')
                                </span>

                                <span v-else>
                                    @lang('admin::app.settings.taxes.rates.create.title')
                                </span>
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>
                            <x-admin::form.control-group.control
                                type="hidden"
                                name="id"
                                v-model="taxRate.id"
                            />

                            <!-- Identifier -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.taxes.rates.create.identifier')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="identifier"
                                    rules="required"
                                    v-model="taxRate.identifier"
                                    :label="trans('admin::app.settings.taxes.rates.create.identifier')"
                                    :placeholder="trans('admin::app.settings.taxes.rates.create.identifier')"
                                />

                                <x-admin::form.control-group.error control-name="identifier" />
                            </x-admin::form.control-group>

                            <!-- Country -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.taxes.rates.create.country')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="country"
                                    rules="required"
                                    v-model="taxRate.country"
                                    :label="trans('admin::app.settings.taxes.rates.create.country')"
                                >
                                    <option value="">
                                        @lang('admin::app.settings.taxes.rates.create.select-country')
                                    </option>

                                    @foreach (core()->countries() as $country)
                                        <option value="{{ $country->code }}">
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="country" />
                            </x-admin::form.control-group>

                            <!-- State -->
                            <x-admin::form.control-group>
                                <template v-if="haveStates()">
                                    <x-admin::form.control-group.label>
                                        @lang('admin::app.settings.taxes.rates.create.state')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="state"
                                        v-model="taxRate.state"
                                        :label="trans('admin::app.settings.taxes.rates.create.state')"
                                    >
                                        <option value="">
                                            @lang('admin::app.settings.taxes.rates.edit.select-state')
                                        </option>

                                        <option
                                            v-for="(state, index) in countryStates[taxRate.country]"
                                            :value="state.code"
                                        >
                                            @{{ state.default_name }}
                                        </option>
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="state" />
                                </template>

                                <template v-else>
                                    <x-admin::form.control-group.label>
                                        @lang('admin::app.settings.taxes.rates.create.state')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="state"
                                        v-model="taxRate.state"
                                        :label="trans('admin::app.settings.taxes.rates.create.state')"
                                        :placeholder="trans('admin::app.settings.taxes.rates.create.state')"
                                    />

                                    <x-admin::form.control-group.error control-name="state" />
                                </template>
                            </x-admin::form.control-group>

                            <!-- Tax Rate -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.taxes.rates.create.tax-rate')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="tax_rate"
                                    rules="required"
                                    v-model="taxRate.tax_rate"
                                    :label="trans('admin::app.settings.taxes.rates.create.tax-rate')"
                                    :placeholder="trans('admin::app.settings.taxes.rates.create.tax-rate')"
                                />

                                <x-admin::form.control-group.error control-name="tax_rate" />
                            </x-admin::form.control-group>

                            <!-- Enable Zip Range -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.taxes.rates.create.is-zip')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="is_zip"
                                    :value="1"
                                    v-model="taxRate.is_zip"
                                    :label="trans('admin::app.settings.taxes.rates.create.is-zip')"
                                />
                            </x-admin::form.control-group>

                            <!-- Zip Code (when is_zip is off) -->
                            <x-admin::form.control-group v-if="! taxRate.is_zip">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.taxes.rates.create.zip-code')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="zip_code"
                                    v-model="taxRate.zip_code"
                                    :label="trans('admin::app.settings.taxes.rates.create.zip-code')"
                                    :placeholder="trans('admin::app.settings.taxes.rates.create.zip-code')"
                                />

                                <x-admin::form.control-group.error control-name="zip_code" />
                            </x-admin::form.control-group>

                            <!-- Zip From / Zip To (when is_zip is on) -->
                            <div v-if="taxRate.is_zip">
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.taxes.rates.create.zip-from')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="zip_from"
                                        rules="required"
                                        v-model="taxRate.zip_from"
                                        :label="trans('admin::app.settings.taxes.rates.create.zip-from')"
                                        :placeholder="trans('admin::app.settings.taxes.rates.create.zip-from')"
                                    />

                                    <x-admin::form.control-group.error control-name="zip_from" />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.taxes.rates.create.zip-to')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="zip_to"
                                        rules="required"
                                        v-model="taxRate.zip_to"
                                        :label="trans('admin::app.settings.taxes.rates.create.zip-to')"
                                        :placeholder="trans('admin::app.settings.taxes.rates.create.zip-to')"
                                    />

                                    <x-admin::form.control-group.error control-name="zip_to" />
                                </x-admin::form.control-group>
                            </div>
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <x-admin::button
                                button-type="button"
                                class="primary-button"
                                :title="trans('admin::app.settings.taxes.rates.create.save-btn')"
                                ::loading="isLoading"
                                ::disabled="isLoading"
                            />
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-tax-rates', {
                template: '#v-tax-rates-template',

                data() {
                    return {
                        taxRate: {
                            country: '',
                            state: '',
                            is_zip: false,
                        },

                        isLoading: false,

                        selectedTaxRate: 0,

                        countryStates: @json(core()->groupedStatesByCountries()),
                    };
                },

                computed: {
                    gridsCount() {
                        let count = this.$refs.datagrid.available.columns.length;

                        if (this.$refs.datagrid.available.actions.length) {
                            ++count;
                        }

                        if (this.$refs.datagrid.available.massActions.length) {
                            ++count;
                        }

                        return count;
                    },
                },

                methods: {
                    haveStates() {
                        return !!this.countryStates[this.taxRate.country]?.length;
                    },

                    updateOrCreate(params, { resetForm, setErrors }) {
                        this.isLoading = true;

                        let formData = new FormData(this.$refs.taxRateForm);

                        if (params.id) {
                            formData.append('_method', 'put');
                        }

                        this.$axios.post(
                            params.id
                                ? "{{ route('admin.settings.taxes.rates.update', '__REPLACE_ID__') }}".replace('__REPLACE_ID__', params.id)
                                : "{{ route('admin.settings.taxes.rates.store') }}",
                            formData
                        )
                        .then((response) => {
                            this.isLoading = false;

                            this.$refs.taxRateModal.close();

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$refs.datagrid.get();

                            resetForm();
                        })
                        .catch(error => {
                            this.isLoading = false;

                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);
                            } else if (error.response.data.message) {
                                this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                            }
                        });
                    },

                    editModal(url) {
                        this.$axios.get(url)
                            .then((response) => {
                                let data = response.data.data;

                                this.taxRate = {
                                    ...data,
                                    is_zip: !!data.is_zip,
                                };

                                this.$refs.taxRateModal.toggle();
                            });
                    },

                    resetForm() {
                        this.taxRate = {
                            country: '',
                            state: '',
                            is_zip: false,
                        };
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
