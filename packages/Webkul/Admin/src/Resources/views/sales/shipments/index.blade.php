<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.sales.shipments.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); box-shadow: 0 4px 15px rgba(20,184,166,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.sales.shipments.index.title')
                </p>
                <p class="text-xs text-gray-400">Доставки</p>
            </div>
        </div>

        <div class="flex items-center gap-x-2.5">
            <!-- Export Modal -->
            <x-admin::datagrid.export :src="route('admin.sales.shipments.index')" />
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.sales.shipments.index')" />

</x-admin::layouts>
