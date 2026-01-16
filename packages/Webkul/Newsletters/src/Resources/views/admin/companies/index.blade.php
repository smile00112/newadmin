<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.companies.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap mb-6">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.companies.title') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.companies.create') }}" class="primary-button">
                {{ __('newsletters::app.admin.datagrid.add') }}
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.companies.name') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.companies.slug') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.companies.status') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.account.current-balance') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.admin.companies.created_at') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('newsletters::app.common.actions.title') }}</th>
            </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($companies as $company)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $company->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $company->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $company->slug }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $company->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                            {{ $company->is_active ? __('newsletters::app.admin.companies.active') : __('newsletters::app.admin.companies.inactive') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($company->account)
                            <span class="{{ $company->account->balance <= 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                                {{ number_format($company->account->balance, 2) }}
                            </span>
                        @else
                            <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $company->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.newsletters.companies.edit', $company->id) }}"
                               class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                               title="{{ __('admin::app.datagrid.edit') }}">
                                <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center icon-edit"></span>
                            </a>
                            <form method="POST" action="{{ route('admin.newsletters.companies.destroy', $company->id) }}" onsubmit="return confirm('{{ __('newsletters::app.admin.companies.delete-confirm') }}')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                        title="{{ __('admin::app.datagrid.delete') }}">
                                    <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center icon-delete"></span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <span class="mx-auto h-12 w-12 text-gray-400 icon-inbox"></span>
                            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                {{ __('newsletters::app.common.messages.no_data') }}
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($companies->hasPages())
        <div class="mt-6 flex items-center justify-between gap-4 max-md:flex-wrap">
            <div class="flex items-center gap-x-2">
                <!-- Dropdown для выбора записей на странице -->
                <form method="GET" action="{{ route('admin.newsletters.companies.index') }}" class="flex items-center gap-x-2">
                    <div class="relative inline-flex w-full max-w-max">
                        <select 
                            name="per_page" 
                            onchange="this.form.submit()"
                            class="inline-flex w-full max-w-max cursor-pointer appearance-none items-center justify-between gap-x-2 rounded-md border bg-white px-2.5 py-1.5 text-center leading-6 text-gray-600 transition-all marker:shadow hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        >
                            <option value="10" {{ $companies->perPage() == 10 ? 'selected' : '' }}>10</option>
                            <option value="15" {{ $companies->perPage() == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ $companies->perPage() == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $companies->perPage() == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $companies->perPage() == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="icon-sort-down text-2xl pointer-events-none absolute right-2 top-1/2 -translate-y-1/2"></span>
                    </div>
                    <p class="whitespace-nowrap text-gray-600 dark:text-gray-300 max-sm:hidden">
                        {{ __('admin::app.components.datagrid.toolbar.per-page') }}
                    </p>
                    @foreach(request()->except('per_page', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                </form>

                <!-- Input для номера страницы -->
                <form method="GET" action="{{ route('admin.newsletters.companies.index') }}" class="flex items-center gap-x-2">
                    <input
                        type="text"
                        name="page"
                        value="{{ $companies->currentPage() }}"
                        onchange="this.form.submit()"
                        class="inline-flex min-h-[38px] max-w-10 appearance-none items-center justify-center gap-x-1 rounded-md border bg-white px-3 py-1.5 text-center leading-6 text-gray-600 transition-all marker:shadow hover:border-gray-400 focus:border-gray-400 focus:outline-none dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400 max-sm:hidden"
                    >
                    <div class="whitespace-nowrap text-gray-600 dark:text-gray-300">
                        <span>{{ __('admin::app.components.datagrid.toolbar.of') }}</span>
                        <span>{{ $companies->lastPage() }}</span>
                    </div>
                    @foreach(request()->except('page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                </form>

                <!-- Кнопки навигации -->
                <div class="flex items-center gap-1">
                    @if($companies->onFirstPage())
                        <span class="inline-flex w-full max-w-max cursor-not-allowed appearance-none items-center justify-between gap-x-1 rounded-md border border-transparent p-1.5 text-center text-gray-400 transition-all marker:shadow dark:text-gray-600">
                            <span class="icon-sort-left rtl:icon-sort-right text-2xl"></span>
                        </span>
                    @else
                        <a 
                            href="{{ $companies->previousPageUrl() }}"
                            class="inline-flex w-full max-w-max cursor-pointer appearance-none items-center justify-between gap-x-1 rounded-md border border-transparent p-1.5 text-center text-gray-600 transition-all marker:shadow hover:bg-gray-200 active:border-gray-300 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            <span class="icon-sort-left rtl:icon-sort-right text-2xl"></span>
                        </a>
                    @endif

                    @if($companies->hasMorePages())
                        <a 
                            href="{{ $companies->nextPageUrl() }}"
                            class="inline-flex w-full max-w-max cursor-pointer appearance-none items-center justify-between gap-x-1 rounded-md border border-transparent p-1.5 text-center text-gray-600 transition-all marker:shadow hover:bg-gray-200 active:border-gray-300 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            <span class="icon-sort-right rtl:icon-sort-left text-2xl"></span>
                        </a>
                    @else
                        <span class="inline-flex w-full max-w-max cursor-not-allowed appearance-none items-center justify-between gap-x-1 rounded-md border border-transparent p-1.5 text-center text-gray-400 transition-all marker:shadow dark:text-gray-600">
                            <span class="icon-sort-right rtl:icon-sort-left text-2xl"></span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- Информация о записях -->
            <div class="text-sm text-gray-600 dark:text-gray-300">
                {{ $companies->firstItem() ?? 0 }} - {{ $companies->lastItem() ?? 0 }} {{ __('admin::app.components.datagrid.toolbar.of') }} {{ $companies->total() }}
            </div>
        </div>
    @endif
</x-admin::layouts>

