<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.customer-numbers.title') }} - {{ __('admin::app.datagrid.add') }}
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.customer-numbers.title') }} - {{ __('admin::app.datagrid.add') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.customer-numbers.index') }}" class="secondary-button">
                {{ __('admin::app.datagrid.back') }}
            </a>
        </div>
    </div>

    <form action="{{ route('admin.newsletters.customer-numbers.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="phone_number">{{ __('newsletters::app.admin.customer-numbers.phone-number') }}</label>
            <input type="text" name="phone_number" id="phone_number" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="name">{{ __('newsletters::app.admin.customer-numbers.name') }}</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="mailing_list_id">{{ __('newsletters::app.admin.customer-numbers.mailing-list') }}</label>
            <select name="mailing_list_id" id="mailing_list_id" class="form-control" required>
                <option value="">Select Mailing List</option>
                <!-- Mailing lists will be populated here -->
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="primary-button">{{ __('admin::app.datagrid.save') }}</button>
        </div>
    </form>
</x-admin::layouts>