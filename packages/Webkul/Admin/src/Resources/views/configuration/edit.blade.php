@php
    $channels = core()->getAllChannels();

    $currentChannel = core()->getRequestedChannel();

    $currentLocale = core()->getRequestedLocale();

    $activeConfiguration = system_config()->getActiveConfigurationItem();
@endphp

<x-admin::layouts>
    <x-slot:title>
        {{ $name = $activeConfiguration->getName() }}
    </x-slot>

    <!-- Configuration form fields -->
    <x-admin::form
        action=""
        enctype="multipart/form-data"
    >
        <!-- Save Inventory -->
        <div class="mt-3.5 flex items-center justify-between gap-4 max-sm:flex-wrap">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); box-shadow: 0 4px 15px rgba(124,58,237,0.3); min-width:44px;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        {{ $name }}
                    </p>
                    <p class="text-xs text-gray-400">Конфигурация системы</p>
                </div>
            </div>

            <!-- Save Inventory -->
            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.configuration.index') }}"
                    style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #6b7280; background: #f3f4f6; transition: all 0.15s; text-decoration: none;"
                    onmouseenter="this.style.background='#e5e7eb'"
                    onmouseleave="this.style.background='#f3f4f6'"
                >
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    @lang('admin::app.configuration.index.back-btn')
                </a>

                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('admin::app.configuration.index.save-btn')
                </button>
            </div>
        </div>

        <div class="mt-7 flex items-center justify-between gap-4 max-md:flex-wrap">
            <div class="flex items-center gap-x-1">
                <!-- Channel Switcher -->
                <x-admin::dropdown :class="$channels->count() <= 1 ? 'hidden' : ''">
                    <!-- Dropdown Toggler -->
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="transparent-button px-1 py-1.5 hover:bg-gray-200 focus:bg-gray-200 dark:text-white dark:hover:bg-gray-800 dark:focus:bg-gray-800"
                        >
                            <span class="icon-store text-2xl"></span>

                            {{ $currentChannel->name }}

                            <input
                                type="hidden"
                                name="channel"
                                value="{{ $currentChannel->code }}"
                            />

                            <span class="icon-sort-down text-2xl"></span>
                        </button>
                    </x-slot>

                    <!-- Dropdown Content -->
                    <x-slot:content class="!p-0">
                        @foreach ($channels as $channel)
                            <a
                                href="?{{ Arr::query(['channel' => $channel->code, 'locale' => $channel->default_locale?->code ?? $currentLocale->code]) }}"
                                class="flex cursor-pointer gap-2.5 px-5 py-2 text-base hover:bg-gray-100 dark:text-white dark:hover:bg-gray-950"
                            >
                                {{ $channel->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-admin::dropdown>

                <!-- Locale Switcher -->
                <x-admin::dropdown :class="$currentChannel->locales->count() <= 1 ? 'hidden' : ''">
                    <!-- Dropdown Toggler -->
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="transparent-button px-1 py-1.5 hover:bg-gray-200 focus:bg-gray-200 dark:text-white dark:hover:bg-gray-800 dark:focus:bg-gray-800"
                        >
                            <span class="icon-language text-2xl"></span>

                            {{ $currentLocale->name }}

                            <input
                                type="hidden"
                                name="locale"
                                value="{{ $currentLocale->code }}"
                            />

                            <span class="icon-sort-down text-2xl"></span>
                        </button>
                    </x-slot>

                    <!-- Dropdown Content -->
                    <x-slot:content class="!p-0">
                        @foreach ($currentChannel->locales->sortBy('name') as $locale)
                            <a
                                href="?{{ Arr::query(['channel' => $currentChannel->code, 'locale' => $locale->code]) }}"
                                class="flex gap-2.5 px-5 py-2 text-base  cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-950 dark:text-white {{ $locale->code == $currentLocale->code ? 'bg-gray-100 dark:bg-gray-950' : ''}}"
                            >
                                {{ $locale->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-admin::dropdown>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-[0.5fr_3fr] gap-10 max-xl:flex-wrap">
            @foreach ($activeConfiguration->getChildren() as $child)
                <div class="grid content-start gap-2.5">
                    <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                        {{ $child->getName() }}
                    </p>

                    <p class="leading-[140%] text-gray-600 dark:text-gray-300">
                        {!! $child->getInfo() !!}
                    </p>
                </div>

                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    @foreach ($child->getFields() as $field)
                        @if (
                            $field->getType() == 'blade'
                            && view()->exists($path = $field->getPath())
                        )
                            {!! view($path, compact('field', 'child', 'currentChannel', 'currentLocale', 'channels'))->render() !!}
                        @else
                            @include ('admin::configuration.field-type')
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
    </x-admin::form>
</x-admin::layouts>
