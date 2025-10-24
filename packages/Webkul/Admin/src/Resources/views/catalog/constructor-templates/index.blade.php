<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.constructor-templates.index.title')
    </x-slot>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
        <p class="text-xl text-gray-800 dark:text-white font-bold">
            @lang('admin::app.catalog.constructor-templates.index.title')
        </p>

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

