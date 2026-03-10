<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.categories.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 15px rgba(16,185,129,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.catalog.categories.index.title')
                </p>
                <p class="text-xs text-gray-400">Дерево категорий</p>
            </div>
        </div>

        <div class="flex items-center gap-x-2.5">
            {!! view_render_event('bagisto.admin.catalog.categories.index.create-button.before') !!}

            @if (bouncer()->hasPermission('catalog.categories.create'))
                <a href="{{ route('admin.catalog.categories.create') }}">
                    <div class="primary-button">
                        @lang('admin::app.catalog.categories.index.add-btn')
                    </div>
                </a>
            @endif

            {!! view_render_event('bagisto.admin.catalog.categories.index.create-button.after') !!}
        </div>        
    </div>

    {!! view_render_event('bagisto.admin.catalog.categories.list.before') !!}

    <x-admin::datagrid :src="route('admin.catalog.categories.index')" />

    {!! view_render_event('bagisto.admin.catalog.categories.list.after') !!}

</x-admin::layouts>
