<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.application_errors.show.title', ['id' => $error->id])
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap mb-6">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 4px 15px rgba(239,68,68,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.application_errors.show.title', ['id' => $error->id])
                </p>
                <p class="text-xs text-gray-400">Детали ошибки</p>
            </div>
        </div>
        <a
            href="{{ route('admin.application_errors.index') }}"
            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
        >
            @lang('admin::app.application_errors.show.back')
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                @lang('admin::app.application_errors.show.details')
            </p>
        </div>
        <div class="p-6 space-y-6">
            <div>
                <p class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    @lang('admin::app.application_errors.index.datagrid.message')
                </p>
                <pre class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700 text-sm text-gray-900 dark:text-white whitespace-pre-wrap break-words">{{ $error->message }}</pre>
            </div>
            @if ($error->code)
                <div>
                    <p class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        @lang('admin::app.application_errors.index.datagrid.code')
                    </p>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $error->code }}</p>
                </div>
            @endif
            @if ($error->file)
                <div>
                    <p class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        @lang('admin::app.application_errors.show.file')
                    </p>
                    <p class="text-sm text-gray-900 dark:text-white break-all">{{ $error->file }}{{ $error->line ? ':' . $error->line : '' }}</p>
                </div>
            @endif
            @if ($error->source)
                <div>
                    <p class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        @lang('admin::app.application_errors.index.datagrid.source')
                    </p>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $error->source }}</p>
                </div>
            @endif
            <div>
                <p class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    @lang('admin::app.application_errors.index.datagrid.created_at')
                </p>
                <p class="text-sm text-gray-900 dark:text-white">{{ $error->created_at?->format('Y-m-d H:i:s') }}</p>
            </div>
            @if ($error->trace)
                <div>
                    <p class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        @lang('admin::app.application_errors.show.trace')
                    </p>
                    <pre class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700 text-xs text-gray-900 dark:text-white whitespace-pre-wrap break-words max-h-96 overflow-y-auto">{{ $error->trace }}</pre>
                </div>
            @endif
            @if ($error->context && count((array) $error->context) > 0)
                <div>
                    <p class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        @lang('admin::app.application_errors.show.context')
                    </p>
                    <pre class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700 text-xs text-gray-900 dark:text-white whitespace-pre-wrap break-words">{{ json_encode($error->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            @endif
        </div>
    </div>
</x-admin::layouts>
