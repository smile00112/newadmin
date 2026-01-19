<x-admin::layouts>
    <x-slot:title>
        @lang('iiko-integration::app.sync.logs')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('iiko-integration::app.sync.logs')
        </p>
    </div>

    <div class="mt-7">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="mb-4">
                <select onchange="filterLogs(this.value)" class="form-control">
                    <option value="">@lang('iiko-integration::app.sync.all-types')</option>
                    <option value="order" {{ $syncType === 'order' ? 'selected' : '' }}>@lang('iiko-integration::app.sync.type-order')</option>
                    <option value="menu" {{ $syncType === 'menu' ? 'selected' : '' }}>@lang('iiko-integration::app.sync.type-menu')</option>
                    <option value="organization" {{ $syncType === 'organization' ? 'selected' : '' }}>@lang('iiko-integration::app.sync.type-organization')</option>
                    <option value="webhook" {{ $syncType === 'webhook' ? 'selected' : '' }}>@lang('iiko-integration::app.sync.type-webhook')</option>
                </select>
            </div>

            <div class="space-y-2">
                @forelse ($logs as $log)
                    <div class="rounded border p-3 {{ $log->status === 'error' ? 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20' : 'border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/20' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                    {{ $log->sync_type }} - {{ $log->status }}
                                </p>
                                @if ($log->entity_id)
                                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                        Entity ID: {{ $log->entity_id }}
                                    </p>
                                @endif
                                @if ($log->error_message)
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">
                                        {{ $log->error_message }}
                                    </p>
                                @endif
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500">@lang('iiko-integration::app.sync.no-logs')</p>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function filterLogs(type) {
                const url = new URL(window.location.href);
                if (type) {
                    url.searchParams.set('sync_type', type);
                } else {
                    url.searchParams.delete('sync_type');
                }
                window.location.href = url.toString();
            }
        </script>
    @endpush
</x-admin::layouts>
