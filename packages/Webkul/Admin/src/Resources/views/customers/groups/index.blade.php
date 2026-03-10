<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.customers.groups.index.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.customers.groups.create.before') !!}

    <v-create-group />

    {!! view_render_event('bagisto.admin.customers.groups.create.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-create-group-template"
        >
            <div>
                <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); box-shadow: 0 4px 15px rgba(6,182,212,0.3); min-width:44px;">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xl font-bold text-gray-800 dark:text-white">
                                @lang('admin::app.customers.groups.index.title')
                            </p>
                            <p class="text-xs text-gray-400">Группы клиентов</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-x-2.5">
                        <div class="flex items-center gap-x-2.5">
                            <!-- Create a new Group -->
                            @if (bouncer()->hasPermission('customers.groups.create'))
                                <button
                                    type="button"
                                    class="primary-button"
                                    @click="selectedGroups=0; $refs.groupUpdateOrCreateModal.open()"
                                >
                                    @lang('admin::app.customers.groups.index.create.create-btn')
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {!! view_render_event('bagisto.admin.customers.groups.list.before') !!}

                <x-admin::datagrid src="{{ route('admin.customers.groups.index') }}" ref="datagrid">
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
                                <!-- ID -->
                                <p>@{{ record.id }}</p>

                                <!-- Code -->
                                <p>@{{ record.code }}</p>

                                <!-- Name -->
                                <p>@{{ record.name }}</p>

                                <!-- Actions -->
                                <div class="flex justify-end">
                                    @if (bouncer()->hasPermission('customers.groups.edit'))
                                        <a @click="selectedGroups=1; editModal(record)">
                                            <span
                                                :class="record.actions.find(action => action.index === 'edit')?.icon"
                                                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                                :title="record.actions.find(action => action.title === '@lang('admin::app.customers.groups.index.datagrid.edit')')?.title"
                                            >
                                            </span>
                                        </a>
                                    @endif

                                    @if (bouncer()->hasPermission('customers.groups.delete'))
                                        <a @click="performAction(record.actions.find(action => action.index === 'delete'))">
                                            <span
                                                :class="record.actions.find(action => action.index === 'delete')?.icon"
                                                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                                :title="record.actions.find(action => action.title === '@lang('admin::app.customers.groups.index.datagrid.delete')')?.title"
                                            >
                                            </span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </template>
                    </template>
                </x-admin::datagrid>

                {!! view_render_event('bagisto.admin.customers.groups.list.after') !!}

                <!-- Modal Form -->
                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                    ref="modalForm"
                >
                    <form
                        @submit="handleSubmit($event, updateOrCreate)"
                        ref="groupCreateForm"
                    >
                        <!-- Create Group Modal -->
                        <x-admin::modal ref="groupUpdateOrCreateModal">
                            <!-- Modal Header -->
                            <x-slot:header>
                                <p class="text-lg font-bold text-gray-800 dark:text-white">
                                    <span v-if="selectedGroups">
                                        @lang('admin::app.customers.groups.index.edit.title')
                                    </span>

                                    <span v-else>
                                        @lang('admin::app.customers.groups.index.create.title')
                                    </span>
                                </p>
                            </x-slot>

                            <!-- Modal Content -->
                            <x-slot:content>
                                <!-- Code -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.customers.groups.index.create.code')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="hidden"
                                        name="id"
                                    />

                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="code"
                                        name="code"
                                        rules="required"
                                        :label="trans('admin::app.customers.groups.index.create.code')"
                                        :placeholder="trans('admin::app.customers.groups.index.create.code')"
                                    />

                                    <x-admin::form.control-group.error control-name="code" />
                                </x-admin::form.control-group>

                                <!-- Last Name -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.customers.groups.index.create.name')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="last_name"
                                        name="name"
                                        rules="required"
                                        :label="trans('admin::app.customers.groups.index.create.name')"
                                        :placeholder="trans('admin::app.customers.groups.index.create.name')"
                                    />

                                    <x-admin::form.control-group.error control-name="name" />
                                </x-admin::form.control-group>
                            </x-slot>

                            <!-- Modal Footer -->
                            <x-slot:footer>
                                <!-- Save Button -->
                                <x-admin::button
                                    button-type="submit"
                                    class="primary-button"
                                    :title="trans('admin::app.customers.groups.index.create.save-btn')"
                                    ::loading="isLoading"
                                    ::disabled="isLoading"
                                />
                            </x-slot>
                        </x-admin::modal>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-create-group', {
                template: '#v-create-group-template',

                data() {
                    return {
                        selectedGroups: 0,

                        isLoading: false,
                    }
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
                    updateOrCreate(params, { resetForm, setErrors  }) {
                        this.isLoading = true;

                        let formData = new FormData(this.$refs.groupCreateForm);

                        if (params.id) {
                            formData.append('_method', 'put');
                        }

                        this.$axios.post(params.id ? "{{ route('admin.customers.groups.update') }}" : "{{ route('admin.customers.groups.store') }}", formData)
                            .then((response) => {
                                this.isLoading = false;

                                this.$refs.groupUpdateOrCreateModal.close();

                                this.$refs.datagrid.get();

                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                resetForm();
                            })
                            .catch(error => {
                                this.isLoading = false;

                                if (error.response.status == 422) {
                                    setErrors(error.response.data.errors);
                                }
                            });
                    },

                    editModal(value) {
                        this.$refs.groupUpdateOrCreateModal.toggle();

                        this.$refs.modalForm.setValues(value);
                    },
                }
            })
        </script>
    @endPushOnce

</x-admin::layouts>
