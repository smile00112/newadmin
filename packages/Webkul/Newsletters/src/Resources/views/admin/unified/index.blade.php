<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.unified.title') }}
    </x-slot:title>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.unified.title') }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <button type="button" class="primary-button" onclick="openCreateModal()">
                {{ __('newsletters::app.admin.unified.create-new') }}
            </button>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="switchTab('mailing-lists')" id="tab-mailing-lists" 
                        class="tab-button active py-4 px-1 border-b-2 font-medium text-sm">
                    {{ __('newsletters::app.admin.unified.mailing-lists') }}
                </button>
                <button onclick="switchTab('whatsapp-instances')" id="tab-whatsapp-instances" 
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm">
                    {{ __('newsletters::app.admin.unified.whatsapp-instances') }}
                </button>
                <button onclick="switchTab('customer-numbers')" id="tab-customer-numbers" 
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm">
                    {{ __('newsletters::app.admin.unified.customer-numbers') }}
                </button>
            </nav>
        </div>
    </div>

    <!-- Mailing Lists Tab -->
    <div id="content-mailing-lists" class="tab-content">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ __('newsletters::app.admin.unified.mailing-lists') }}
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.mailing-lists.message-text') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.mailing-lists.active') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.mailing-lists.start-at') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('admin::app.datagrid.action') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($mailingLists as $mailingList)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $mailingList->id }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ Str::limit($mailingList->message_text, 50) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $mailingList->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $mailingList->active ? __('admin::app.datagrid.yes') : __('admin::app.datagrid.no') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $mailingList->start_at ? $mailingList->start_at->format('Y-m-d H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="editEntry({{ $mailingList->id }})" 
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            {{ __('newsletters::app.common.actions.edit') }}
                                        </button>
                                        <button onclick="deleteEntry({{ $mailingList->id }})" 
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            {{ __('newsletters::app.common.actions.delete') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('newsletters::app.admin.mailing-lists.no-lists') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- WhatsApp Instances Tab -->
    <div id="content-whatsapp-instances" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ __('newsletters::app.admin.unified.whatsapp-instances') }}
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.whatsapp-instances.link-name') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.whatsapp-instances.login') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.whatsapp-instances.mailing-list') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('admin::app.datagrid.action') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($whatsappInstances as $instance)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $instance->id }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $instance->link_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $instance->login }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $instance->mailingList->message_text ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="editEntry({{ $instance->mailing_list_id }})" 
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            {{ __('newsletters::app.common.actions.edit') }}
                                        </button>
                                        <button onclick="deleteEntry({{ $instance->mailing_list_id }})" 
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            {{ __('newsletters::app.common.actions.delete') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('newsletters::app.admin.whatsapp-instances.no-instances') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Customer Numbers Tab -->
    <div id="content-customer-numbers" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ __('newsletters::app.admin.unified.customer-numbers') }}
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.customer-numbers.phone-number') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.customer-numbers.name') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.customer-numbers.email') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('newsletters::app.admin.customer-numbers.mailing-list') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($customerNumbers as $customer)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $customer->id }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $customer->phone_number }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $customer->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $customer->email ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $customer->mailingList->message_text ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('newsletters::app.admin.customer-numbers.no-numbers') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div id="createEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="modalTitle">
                        {{ __('newsletters::app.admin.unified.create-new') }}
                    </h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="unifiedForm" method="POST">
                    @csrf
                    <div id="formMethod" style="display: none;"></div>

                    <!-- Mailing List Section -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">
                            {{ __('newsletters::app.admin.unified.mailing-list-section') }}
                        </h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="mailing_list_message_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('newsletters::app.admin.mailing-lists.message-text') }} <span class="text-red-500">*</span>
                                </label>
                                <textarea name="mailing_list[message_text]" id="mailing_list_message_text" rows="3" 
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="mailing_list_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ __('newsletters::app.admin.mailing-lists.active') }}
                                    </label>
                                    <select name="mailing_list[active]" id="mailing_list_active" 
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="1">{{ __('admin::app.datagrid.yes') }}</option>
                                        <option value="0">{{ __('admin::app.datagrid.no') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="mailing_list_start_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ __('newsletters::app.admin.mailing-lists.start-at') }}
                                    </label>
                                    <input type="datetime-local" name="mailing_list[start_at]" id="mailing_list_start_at" 
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- WhatsApp Instance Section -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">
                            {{ __('newsletters::app.admin.unified.whatsapp-instance-section') }}
                        </h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="whatsapp_instance_link_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('newsletters::app.admin.whatsapp-instances.link-name') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="whatsapp_instance[link_name]" id="whatsapp_instance_link_name" 
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                                    required>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="whatsapp_instance_login" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ __('newsletters::app.admin.whatsapp-instances.login') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="whatsapp_instance[login]" id="whatsapp_instance_login" 
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

                    <!-- Customer Numbers Section -->
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">
                            {{ __('newsletters::app.admin.unified.customer-numbers-section') }}
                        </h4>
                        <div id="customerNumbersContainer">
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
                        </div>
                        <button type="button" onclick="addCustomerNumberRow()" 
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('newsletters::app.admin.unified.add-customer-number') }}
                        </button>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()" 
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            {{ __('newsletters::app.common.actions.cancel') }}
                        </button>
                        <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('newsletters::app.common.actions.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .tab-button {
            border-color: transparent;
            color: #6b7280;
        }
        .tab-button.active {
            border-color: #3b82f6;
            color: #3b82f6;
        }
        .tab-button:hover {
            color: #374151;
            border-color: #d1d5db;
        }
    </style>

    <script>
        let customerNumberIndex = 1;

        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            // Add active class to selected tab button
            document.getElementById('tab-' + tabName).classList.add('active');
        }

        function openCreateModal() {
            document.getElementById('modalTitle').textContent = '{{ __("newsletters::app.admin.unified.create-new") }}';
            document.getElementById('unifiedForm').action = '{{ route("admin.newsletters.unified.store") }}';
            document.getElementById('formMethod').innerHTML = '';
            document.getElementById('unifiedForm').reset();
            document.getElementById('createEditModal').classList.remove('hidden');
        }

        function editEntry(mailingListId) {
            // This would load the existing data into the form
            // For now, we'll just show the modal
            document.getElementById('modalTitle').textContent = '{{ __("newsletters::app.admin.unified.edit-entry") }}';
            document.getElementById('unifiedForm').action = '{{ route("admin.newsletters.unified.update", ":id") }}'.replace(':id', mailingListId);
            document.getElementById('formMethod').innerHTML = '@method("PUT")';
            document.getElementById('createEditModal').classList.remove('hidden');
        }

        function deleteEntry(mailingListId) {
            if (confirm('{{ __("newsletters::app.admin.unified.delete-confirm") }}')) {
                fetch('{{ route("admin.newsletters.unified.destroy", ":id") }}'.replace(':id', mailingListId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ __("newsletters::app.admin.unified.delete-failed") }}');
                });
            }
        }

        function closeModal() {
            document.getElementById('createEditModal').classList.add('hidden');
        }

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

        // Add form submission handler to prevent HTML5 validation on hidden fields
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('unifiedForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Ensure modal is visible before validation
                    const modal = document.getElementById('createEditModal');
                    if (modal && modal.classList.contains('hidden')) {
                        modal.classList.remove('hidden');
                    }

                    // Manual validation for message_text
                    const messageText = document.getElementById('mailing_list_message_text');
                    if (!messageText || !messageText.value.trim()) {
                        e.preventDefault();
                        alert('{{ __("newsletters::app.admin.mailing-lists.message-text") }} {{ __("admin::app.datagrid.is-required") }}');
                        messageText.focus();
                        return false;
                    }

                    // Validate WhatsApp instance fields
                    const linkName = document.getElementById('whatsapp_instance_link_name');
                    const login = document.getElementById('whatsapp_instance_login');
                    const password = document.getElementById('whatsapp_instance_password');
                    
                    if (!linkName || !linkName.value.trim()) {
                        e.preventDefault();
                        alert('{{ __("newsletters::app.admin.whatsapp-instances.link-name") }} {{ __("admin::app.datagrid.is-required") }}');
                        linkName.focus();
                        return false;
                    }
                    
                    if (!login || !login.value.trim()) {
                        e.preventDefault();
                        alert('{{ __("newsletters::app.admin.whatsapp-instances.login") }} {{ __("admin::app.datagrid.is-required") }}');
                        login.focus();
                        return false;
                    }
                    
                    if (!password || !password.value.trim()) {
                        e.preventDefault();
                        alert('{{ __("newsletters::app.admin.whatsapp-instances.password") }} {{ __("admin::app.datagrid.is-required") }}');
                        password.focus();
                        return false;
                    }

                    // Validate customer numbers
                    const customerRows = document.querySelectorAll('.customer-number-row');
                    for (let row of customerRows) {
                        const phoneInput = row.querySelector('input[name*="[phone_number]"]');
                        const nameInput = row.querySelector('input[name*="[name]"]');
                        
                        if (phoneInput && !phoneInput.value.trim()) {
                            e.preventDefault();
                            alert('{{ __("newsletters::app.admin.customer-numbers.phone-number") }} {{ __("admin::app.datagrid.is-required") }}');
                            phoneInput.focus();
                            return false;
                        }
                        
                        if (nameInput && !nameInput.value.trim()) {
                            e.preventDefault();
                            alert('{{ __("newsletters::app.admin.customer-numbers.name") }} {{ __("admin::app.datagrid.is-required") }}');
                            nameInput.focus();
                            return false;
                        }
                    }
                });
            }
        });
    </script>
</x-admin::layouts>
