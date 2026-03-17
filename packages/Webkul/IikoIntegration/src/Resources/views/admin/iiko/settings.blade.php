<x-admin::layouts>
    <x-slot:title>
        @lang('iiko-integration::app.settings.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('iiko-integration::app.settings.title')
        </p>
    </div>

    <div class="mt-7">
        <x-admin::form
            :action="route('admin.iiko.settings.store')"
            method="POST"
        >
            <input type="hidden" name="channel_code" value="{{ $channelCode }}">

            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                {{-- Tabs navigation --}}
                <div class="mb-4 border-b border-gray-200 dark:border-gray-800">
                    <nav class="flex space-x-2" aria-label="Tabs">
                        @foreach ($tabs as $tabKey => $tabName)
                            <a
                                href="{{ route('admin.iiko.settings.index', ['tab' => $tabKey, 'channel' => $channelCode]) }}"
                                class="px-4 py-2 text-sm font-medium rounded-t-lg transition-colors {{ $activeTab === $tabKey ? 'bg-blue-50 text-blue-600 border-b-2 border-blue-600 dark:bg-gray-800 dark:text-blue-400' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-800' }}"
                            >
                                {{ $tabName }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="mt-4">
                    @if ($activeTab === 'payment_methods')
                        {{-- Payment Methods Mapping Tab --}}
                        <div id="payment-methods-mapping-container">
                            <div class="mb-4 flex items-center justify-between">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    @lang('iiko-integration::app.settings.payment-methods-description')
                                </p>
                                <button
                                    type="button"
                                    id="sync-payment-types-btn"
                                    class="secondary-button"
                                    onclick="syncPaymentTypes()"
                                >
                                    @lang('iiko-integration::app.settings.sync-payment-types')
                                </button>
                            </div>

                            <div id="payment-methods-loading" class="hidden text-center py-8">
                                <p class="text-gray-500">@lang('iiko-integration::app.settings.loading')</p>
                            </div>

                            <div id="payment-methods-error" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded dark:bg-red-900/20 dark:border-red-800">
                                <p class="text-red-600 dark:text-red-400" id="payment-methods-error-message"></p>
                            </div>

                            <div id="payment-methods-table-container" class="hidden">
                                <div class="overflow-x-auto">
                                    <table class="w-full border border-gray-200 dark:border-gray-700 rounded-lg">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                    @lang('iiko-integration::app.settings.iiko-payment-type')
                                                </th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                    @lang('iiko-integration::app.settings.kind')
                                                </th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                    @lang('iiko-integration::app.settings.admin-payment-method')
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="payment-methods-tbody" class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                        </tbody>
                                    </table>
                                </div>

                                <div class="flex items-center justify-end gap-4 mt-6">
                                    <button
                                        type="button"
                                        id="save-payment-methods-mapping-btn"
                                        class="primary-button"
                                        onclick="savePaymentMethodsMapping()"
                                    >
                                        @lang('admin::app.save')
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Other Tabs --}}
                        @foreach ($fields as $field)
                            <div class="mb-4">
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        {{ trans($field['title']) }}
                                    </x-admin::form.control-group.label>

                                    @if ($field['type'] === 'text')
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="settings[{{ $field['key'] }}]"
                                            :value="$field['value'] ?? ''"
                                            :placeholder="trans($field['title'])"
                                        />
                                    @elseif ($field['type'] === 'password')
                                        <x-admin::form.control-group.control
                                            type="password"
                                            name="settings[{{ $field['key'] }}]"
                                            :value="$field['value'] ?? ''"
                                            :placeholder="trans($field['title'])"
                                        />
                                    @elseif ($field['type'] === 'boolean')
                                        <x-admin::form.control-group.control
                                            type="switch"
                                            name="settings[{{ $field['key'] }}]"
                                            :value="1"
                                            :checked="(bool) ($field['value'] ?? false)"
                                        />
                                    @endif

                                    @if (isset($field['description']))
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ trans($field['description']) }}
                                        </p>
                                    @endif
                                </x-admin::form.control-group>
                            </div>
                        @endforeach

                        <div class="flex items-center justify-between gap-4 mt-6">
                            @if ($activeTab === 'configuration')
                                <button
                                    type="button"
                                    id="test-connection-btn"
                                    class="secondary-button"
                                    onclick="testConnection()"
                                >
                                    @lang('iiko-integration::app.settings.test-connection')
                                </button>
                            @else
                                <div></div>
                            @endif

                            <button type="submit" class="primary-button">
                                @lang('admin::app.save')
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </x-admin::form>
    </div>

    @push('scripts')
        <script>
            function testConnection() {
                const btn = document.getElementById('test-connection-btn');
                const originalText = btn.textContent;
                btn.disabled = true;
                btn.textContent = '@lang('iiko-integration::app.settings.testing')';

                fetch('{{ route('admin.iiko.settings.test') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    alert('@lang('iiko-integration::app.settings.connection-error')');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
            }

            @if ($activeTab === 'payment_methods')
            let adminPaymentMethods = [];
            let iikoPaymentTypes = [];

            // Load payment methods mapping on page load
            document.addEventListener('DOMContentLoaded', function() {
                loadPaymentMethodsMapping();
            });

            function loadPaymentMethodsMapping() {
                const loadingEl = document.getElementById('payment-methods-loading');
                const errorEl = document.getElementById('payment-methods-error');
                const errorMessageEl = document.getElementById('payment-methods-error-message');
                const tableContainer = document.getElementById('payment-methods-table-container');
                const tbody = document.getElementById('payment-methods-tbody');

                loadingEl.classList.remove('hidden');
                errorEl.classList.add('hidden');
                tableContainer.classList.add('hidden');

                fetch('{{ route('admin.iiko.settings.payment-methods-mapping') }}?channel={{ $channelCode }}', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    loadingEl.classList.add('hidden');

                    if (data.success) {
                        adminPaymentMethods = data.admin_payment_methods || [];
                        iikoPaymentTypes = data.iiko_payment_types || [];

                        if (iikoPaymentTypes.length === 0) {
                            errorMessageEl.textContent = '@lang('iiko-integration::app.settings.no-payment-types-found')';
                            errorEl.classList.remove('hidden');
                        } else {
                            renderPaymentMethodsTable();
                            tableContainer.classList.remove('hidden');
                        }
                    } else {
                        errorMessageEl.textContent = data.message || '@lang('iiko-integration::app.settings.error-loading-payment-methods')';
                        errorEl.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    loadingEl.classList.add('hidden');
                    errorMessageEl.textContent = '@lang('iiko-integration::app.settings.error-loading-payment-methods')';
                    errorEl.classList.remove('hidden');
                });
            }

            function renderPaymentMethodsTable() {
                const tbody = document.getElementById('payment-methods-tbody');
                tbody.innerHTML = '';

                iikoPaymentTypes.forEach(paymentType => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50 dark:hover:bg-gray-800';

                    const nameCell = document.createElement('td');
                    nameCell.className = 'px-4 py-3 text-sm text-gray-900 dark:text-gray-100';
                    nameCell.textContent = paymentType.name || '-';

                    const kindCell = document.createElement('td');
                    kindCell.className = 'px-4 py-3 text-sm text-gray-600 dark:text-gray-400';
                    kindCell.textContent = paymentType.kind || '-';

                    const mappingCell = document.createElement('td');
                    mappingCell.className = 'px-4 py-3';

                    const select = document.createElement('select');
                    select.className = 'w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100';
                    select.name = `payment_method_mapping[${paymentType.id}]`;
                    select.dataset.paymentTypeId = paymentType.id;

                    const emptyOption = document.createElement('option');
                    emptyOption.value = '';
                    emptyOption.textContent = '@lang('iiko-integration::app.settings.select-payment-method')';
                    select.appendChild(emptyOption);

                    adminPaymentMethods.forEach(method => {
                        const option = document.createElement('option');
                        option.value = method.method;
                        option.textContent = method.method_title || method.method;
                        if (paymentType.payment_method_code === method.method) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });

                    mappingCell.appendChild(select);
                    row.appendChild(nameCell);
                    row.appendChild(kindCell);
                    row.appendChild(mappingCell);
                    tbody.appendChild(row);
                });
            }

            function syncPaymentTypes() {
                const btn = document.getElementById('sync-payment-types-btn');
                const originalText = btn.textContent;
                btn.disabled = true;
                btn.textContent = '@lang('iiko-integration::app.settings.syncing')';

                fetch('{{ route('admin.iiko.settings.sync-payment-types') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        channel: '{{ $channelCode }}'
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        loadPaymentMethodsMapping();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    alert('@lang('iiko-integration::app.settings.error-syncing-payment-types')');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
            }

            function savePaymentMethodsMapping() {
                const btn = document.getElementById('save-payment-methods-mapping-btn');
                const originalText = btn.textContent;
                btn.disabled = true;
                btn.textContent = '@lang('iiko-integration::app.settings.saving')';

                const mappings = [];
                const selects = document.querySelectorAll('select[name^="payment_method_mapping"]');

                selects.forEach(select => {
                    mappings.push({
                        id: select.dataset.paymentTypeId,
                        payment_method_code: select.value || null
                    });
                });

                fetch('{{ route('admin.iiko.settings.store-payment-methods-mapping') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        mappings: mappings
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    alert('@lang('iiko-integration::app.settings.error-saving-mapping')');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = originalText;
                });
            }
            @endif
        </script>
    @endpush
</x-admin::layouts>
