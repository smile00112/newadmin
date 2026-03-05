<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.constructor-templates.index.title')
    </x-slot>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 4px 15px rgba(245,158,11,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>
            </div>
            <div>
                <p class="text-xl text-gray-800 dark:text-white font-bold">
                    @lang('admin::app.catalog.constructor-templates.index.title')
                </p>
                <p class="text-xs text-gray-400">Шаблоны конструктора</p>
            </div>
        </div>

        <div class="flex gap-x-2.5 items-center">
            @if (bouncer()->hasPermission('catalog.constructor_templates.create'))
                <a
                    href="{{ route('admin.catalog.constructor_templates.create') }}"
                    class="primary-button"
                >
                    @lang('admin::app.catalog.constructor-templates.index.create-btn')
                </a>
            @endif
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.catalog.constructor_templates.index')" />
</x-admin::layouts>

