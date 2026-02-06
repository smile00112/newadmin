<x-admin::layouts>
    <x-slot:title>
        @lang('mobile_app::app.settings.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('mobile_app::app.settings.title')
        </p>
    </div>

    <div class="mt-7 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <x-admin::form
                :action="route('admin.mobile_app.settings.store')"
                method="POST"
            >
                <input type="hidden" name="channel_code" value="{{ $channelCode }}">

                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('mobile_app::app.settings.general.title')
                    </p>

                    @php
                        $contactFields = [];
                        $documentFields = [];
                        $otherFields = [];
                        foreach ($fields as $field) {
                            if (isset($field['group']) && $field['group'] === 'contact') {
                                $contactFields[] = $field;
                            } elseif (isset($field['group']) && $field['group'] === 'documents') {
                                $documentFields[] = $field;
                            } else {
                                $otherFields[] = $field;
                            }
                        }
                    @endphp

                    @foreach ($otherFields as $field)
                        <div class="mb-4">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    {{ trans($field['title']) }}
                                </x-admin::form.control-group.label>

                                @if ($field['type'] === 'text')
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="settings[{{ $field['key'] }}]"
                                        :value="$field['value'] ?? ''"
                                        :placeholder="trans($field['title'])"
                                    />
                                @elseif ($field['type'] === 'textarea')
                                    <x-admin::form.control-group.control
                                        type="textarea"
                                        name="settings[{{ $field['key'] }}]"
                                        :value="$field['value'] ?? ''"
                                        :placeholder="trans($field['title'])"
                                        rows="5"
                                    />
                                @elseif ($field['type'] === 'boolean')
                                    <x-admin::form.control-group.control
                                        type="switch"
                                        name="settings[{{ $field['key'] }}]"
                                        :value="1"
                                        :checked="(bool) ($field['value'] ?? false)"
                                    />
                                @elseif ($field['type'] === 'select' && isset($field['options']))
                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="settings[{{ $field['key'] }}]"
                                        :value="$field['value'] ?? ''"
                                    >
                                        <option value="">{{ __('admin::app.common.select') }}</option>
                                        @foreach ($field['options'] as $option)
                                            <option 
                                                value="{{ $option['value'] }}"
                                                {{ ($field['value'] ?? '') == $option['value'] ? 'selected' : '' }}
                                            >
                                                {{ $option['title'] }}
                                            </option>
                                        @endforeach
                                    </x-admin::form.control-group.control>
                                @elseif ($field['type'] === 'multiselect' && isset($field['options']))
                                    <select
                                        name="settings[{{ $field['key'] }}][]"
                                        class="custom-select w-full rounded border border-gray-200 px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                        multiple
                                    >
                                        @php
                                            $selectedValues = is_array($field['value']) ? $field['value'] : [];
                                        @endphp
                                        @foreach ($field['options'] as $option)
                                            <option 
                                                value="{{ $option['value'] }}"
                                                {{ in_array($option['value'], $selectedValues) ? 'selected' : '' }}
                                            >
                                                {{ $option['title'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif

                                @if (isset($field['description']))
                                    <x-admin::form.control-group.error control-name="settings[{{ $field['key'] }}]" />
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ trans($field['description']) }}
                                    </p>
                                @endif
                            </x-admin::form.control-group>
                        </div>
                    @endforeach

                    @if (count($contactFields) > 0)
                        {{-- Contact Links Group --}}
                        <div class="mt-6 mb-4 rounded-lg border-2 border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                            <p class="mb-4 text-base font-semibold text-blue-800 dark:text-blue-300">
                                @lang('mobile_app::app.settings.general.contact-us.title')
                            </p>
                            <p class="mb-4 text-sm text-blue-700 dark:text-blue-400">
                                @lang('mobile_app::app.settings.general.contact-us.info')
                            </p>

                            @foreach ($contactFields as $field)
                                <div class="mb-4">
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label>
                                            {{ trans($field['title']) }}
                                        </x-admin::form.control-group.label>

                                        @if ($field['type'] === 'text')
                                            <x-admin::form.control-group.control
                                                type="text"
                                                name="settings[{{ $field['key'] }}]"
                                                :value="$field['value'] ?? ''"
                                                :placeholder="trans($field['title'])"
                                            />
                                        @endif

                                        @if (isset($field['description']))
                                            <x-admin::form.control-group.error control-name="settings[{{ $field['key'] }}]" />
                                            <p class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                                                {{ trans($field['description']) }}
                                            </p>
                                        @endif
                                    </x-admin::form.control-group>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if (count($documentFields) > 0)
                        {{-- Document Links Group --}}
                        <div class="mt-6 mb-4 rounded-lg border-2 border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                            <p class="mb-4 text-base font-semibold text-green-800 dark:text-green-300">
                                @lang('mobile_app::app.settings.general.documents.title')
                            </p>
                            <p class="mb-4 text-sm text-green-700 dark:text-green-400">
                                @lang('mobile_app::app.settings.general.documents.info')
                            </p>

                            @foreach ($documentFields as $field)
                                <div class="mb-4">
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label>
                                            {{ trans($field['title']) }}
                                        </x-admin::form.control-group.label>

                                        @if ($field['type'] === 'select' && isset($field['options']))
                                            <x-admin::form.control-group.control
                                                type="select"
                                                name="settings[{{ $field['key'] }}]"
                                                :value="$field['value'] ?? ''"
                                            >
                                                <option value="">{{ __('admin::app.common.select') }}</option>
                                                @foreach ($field['options'] as $option)
                                                    <option 
                                                        value="{{ $option['value'] }}"
                                                        {{ ($field['value'] ?? '') == $option['value'] ? 'selected' : '' }}
                                                    >
                                                        {{ $option['title'] }}
                                                    </option>
                                                @endforeach
                                            </x-admin::form.control-group.control>
                                        @endif

                                        @if (isset($field['description']))
                                            <x-admin::form.control-group.error control-name="settings[{{ $field['key'] }}]" />
                                            <p class="mt-1 text-xs text-green-600 dark:text-green-400">
                                                {{ trans($field['description']) }}
                                            </p>
                                        @endif
                                    </x-admin::form.control-group>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('admin::app.configuration.index.save-btn')
                        </button>
                    </div>
                </div>
            </x-admin::form>
        </div>

        {{-- Channel Selector --}}
        <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
            <x-admin::accordion>
                <x-slot:header>
                    <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.configuration.index.channel')
                    </p>
                </x-slot>

                <x-slot:content>
                    @foreach (core()->getAllChannels() as $channel)
                        <div class="mb-2">
                            <a
                                href="{{ route('admin.mobile_app.settings.index', ['channel' => $channel->code]) }}"
                                class="block rounded p-2 text-sm {{ $channelCode === $channel->code ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
                            >
                                {{ $channel->name }}
                            </a>
                        </div>
                    @endforeach
                </x-slot>
            </x-admin::accordion>
        </div>
    </div>
</x-admin::layouts>


