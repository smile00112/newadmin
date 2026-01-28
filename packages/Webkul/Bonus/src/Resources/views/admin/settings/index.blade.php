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
            <x-admin::form
                :action="route('admin.bonus.settings.store')"
                method="POST"
            >
                <input type="hidden" name="channel_code" value="{{ $channelCode }}">

                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
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
                </div>
            </x-admin::form>
        </div>
    </div>
</x-admin::layouts>
