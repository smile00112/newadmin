<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.marketing.communications.templates.index.title')
    </x-slot>

    <v-email-templates>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.marketing.communications.templates.index.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                @if (bouncer()->hasPermission('marketing.communications.email_templates.create'))
                    <button type="button" class="primary-button">
                        @lang('admin::app.marketing.communications.templates.index.create-btn')
                    </button>
                @endif
            </div>
        </div>

        <!-- DataGrid Shimmer -->
        <x-admin::shimmer.datagrid />
    </v-email-templates>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-email-templates-template"
        >
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.marketing.communications.templates.index.title')
                </p>

                <div class="flex items-center gap-x-2.5">
                    @if (bouncer()->hasPermission('marketing.communications.email_templates.create'))
                        <button
                            type="button"
                            class="primary-button"
                            @click="selectedTemplate=0; resetForm(); $refs.templateModal.toggle()"
                        >
                            @lang('admin::app.marketing.communications.templates.index.create-btn')
                        </button>
                    @endif
                </div>
            </div>

            {!! view_render_event('bagisto.admin.marketing.communications.templates.list.before') !!}

            <x-admin::datagrid
                :src="route('admin.marketing.communications.email_templates.index')"
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
                            <p>@{{ record.name }}</p>
                            <p>@{{ record.status }}</p>

                            <!-- Actions -->
                            <div class="flex justify-end">
                                @if (bouncer()->hasPermission('marketing.communications.email_templates.edit'))
                                    <a @click="selectedTemplate=1; editModal(record.actions.find(action => action.index === 'edit')?.url)">
                                        <span
                                            :class="record.actions.find(action => action.index === 'edit')?.icon"
                                            class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                        >
                                        </span>
                                    </a>
                                @endif

                                @if (bouncer()->hasPermission('marketing.communications.email_templates.delete'))
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

            {!! view_render_event('bagisto.admin.marketing.communications.templates.list.after') !!}

            <!-- Email Template Create/Edit Modal -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form
                    @submit="handleSubmit($event, updateOrCreate)"
                    ref="templateForm"
                >
                    <x-admin::modal ref="templateModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                <span v-if="selectedTemplate">
                                    @lang('admin::app.marketing.communications.templates.edit.title')
                                </span>

                                <span v-else>
                                    @lang('admin::app.marketing.communications.templates.create.title')
                                </span>
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>
                            <x-admin::form.control-group.control
                                type="hidden"
                                name="id"
                                v-model="template.id"
                            />

                            <!-- Name -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.marketing.communications.templates.create.name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="name"
                                    rules="required"
                                    v-model="template.name"
                                    :label="trans('admin::app.marketing.communications.templates.create.name')"
                                    :placeholder="trans('admin::app.marketing.communications.templates.create.name')"
                                />

                                <x-admin::form.control-group.error control-name="name" />
                            </x-admin::form.control-group>

                            <!-- Status -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.marketing.communications.templates.create.status')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="status"
                                    rules="required"
                                    v-model="template.status"
                                    :label="trans('admin::app.marketing.communications.templates.create.status')"
                                >
                                    <option value="">
                                        @lang('admin::app.marketing.communications.templates.create.select-status')
                                    </option>

                                    <option value="active">
                                        @lang('admin::app.marketing.communications.templates.create.active')
                                    </option>

                                    <option value="inactive">
                                        @lang('admin::app.marketing.communications.templates.create.inactive')
                                    </option>

                                    <option value="draft">
                                        @lang('admin::app.marketing.communications.templates.create.draft')
                                    </option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="status" />
                            </x-admin::form.control-group>

                            <!-- Content -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.marketing.communications.templates.create.content')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    id="content"
                                    name="content"
                                    rules="required"
                                    v-model="template.content"
                                    :label="trans('admin::app.marketing.communications.templates.create.content')"
                                    :placeholder="trans('admin::app.marketing.communications.templates.create.content')"
                                    :tinymce="true"
                                />

                                <x-admin::form.control-group.error control-name="content" />
                            </x-admin::form.control-group>
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <x-admin::button
                                button-type="button"
                                class="primary-button"
                                :title="trans('admin::app.marketing.communications.templates.create.save-btn')"
                                ::loading="isLoading"
                                ::disabled="isLoading"
                            />
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-email-templates', {
                template: '#v-email-templates-template',

                data() {
                    return {
                        template: {
                            name: '',
                            status: '',
                            content: '',
                        },

                        isLoading: false,

                        selectedTemplate: 0,
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

                        let formData = new FormData(this.$refs.templateForm);

                        if (params.id) {
                            formData.append('_method', 'put');
                        }

                        this.$axios.post(
                            params.id
                                ? "{{ route('admin.marketing.communications.email_templates.update', '__REPLACE_ID__') }}".replace('__REPLACE_ID__', params.id)
                                : "{{ route('admin.marketing.communications.email_templates.store') }}",
                            formData
                        )
                        .then((response) => {
                            this.isLoading = false;

                            this.$refs.templateModal.close();

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

                                this.template = {
                                    ...data,
                                };

                                this.$refs.templateModal.toggle();
                            });
                    },

                    resetForm() {
                        this.template = {
                            name: '',
                            status: '',
                            content: '',
                        };
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
