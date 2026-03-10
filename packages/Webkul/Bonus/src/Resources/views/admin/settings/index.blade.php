<x-admin::layouts>
    <x-slot:title>
        @lang('bonus::app.admin.settings.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('bonus::app.admin.settings.title')
        </p>
    </div>

    <div class="mt-7 flex gap-2.5 max-xl:flex-wrap">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <div class="box-shadow rounded bg-white dark:bg-gray-900">
                {{-- Tabs --}}
                <div class="border-b border-gray-200 dark:border-gray-800">
                    <nav class="flex space-x-2 px-4" aria-label="Tabs">
                        @foreach ($tabs as $tabKey)
                            <a
                                href="{{ route('admin.bonus.settings.index', ['tab' => $tabKey, 'channel' => $channelCode]) }}"
                                class="px-4 py-3 text-sm font-medium rounded-t-lg transition-colors {{ $tab === $tabKey ? 'bg-blue-50 text-blue-600 border-b-2 border-blue-600 dark:bg-gray-800 dark:text-blue-400' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-800' }}"
                            >
                                {{ $tabLabels[$tabKey] ?? $tabKey }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                {{-- Tab content --}}
                <div class="p-4">
                    @if ($tab === 'settings')
                        <x-admin::form
                            :action="route('admin.bonus.settings.store', ['tab' => 'settings'])"
                            method="POST"
                        >
                            <input type="hidden" name="channel_code" value="{{ $channelCode }}">

                            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('bonus::app.admin.settings.general.title')
                            </p>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('bonus::app.admin.settings.fields.enabled')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="settings[enabled]"
                                    :value="1"
                                    :checked="(bool) ($settings['enabled'] ?? false)"
                                />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('bonus::app.admin.settings.fields.max-usage-percent')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="settings[max_usage_percent]"
                                    :value="$settings['max_usage_percent'] ?? 100"
                                />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('bonus::app.admin.settings.fields.expiry-days')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="settings[expiry_days]"
                                    :value="$settings['expiry_days'] ?? 365"
                                />
                            </x-admin::form.control-group>

                            <div class="flex justify-end mt-4">
                                <button type="submit" class="primary-button">
                                    @lang('admin::app.configuration.index.save-btn')
                                </button>
                            </div>
                        </x-admin::form>
                    @elseif ($tab === 'levels')
                        @include('bonus::admin.settings.levels')
                    @elseif ($tab === 'manage')
                        @include('bonus::admin.settings.manage')
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin::layouts>
