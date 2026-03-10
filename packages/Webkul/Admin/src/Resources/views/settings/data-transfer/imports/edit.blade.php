<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.data-transfer.imports.edit.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.settings.data_transfer.imports.create.before', ['import' => $import]) !!}

    <x-admin::form
        :action="route('admin.settings.data_transfer.imports.update', $import->id)"
        method="PUT"
        enctype="multipart/form-data"
    >
        {!! view_render_event('bagisto.admin.settings.data_transfer.imports.create.create_form_controls.before', ['import' => $import]) !!}

        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); box-shadow: 0 4px 15px rgba(99,102,241,0.3); min-width:44px;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.data-transfer.imports.edit.title')
                    </p>
                    <p class="text-xs text-gray-400">Редактирование импорта</p>
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
                    @lang('admin::app.settings.data-transfer.imports.edit.back-btn')
                </a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('admin::app.settings.data-transfer.imports.edit.save-btn')
                </button>
            </div>
        </div>

        <!-- Body Content -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Container -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                {!! view_render_event('bagisto.admin.settings.data_transfer.imports.create.card.general.before', ['import' => $import]) !!}

                <!-- Setup Import Panel -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.data-transfer.imports.edit.general')
                    </p>

                    <!-- Type -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.data-transfer.imports.edit.type')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="type"
                            id="type"
                            :value="old('type') ?? $import->type"
                            ref="importType"
                            rules="required"
                            :label="trans('admin::app.settings.data-transfer.imports.edit.type')"
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
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.data-transfer.imports.edit.file')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="file"
                            name="file"
                            :label="trans('admin::app.settings.data-transfer.imports.edit.file')"
                        />

                        <!-- Display Existing File -->
                        @if(isset($import) && $import->file_path)
                            <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.data-transfer.imports.edit.current-file'):
                                <a 
                                    href="{{ route('admin.settings.data_transfer.imports.download', $import->id) }}" 
                                    class="cursor-pointer text-sm text-blue-600 transition-all hover:underline"
                                    target="_blank"
                                >
                                    {{ basename($import->file_path) }}
                                </a>
                            </div>
                        @endif

                        <x-admin::form.control-group.error control-name="file" />
                    </x-admin::form.control-group>

                    <!-- Images Directory Path -->
                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.data-transfer.imports.edit.images-directory')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="images_directory_path"
                            :value="old('images_directory_path') ?? $import->images_directory_path"
                            :placeholder="trans('admin::app.settings.data-transfer.imports.edit.images-directory')"
                        />

                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.data-transfer.imports.edit.file-info')
                        </p>

                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.data-transfer.imports.edit.file-info-example')
                        </p>
                    </x-admin::form.control-group>
                </div>

                {!! view_render_event('bagisto.admin.settings.data_transfer.imports.create.card.general.after', ['import' => $import]) !!}
            </div>

            <!-- Right Container -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                {!! view_render_event('bagisto.admin.settings.data_transfer.imports.create.card.accordion.settings.before', ['import' => $import]) !!}

                <!-- Settings Panel -->
                <x-admin::accordion>
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.data-transfer.imports.edit.settings')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <!-- Action -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.data-transfer.imports.edit.action')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="action"
                                id="action"
                                :value="old('action') ?? $import->action"
                                rules="required"
                                :label="trans('admin::app.settings.data-transfer.imports.edit.action')"
                            >
                                <option value="append">@lang('admin::app.settings.data-transfer.imports.edit.create-update')</option>
                                <option value="delete">@lang('admin::app.settings.data-transfer.imports.edit.delete')</option>
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="action" />
                        </x-admin::form.control-group>

                        <!-- Validation Strategy -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.data-transfer.imports.edit.validation-strategy')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="validation_strategy"
                                id="validation_strategy"
                                :value="old('validation_strategy') ?? $import->validation_strategy"
                                rules="required"
                                :label="trans('admin::app.settings.data-transfer.imports.edit.validation-strategy')"
                            >
                                <option value="stop-on-errors">@lang('admin::app.settings.data-transfer.imports.edit.stop-on-errors')</option>
                                <option value="skip-erros">@lang('admin::app.settings.data-transfer.imports.edit.skip-errors')</option>
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="validation_strategy" />
                        </x-admin::form.control-group>

                        <!-- Allowed Errors -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.data-transfer.imports.edit.allowed-errors')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="allowed_errors"
                                :value="old('allowed_errors') ?? $import->allowed_errors"
                                rules="required"
                                :label="trans('admin::app.settings.data-transfer.imports.edit.allowed-errors')"
                                :placeholder="trans('admin::app.settings.data-transfer.imports.edit.allowed-errors')"
                            />

                            <x-admin::form.control-group.error control-name="allowed_errors" />
                        </x-admin::form.control-group>

                        <!-- CSV Field Separator -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.data-transfer.imports.edit.field-separator')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="field_separator"
                                :value="old('field_separator') ?? $import->field_separator"
                                rules="required"
                                :label="trans('admin::app.settings.data-transfer.imports.edit.field-separator')"
                                :placeholder="trans('admin::app.settings.data-transfer.imports.edit.field-separator')"
                            />

                            <x-admin::form.control-group.error control-name="field_separator" />
                        </x-admin::form.control-group>

                        <!-- Process In Queue -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.data-transfer.imports.edit.process-in-queue')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="switch"
                                name="process_in_queue"
                                :value="1"
                                :checked="(boolean) $import->process_in_queue"
                            />

                            <x-admin::form.control-group.error control-name="process_in_queue" />
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>

                {!! view_render_event('bagisto.admin.settings.data_transfer.imports.create.card.accordion.settings.after', ['import' => $import]) !!}
            </div>
        </div>

        {!! view_render_event('bagisto.admin.settings.data_transfer.imports.create.create_form_controls.after', ['import' => $import]) !!}
    </x-admin::form>
</x-admin::layouts>
