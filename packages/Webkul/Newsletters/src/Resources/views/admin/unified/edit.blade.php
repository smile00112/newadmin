<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.unified.edit-entry') }}
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.unified.edit-entry') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.newsletters.unified.index') }}" class="secondary-button">
                {{ __('newsletters::app.common.actions.back') }}
            </a>
        </div>
    </div>

    <form action="{{ route('admin.newsletters.unified.update', $mailingList->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Mailing List Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ __('newsletters::app.admin.unified.mailing-list-section') }}
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="mailing_list_message_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('newsletters::app.admin.mailing-lists.message-text') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea name="mailing_list[message_text]" id="mailing_list_message_text" rows="3" 
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                            required>{{ old('mailing_list.message_text', $mailingList->message_text) }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="mailing_list_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('newsletters::app.admin.mailing-lists.active') }}
                            </label>
                            <select name="mailing_list[active]" id="mailing_list_active" 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="1" {{ old('mailing_list.active', $mailingList->active) ? 'selected' : '' }}>
                                    {{ __('admin::app.datagrid.yes') }}
                                </option>
                                <option value="0" {{ !old('mailing_list.active', $mailingList->active) ? 'selected' : '' }}>
                                    {{ __('admin::app.datagrid.no') }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="mailing_list_start_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('newsletters::app.admin.mailing-lists.start-at') }}
                            </label>
                            <input type="datetime-local" name="mailing_list[start_at]" id="mailing_list_start_at" 
                                value="{{ old('mailing_list.start_at', $mailingList->start_at ? $mailingList->start_at->format('Y-m-d\TH:i') : '') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WhatsApp Instance Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ __('newsletters::app.admin.unified.whatsapp-instance-section') }}
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="whatsapp_instance_link_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('newsletters::app.admin.whatsapp-instances.link-name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="whatsapp_instance[link_name]" id="whatsapp_instance_link_name" 
                            value="{{ old('whatsapp_instance.link_name', $whatsappInstance->link_name ?? '') }}"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                            required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="whatsapp_instance_login" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('newsletters::app.admin.whatsapp-instances.login') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="whatsapp_instance[login]" id="whatsapp_instance_login" 
                                value="{{ old('whatsapp_instance.login', $whatsappInstance->login ?? '') }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                required>
                        </div>
                        <div>
                            <label for="whatsapp_instance_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('newsletters::app.admin.whatsapp-instances.password') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="whatsapp_instance[password]" id="whatsapp_instance_password" 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Numbers Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ __('newsletters::app.admin.unified.customer-numbers-section') }}
                </h3>
            </div>
            <div class="p-6">
                <div id="customerNumbersContainer">
                    @forelse($customerNumbers as $index => $customer)
                        <div class="customer-number-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('newsletters::app.admin.customer-numbers.phone-number') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="customer_numbers[{{ $index }}][phone_number]" 
                                    value="{{ old('customer_numbers.' . $index . '.phone_number', $customer->phone_number) }}"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('newsletters::app.admin.customer-numbers.name') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="customer_numbers[{{ $index }}][name]" 
                                    value="{{ old('customer_numbers.' . $index . '.name', $customer->name) }}"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('newsletters::app.admin.customer-numbers.email') }}
                                </label>
                                <input type="email" name="customer_numbers[{{ $index }}][email]" 
                                    value="{{ old('customer_numbers.' . $index . '.email', $customer->email) }}"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    @empty
                        <div class="customer-number-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('newsletters::app.admin.customer-numbers.phone-number') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="customer_numbers[0][phone_number]" 
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('newsletters::app.admin.customer-numbers.name') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="customer_numbers[0][name]" 
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('newsletters::app.admin.customer-numbers.email') }}
                                </label>
                                <input type="email" name="customer_numbers[0][email]" 
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    @endforelse
                </div>
                <button type="button" onclick="addCustomerNumberRow()" 
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ __('newsletters::app.admin.unified.add-customer-number') }}
                </button>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.newsletters.unified.index') }}" 
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                {{ __('newsletters::app.common.actions.cancel') }}
            </a>
            <button type="submit" 
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('newsletters::app.common.actions.update') }}
            </button>
        </div>
    </form>

    <script>
        let customerNumberIndex = {{ count($customerNumbers) }};

        function addCustomerNumberRow() {
            const container = document.getElementById('customerNumbersContainer');
            const newRow = document.createElement('div');
            newRow.className = 'customer-number-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg';
            newRow.innerHTML = `
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('newsletters::app.admin.customer-numbers.phone-number') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="customer_numbers[${customerNumberIndex}][phone_number]" 
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                        required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('newsletters::app.admin.customer-numbers.name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="customer_numbers[${customerNumberIndex}][name]" 
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                        required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('newsletters::app.admin.customer-numbers.email') }}
                    </label>
                    <input type="email" name="customer_numbers[${customerNumberIndex}][email]" 
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            `;
            container.appendChild(newRow);
            customerNumberIndex++;
        }
    </script>
</x-admin::layouts>
