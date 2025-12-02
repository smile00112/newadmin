<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.companies.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.companies.title') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.companies.create') }}" class="primary-button">
                {{ __('newsletters::app.admin.datagrid.add') }}
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>{{ __('newsletters::app.admin.companies.name') }}</th>
                <th>{{ __('newsletters::app.admin.companies.slug') }}</th>
                <th>{{ __('newsletters::app.admin.companies.status') }}</th>
                <th>{{ __('newsletters::app.admin.companies.created_at') }}</th>
                <th>{{ __('newsletters::app.common.actions.title') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($companies as $company)
                <tr>
                    <td>{{ $company->id }}</td>
                    <td>{{ $company->name }}</td>
                    <td>{{ $company->slug }}</td>
                    <td>
                        <span class="badge {{ $company->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $company->is_active ? __('newsletters::app.admin.companies.active') : __('newsletters::app.admin.companies.inactive') }}
                        </span>
                    </td>
                    <td>{{ $company->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <div class="flex items-center gap-x-2.5">
                            <a href="{{ route('admin.newsletters.companies.edit', $company->id) }}" class="icon-pencil"></a>
                            <form method="POST" action="{{ route('admin.newsletters.companies.destroy', $company->id) }}" onsubmit="return confirm('{{ __('newsletters::app.admin.companies.delete-confirm') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-trash text-red-600"></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">
                        <p>{{ __('newsletters::app.common.messages.no_data') }}</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-admin::layouts>

