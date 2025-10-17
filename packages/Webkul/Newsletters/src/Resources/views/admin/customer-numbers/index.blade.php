<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.customer-numbers.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.customer-numbers.title') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.customer-numbers.create') }}" class="primary-button">
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
                    <th>Name</th>
                    <th>Mailing List</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="text-center">
                        <p>No customer numbers found. <a href="{{ route('admin.newsletters.customer-numbers.create') }}">Create your first customer number</a></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</x-admin::layouts>