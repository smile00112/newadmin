<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.contact-groups.edit-title') }} - {{ $group->name }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.contact-groups.edit-title') }} - {{ $group->name }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.contact-groups.index') }}" class="secondary-button">
                {{ __('newsletters::app.common.actions.back') }}
            </a>
        </div>
    </div>

    <form action="{{ route('admin.newsletters.contact-groups.update', $group->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.contact-groups.name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                           required
                           value="{{ old('name', $group->name) }}">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.contact-groups.description') }}
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray手0 dark:text-white">{{ old('description', $group->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-x-2.5">
            <a href="{{ route('admin.newsletters.contact-groups.index') }}" class="secondary-button">
                {{ __('newsletters::app.common.actions.cancel') }}
            </a>
            <button type="submit" class="primary-button">
                {{ __('newsletters::app.common.actions.update') }}
            </button>
        </div>
    </form>
</x-admin::layouts>

