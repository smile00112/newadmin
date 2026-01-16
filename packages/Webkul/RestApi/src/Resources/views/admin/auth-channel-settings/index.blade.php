<x-admin::layouts>
    <x-slot:title>
        @lang('rest-api::app.auth_channels.settings.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('rest-api::app.auth_channels.settings.title')
        </p>
    </div>

    <div class="mt-7 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            {{-- Tabs for different auth channels --}}
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-4 border-b border-gray-200 dark:border-gray-800">
                    <nav class="flex space-x-2" aria-label="Tabs">
                        @foreach ($channels as $channelKey => $channelName)
                            <a
                                href="{{ route('admin.settings.auth_channels.index', ['auth_channel' => $channelKey, 'channel' => $channelCode]) }}"
                                class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors {{ $selectedChannel === $channelKey ? 'bg-blue-50 text-blue-600 border-b-2 border-blue-600 dark:bg-gray-800 dark:text-blue-400' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-800' }}"
                            >
                                {{ $channelName }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <x-admin::form
                    :action="route('admin.settings.auth_channels.store')"
                    method="POST"
                >
                    <input type="hidden" name="channel_code" value="{{ $channelCode }}">
                    <input type="hidden" name="auth_channel" value="{{ $selectedChannel }}">

                    <div class="mt-4">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('rest-api::app.auth_channels.settings.' . $selectedChannel . '.title')
                        </p>

                        @foreach ($fields as $field)
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
                                    @elseif ($field['type'] === 'password')
                                        <x-admin::form.control-group.control
                                            type="password"
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
                                href="{{ route('admin.settings.auth_channels.index', ['channel' => $channel->code, 'auth_channel' => $selectedChannel]) }}"
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
