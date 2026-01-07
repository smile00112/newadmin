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
                        <button onclick="prepareImport()" class="primary-button">
                            {{ __('newsletters::app.common.actions.next') }}
                        </button>
                    </div>
                </div>

                <!-- Step 3: Import Process Preview -->
                <div id="processStep" class="hidden space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                Источник данных
                            </h4>
                            <dl class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                <div class="flex justify-between gap-4">
                                    <dt class="font-medium">Файл</dt>
                                    <dd id="processFileName" class="text-right truncate max-w-[180px]">—</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="font-medium">{{ __('newsletters::app.admin.contacts.rows-found') }}</dt>
                                    <dd id="processRowCount" class="text-right">—</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="font-medium">{{ __('newsletters::app.admin.contacts.delimiter') }}</dt>
                                    <dd id="processDelimiter" class="text-right">,</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="font-medium">{{ __('newsletters::app.admin.contacts.has-header') }}</dt>
                                    <dd id="processHeader" class="text-right">—</dd>
                                </div>
                            </dl>
                        </div>
                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ __('newsletters::app.admin.contacts.select-columns') }}
                            </h4>
                            <ul id="processMappingList" class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300 max-h-48 overflow-y-auto pr-1">
                                <li class="text-gray-400">{{ __('newsletters::app.admin.contacts.not-selected') }}</li>
                            </ul>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                Прогресс импорта
                            </span>
                            <span id="importProgressValue" class="text-xs text-gray-500 dark:text-gray-400">0%</span>
                        </div>
                        <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div id="importProgressBar" class="h-3 bg-indigo-600 rounded-full transition-all duration-300" style="width: 0%;"></div>
                        </div>
                        <p id="importProgressStatus" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Ожидает запуска
                        </p>
                    </div>

                    <div class="flex justify-end gap-x-2 pt-4">
                        <button onclick="backToMapping()" class="secondary-button">
                            {{ __('newsletters::app.common.actions.back') }}
                        </button>
                        <button onclick="startImport()" class="primary-button">
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
            'telegram_user_id': '{{ __('newsletters::app.admin.contacts.field-telegram-user-id') }}',
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
        let rowCount = 0;
        let preparedImportPayload = null;
        let progressInterval = null;
        let progressValue = 0;

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
            document.getElementById('processStep').classList.add('hidden');
            document.getElementById('loadingIndicator').classList.add('hidden');
            document.getElementById('csvFile').value = '';
            document.getElementById('delimiter').value = ',';
            document.getElementById('hasHeader').checked = true;
            csvHeaders = [];
            csvFile = null;
            rowCount = 0;
            preparedImportPayload = null;
            stopProgressSimulation();
            resetProgressUI();
            document.getElementById('processMappingList').innerHTML = '<li class="text-gray-400">{{ __('newsletters::app.admin.contacts.not-selected') }}</li>';
            document.getElementById('processFileName').textContent = '—';
            document.getElementById('processRowCount').textContent = '—';
            document.getElementById('processDelimiter').textContent = ',';
            document.getElementById('processHeader').textContent = '—';
        }

        function backToUpload() {
            document.getElementById('uploadStep').classList.remove('hidden');
            document.getElementById('mappingStep').classList.add('hidden');
            document.getElementById('processStep').classList.add('hidden');
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

        function showMappingStep(totalRows) {
            document.getElementById('uploadStep').classList.add('hidden');
            document.getElementById('mappingStep').classList.remove('hidden');
            document.getElementById('processStep').classList.add('hidden');

            rowCount = totalRows;
            document.getElementById('rowCount').textContent = '{{ __('newsletters::app.admin.contacts.rows-found') }}: ' + totalRows;

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

        async function prepareImport() {
            const mapping = collectMapping();

            if (!validateMapping(mapping)) {
                return;
            }

            try {
                await persistMapping(mapping);
            } catch (error) {
                alert('{{ __('newsletters::app.admin.contacts.import-failed') }}: ' + error.message);
                return;
            }

            const mappingLabels = {};
            Object.keys(mapping).forEach(field => {
                mappingLabels[field] = csvHeaders[mapping[field]] ?? null;
            });

            preparedImportPayload = {
                group_id: {{ $group->id }},
                delimiter,
                has_header: hasHeader,
                mapping,
                headers: csvHeaders,
                file_name: csvFile ? csvFile.name : null,
                row_count: rowCount,
                file_preview: csvFile ? { name: csvFile.name, size: csvFile.size, type: csvFile.type } : null,
                mapping_labels: mappingLabels,
            };

            renderProcessSummary(mapping);
            document.getElementById('mappingStep').classList.add('hidden');
            document.getElementById('processStep').classList.remove('hidden');
        }

        function collectMapping() {
            const mapping = {};

            Object.keys(contactFields).forEach(field => {
                const select = document.getElementById(`mapping_${field}`);
                if (select && select.value !== '') {
                    mapping[field] = parseInt(select.value, 10);
                }
            });

            return mapping;
        }

        function validateMapping(mapping) {
            if (!mapping.hasOwnProperty('full_name')) {
                alert('{{ __('newsletters::app.admin.contacts.field-required', ['field' => __('newsletters::app.admin.contacts.field-full-name')]) }}');
                return false;
            }

            if (!mapping.hasOwnProperty('phone')) {
                alert('{{ __('newsletters::app.admin.contacts.field-required', ['field' => __('newsletters::app.admin.contacts.field-phone')]) }}');
                return false;
            }

            return true;
        }

        async function persistMapping(mapping) {
            console.log('persistMapping called', {
                mapping,
                headers: csvHeaders,
                groupId: {{ $group->id }},
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const url = '{{ route('admin.newsletters.contact-groups.import-mapping', $group->id) }}';

            console.log('Sending request to:', url);

            try {
                const requestBody = {
                    mapping,
                    headers: csvHeaders,
                };

                console.log('Request body:', JSON.stringify(requestBody, null, 2));

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(requestBody),
                });

                console.log('Response status:', response.status, response.statusText);

                let data;
                try {
                    const responseText = await response.text();
                    console.log('Response text:', responseText);
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Failed to parse response:', e);
                    throw new Error('Invalid response from server: ' + e.message);
                }

                console.log('Response data:', data);

                if (!response.ok) {
                    const errorMessage = data.message || data.errors 
                        ? JSON.stringify(data.errors || data.message) 
                        : `Server error: ${response.status} ${response.statusText}`;
                    throw new Error(errorMessage);
                }

                if (!data.success) {
                    throw new Error(data.message || 'Failed to persist mapping');
                }

                console.log('Mapping saved successfully:', data);
            } catch (error) {
                console.error('Error saving mapping:', error);
                throw error;
            }
        }

        function renderProcessSummary(mapping) {
            document.getElementById('processFileName').textContent = csvFile ? csvFile.name : '—';
            document.getElementById('processRowCount').textContent = rowCount;
            document.getElementById('processDelimiter').textContent = delimiter;
            document.getElementById('processHeader').textContent = hasHeader ? 'Да' : 'Нет';

            const mappingList = document.getElementById('processMappingList');
            mappingList.innerHTML = '';

            Object.keys(mapping).forEach(field => {
                const li = document.createElement('li');
                li.className = 'flex items-center justify-between gap-2 border-b border-dashed border-gray-200 dark:border-gray-700 pb-1';

                const label = document.createElement('span');
                label.className = 'font-medium text-gray-900 dark:text-white text-sm';
                label.textContent = contactFields[field];

                const value = document.createElement('span');
                value.className = 'text-gray-600 dark:text-gray-300 text-sm';
                value.textContent = csvHeaders[mapping[field]] ?? '{{ __('newsletters::app.admin.contacts.not-selected') }}';

                li.appendChild(label);
                li.appendChild(value);

                mappingList.appendChild(li);
            });

            if (mappingList.children.length === 0) {
                const empty = document.createElement('li');
                empty.className = 'text-gray-400';
                empty.textContent = '{{ __('newsletters::app.admin.contacts.not-selected') }}';
                mappingList.appendChild(empty);
            }

            resetProgressUI();
        }

        function backToMapping() {
            document.getElementById('processStep').classList.add('hidden');
            document.getElementById('mappingStep').classList.remove('hidden');
            stopProgressSimulation();
            resetProgressUI();
        }

        function startImport() {
            if (!preparedImportPayload) {
                alert('{{ __('newsletters::app.admin.contacts.please-select-file') }}');
                return;
            }

            console.log('Prepared import payload:', preparedImportPayload);
            simulateProgress();
        }

        function simulateProgress() {
            stopProgressSimulation();
            progressValue = 0;
            updateProgressUI(progressValue, 'Запуск импорта...');

            progressInterval = setInterval(() => {
                progressValue = Math.min(progressValue + Math.floor(Math.random() * 20) + 10, 100);
                updateProgressUI(progressValue, progressValue < 100 ? 'Идет подготовка данных...' : 'Данные готовы для отправки');

                if (progressValue === 100) {
                    stopProgressSimulation();
                }
            }, 600);
        }

        function updateProgressUI(value, statusText) {
            const progressBar = document.getElementById('importProgressBar');
            const progressValueLabel = document.getElementById('importProgressValue');
            const progressStatus = document.getElementById('importProgressStatus');

            progressBar.style.width = value + '%';
            progressValueLabel.textContent = value + '%';
            progressStatus.textContent = statusText;
        }

        function resetProgressUI() {
            updateProgressUI(0, 'Ожидает запуска');
        }

        function stopProgressSimulation() {
            if (progressInterval) {
                clearInterval(progressInterval);
                progressInterval = null;
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
                            {{ __('newsletters::app.admin.contacts.table.telegram-user-id') }}
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
                                {{ $contact->telegram_user_id ?? '-' }}
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
                            <td colspan="7" class="px-6 py-12 text-center">
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

