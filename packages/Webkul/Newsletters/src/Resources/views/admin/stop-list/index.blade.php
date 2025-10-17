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
                {{ __('admin::app.datagrid.add') }}
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Phone Number</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="text-center">
                        <p>No blocked phone numbers found. <a href="{{ route('admin.newsletters.stop-list.create') }}">Add your first blocked number</a></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</x-admin::layouts>