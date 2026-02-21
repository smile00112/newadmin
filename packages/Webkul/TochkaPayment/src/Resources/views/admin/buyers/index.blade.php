<x-admin::layouts>
    <x-slot:title>
        @lang('tochka-payment::app.admin.buyers.index.title')
    </x-slot:title>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('tochka-payment::app.admin.buyers.index.title')
        </p>
    </div>

    @if ($isSuperAdmin && $companies->isNotEmpty())
        <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <form method="get" action="{{ route('admin.tochka-payment.buyers.index') }}" class="flex items-center gap-4">
                <label for="company_id" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    @lang('tochka-payment::app.admin.settings.index.company')
                </label>
                <select
                    name="company_id"
                    id="company_id"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm transition-all dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    onchange="this.form.submit()"
                >
                    @foreach ($companies as $c)
                        <option value="{{ $c->id }}" {{ (int) $companyId === (int) $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.buyers.index.id')
                    </th>
                    @if ($isSuperAdmin)
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            @lang('tochka-payment::app.admin.settings.index.company')
                        </th>
                    @endif
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.buyers.index.email')
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.buyers.index.name')
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.buyers.index.phone')
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.buyers.index.consumer_id')
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.buyers.index.created_at')
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                        @lang('tochka-payment::app.admin.buyers.index.actions')
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                @forelse ($buyers as $buyer)
                    <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                            {{ $buyer->id }}
                        </td>
                        @if ($isSuperAdmin)
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ $buyer->company?->name ?? '—' }}
                            </td>
                        @endif
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                            {{ $buyer->client_email ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                            {{ $buyer->client_name ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                            {{ $buyer->client_phone ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white font-mono">
                            @if ($buyer->consumer_id)
                                {{ substr($buyer->consumer_id, 0, 8) }}...
                            @else
                                —
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                            {{ $buyer->created_at?->format('Y-m-d H:i') ?? '—' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                            @if ($buyer->owner_id)
                                <span class="inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                    @lang('tochka-payment::app.admin.buyers.index.owner-created')
                                </span>
                            @else
                                <form method="POST" action="{{ route('admin.tochka-payment.buyers.create-owner', $buyer->id) }}">
                                    @csrf

                                    <button
                                        type="submit"
                                        class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700"
                                    >
                                        @lang('tochka-payment::app.admin.buyers.index.create-owner')
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isSuperAdmin ? 8 : 7 }}" class="px-6 py-12 text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @lang('tochka-payment::app.admin.buyers.index.empty')
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($buyers->hasPages())
        <div class="mt-4">
            {{ $buyers->withQueryString()->links() }}
        </div>
    @endif
</x-admin::layouts>
