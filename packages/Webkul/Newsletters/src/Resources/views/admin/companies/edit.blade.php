<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.companies.edit-title') }}
    </x-slot:title>

    <form method="POST" action="{{ route('admin.newsletters.companies.update', $company->id) }}">
        @csrf
        @method('PUT')

        <div class="flex items-center justify-between">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                {{ __('newsletters::app.admin.companies.edit-title') }}
            </p>

            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.newsletters.companies.index') }}" class="secondary-button">
                    {{ __('newsletters::app.common.actions.cancel') }}
                </a>
                <button type="submit" class="primary-button">
                    {{ __('newsletters::app.common.actions.save') }}
                </button>
            </div>
        </div>

        <div class="p-3 mt-4 box-shadow rounded bg-white p-6 dark:bg-gray-900">
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.companies.name') }}
                        <span class="text-red-600 dark:text-red-400">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $company->name) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.companies.slug') }}
                    </label>
                    <input
                        type="text"
                        name="slug"
                        id="slug"
                        value="{{ old('slug', $company->slug) }}"
                        placeholder="{{ __('newsletters::app.admin.companies.slug-placeholder') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors"
                    >
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.companies.slug-help') }}</p>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.admin.companies.description') }}
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors resize-y"
                    >{{ old('description', $company->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', $company->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-400 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                        >
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('newsletters::app.admin.companies.is_active') }}
                        </span>
                    </label>
                </div>
            </div>
        </div>
    </form>
</x-admin::layouts>

