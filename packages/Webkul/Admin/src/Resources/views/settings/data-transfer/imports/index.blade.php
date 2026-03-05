<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.data-transfer.imports.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); box-shadow: 0 4px 15px rgba(99,102,241,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.data-transfer.imports.index.title')
                </p>
                <p class="text-xs text-gray-400">Импорт данных</p>
            </div>
        </div>

        <div class="flex items-center gap-x-2.5">
            <!-- Create New Tax Rate Button -->
            @if (bouncer()->hasPermission('settings.data_transfer.imports.create'))
                <a href="{{ route('admin.settings.data_transfer.imports.create') }}" class="primary-button">
                    @lang('admin::app.settings.data-transfer.imports.index.button-title')
                </a>
            @endif
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.settings.data_transfer.imports.index')"/>
</x-admin::layouts>
