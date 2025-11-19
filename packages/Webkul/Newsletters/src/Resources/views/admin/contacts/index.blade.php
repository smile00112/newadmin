<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.contacts.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ __('newsletters::app.admin.contacts.title') }}
        </h1>
    </div>

    @include('newsletters::admin.components.contacts-table')
</x-admin::layouts>

