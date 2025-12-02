<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.companies.create-title') }}
    </x-slot:title>

    <form method="POST" action="{{ route('admin.newsletters.companies.store') }}">
        @csrf

        <div class="flex items-center justify-between">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                {{ __('newsletters::app.admin.companies.create-title') }}
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

        <div class="mt-4">
            <div class="grid grid-cols-1 gap-4">
                <div class="control-group">
                    <label for="name" class="required">
                        {{ __('newsletters::app.admin.companies.name') }}
                    </label>
                    <input type="text" name="name" id="name" class="control" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="control-group">
                    <label for="slug">
                        {{ __('newsletters::app.admin.companies.slug') }}
                    </label>
                    <input type="text" name="slug" id="slug" class="control" value="{{ old('slug') }}" placeholder="{{ __('newsletters::app.admin.companies.slug-placeholder') }}">
                    <p class="text-gray-500 text-sm mt-1">{{ __('newsletters::app.admin.companies.slug-help') }}</p>
                    @error('slug')
                        <span class="text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="control-group">
                    <label for="description">
                        {{ __('newsletters::app.admin.companies.description') }}
                    </label>
                    <textarea name="description" id="description" class="control" rows="4">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="control-group">
                    <label class="checkbox">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        {{ __('newsletters::app.admin.companies.is_active') }}
                    </label>
                </div>
            </div>
        </div>
    </form>
</x-admin::layouts>

