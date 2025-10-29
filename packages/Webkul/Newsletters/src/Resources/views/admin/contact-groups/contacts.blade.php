<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.contact-groups.contacts') }} - {{ $group->name }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.newsletters.contact-groups.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 mb-2 inline-block">
                ← {{ __('newsletters::app.common.actions.back') }} {{ __('newsletters::app.admin.contact-groups.title') }}
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $group->name }}
            </h1>
            @if($group->description)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $group->description }}
                </p>
            @endif
        </div>

        <div class="flex items-center gap-x-2.5">
            <button onclick="alert('CSV Import будет реализован в следующем шаге')" class="primary-button">
                {{ __('newsletters::app.common.actions.import') }} CSV
            </button>
            <a href="{{ route('admin.newsletters.contact-groups.edit', $group->id) }}" class="secondary-button">
                {{ __('newsletters::app.common.actions.edit') }} {{ __('newsletters::app.admin.contact-groups.group') }}
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.contacts.full-name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.contacts.phone') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.contacts.email') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.contacts.orders-count') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.created_at') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($contacts as $contact)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $contact->id }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ $contact->full_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $contact->phone }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $contact->email ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $contact->orders_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $contact->created_at->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-gray-500 dark:text-gray-400">
                                    {{ __('newsletters::app.admin.contacts.no-contacts') }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contacts->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>
</x-admin::layouts>

