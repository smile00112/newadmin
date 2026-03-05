<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.families.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); box-shadow: 0 4px 15px rgba(139,92,246,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.catalog.families.index.title')
                </p>
                <p class="text-xs text-gray-400">Группы атрибутов</p>
            </div>
        </div>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('catalog.families.create'))
                <a href="{{ route('admin.catalog.families.create') }}">
                    <div class="primary-button">
                        @lang('admin::app.catalog.families.index.add')
                    </div>
                </a>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.admin.catalog.families.list.before') !!}

    <x-admin::datagrid :src="route('admin.catalog.families.index')" />

    {!! view_render_event('bagisto.admin.catalog.families.list.after') !!}

</x-admin::layouts>