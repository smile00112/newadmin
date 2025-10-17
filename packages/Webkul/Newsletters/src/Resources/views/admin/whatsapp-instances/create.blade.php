<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.whatsapp-instances.title') }} - {{ __('admin::app.datagrid.add') }}
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.whatsapp-instances.title') }} - {{ __('admin::app.datagrid.add') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.whatsapp-instances.index') }}" class="secondary-button">
                {{ __('admin::app.datagrid.back') }}
            </a>
        </div>
    </div>

    <form action="{{ route('admin.newsletters.whatsapp-instances.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="link_name">{{ __('newsletters::app.admin.whatsapp-instances.link-name') }}</label>
            <input type="text" name="link_name" id="link_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="login">{{ __('newsletters::app.admin.whatsapp-instances.login') }}</label>
            <input type="text" name="login" id="login" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password">{{ __('newsletters::app.admin.whatsapp-instances.password') }}</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="mailing_list_id">{{ __('newsletters::app.admin.whatsapp-instances.mailing-list') }}</label>
            <select name="mailing_list_id" id="mailing_list_id" class="form-control">
                <option value="">Select Mailing List</option>
                <!-- Mailing lists will be populated here -->
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="primary-button">{{ __('admin::app.datagrid.save') }}</button>
        </div>
    </form>
</x-admin::layouts>