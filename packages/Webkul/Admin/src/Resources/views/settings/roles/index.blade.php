<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.roles.index.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.settings.roles.list.before') !!}

    {{-- Hidden: register tree view JS components --}}
    <div class="hidden">
        <x-admin::tree.view
            input-type="checkbox"
            :items="json_encode([])"
        />
    </div>

    <v-roles>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.settings.roles.index.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                @if (bouncer()->hasPermission('settings.roles.create'))
                    <button
                        type="button"
                        class="primary-button"
                    >
                        @lang('admin::app.settings.roles.index.create-btn')
                    </button>
                @endif
            </div>
        </div>

        <!-- DataGrid Shimmer -->
        <x-admin::shimmer.datagrid />
    </v-roles>

    {!! view_render_event('bagisto.admin.settings.roles.list.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-roles-template"
        >
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.roles.index.title')
                </p>

                <div class="flex items-center gap-x-2.5">
                    @if (bouncer()->hasPermission('settings.roles.create'))
                        <button
                            type="button"
                            class="primary-button"
                            @click="selectedRole=0; resetForm(); $refs.roleModal.toggle()"
                        >
                            @lang('admin::app.settings.roles.index.create-btn')
                        </button>
                    @endif
                </div>
            </div>

            <x-admin::datagrid
                :src="route('admin.settings.roles.index')"
                ref="datagrid"
            >
                <!-- DataGrid Body -->
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

                            <!-- Name -->
                            <p>@{{ record.name }}</p>

                            <!-- Permission Type -->
                            <p>@{{ record.permission_type }}</p>

                            <!-- Actions -->
                            <div class="flex justify-end">
                                @if (bouncer()->hasPermission('settings.roles.edit'))
                                    <a @click="selectedRole=1; editModal(record.actions.find(action => action.index === 'edit')?.url)">
                                        <span
                                            :class="record.actions.find(action => action.index === 'edit')?.icon"
                                            class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                        >
                                        </span>
                                    </a>
                                @endif

                                @if (bouncer()->hasPermission('settings.roles.delete'))
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

            <!-- Role Create/Edit Modal -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form
                    @submit="handleSubmit($event, updateOrCreate)"
                    ref="roleForm"
                >
                    <x-admin::modal ref="roleModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                <span v-if="selectedRole">
                                    @lang('admin::app.settings.roles.edit.title')
                                </span>

                                <span v-else>
                                    @lang('admin::app.settings.roles.create.title')
                                </span>
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>
                            <x-admin::form.control-group.control
                                type="hidden"
                                name="id"
                                v-model="role.id"
                            />

                            <!-- Name -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.roles.create.name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="name"
                                    name="name"
                                    rules="required"
                                    v-model="role.name"
                                    :label="trans('admin::app.settings.roles.create.name')"
                                    :placeholder="trans('admin::app.settings.roles.create.name')"
                                />

                                <x-admin::form.control-group.error control-name="name" />
                            </x-admin::form.control-group>

                            <!-- Description -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.roles.create.description')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    id="description"
                                    name="description"
                                    rules="required"
                                    v-model="role.description"
                                    :label="trans('admin::app.settings.roles.create.description')"
                                    :placeholder="trans('admin::app.settings.roles.create.description')"
                                />

                                <x-admin::form.control-group.error control-name="description" />
                            </x-admin::form.control-group>

                            <!-- Permission Type -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.roles.create.permissions')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="permission_type"
                                    name="permission_type"
                                    rules="required"
                                    v-model="role.permission_type"
                                    :label="trans('admin::app.settings.roles.create.permissions')"
                                >
                                    <option value="custom">
                                        @lang('admin::app.settings.roles.create.custom')
                                    </option>

                                    <option value="all">
                                        @lang('admin::app.settings.roles.create.all')
                                    </option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="permission_type" />
                            </x-admin::form.control-group>

                            <!-- Permissions Tree (shown only for custom) -->
                            <div
                                v-if="role.permission_type == 'custom'"
                                class="max-h-[300px] overflow-y-auto rounded border border-gray-200 p-2 dark:border-gray-800"
                            >
                                <x-admin::form.control-group.error control-name="permissions" />

                                <v-tree-view
                                    input-type="checkbox"
                                    value-field="key"
                                    id-field="key"
                                    name-field="permissions"
                                    items='@json(acl()->getItems())'
                                    :value="JSON.stringify(role.permissions || [])"
                                    fallback-locale="{{ config('app.fallback_locale') }}"
                                    :key="treeKey"
                                ></v-tree-view>
                            </div>
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <x-admin::button
                                button-type="button"
                                class="primary-button"
                                :title="trans('admin::app.settings.roles.create.save-btn')"
                                ::loading="isLoading"
                                ::disabled="isLoading"
                            />
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-roles', {
                template: '#v-roles-template',

                data() {
                    return {
                        role: {
                            permission_type: 'custom',
                            permissions: [],
                        },

                        isLoading: false,

                        selectedRole: 0,

                        treeKey: 0,
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
                    updateOrCreate(params, { resetForm, setErrors }) {
                        this.isLoading = true;

                        let formData = new FormData(this.$refs.roleForm);

                        if (params.id) {
                            formData.append('_method', 'put');
                        }

                        this.$axios.post(
                            params.id
                                ? "{{ route('admin.settings.roles.update', '__REPLACE_ID__') }}".replace('__REPLACE_ID__', params.id)
                                : "{{ route('admin.settings.roles.store') }}",
                            formData
                        )
                        .then((response) => {
                            this.isLoading = false;

                            this.$refs.roleModal.close();

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
                                this.role = {
                                    ...response.data.data,
                                    permissions: response.data.data.permissions || [],
                                };

                                this.treeKey++;

                                this.$refs.roleModal.toggle();
                            });
                    },

                    resetForm() {
                        this.role = {
                            permission_type: 'custom',
                            permissions: [],
                        };

                        this.treeKey++;
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>