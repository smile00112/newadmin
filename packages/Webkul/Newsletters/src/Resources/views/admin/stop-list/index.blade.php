<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.stop-list.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.stop-list.title') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.stop-list.create') }}" class="primary-button">
                {{ __('newsletters::app.admin.stop-list.create') }}
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('newsletters::app.admin.stop-list.table.id') }}</th>
                    <th>{{ __('newsletters::app.admin.stop-list.table.phone-number') }}</th>
                    <th>{{ __('newsletters::app.admin.stop-list.table.created-at') }}</th>
                    <th>{{ __('newsletters::app.admin.stop-list.table.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="text-center">
                        <p>{{ __('newsletters::app.admin.stop-list.no-numbers-found') }} <a href="{{ route('admin.newsletters.stop-list.create') }}">{{ __('newsletters::app.admin.stop-list.add-first-number') }}</a></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</x-admin::layouts>
