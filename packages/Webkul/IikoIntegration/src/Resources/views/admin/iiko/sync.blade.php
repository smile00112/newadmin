<x-admin::layouts>
    <x-slot:title>
        @lang('iiko-integration::app.sync.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('iiko-integration::app.sync.title')
        </p>
    </div>

    <div class="mt-7 grid gap-4">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                @lang('iiko-integration::app.sync.organizations')
            </h3>
            <button
                type="button"
                class="primary-button"
                onclick="syncOrganizations()"
            >
                @lang('iiko-integration::app.sync.sync-organizations')
            </button>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                @lang('iiko-integration::app.sync.menu')
            </h3>
            <div class="flex gap-4">
                <select id="organization-select" class="form-control">
                    <option value="">@lang('iiko-integration::app.sync.select-organization')</option>
                    @if (!empty($organizations) && is_array($organizations))
                        @foreach ($organizations as $org)
                            <option value="{{ $org['id'] ?? '' }}">{{ $org['name'] ?? '' }}</option>
                        @endforeach
                    @endif
                </select>
                <button
                    type="button"
                    class="primary-button"
                    onclick="syncMenu()"
                >
                    @lang('iiko-integration::app.sync.sync-menu')
                </button>
            </div>
        </div>

        @if ($recentLogs && $recentLogs->count() > 0)
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                    @lang('iiko-integration::app.sync.recent-errors')
                </h3>
                <div class="space-y-2">
                    @foreach ($recentLogs as $log)
                        <div class="rounded border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20">
                            <p class="text-sm text-gray-800 dark:text-white">
                                <strong>{{ $log->sync_type }}</strong>: {{ $log->error_message }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function syncOrganizations() {
                fetch('{{ route('admin.iiko.sync.organizations') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('@lang('iiko-integration::app.sync.error')');
                });
            }

            function syncMenu() {
                const orgId = document.getElementById('organization-select').value;
                if (!orgId) {
                    alert('@lang('iiko-integration::app.sync.select-organization')');
                    return;
                }

                fetch('{{ route('admin.iiko.sync.menu') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ organization_id: orgId }),
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                })
                .catch(error => {
                    alert('@lang('iiko-integration::app.sync.error')');
                });
            }
        </script>
    @endpush
</x-admin::layouts>
