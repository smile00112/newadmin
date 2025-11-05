<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.contact-groups.contacts') }} - {{ $group->name }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.newsletters.contact-groups.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 mb-2 inline-block">
                ← {{ __('newsletters::app.common.actions.back') }} {{ __('newsletters::app.admin.contact-groups.title') }}
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $group->name }}
            </h1>
            @if($group->description)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $group->description }}
                </p>
            @endif
        </div>

        <div class="flex items-center gap-x-2.5">
            <button onclick="openImportModal()" class="primary-button">
                {{ __('newsletters::app.common.actions.import') }} CSV
            </button>
            <a href="{{ route('admin.newsletters.contact-groups.edit', $group->id) }}" class="secondary-button">
                {{ __('newsletters::app.common.actions.edit') }} {{ __('newsletters::app.admin.contact-groups.group') }}
            </a>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-10">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('newsletters::app.admin.contacts.import-csv') }}
                    </h3>
                    <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <span class="icon-cross text-2xl"></span>
                    </button>
                </div>

                <!-- Step 1: Upload File -->
                <div id="uploadStep" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.common.fields.csv_file') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="csvFile" accept=".csv,.txt" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('newsletters::app.admin.contacts.delimiter') }}
                            </label>
                            <input type="text" id="delimiter" value="," maxlength="1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        </div>

                        <div class="flex items-end">
                            <label class="flex items-center">
                                <input type="checkbox" id="hasHeader" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                    {{ __('newsletters::app.admin.contacts.has-header') }}
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-x-2 pt-4">
                        <button onclick="closeImportModal()" class="secondary-button">
                            {{ __('newsletters::app.common.actions.cancel') }}
                        </button>
                        <button onclick="previewCsv()" class="primary-button">
                            {{ __('newsletters::app.common.actions.next') }}
                        </button>
                    </div>
                </div>

                <!-- Step 2: Column Mapping -->
                <div id="mappingStep" class="hidden space-y-4">
                    <div id="mappingInfo" class="text-sm text-gray-600 dark:text-gray-400 mb-4 p-3 bg-blue-50 dark:bg-blue-900 rounded">
                        <p class="font-medium">{{ __('newsletters::app.admin.contacts.select-columns') }}</p>
                        <p id="rowCount" class="mt-1"></p>
                    </div>

                    <div class="max-h-96 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-md">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        {{ __('newsletters::app.admin.contacts.field') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        {{ __('newsletters::app.admin.contacts.csv-column') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="mappingTable" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Dynamic mapping rows will be inserted here -->
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end gap-x-2 pt-4">
                        <button onclick="backToUpload()" class="secondary-button">
                            {{ __('newsletters::app.common.actions.back') }}
                        </button>
                        <button onclick="importContacts()" class="primary-button">
                            {{ __('newsletters::app.common.actions.import') }}
                        </button>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="hidden text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('newsletters::app.common.messages.loading') }}</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const contactFields = {
            'full_name': '{{ __('newsletters::app.admin.contacts.field-full-name') }}',
            'phone': '{{ __('newsletters::app.admin.contacts.field-phone') }}',
            'email': '{{ __('newsletters::app.admin.contacts.field-email') }}',
            'gender': '{{ __('newsletters::app.admin.contacts.field-gender') }}',
            'last_order_date': '{{ __('newsletters::app.admin.contacts.field-last-order-date') }}',
            'registration_date': '{{ __('newsletters::app.admin.contacts.field-registration-date') }}',
            'birth_date': '{{ __('newsletters::app.admin.contacts.field-birth-date') }}',
            'orders_count': '{{ __('newsletters::app.admin.contacts.field-orders-count') }}',
            'average_check': '{{ __('newsletters::app.admin.contacts.field-average-check') }}',
            'total_check': '{{ __('newsletters::app.admin.contacts.field-total-check') }}',
            'average_order_rating': '{{ __('newsletters::app.admin.contacts.field-average-rating') }}',
            'favorite_category': '{{ __('newsletters::app.admin.contacts.field-favorite-category') }}',
            'favorite_dish': '{{ __('newsletters::app.admin.contacts.field-favorite-dish') }}',
            'store': '{{ __('newsletters::app.admin.contacts.field-store') }}',
        };

        let csvHeaders = [];
        let csvFile = null;
        let delimiter = ',';
        let hasHeader = true;

        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
            resetModal();
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
            resetModal();
        }

        function resetModal() {
            document.getElementById('uploadStep').classList.remove('hidden');
            document.getElementById('mappingStep').classList.add('hidden');
            document.getElementById('loadingIndicator').classList.add('hidden');
            document.getElementById('csvFile').value = '';
            document.getElementById('delimiter').value = ',';
            document.getElementById('hasHeader').checked = true;
            csvHeaders = [];
            csvFile = null;
        }

        function backToUpload() {
            document.getElementById('uploadStep').classList.remove('hidden');
            document.getElementById('mappingStep').classList.add('hidden');
        }

        async function previewCsv() {
            const fileInput = document.getElementById('csvFile');
            const file = fileInput.files[0];

            if (!file) {
                alert('{{ __('newsletters::app.admin.contacts.please-select-file') }}');
                return;
            }

            csvFile = file;
            delimiter = document.getElementById('delimiter').value || ',';
            hasHeader = document.getElementById('hasHeader').checked;

            // Show loading
            document.getElementById('uploadStep').classList.add('hidden');
            document.getElementById('mappingStep').classList.add('hidden');
            document.getElementById('loadingIndicator').classList.remove('hidden');

            try {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('delimiter', delimiter);
                formData.append('has_header', hasHeader ? '1' : '0');

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const response = await fetch('{{ route('admin.newsletters.contact-groups.csv.preview') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    csvHeaders = data.headers;
                    showMappingStep(data.row_count);
                } else {
                    alert(data.message || '{{ __('newsletters::app.admin.contacts.import-failed') }}');
                    backToUpload();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('{{ __('newsletters::app.admin.contacts.import-failed') }}: ' + error.message);
                backToUpload();
            } finally {
                document.getElementById('loadingIndicator').classList.add('hidden');
            }
        }

        function showMappingStep(rowCount) {
            document.getElementById('uploadStep').classList.add('hidden');
            document.getElementById('mappingStep').classList.remove('hidden');

            document.getElementById('rowCount').textContent = '{{ __('newsletters::app.admin.contacts.rows-found') }}: ' + rowCount;

            const mappingTable = document.getElementById('mappingTable');
            mappingTable.innerHTML = '';

            Object.keys(contactFields).forEach(field => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 dark:hover:bg-gray-700';

                const fieldCell = document.createElement('td');
                fieldCell.className = 'px-4 py-3 text-sm text-gray-900 dark:text-white';
                fieldCell.textContent = contactFields[field];
                row.appendChild(fieldCell);

                const selectCell = document.createElement('td');
                selectCell.className = 'px-4 py-3';

                const select = document.createElement('select');
                select.className = 'w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white';
                select.name = `mapping[${field}]`;
                select.id = `mapping_${field}`;

                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = '{{ __('newsletters::app.admin.contacts.not-selected') }}';
                select.appendChild(emptyOption);

                csvHeaders.forEach((header, index) => {
                    const option = document.createElement('option');
                    option.value = index;
                    option.textContent = header;
                    select.appendChild(option);
                });

                selectCell.appendChild(select);
                row.appendChild(selectCell);

                mappingTable.appendChild(row);
            });
        }

        async function importContacts() {
            const mapping = {};
            Object.keys(contactFields).forEach(field => {
                const select = document.getElementById(`mapping_${field}`);
                const value = select.value;
                if (value !== '') {
                    mapping[field] = parseInt(value);
                }
            });

            // Validate required fields
            if (!mapping['full_name'] && mapping['full_name'] !== 0) {
                alert('{{ __('newsletters::app.admin.contacts.field-required', ['field' => __('newsletters::app.admin.contacts.field-full-name')]) }}');
                return;
            }

            if (!mapping['phone'] && mapping['phone'] !== 0) {
                alert('{{ __('newsletters::app.admin.contacts.field-required', ['field' => __('newsletters::app.admin.contacts.field-phone')]) }}');
                return;
            }

            document.getElementById('mappingStep').classList.add('hidden');
            document.getElementById('loadingIndicator').classList.remove('hidden');

            try {
                const formData = new FormData();
                formData.append('file', csvFile);
                formData.append('delimiter', delimiter);
                formData.append('has_header', hasHeader ? 1 : 0);
                formData.append('mapping', JSON.stringify(mapping));

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const response = await fetch('{{ route('admin.newsletters.contact-groups.import', $group->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message + (data.skipped > 0 ? ' Пропущено: ' + data.skipped : ''));
                    closeImportModal();
                    window.location.reload();
                } else {
                    alert(data.message || '{{ __('newsletters::app.admin.contacts.import-failed') }}');
                    document.getElementById('mappingStep').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('{{ __('newsletters::app.admin.contacts.import-failed') }}: ' + error.message);
                document.getElementById('mappingStep').classList.remove('hidden');
            } finally {
                document.getElementById('loadingIndicator').classList.add('hidden');
            }
        }
    </script>
    @endpush

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.contacts.full-name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.contacts.phone') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.contacts.email') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.admin.contacts.orders-count') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('newsletters::app.common.fields.created_at') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($contacts as $contact)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $contact->id }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ $contact->full_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $contact->phone }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $contact->email ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $contact->orders_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $contact->created_at->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-gray-500 dark:text-gray-400">
                                    {{ __('newsletters::app.admin.contacts.no-contacts') }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contacts->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>
</x-admin::layouts>

