<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.inventory-sources.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 4px 15px rgba(245,158,11,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.inventory-sources.index.title')
                </p>
                <p class="text-xs text-gray-400">Склады</p>
            </div>
        </div>

        <!-- Create Button -->
        @if (bouncer()->hasPermission('settings.inventory_sources.create'))
            <a href="{{ route('admin.settings.inventory_sources.create') }}">
                <div class="primary-button">
                    @lang('admin::app.settings.inventory-sources.index.create-btn')
                </div>
            </a>
        @endif
    </div>

    {!! view_render_event('bagisto.admin.settings.inventory_sources.list.before') !!}

    <x-admin::datagrid :src="route('admin.settings.inventory_sources.index')" />

    {!! view_render_event('bagisto.admin.settings.inventory_sources.list.after') !!}

</x-admin::layouts>
