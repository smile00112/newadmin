<x-admin::layouts>
    <x-slot:title>
        @lang('iiko-integration::app.menu.management')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('iiko-integration::app.menu.management')
        </p>
    </div>

    <div class="mt-7">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex flex-col items-center justify-center py-12">
                <div class="mb-4">
                    <svg class="w-24 h-24 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">
                    @lang('iiko-integration::app.management.title')
                </h3>
                <p class="text-gray-600 dark:text-gray-400 text-center max-w-md">
                    @lang('iiko-integration::app.management.description')
                </p>
            </div>
        </div>
    </div>
</x-admin::layouts>
