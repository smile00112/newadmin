<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.whatsapp-instances.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.whatsapp-instances.title') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.whatsapp-instances.create') }}" class="primary-button">
                {{ __('newsletters::app.admin.datagrid.add') }}
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
{{--                <th>{{ __('newsletters::app.common.fields.id') }}</th>--}}
{{--                <th>{{ __('newsletters::app.admin.mailing-lists.message-text') }}</th>--}}

                <th>ID</th>
                <th>Link Name</th>
                <th>Login</th>
                <th>Mailing List</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="4" class="text-center">
                    <p>{{ __('newsletters::app.common.messages.no_data') }}
                        <a href="{{ route('admin.newsletters.mailing-lists.create') }}">
                            {{ __('newsletters::app.common.actions.create') }} {{ __('newsletters::app.admin.mailing-lists.title') }}
                        </a>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</x-admin::layouts>
