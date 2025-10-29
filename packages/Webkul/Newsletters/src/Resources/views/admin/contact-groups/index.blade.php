<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.contact-groups.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ __('newsletters::app.admin.contact-groups.title') }}
        </h1>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.contact-groups.create') }}" class="primary-button">
                {{ __('newsletters::app.common.actions.create') }} {{ __('newsletters::app.admin.contact-groups.group') }}
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.id') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.contact-groups.name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.contact-groups.description') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.contact-groups.contacts_count') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.created_at') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($groups as $group)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" 
                            onclick="window.location.href='{{ route('admin.newsletters.contact-groups.contacts', $group->id) }}'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $group->id }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white font-medium">
                                {{ $group->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ Str::limit($group->description ?? '-', 50) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $group->contacts_count }} {{ __('newsletters::app.admin.contact-groups.contacts') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $group->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" onclick="event.stopPropagation()">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.newsletters.contact-groups.edit', $group->id) }}"
                                       class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" 
                                       title="{{ __('newsletters::app.common.actions.edit') }}">
                                        <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-edit"></span>
                                    </a>
                                    <button onclick="deleteGroup({{ $group->id }})"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            title="{{ __('newsletters::app.common.actions.delete') }}">
                                        <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-delete"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('newsletters::app.admin.contact-groups.no-groups') }}
                                    </p>
                                    <a href="{{ route('admin.newsletters.contact-groups.create') }}" class="mt-4 primary-button">
                                        {{ __('newsletters::app.common.actions.create') }} {{ __('newsletters::app.admin.contact-groups.group') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        function deleteGroup(id) {
            if (confirm('{{ __('newsletters::app.admin.contact-groups.delete-confirm') }}')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('admin/newsletters/contact-groups') }}/${id}`;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = csrfToken.getAttribute('content');
                form.appendChild(tokenInput);

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    @endpush
</x-admin::layouts>

