<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.data-transfer.imports.create.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.settings.data_transfer.imports.create.before') !!}

    <x-admin::form
        :action="route('admin.settings.data_transfer.imports.store')"
        enctype="multipart/form-data"
    >
        {!! view_render_event('bagisto.admin.settings.data_transfer.imports.create.create_form_controls.before') !!}

        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); box-shadow: 0 4px 15px rgba(99,102,241,0.3); min-width:44px;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.data-transfer.imports.create.title')
                    </p>
                    <p class="text-xs text-gray-400">Новый импорт данных</p>
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.settings.data_transfer.imports.index') }}"
                    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #6b7280; background: #f3f4f6; transition: all 0.15s; text-decoration: none;"
                    onmouseenter="this.style.background='#e5e7eb'"
                    onmouseleave="this.style.background='#f3f4f6'"
                >
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    @lang('admin::app.settings.data-transfer.imports.create.back-btn')
                </a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('admin::app.settings.data-transfer.imports.create.save-btn')
                </button>
            </div>
        </div>

        <!-- Body Content -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Container -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                {!! view_render_event('bagisto.admin.settings.data_transfer.imports.create.card.general.before') !!}

                <!-- Setup Import Panel -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.data-transfer.imports.create.general')
                    </p>

                    <!-- Type -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.data-transfer.imports.create.type')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="type"
                            id="import-type"
                            :value="old('type') ?? 'products'"
                            ref="importType"
                            rules="required"
                            :label="trans('admin::app.settings.data-transfer.imports.create.type')"
                        >
                            @foreach (config('importers') as $code => $importer)
                                <option value="{{ $code }}">@lang($importer['title'])</option>
                            @endforeach
                        </x-admin::form.control-group.control>

                        <!-- Source Sample Download Links -->
                        <div class="flex items-center mt-2.5">
                            <span>
                                @lang('admin::app.settings.data-transfer.imports.create.download-sample')
                            </span>

                            <x-admin::dropdown>
                                <x-slot:toggle>
                                    <span class="cursor-pointer text-2xl icon-arrow-down"></span>
                                </x-slot>

                                <x-slot:content>
                                    <div class="grid gap-2.5 max-md:my-0">
                                        @foreach ($supportedFormats as $format)
                                            <a
                                                :href="'{{ route('admin.settings.data_transfer.imports.download_sample', ['type' => ':type:', 'format' => ':format:']) }}'.replace(':type:', $refs['importType']?.value).replace(':format:', '{{ $format }}')"
                                                target="_blank"
                                                id="source-sample-link"
                                                class="cursor-pointer text-sm text-blue-600 transition-all hover:underline"
                                            >
                                                {{ strtoupper($format) }}
                                            </a>
                                        @endforeach
                                    </div>
                                </x-slot>
                            </x-admin::dropdown>
                        </div>

                        <x-admin::form.control-group.error control-name="type" />
                    </x-admin::form.control-group>

                    <!-- Images Directory Path -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.data-transfer.imports.create.file')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="file"
                            name="file"
                            rules="required"
                            :label="trans('admin::app.settings.data-transfer.imports.create.file')"
                        />

                        <x-admin::form.control-group.error control-name="file" />
                    </x-admin::form.control-group>

                    <!-- Images Directory Path -->
                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.data-transfer.imports.create.images-directory')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="images_directory_path"
                            :value="old('images_directory_path')"
                            :placeholder="trans('admin::app.settings.data-transfer.imports.create.images-directory')"
                        />

                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.data-transfer.imports.create.file-info')
                        </p>

                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.data-transfer.imports.create.file-info-example')
                        </p>
                    </x-admin::form.control-group>
                </div>

                {!! view_render_event('bagisto.admin.settings.data_transfer.imports.create.card.general.after') !!}
            </div>

            <!-- Right Container -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                {!! view_render_event('bagisto.admin.settings.data_transfer.imports.create.card.accordion.settings.before') !!}

                <!-- Settings Panel -->
                <x-admin::accordion>
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.data-transfer.imports.create.settings')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <!-- Action -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.data-transfer.imports.create.action')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="action"
                                id="action"
                                :value="old('action') ?? 'append'"
                                rules="required"
                                :label="trans('admin::app.settings.data-transfer.imports.create.action')"
                            >
                                <option value="append">@lang('admin::app.settings.data-transfer.imports.create.create-update')</option>
                                <option value="delete">@lang('admin::app.settings.data-transfer.imports.create.delete')</option>
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="action" />
                        </x-admin::form.control-group>

                        <!-- Validation Strategy -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.data-transfer.imports.create.validation-strategy')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="validation_strategy"
                                id="validation_strategy"
                                :value="old('validation_strategy') ?? 'stop-on-errors'"
                                rules="required"
                                :label="trans('admin::app.settings.data-transfer.imports.create.validation-strategy')"
                            >
                                <option value="stop-on-errors">@lang('admin::app.settings.data-transfer.imports.create.stop-on-errors')</option>
                                <option value="skip-erros">@lang('admin::app.settings.data-transfer.imports.create.skip-errors')</option>
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="validation_strategy" />
                        </x-admin::form.control-group>

                        <!-- Allowed Errors -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.data-transfer.imports.create.allowed-errors')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="allowed_errors"
                                :value="old('allowed_errors') ?? 10"
                                rules="required"
                                :label="trans('admin::app.settings.data-transfer.imports.create.allowed-errors')"
                                :placeholder="trans('admin::app.settings.data-transfer.imports.create.allowed-errors')"
                            />

                            <x-admin::form.control-group.error control-name="allowed_errors" />
                        </x-admin::form.control-group>

                        <!-- CSV Field Separator -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.data-transfer.imports.create.field-separator')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="field_separator"
                                :value="old('field_separator') ?? ','"
                                rules="required"
                                :label="trans('admin::app.settings.data-transfer.imports.create.field-separator')"
                                :placeholder="trans('admin::app.settings.data-transfer.imports.create.field-separator')"
                            />

                            <x-admin::form.control-group.error control-name="field_separator" />
                        </x-admin::form.control-group>

                        <!-- Process In Queue -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.data-transfer.imports.create.process-in-queue')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="switch"
                                name="process_in_queue"
                                id="maintenance-mode-status"
                                :checked="false"
                            />

                            <x-admin::form.control-group.error control-name="process_in_queue" />
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>

                {!! view_render_event('bagisto.admin.settings.data_transfer.imports.create.card.accordion.settings.after') !!}
            </div>
        </div>

        {!! view_render_event('bagisto.admin.settings.data_transfer.imports.create.create_form_controls.after') !!}
    </x-admin::form>
</x-admin::layouts>
