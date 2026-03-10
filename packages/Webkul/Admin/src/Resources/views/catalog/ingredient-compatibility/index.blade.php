<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.ingredient-compatibility.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 15px rgba(16,185,129,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.catalog.ingredient-compatibility.index.title')
                </p>
                <p class="text-xs text-gray-400">Совместимость ингредиентов</p>
            </div>
        </div>

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

