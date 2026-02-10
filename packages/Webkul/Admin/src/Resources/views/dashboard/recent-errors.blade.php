<!-- Recent Application Errors -->
<div class="grid gap-4 border-b px-4 py-2 dark:border-gray-800">
    <div class="flex items-center justify-between">
        <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
            @lang('admin::app.dashboard.index.recent-errors')
        </p>
        <a
            href="{{ route('admin.application_errors.index') }}"
            class="text-sm font-semibold text-blue-600 transition-all hover:underline dark:text-blue-400 dark:hover:text-blue-300"
        >
            @lang('admin::app.dashboard.index.view-all-errors')
        </a>
    </div>

    @if ($recentErrors->isEmpty())
        <p class="px-2 py-4 text-sm text-gray-500 dark:text-gray-400">
            @lang('admin::app.dashboard.index.no-recent-errors')
        </p>
    @else
        <div class="flex flex-col gap-2">
            @foreach ($recentErrors as $error)
                <a
                    href="{{ route('admin.application_errors.show', $error->id) }}"
                    class="flex flex-col gap-1 rounded p-2 transition-all hover:bg-gray-50 dark:hover:bg-gray-950"
                >
                    <p class="truncate text-sm font-medium text-gray-800 dark:text-white" title="{{ $error->message }}">
                        {{ \Illuminate\Support\Str::limit($error->message, 50) }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $error->created_at?->format('Y-m-d H:i') }}
                        @if ($error->source)
                            · {{ $error->source }}
                        @endif
                    </p>
                </a>
            @endforeach
        </div>
    @endif
</div>
