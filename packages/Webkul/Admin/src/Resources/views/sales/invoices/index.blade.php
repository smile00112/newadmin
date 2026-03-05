<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.sales.invoices.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); box-shadow: 0 4px 15px rgba(59,130,246,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.sales.invoices.index.title')
                </p>
                <p class="text-xs text-gray-400">Счета на оплату</p>
            </div>
        </div>

        <div class="flex items-center gap-x-2.5">
            <!-- Export Modal -->
            <x-admin::datagrid.export :src="route('admin.sales.invoices.index')" />
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.sales.invoices.index')" />

</x-admin::layouts>
