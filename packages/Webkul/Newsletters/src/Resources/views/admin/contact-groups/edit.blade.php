<x-admin::layouts>
    <x-slot:title>
        {{ __('newsletters::app.admin.contact-groups.edit-title') }} - {{ $group->name }}
    </x-slot:title>

    <div class="flex items-center justify-between mb-6">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            {{ __('newsletters::app.admin.contact-groups.edit-title') }} - {{ $group->name }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <button onclick="openImportModal()" class="primary-button">
                {{ __('newsletters::app.common.actions.import') }} CSV
            </button>
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
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">{{ old('description', $group->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        {{ __('newsletters::app.admin.contact-groups.has-external-integration') }}
                    </h3>
                    
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="has_external_integration" 
                                   id="has_external_integration"
                                   value="1"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                   {{ old('has_external_integration', $group->has_external_integration) ? 'checked' : '' }}
                                   onchange="toggleExternalIntegrationFields()">
                            <span class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                {{ __('newsletters::app.admin.contact-groups.has-external-integration') }}
                            </span>
                        </label>
                        @error('has_external_integration')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="external-integration-fields" class="space-y-4 {{ old('has_external_integration', $group->has_external_integration) ? '' : 'hidden' }}">
                        <div>
                            <label for="request_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('newsletters::app.admin.contact-groups.request-url') }}
                            </label>
                            <input type="url" 
                                   name="request_url" 
                                   id="request_url" 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                   value="{{ old('request_url', $group->request_url) }}"
                                   placeholder="https://example.com/api/contacts">
                            @error('request_url')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="request_token" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('newsletters::app.admin.contact-groups.request-token') }}
                            </label>
                            <input type="text" 
                                   name="request_token" 
                                   id="request_token" 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                   value="{{ old('request_token', $group->request_token) }}"
                                   placeholder="Введите токен для авторизации">
                            @error('request_token')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="auto_request_frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('newsletters::app.admin.contact-groups.auto-request-frequency') }}
                            </label>
                            <select name="auto_request_frequency" 
                                    id="auto_request_frequency" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                                <option value="">{{ __('newsletters::app.common.actions.select') }}</option>
                                <option value="86400" {{ old('auto_request_frequency', $group->auto_request_frequency) == '86400' || old('auto_request_frequency', $group->auto_request_frequency) == 86400 ? 'selected' : '' }}>
                                    {{ __('newsletters::app.admin.contact-groups.frequency-daily') }}
                                </option>
                                <option value="172800" {{ old('auto_request_frequency', $group->auto_request_frequency) == '172800' || old('auto_request_frequency', $group->auto_request_frequency) == 172800 ? 'selected' : '' }}>
                                    {{ __('newsletters::app.admin.contact-groups.frequency-every-2-days') }}
                                </option>
                                <option value="259200" {{ old('auto_request_frequency', $group->auto_request_frequency) == '259200' || old('auto_request_frequency', $group->auto_request_frequency) == 259200 ? 'selected' : '' }}>
                                    {{ __('newsletters::app.admin.contact-groups.frequency-every-3-days') }}
                                </option>
                                <option value="604800" {{ old('auto_request_frequency', $group->auto_request_frequency) == '604800' || old('auto_request_frequency', $group->auto_request_frequency) == 604800 ? 'selected' : '' }}>
                                    {{ __('newsletters::app.admin.contact-groups.frequency-weekly') }}
                                </option>
                            </select>
                            @error('auto_request_frequency')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ __('newsletters::app.admin.contact-groups.external-import.title') }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('newsletters::app.admin.contact-groups.external-import.description') }}
                                    </p>
                                </div>
                                <button type="button"
                                        class="primary-button w-full lg:w-auto"
                                        onclick="runExternalImport()">
                                    {{ __('newsletters::app.admin.contact-groups.external-import.start-button') }}
                                </button>
                            </div>

                            <div id="external-import-progress" class="hidden mt-4">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                    <div id="external-import-progress-bar"
                                         class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300"
                                         style="width: 0%;"></div>
                                </div>
                                <div class="mt-2 text-sm text-gray-600 dark:text-gray-300 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <span id="external-import-status"
                                          data-idle="{{ __('newsletters::app.admin.contact-groups.external-import.status-idle') }}"
                                          data-preparing="{{ __('newsletters::app.admin.contact-groups.external-import.status-preparing') }}"
                                          data-page-template="{{ __('newsletters::app.admin.contact-groups.external-import.status-page', ['current' => '__CURRENT__', 'total' => '__TOTAL__']) }}"
                                          data-success="{{ __('newsletters::app.admin.contact-groups.external-import.status-success') }}"
                                          data-error="{{ __('newsletters::app.admin.contact-groups.external-import.status-error', ['message' => '__MESSAGE__']) }}">
                                        {{ __('newsletters::app.admin.contact-groups.external-import.status-idle') }}
                                    </span>
                                    <span id="external-import-summary" class="text-sm text-gray-500 dark:text-gray-400"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            function toggleExternalIntegrationFields() {
                const checkbox = document.getElementById('has_external_integration');
                const fields = document.getElementById('external-integration-fields');
                
                if (checkbox.checked) {
                    fields.classList.remove('hidden');
                } else {
                    fields.classList.add('hidden');
                }
            }

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                toggleExternalIntegrationFields();
            });
        </script>
        @endpush

        <div class="flex items-center justify-end gap-x-2.5">
            <a href="{{ route('admin.newsletters.contact-groups.index') }}" class="secondary-button">
                {{ __('newsletters::app.common.actions.cancel') }}
            </a>
            <button type="submit" class="primary-button">
                {{ __('newsletters::app.common.actions.update') }}
            </button>
        </div>
    </form>

    <!-- Contacts Table -->
    <div class="mt-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {{ __('newsletters::app.admin.contacts.title') }}
        </h2>
        @include('newsletters::admin.components.contacts-table', ['contactGroupId' => $group->id])
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
                <div id="uploadStep" class="space-y-4 columns-2 sm:columns-1">
                    <div class="">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('newsletters::app.common.fields.csv_file') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="csvFile" accept=".csv,.txt" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                    </div>
{{--                    <div>--}}
{{--                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"> Формат CSV </label><div id="formatInfo" class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded border">--}}
{{--                            <p class="mb-1 text-xs font-medium">newsletters::app.admin.contacts.csv_format_customers:</p>--}}
{{--                            <code class="block text-xs font-mono">phone_number,name,email</code>--}}
{{--                        </div>--}}
{{--                    </div>--}}
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
        let externalImportState = {
            running: false,
            currentPage: 1,
            totalPages: null,
            imported: 0,
            skipped: 0,
        };

        function getExternalImportElements() {
            return {
                progress: document.getElementById('external-import-progress'),
                progressBar: document.getElementById('external-import-progress-bar'),
                status: document.getElementById('external-import-status'),
                summary: document.getElementById('external-import-summary'),
            };
        }

        function showExternalImportProgressState(stateKey, extraMessage = '') {
            const { progress, status } = getExternalImportElements();

            if (!progress || !status) {
                return;
            }

            progress.classList.remove('hidden');

            const template = status.dataset[stateKey];

            if (template) {
                status.textContent = template.replace('__MESSAGE__', extraMessage);
            }
        }

        function updateExternalImportProgress(currentPage, totalPages) {
            const { progress, progressBar, status } = getExternalImportElements();

            if (!progress || !progressBar || !status) {
                return;
            }

            progress.classList.remove('hidden');

            const safeTotal = Math.max(totalPages || 1, 1);
            const percentage = Math.min(100, Math.round((currentPage / safeTotal) * 100));
            progressBar.style.width = percentage + '%';

            const template = status.dataset.pageTemplate;
            if (template) {
                status.textContent = template.replace('__CURRENT__', currentPage).replace('__TOTAL__', safeTotal);
            }
        }

        function updateExternalImportSummary(state) {
            const { summary } = getExternalImportElements();

            if (!summary) {
                return;
            }

            summary.textContent = '{{ __('newsletters::app.admin.contact-groups.external-import.summary') }}'
                .replace('__IMPORTED__', state.imported)
                .replace('__SKIPPED__', state.skipped);
        }

        function finalizeExternalImport(isSuccess, message = '') {
            const { status } = getExternalImportElements();

            if (!status) {
                return;
            }

            if (isSuccess) {
                status.textContent = status.dataset.success;
            } else {
                status.textContent = status.dataset.error.replace('__MESSAGE__', message);
            }
        }

        async function runExternalImport() {
            if (externalImportState.running) {
                return;
            }

            const integrationCheckbox = document.getElementById('has_external_integration');
            const requestUrlInput = document.getElementById('request_url');
            const requestTokenInput = document.getElementById('request_token');

            const hasIntegration = integrationCheckbox ? integrationCheckbox.checked : false;
            const requestUrl = requestUrlInput ? requestUrlInput.value.trim() : '';
            const requestToken = requestTokenInput ? requestTokenInput.value.trim() : '';

            if (!hasIntegration) {
                alert('{{ __('newsletters::app.admin.contact-groups.external-import.integration-disabled') }}');
                return;
            }

            if (!requestUrl || !requestToken) {
                alert('{{ __('newsletters::app.admin.contact-groups.external-import.fill-fields') }}');
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]');

            if (!csrfToken) {
                alert('CSRF token not found. Please refresh the page.');
                return;
            }

            externalImportState = {
                running: true,
                currentPage: 1,
                totalPages: null,
                imported: 0,
                skipped: 0,
            };

            showExternalImportProgressState('preparing');
            updateExternalImportSummary(externalImportState);

            const token = csrfToken.getAttribute('content');

            try {
                while (true) {
                    const response = await fetch('{{ route('admin.newsletters.contact-groups.external-import', $group->id) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            page: externalImportState.currentPage,
                            request_url: requestUrl,
                            request_token: requestToken,
                        }),
                        credentials: 'same-origin',
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || data.details || 'Request failed');
                    }

                    const currentPage = data.page || externalImportState.currentPage;
                    const totalPages = data.total_pages || externalImportState.totalPages || currentPage;

                    externalImportState.totalPages = totalPages;
                    externalImportState.imported += data.imported || 0;
                    externalImportState.skipped += data.skipped || 0;

                    updateExternalImportProgress(currentPage, totalPages);
                    updateExternalImportSummary(externalImportState);

                    if (currentPage >= totalPages) {
                        finalizeExternalImport(true);
                        break;
                    }

                    externalImportState.currentPage = currentPage + 1;
                }

                alert('{{ __('newsletters::app.admin.contact-groups.external-import.completed') }}'.replace('__IMPORTED__', externalImportState.imported));
                setTimeout(() => window.location.reload(), 1000);
            } catch (error) {
                finalizeExternalImport(false, error.message);
                alert('{{ __('newsletters::app.admin.contact-groups.external-import.failed') }}'.replace('__ERROR__', error.message));
            } finally {
                externalImportState.running = false;
            }
        }

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

                const csrfToken = document.querySelector('meta[name="csrf-token"]');

                if (!csrfToken) {
                    alert('CSRF токен не найден. Пожалуйста, обновите страницу.');
                    backToUpload();
                    return;
                }

                const token = csrfToken.getAttribute('content');
                formData.append('_token', token);

                const response = await fetch('{{ route('admin.newsletters.contact-groups.csv.preview') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: formData,
                    credentials: 'same-origin'
                });

                // Check if response is JSON
                let data;
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    // If redirect (302), response is HTML
                    const text = await response.text();
                    console.error('Unexpected response:', text);
                    alert('Ошибка: получен редирект. Возможно, проблема с аутентификацией или CSRF токеном. Пожалуйста, обновите страницу и попробуйте снова.');
                    backToUpload();
                    return;
                }

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

        // Функция нормализации строк для сравнения
        function normalizeString(str) {
            if (!str) return '';
            return str.toString()
                .toLowerCase()
                .trim()
                .replace(/[_\s\-\.]/g, '') // Убираем подчеркивания, пробелы, дефисы, точки
                .replace(/[а-яё]/g, function(match) {
                    // Транслитерация русских букв
                    const translit = {
                        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd',
                        'е': 'e', 'ё': 'e', 'ж': 'zh', 'з': 'z', 'и': 'i',
                        'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
                        'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't',
                        'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch',
                        'ш': 'sh', 'щ': 'sch', 'ъ': '', 'ы': 'y', 'ь': '',
                        'э': 'e', 'ю': 'yu', 'я': 'ya'
                    };
                    return translit[match] || match;
                });
        }

        // Функция автоматического сопоставления полей
        function autoMapField(field, fieldLabel, csvHeaders, usedIndices) {
            // Создаем список вариантов для сопоставления
            const matchVariants = [
                field, // Название поля модели (например, "full_name", "phone")
                fieldLabel, // Перевод поля (например, "ФИО", "Телефон")
                field.replace(/_/g, ' '), // Название с пробелами (например, "full name")
                field.replace(/_/g, ''), // Название без подчеркиваний (например, "fullname")
            ];

            // Нормализуем все варианты
            const normalizedVariants = matchVariants.map(v => normalizeString(v));

            let bestMatchIndex = -1;
            let bestMatchScore = 0;
            const matches = []; // Массив всех совпадений с их оценками

            // Ищем все совпадения среди заголовков CSV
            csvHeaders.forEach((header, index) => {
                const normalizedHeader = normalizeString(header);
                let score = 0;
                const isUsed = usedIndices && usedIndices.has(index);
                
                // Проверяем точное совпадение
                if (normalizedVariants.includes(normalizedHeader)) {
                    score = 100;
                } else if (!isUsed) {
                    // Проверяем частичное совпадение только для неиспользованных индексов
                    normalizedVariants.forEach(variant => {
                        if (variant && normalizedHeader) {
                            // Если заголовок начинается с варианта или наоборот
                            if (normalizedHeader.startsWith(variant) || variant.startsWith(normalizedHeader)) {
                                score = Math.max(score, 80);
                            }
                            
                            // Если заголовок содержит вариант или наоборот
                            if (normalizedHeader.includes(variant) || variant.includes(normalizedHeader)) {
                                score = Math.max(score, 60);
                            }
                            
                            // Если есть общие слова (для составных названий)
                            const headerWords = normalizedHeader.split(/\s+/).filter(w => w.length > 0);
                            const variantWords = variant.split(/\s+/).filter(w => w.length > 0);
                            const commonWords = headerWords.filter(w => variantWords.includes(w));
                            if (commonWords.length > 0) {
                                score = Math.max(score, 40 + commonWords.length * 10);
                            }
                        }
                    });
                }
                
                if (score >= 40) {
                    matches.push({ index, score, isUsed });
                    if (score > bestMatchScore && !isUsed) {
                        bestMatchScore = score;
                        bestMatchIndex = index;
                    }
                }
            });

            // Если есть точное совпадение, используем его (даже если индекс уже использован)
            const exactMatch = matches.find(m => m.score === 100);
            if (exactMatch) {
                return exactMatch.index;
            }

            // Возвращаем лучшее частичное совпадение среди неиспользованных
            return bestMatchIndex >= 0 ? bestMatchIndex : null;
        }

        function showMappingStep(rowCount) {
            document.getElementById('uploadStep').classList.add('hidden');
            document.getElementById('mappingStep').classList.remove('hidden');

            document.getElementById('rowCount').textContent = '{{ __('newsletters::app.admin.contacts.rows-found') }}: ' + rowCount;

            const mappingTable = document.getElementById('mappingTable');
            mappingTable.innerHTML = '';

            // Отслеживаем уже использованные индексы CSV, чтобы избежать дублирования
            const usedIndices = new Set();

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

                // Автоматическое сопоставление
                const autoMappedIndex = autoMapField(field, contactFields[field], csvHeaders, usedIndices);
                if (autoMappedIndex !== null) {
                    select.value = autoMappedIndex;
                    usedIndices.add(autoMappedIndex);
                }

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

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    alert('CSRF токен не найден. Пожалуйста, обновите страницу.');
                    document.getElementById('mappingStep').classList.remove('hidden');
                    return;
                }

                const token = csrfToken.getAttribute('content');
                formData.append('_token', token);

                const response = await fetch('{{ route('admin.newsletters.contact-groups.import', $group->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (data.success) {
                    let message = data.message;
                    
                    // Add detailed information if available
                    if (data.total_rows !== undefined) {
                        message += '\n\nВсего строк обработано: ' + data.total_rows;
                    }
                    
                    if (data.skipped > 0 && data.skipped_reasons) {
                        const reasons = [];
                        if (data.skipped_reasons.empty_name > 0) {
                            reasons.push('Без имени: ' + data.skipped_reasons.empty_name);
                        }
                        if (data.skipped_reasons.empty_phone > 0) {
                            reasons.push('Без телефона: ' + data.skipped_reasons.empty_phone);
                        }
                        if (data.skipped_reasons.duplicate > 0) {
                            reasons.push('Дубликаты: ' + data.skipped_reasons.duplicate);
                        }
                        if (data.skipped_reasons.error > 0) {
                            reasons.push('Ошибки: ' + data.skipped_reasons.error);
                        }
                        
                        if (reasons.length > 0) {
                            message += '\nПропущено (' + data.skipped + '): ' + reasons.join(', ');
                        }
                    }
                    
                    if (data.errors && data.errors.length > 0) {
                        message += '\n\nОшибки:\n' + data.errors.slice(0, 10).join('\n');
                        if (data.errors.length > 10) {
                            message += '\n... и ещё ' + (data.errors.length - 10) + ' ошибок';
                        }
                    }
                    
                    alert(message);
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
</x-admin::layouts>

