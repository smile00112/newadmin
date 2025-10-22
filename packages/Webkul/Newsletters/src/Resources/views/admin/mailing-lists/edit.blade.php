<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.common.actions.edit') }} - {{ __('newsletters::app.admin.mailing-lists.title_sklon') }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.newsletters.mailing-lists.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('newsletters::app.common.actions.back') }}
            </a>
{{--            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">--}}
{{--                {{ __('newsletters::app.admin.mailing-lists.title') }} - {{ __('newsletters::app.common.actions.edit') }}--}}
{{--            </h1>--}}
        </div>
    </div>

    <form action="{{ route('admin.newsletters.mailing-lists.update', $mailingList->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Mailing List Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 my-5 p5">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('newsletters::app.common.actions.edit') }} {{ __('newsletters::app.admin.mailing-lists.title_sklon') }}
                        </h2>
                        <div class="flex gap-2 space-x-2">
                            <a href="{{ route('admin.newsletters.mailing-lists.index') }}"
                               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                {{ __('newsletters::app.common.actions.cancel') }}
                            </a>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('newsletters::app.common.actions.update') }}
                            </button>
                        </div>
                    </div>
                </div>
            <div class="p-4 space-y-4">
                    <!-- Message Text -->
                    <div>
                        <label for="message_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.mailing-lists.message-text') }}
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            name="message_text"
                            id="message_text"
                        rows="6"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="{{ __('newsletters::app.admin.mailing-lists.message-text') }}"
                            required
                        >{{ old('message_text', $mailingList->message_text) }}</textarea>
                        @error('message_text')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Active Status -->
                    <div class="flex items-center">
                        <label class="flex items-center space-x-3">
                            <input
                                type="checkbox"
                                name="active"
                                value="1"
                                {{ old('active', $mailingList->active) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                            >
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('newsletters::app.admin.mailing-lists.active') }}
                            </span>
                        </label>
                        @error('active')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Start At -->
{{--                    <div>--}}
{{--                        <label for="start_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">--}}
{{--                            {{ __('newsletters::app.admin.mailing-lists.start-at') }}--}}
{{--                        </label>--}}
{{--                        <input--}}
{{--                            type="datetime-local"--}}
{{--                            name="start_at"--}}
{{--                            id="start_at"--}}
{{--                            value="{{ old('start_at', $mailingList->start_at ? $mailingList->start_at->format('Y-m-d\TH:i') : '') }}"--}}
{{--                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"--}}
{{--                        >--}}
{{--                        @error('start_at')--}}
{{--                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>--}}
{{--                        @enderror--}}
{{--                    </div>--}}
{{--                    </div>--}}
            </div>
        </div>

        <!-- WhatsApp Instances and Customer Numbers Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- WhatsApp Instances Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('newsletters::app.admin.whatsapp-instances.title') }}
                    </h2>
                    <div class="flex space-x-2 gap-2">
                        <button type="button" onclick="addWhatsAppInstanceRow()"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('newsletters::app.common.actions.add') }} {{ __('newsletters::app.admin.whatsapp-instances.title') }}
                        </button>
                        <button type="button" onclick="openCSVImportModal('whatsapp')"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('newsletters::app.common.actions.import') }} CSV
                        </button>
                        </div>
                </div>
                </div>
                <div class="p-6">
                <div id="whatsappInstancesContainer">
                    @forelse($whatsappInstances as $index => $instance)
                        <div class="whatsapp-instance-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.whatsapp-instances.link-name') }}
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="whatsapp_instances[{{ $index }}][link_name]"
                                        value="{{ old('whatsapp_instances.' . $index . '.link_name', $instance->link_name) }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required
                                    >
                                        </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.whatsapp-instances.login') }}
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="whatsapp_instances[{{ $index }}][login]"
                                        value="{{ old('whatsapp_instances.' . $index . '.login', $instance->login) }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required
                                    >
                                    </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.whatsapp-instances.password') }}
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="password"
                                        name="whatsapp_instances[{{ $index }}][password]"
                                        value="{{ old('whatsapp_instances.' . $index . '.password', $instance->password) }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required
                                    >
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" onclick="removeWhatsAppInstanceRow(this)"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    {{ __('newsletters::app.common.actions.delete') }}
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.whatsapp-instances.no-instances') }}</p>
                        </div>
                    @endforelse
                </div>
                </div>
            </div>

            <!-- Customer Numbers Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('newsletters::app.admin.customer-numbers.title') }}
                    </h2>
                    <div class="flex space-x-2 gap-2">
                        <button type="button" onclick="addCustomerNumberRow()"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('newsletters::app.common.actions.add') }} {{ __('newsletters::app.admin.customer-numbers.title') }}
                        </button>
                        <button type="button" onclick="openCSVImportModal('customers')"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('newsletters::app.common.actions.import') }} CSV
                        </button>
                    </div>
                </div>
                </div>
                <div class="p-6">
                <div id="customerNumbersContainer">
                    @forelse($customerNumbers as $index => $customer)
                        <div class="customer-number-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.customer-numbers.phone-number') }}
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="customer_numbers[{{ $index }}][phone_number]"
                                        value="{{ old('customer_numbers.' . $index . '.phone_number', $customer->phone_number) }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required
                                    >
                                        </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.customer-numbers.name') }}
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="customer_numbers[{{ $index }}][name]"
                                        value="{{ old('customer_numbers.' . $index . '.name', $customer->name) }}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required
                                    >
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" onclick="removeCustomerNumberRow(this)"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    {{ __('newsletters::app.common.actions.delete') }}
                                </button>
                                </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.customer-numbers.no-numbers') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        </div>
        <!-- Submit Buttons -->
        <div class="flex justify-end space-x-3 mt-4">
            <a href="{{ route('admin.newsletters.mailing-lists.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                {{ __('newsletters::app.common.actions.cancel') }}
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                {{ __('newsletters::app.common.actions.update') }}
            </button>
        </div>
    </form>

    <!-- CSV Import Modals -->
    <div id="csvImportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 9999;" onclick="closeCSVImportModal()">
        <div class="modal-content relative top-20 sm:top-32 mx-auto p-4 sm:p-6 border w-80 max-w-md shadow-xl rounded-lg bg-white dark:bg-gray-800" style="z-index: 10000;" onclick="event.stopPropagation()">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="modalTitle">
                        {{ __('newsletters::app.common.actions.import') }} CSV
                    </h3>
                    <button onclick="closeCSVImportModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.common.fields.csv_file') }}
                    </label>
                    <input type="file" id="csvFile" accept=".csv"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('newsletters::app.common.fields.csv_format') }}
                    </label>
                    <div id="formatInfo" class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded border">
                        <!-- Format info will be populated by JavaScript -->
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button onclick="closeCSVImportModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        {{ __('newsletters::app.common.actions.cancel') }}
                    </button>
                    <button onclick="processCSVImport()"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('newsletters::app.common.actions.import') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        #csvImportModal {
            z-index: 9999 !important;
        }
        #csvImportModal .modal-content {
            z-index: 10000 !important;
        }
    </style>

    <script>
        let customerNumberIndex = {{ count($customerNumbers) }};
        let whatsappInstanceIndex = {{ count($whatsappInstances) }};
        let currentImportType = '';

        function addCustomerNumberRow() {
            const container = document.getElementById('customerNumbersContainer');
            const newRow = document.createElement('div');
            newRow.className = 'customer-number-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg';
            newRow.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.phone-number') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="customer_numbers[${customerNumberIndex}][phone_number]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.customer-numbers.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="customer_numbers[${customerNumberIndex}][name]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="removeCustomerNumberRow(this)"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        {{ __('newsletters::app.common.actions.delete') }}
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            customerNumberIndex++;
        }

        function removeCustomerNumberRow(button) {
            if (confirm('{{ __("newsletters::app.common.messages.confirm_delete") }}')) {
                button.closest('.customer-number-row').remove();
            }
        }

        function addWhatsAppInstanceRow() {
            const container = document.getElementById('whatsappInstancesContainer');
            const newRow = document.createElement('div');
            newRow.className = 'whatsapp-instance-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg';
            newRow.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.whatsapp-instances.link-name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="whatsapp_instances[${whatsappInstanceIndex}][link_name]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.whatsapp-instances.login') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="whatsapp_instances[${whatsappInstanceIndex}][login]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.admin.whatsapp-instances.password') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="whatsapp_instances[${whatsappInstanceIndex}][password]"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="removeWhatsAppInstanceRow(this)"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        {{ __('newsletters::app.common.actions.delete') }}
                    </button>
                </div>
            `;
            container.appendChild(newRow);
            whatsappInstanceIndex++;
        }

        function removeWhatsAppInstanceRow(button) {
            if (confirm('{{ __("newsletters::app.common.messages.confirm_delete") }}')) {
                button.closest('.whatsapp-instance-row').remove();
            }
        }

        function openCSVImportModal(type) {
            currentImportType = type;
            const modal = document.getElementById('csvImportModal');
            const title = document.getElementById('modalTitle');
            const formatInfo = document.getElementById('formatInfo');

            if (type === 'whatsapp') {
                title.textContent = '{{ __("newsletters::app.common.actions.import") }} WhatsApp CSV';
                formatInfo.innerHTML = `
                    <p class="mb-1 text-xs font-medium">{{ __('newsletters::app.common.csv_format_whatsapp') }}:</p>
                    <code class="block text-xs font-mono">link_name,login,password</code>
                `;
            } else if (type === 'customers') {
                title.textContent = '{{ __("newsletters::app.common.actions.import") }} Customers CSV';
                formatInfo.innerHTML = `
                    <p class="mb-1 text-xs font-medium">{{ __('newsletters::app.common.csv_format_customers') }}:</p>
                    <code class="block text-xs font-mono">phone_number,name,email</code>
                `;
            }

            modal.classList.remove('hidden');
        }

        function closeCSVImportModal() {
            document.getElementById('csvImportModal').classList.add('hidden');
            document.getElementById('csvFile').value = '';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeCSVImportModal();
            }
        });

        function processCSVImport() {
            const fileInput = document.getElementById('csvFile');
            const file = fileInput.files[0];

            if (!file) {
                alert('{{ __("newsletters::app.common.messages.please_select_file") }}');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const csv = e.target.result;
                    const lines = csv.split('\n').filter(line => line.trim());

                    console.log('CSV lines:', lines); // Debug log

                    if (lines.length < 2) {
                        alert('CSV file must contain at least a header row and one data row.');
                        return;
                    }

                    if (currentImportType === 'whatsapp') {
                        importWhatsAppInstances(lines);
                    } else if (currentImportType === 'customers') {
                        importCustomerNumbers(lines);
                    }

                    closeCSVImportModal();
                } catch (error) {
                    console.error('Error processing CSV:', error);
                    alert('Error processing CSV file: ' + error.message);
                }
            };
            reader.readAsText(file);
        }

        function importWhatsAppInstances(lines) {
            const container = document.getElementById('whatsappInstancesContainer');
            let importedCount = 0;

            console.log('Importing WhatsApp instances from CSV...');

            lines.forEach((line, index) => {
                if (index === 0) {
                    console.log('Header row:', line);
                    return; // Skip header
                }

                console.log('Processing line', index, ':', line);

                // Parse CSV line properly - handle commas within quoted fields
                const fields = parseCSVLine(line);
                console.log('Parsed fields:', fields);

                if (fields.length >= 3) {
                    const link_name = fields[0] ? fields[0].trim() : '';
                    const login = fields[1] ? fields[1].trim() : '';
                    const password = fields[2] ? fields[2].trim() : '';

                    console.log('Extracted data:', { link_name, login, password });

                    if (link_name && login && password) {
                        const newRow = document.createElement('div');
                        newRow.className = 'whatsapp-instance-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg';
                        newRow.innerHTML = `
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.whatsapp-instances.link-name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="whatsapp_instances[${whatsappInstanceIndex}][link_name]"
                                        value="${link_name.replace(/"/g, '&quot;')}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.whatsapp-instances.login') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="whatsapp_instances[${whatsappInstanceIndex}][login]"
                                        value="${login.replace(/"/g, '&quot;')}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.whatsapp-instances.password') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" name="whatsapp_instances[${whatsappInstanceIndex}][password]"
                                        value="${password.replace(/"/g, '&quot;')}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" onclick="removeWhatsAppInstanceRow(this)"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    {{ __('newsletters::app.common.actions.delete') }}
                                </button>
                            </div>
                        `;
                        container.appendChild(newRow);
                        whatsappInstanceIndex++;
                        importedCount++;
                        console.log('Added row for:', link_name, login);
                    } else {
                        console.log('Skipping row - missing required fields:', { link_name, login, password });
                    }
                } else {
                    console.log('Skipping row - not enough fields:', fields);
                }
            });

            console.log('Imported', importedCount, 'WhatsApp instances');
            if (importedCount === 0) {
                alert('No valid WhatsApp instances found in CSV. Please check the format.');
            } else {
                alert(`Successfully imported ${importedCount} WhatsApp instances.`);
            }
        }

        function importCustomerNumbers(lines) {
            const container = document.getElementById('customerNumbersContainer');
            let importedCount = 0;

            console.log('Importing customer numbers from CSV...');

            lines.forEach((line, index) => {
                if (index === 0) {
                    console.log('Header row:', line);
                    return; // Skip header
                }

                console.log('Processing line', index, ':', line);

                // Parse CSV line properly - handle commas within quoted fields
                const fields = parseCSVLine(line);
                console.log('Parsed fields:', fields);

                if (fields.length >= 2) {
                    const phone_number = fields[0] ? fields[0].trim() : '';
                    const name = fields[1] ? fields[1].trim() : '';

                    console.log('Extracted data:', { phone_number, name });

                    if (phone_number && name) {
                        const newRow = document.createElement('div');
                        newRow.className = 'customer-number-row grid grid-cols-1 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg';
                        newRow.innerHTML = `
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.customer-numbers.phone-number') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="customer_numbers[${customerNumberIndex}][phone_number]"
                                        value="${phone_number.replace(/"/g, '&quot;')}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('newsletters::app.admin.customer-numbers.name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="customer_numbers[${customerNumberIndex}][name]"
                                        value="${name.replace(/"/g, '&quot;')}"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" onclick="removeCustomerNumberRow(this)"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    {{ __('newsletters::app.common.actions.delete') }}
                                </button>
                            </div>
                        `;
                        container.appendChild(newRow);
                        customerNumberIndex++;
                        importedCount++;
                        console.log('Added row for:', name, phone_number);
                    } else {
                        console.log('Skipping row - missing required fields:', { phone_number, name });
                    }
                } else {
                    console.log('Skipping row - not enough fields:', fields);
                }
            });

            console.log('Imported', importedCount, 'customer numbers');
            if (importedCount === 0) {
                alert('No valid customer numbers found in CSV. Please check the format.');
            } else {
                alert(`Successfully imported ${importedCount} customer numbers.`);
            }
        }

        function parseCSVLine(line) {
            const result = [];
            let current = '';
            let inQuotes = false;

            for (let i = 0; i < line.length; i++) {
                const char = line[i];

                if (char === '"') {
                    inQuotes = !inQuotes;
                } else if (char === ',' && !inQuotes) {
                    result.push(current);
                    current = '';
                } else {
                    current += char;
                }
            }

            result.push(current);
            return result;
        }
    </script>
</x-admin::layouts>
