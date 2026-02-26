<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.registration-notifications.index.title')
    </x-slot>

    <v-registration-notifications>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.settings.registration-notifications.index.title')
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
                            @lang('admin::app.settings.registration-notifications.index.description')
                        </p>
                    </div>

                    <!-- Email Addresses -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.registration-notifications.index.emails-label')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            name="emails"
                            rules=""
                            :value="old('emails', $emails)"
                            v-model="emails"
                            :label="trans('admin::app.settings.registration-notifications.index.emails-label')"
                            :placeholder="trans('admin::app.settings.registration-notifications.index.emails-placeholder')"
                            rows="5"
                        />

                        <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.registration-notifications.index.emails-help')
                        </p>

                        <x-admin::form.control-group.error control-name="emails" />
                    </x-admin::form.control-group>

                    <!-- New Registration Emails -->
                    <x-admin::form.control-group class="mt-4">
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.registration-notifications.index.new-registration-emails-label')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            name="new_registration_emails"
                            rules=""
                            :value="old('new_registration_emails', $newRegistrationEmails ?? '')"
                            v-model="newRegistrationEmails"
                            :label="trans('admin::app.settings.registration-notifications.index.new-registration-emails-label')"
                            :placeholder="trans('admin::app.settings.registration-notifications.index.new-registration-emails-placeholder')"
                            rows="5"
                        />

                        <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.registration-notifications.index.new-registration-emails-help')
                        </p>

                        <x-admin::form.control-group.error control-name="new_registration_emails" />
                    </x-admin::form.control-group>

                    <!-- Save Button -->
                    <div class="mt-6 flex items-center justify-end gap-x-2.5">
                        <x-admin::button
                            button-type="button"
                            class="primary-button"
                            :title="trans('admin::app.settings.registration-notifications.index.save-btn')"
                            ::loading="isLoading"
                            ::disabled="isLoading"
                        />
                    </div>
                </div>
            </form>
        </x-admin::form>
    </v-registration-notifications>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-registration-notifications-template"
        >
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.registration-notifications.index.title')
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
                                @lang('admin::app.settings.registration-notifications.index.description')
                            </p>
                        </div>

                        <!-- Email Addresses -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.registration-notifications.index.emails-label')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                name="emails"
                                rules=""
                                v-model="emails"
                                :label="trans('admin::app.settings.registration-notifications.index.emails-label')"
                                :placeholder="trans('admin::app.settings.registration-notifications.index.emails-placeholder')"
                                rows="5"
                            />

                            <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.registration-notifications.index.emails-help')
                            </p>

                            <x-admin::form.control-group.error control-name="emails" />
                        </x-admin::form.control-group>

                        <!-- New Registration Emails -->
                        <x-admin::form.control-group class="mt-4">
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.registration-notifications.index.new-registration-emails-label')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                name="new_registration_emails"
                                rules=""
                                v-model="newRegistrationEmails"
                                :label="trans('admin::app.settings.registration-notifications.index.new-registration-emails-label')"
                                :placeholder="trans('admin::app.settings.registration-notifications.index.new-registration-emails-placeholder')"
                                rows="5"
                            />

                            <p class="mt-1 block text-xs italic leading-5 text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.registration-notifications.index.new-registration-emails-help')
                            </p>

                            <x-admin::form.control-group.error control-name="new_registration_emails" />
                        </x-admin::form.control-group>

                        <!-- Save Button -->
                        <div class="mt-6 flex items-center justify-end gap-x-2.5">
                            <x-admin::button
                                button-type="button"
                                class="primary-button"
                                :title="trans('admin::app.settings.registration-notifications.index.save-btn')"
                                ::loading="isLoading"
                                ::disabled="isLoading"
                            />
                        </div>
                    </div>
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-registration-notifications', {
                template: '#v-registration-notifications-template',

                data() {
                    return {
                        emails: @json($emails),
                        newRegistrationEmails: @json($newRegistrationEmails ?? ''),

                        isLoading: false,
                    };
                },

                methods: {
                    updateSettings(params, { resetForm, setErrors }) {
                        this.isLoading = true;

                        let formData = new FormData(this.$refs.settingsForm);
                        formData.append('_method', 'put');

                        this.$axios.post("{{ route('admin.settings.registration-notifications.update') }}", formData)
                            .then((response) => {
                                this.isLoading = false;

                                // Update values from server response if provided
                                if (response.data.emails !== undefined) {
                                    this.emails = response.data.emails;
                                }
                                if (response.data.new_registration_emails !== undefined) {
                                    this.newRegistrationEmails = response.data.new_registration_emails;
                                }

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
