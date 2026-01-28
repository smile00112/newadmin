<x-admin::layouts>
    <x-slot:title>
        @lang('bonus::app.admin.levels.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('bonus::app.admin.levels.title')
        </p>
        <a href="{{ route('admin.bonus.levels.create') }}" class="primary-button">
            @lang('bonus::app.admin.levels.create')
        </a>
    </div>

    <div class="mt-7">
        <div class="box-shadow rounded bg-white dark:bg-gray-900">
            <div class="overflow-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <th class="px-4 py-3 text-left">@lang('bonus::app.admin.levels.name')</th>
                            <th class="px-4 py-3 text-left">@lang('bonus::app.admin.levels.cashback-percent')</th>
                            <th class="px-4 py-3 text-left">@lang('bonus::app.admin.levels.threshold')</th>
                            <th class="px-4 py-3 text-left">@lang('bonus::app.admin.levels.status')</th>
                            <th class="px-4 py-3 text-right">@lang('admin::app.datagrid.actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($levels as $level)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-4 py-3">{{ $level->name }}</td>
                                <td class="px-4 py-3">{{ $level->cashback_percent }}%</td>
                                <td class="px-4 py-3">{{ $level->threshold_value }}</td>
                                <td class="px-4 py-3">{{ $level->is_active ? __('admin::app.datagrid.active') : __('admin::app.datagrid.inactive') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.bonus.levels.edit', $level->id) }}" class="text-blue-600 hover:text-blue-800">
                                        @lang('admin::app.datagrid.edit')
                                    </a>
                                    <form action="{{ route('admin.bonus.levels.destroy', $level->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 ml-2">
                                            @lang('admin::app.datagrid.delete')
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-center">@lang('bonus::app.admin.levels.no-levels')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin::layouts>
