<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.marketing.communications.subscribers.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%); box-shadow: 0 4px 15px rgba(168,85,247,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.marketing.communications.subscribers.index.title')
                </p>
                <p class="text-xs text-gray-400">Подписчики</p>
            </div>
        </div>
    </div>

    <v-subscribers>
        <!-- DataGrid Shimmer -->
        <x-admin::shimmer.datagrid />
    </v-subscribers>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-subscribers-template"
        >
            <div>
                <x-admin::datagrid
                    :src="route('admin.marketing.communications.subscribers.index')"
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
                            <!-- Id -->
                            <p>@{{ record.id }}</p>

                            <!-- Status -->
                            <p>@{{ record.status }}</p>

                            <!-- Email -->
                            <p>@{{ record.email }}</p>

                                <!-- Actions -->
                                <div class="flex justify-end">
                                    @if (bouncer()->hasPermission('marketing.communications.subscribers.edit'))
                                        <a @click="editModal(record.actions.find(action => action.index === 'edit')?.url)">
                                            <span
                                                :class="record.actions.find(action => action.index === 'edit')?.icon"
                                                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                            >
                                            </span>
                                        </a>
                                    @endif

                                    @if (bouncer()->hasPermission('marketing.communications.subscribers.delete'))
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

                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                    ref="modalForm"
                >
                    <form
                        @submit="handleSubmit($event, update)"
                        ref="subscriberCreateForm"
                    >
                        <!-- Create Group Modal -->
                        <x-admin::modal ref="groupCreateModal">
                            <!-- Modal Header -->
                            <x-slot:header>
                                <p class="text-lg font-bold text-gray-800 dark:text-white">
                                    @lang('admin::app.marketing.communications.subscribers.index.edit.title')
                                </p>
                            </x-slot>

                            <!-- Modal Content -->
                            <x-slot:content>
                                <!-- ID -->
                                <x-admin::form.control-group.control
                                    type="hidden"
                                    name="id"
                                />

                                <!-- Email -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.marketing.communications.subscribers.index.edit.email')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="hidden"
                                        name="id"
                                        v-model="selectedSubscriber.id"
                                    />

                                    <x-admin::form.control-group.control
                                        type="text"
                                        class="mb-1 cursor-not-allowed"
                                        name="email"
                                        rules="required"
                                        :value="old('email')"
                                        v-model="selectedSubscriber.email"
                                        :label="trans('admin::app.marketing.communications.subscribers.index.edit.email')"
                                        disabled
                                    />

                                    <x-admin::form.control-group.error control-name="email" />
                                </x-admin::form.control-group>

                                <!-- Subscribed -->
                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.marketing.communications.subscribers.index.edit.subscribed')
                                    </x-admin::form.control-group.label>

                                    @php
                                        $selectedOption = old('status');
                                    @endphp

                                    <x-admin::form.control-group.control
                                        type="select"
                                        class="mb-1 cursor-pointer"
                                        name="is_subscribed"
                                        rules="required"
                                        v-model="selectedSubscriber.is_subscribed"
                                        :label="trans('admin::app.marketing.communications.subscribers.index.edit.subscribed')"
                                    >
                                        @foreach (['true', 'false'] as $state)
                                            <option
                                                value="{{ $state == 'true' ? 1 : 0 }}"
                                            >
                                                @lang('admin::app.marketing.communications.subscribers.index.edit.' . $state)
                                            </option>
                                        @endforeach
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="is_subscribed" />
                                </x-admin::form.control-group>
                            </x-slot>

                            <!-- Modal Footer -->
                            <x-slot:footer>
                                <!-- Save Button -->
                                <x-admin::button
                                    button-type="submit"
                                    class="primary-button"
                                    :title="trans('admin::app.marketing.communications.subscribers.index.edit.save-btn')"
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
            app.component('v-subscribers', {
                template: '#v-subscribers-template',

                data() {
                    return {
                        selectedSubscriber: {},

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
                    update(params, { resetForm, setErrors  }) {
                        this.isLoading = true;

                        let formData = new FormData(this.$refs.subscriberCreateForm);

                        formData.append('_method', 'put');

                        this.$axios.post("{{ route('admin.marketing.communications.subscribers.update') }}", formData)
                        .then((response) => {
                            this.isLoading = false;

                            this.$refs.groupCreateModal.close();

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

                    editModal(url) {
                        this.$axios.get(url)
                            .then((response) => {
                                this.selectedSubscriber = response.data.data;

                                this.$refs.groupCreateModal.toggle();
                            })
                            .catch(error => this.$emitter.emit('add-flash', {
                                type: 'error', message: error.response.data.message
                            }));
                    }
                }
            })
        </script>
    @endPushOnce
</x-admin::layouts>
