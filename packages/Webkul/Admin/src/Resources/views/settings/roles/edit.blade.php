<x-admin::layouts>

    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.roles.edit.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.settings.roles.edit.before', ['role' => $role]) !!}

    <x-admin::form
        method="PUT"
        :action="route('admin.settings.roles.update', $role->id)"
    >

        {!! view_render_event('bagisto.admin.settings.roles.edit.edit_form_controls.before', ['role' => $role]) !!}

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); box-shadow: 0 4px 15px rgba(244,63,94,0.3); min-width:44px;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.roles.edit.title')
                    </p>
                    <p class="text-xs text-gray-400">Редактирование роли</p>
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.settings.roles.index') }}"
                    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #6b7280; background: #f3f4f6; transition: all 0.15s; text-decoration: none;"
                    onmouseenter="this.style.background='#e5e7eb'"
                    onmouseleave="this.style.background='#f3f4f6'"
                >
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    @lang('admin::app.settings.roles.edit.back-btn')
                </a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('admin::app.settings.roles.edit.save-btn')
                </button>
            </div>
        </div>

        <!-- body content -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left sub-component -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                {!! view_render_event('bagisto.admin.settings.roles.edit.card.access-control.before', ['role' => $role]) !!}

                <!-- Access Control Input Fields -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.roles.edit.access-control')
                    </p>

                    <!-- Edit Role for  -->
                    <v-access-control>
                        <!-- Shimmer Effect -->
                        <div class="mb-4">
                            <div class="shimmer mb-1.5 h-4 w-24"></div>

                            <div class="custom-select h-11 w-full rounded-md border bg-white px-3 py-2.5 text-sm font-normal text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"></div>
                        </div>

                        <!-- Roles Checkbox -->
                        <x-admin::shimmer.tree />
                    </v-access-control>
                </div>

                {!! view_render_event('bagisto.admin.settings.roles.edit.card.access-control.after', ['role' => $role]) !!}

            </div>

            <!-- Right sub-component -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">

                {!! view_render_event('bagisto.admin.settings.roles.edit.card.accordion.general.before', ['role' => $role]) !!}

                <x-admin::accordion>
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.roles.edit.general')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <!-- Name -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.roles.edit.name')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="name"
                                name="name"
                                rules="required"
                                value="{{ old('name') ?: $role->name }}"
                                :label="trans('admin::app.settings.roles.edit.name')"
                                :placeholder="trans('admin::app.settings.roles.edit.name')"
                            />

                            <x-admin::form.control-group.error control-name="name" />
                        </x-admin::form.control-group>

                        <!-- Description -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.roles.edit.description')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                id="description"
                                name="description"
                                rules="required"
                                value="{{ old('description') ?: $role->description }}"
                                :label="trans('admin::app.settings.roles.edit.description')"
                                :placeholder="trans('admin::app.settings.roles.edit.description')"
                            />

                            <x-admin::form.control-group.error control-name="description" />
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>

                {!! view_render_event('bagisto.admin.settings.roles.edit.card.accordion.general.after', ['role' => $role]) !!}

            </div>
        </div>

        {!! view_render_event('bagisto.admin.settings.roles.edit.edit_form_controls.after', ['role' => $role]) !!}

    </x-admin::form>

    {!! view_render_event('bagisto.admin.settings.roles.edit.after', ['role' => $role]) !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-access-control-template"
        >
            <div>
                <!-- Permission Type -->
                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('admin::app.settings.roles.edit.permissions')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="select"
                        id="permission_type"
                        name="permission_type"
                        v-model="permission_type"
                        :label="trans('admin::app.settings.roles.edit.permissions')"
                        :placeholder="trans('admin::app.settings.roles.edit.permissions')"
                    >
                        <option value="custom">
                            @lang('admin::app.settings.roles.edit.custom')
                        </option>

                        <option value="all">
                            @lang('admin::app.settings.roles.edit.all')
                        </option>
                    </x-admin::form.control-group.control>

                    <x-admin::form.control-group.error control-name="permission_type" />
                </x-admin::form.control-group>
                
                <!-- Tree structure -->
                <div v-if="permission_type == 'custom'">
                    <x-admin::tree.view
                        input-type="checkbox"
                        value-field="key"
                        id-field="key"
                        :items="json_encode(acl()->getItems())"
                        :value="json_encode($role->permissions ?? [])"
                        :fallback-locale="config('app.fallback_locale')"
                    />
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-access-control', {
                template: '#v-access-control-template',

                data() {
                    return {
                        permission_type: "{{ $role->permission_type }}"
                    };
                }
            })
        </script>
    @endPushOnce
</x-admin::layouts>
