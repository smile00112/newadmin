<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.ingredient-compatibility.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('admin::app.catalog.ingredient-compatibility.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('catalog.ingredient_compatibility.create'))
                <a
                    href="{{ route('admin.catalog.ingredient_compatibility.create') }}"
                    class="primary-button"
                >
                    @lang('admin::app.catalog.ingredient-compatibility.index.create-btn')
                </a>
            @endif
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.catalog.ingredient_compatibility.index')" />
</x-admin::layouts>

