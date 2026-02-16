<x-admin::layouts>
    <x-slot:title>
        @lang('external-payments::app.admin.systems.index.title')
    </x-slot:title>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('external-payments::app.admin.systems.index.title')
        </p>
        <a
            href="{{ route('admin.external-payments.systems.create') }}"
            class="primary-button"
        >
            @lang('external-payments::app.admin.systems.index.create')
        </a>
    </div>

    <div class="mt-4 overflow-x-auto rounded-lg bg-white shadow dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('external-payments::app.admin.systems.index.name')
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('external-payments::app.admin.systems.index.webhook_url')
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('external-payments::app.admin.systems.index.providers')
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('external-payments::app.admin.systems.index.is_active')
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('external-payments::app.admin.systems.index.actions')
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                @forelse ($systems as $system)
                    <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                            {{ $system->name }}
                        </td>
                        <td class="max-w-[200px] truncate px-6 py-4 text-sm text-gray-600 dark:text-gray-400" title="{{ $system->webhook_url ?? '' }}">
                            {{ $system->webhook_url ? \Illuminate\Support\Str::limit($system->webhook_url, 40) : '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            @php
                                $names = [];
                                foreach ($system->paymentProviders as $pp) {
                                    $names[] = $providerNames[$pp->payment_provider]['name'] ?? $pp->payment_provider;
                                }
                            @endphp
                            {{ implode(', ', $names) ?: '—' }}
                            @if ($system->default_provider)
                                <span class="text-xs text-gray-500">(default: {{ $providerNames[$system->default_provider]['name'] ?? $system->default_provider }})</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            @if ($system->is_active)
                                <span class="text-green-600 dark:text-green-400">@lang('external-payments::app.admin.systems.index.yes')</span>
                            @else
                                <span class="text-red-600 dark:text-red-400">@lang('external-payments::app.admin.systems.index.no')</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            <a
                                href="{{ route('admin.external-payments.systems.edit', $system->id) }}"
                                class="text-blue-600 transition hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                @lang('external-payments::app.admin.systems.index.edit')
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            No external systems yet. <a href="{{ route('admin.external-payments.systems.create') }}" class="text-blue-600 dark:text-blue-400">Create one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($systems->hasPages())
        <div class="mt-4">
            {{ $systems->withQueryString()->links() }}
        </div>
    @endif
</x-admin::layouts>
