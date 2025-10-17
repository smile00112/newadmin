<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.stop-list.title') }} - {{ __('admin::app.datagrid.add') }}
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.stop-list.title') }} - {{ __('admin::app.datagrid.add') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.stop-list.index') }}" class="secondary-button">
                {{ __('admin::app.datagrid.back') }}
            </a>
        </div>
    </div>

    <form action="{{ route('admin.newsletters.stop-list.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="phone_number">{{ __('newsletters::app.admin.stop-list.phone-number') }}</label>
            <input type="text" name="phone_number" id="phone_number" class="form-control" required>
        </div>

        <div class="form-group">
            <button type="submit" class="primary-button">{{ __('admin::app.datagrid.save') }}</button>
        </div>
    </form>
</x-admin::layouts>