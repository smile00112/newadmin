<x-admin::layouts>
    <x-slot:title>
        @lang('newsletters::app.admin.settings.newsletters.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('newsletters::app.admin.settings.newsletters.title')
        </p>
    </div>

    <div class="mt-4 rounded-md border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <form
            method="POST"
            action="{{ route('admin.settings.newsletters.store') }}"
        >
            @csrf

            <x-admin::form.control-group>
                <x-admin::form.control-group.label class="required">
                    @lang('newsletters::app.admin.settings.newsletters.timezone')
                </x-admin::form.control-group.label>

                @php
                    $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
                @endphp

                <x-admin::form.control-group.control
                    type="select"
                    name="timezone"
                    rules="required"
                    :value="$timezone"
                    :label="trans('newsletters::app.admin.settings.newsletters.timezone')"
                >
                    <option value="" disabled>
                        @lang('newsletters::app.admin.settings.newsletters.select-timezone')
                    </option>

                    @foreach($tzlist as $tz)
                        <option
                            value="{{ $tz }}"
                            {{ $tz === $timezone ? 'selected' : '' }}
                        >
                            {{ $tz }}
                        </option>
                    @endforeach
                </x-admin::form.control-group.control>

                <x-admin::form.control-group.error control-name="timezone" />
            </x-admin::form.control-group>

            <div class="mt-4 flex items-center gap-x-2.5">
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('admin::app.settings.locales.index.save-btn')
                </button>
            </div>
        </form>
    </div>
</x-admin::layouts>

