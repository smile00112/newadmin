<x-admin::layouts>
    <x-slot:title>
        @lang('iiko-integration::app.menu.management')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('iiko-integration::app.menu.management')
        </p>
    </div>

    <div class="mt-7 space-y-6">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                @lang('iiko-integration::app.management.get-organizations')
            </h3>
            <button
                type="button"
                id="btn-get-organizations"
                class="primary-button"
                onclick="getOrganizations()"
            >
                @lang('iiko-integration::app.management.get-organizations')
            </button>
            <div id="organizations-select-container" class="mt-4 {{ !empty($savedOrganizations) ? '' : 'hidden' }}">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    @lang('iiko-integration::app.management.select-organization')
                </label>
                <select
                    id="organizations-select"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white bg-white dark:bg-gray-700"
                    onchange="onOrganizationChange()"
                >
                    <option value="">@lang('iiko-integration::app.management.select-organization')</option>
                    @if(!empty($savedOrganizations))
                        @foreach($savedOrganizations as $org)
                            <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                @lang('iiko-integration::app.management.get-terminals')
            </h3>
            <div class="flex gap-2">
                <button
                    type="button"
                    id="btn-get-terminals"
                    class="primary-button"
                    onclick="getTerminals(true)"
                    {{ !empty($savedOrganizations) ? '' : 'disabled' }}
                >
                    @lang('iiko-integration::app.management.get-terminals')
                </button>
                <button
                    type="button"
                    id="btn-import-terminal"
                    class="primary-button"
                    onclick="importTerminal()"
                    disabled
                >
                    @lang('iiko-integration::app.management.import-terminal')
                </button>
            </div>
            <div id="terminals-select-container" class="mt-4 hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    @lang('iiko-integration::app.management.select-terminal')
                </label>
                <select
                    id="terminals-select"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white bg-white dark:bg-gray-700"
                    onchange="onTerminalChange()"
                >
                    <option value="">@lang('iiko-integration::app.management.select-terminal')</option>
                </select>
            </div>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                @lang('iiko-integration::app.management.get-menu')
            </h3>
            <button
                type="button"
                id="btn-get-menu"
                class="primary-button"
                onclick="getMenu()"
                disabled
            >
                @lang('iiko-integration::app.management.get-menu')
            </button>
            <div id="menu-select-container" class="mt-4 hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    @lang('iiko-integration::app.management.select-menu')
                </label>
                <select
                    id="menu-select"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white bg-white dark:bg-gray-700"
                    onchange="onMenuChange()"
                >
                    <option value="">@lang('iiko-integration::app.management.select-menu')</option>
                </select>
            </div>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                @lang('iiko-integration::app.management.get-nomenclature')
            </h3>
            <div class="flex gap-2">
                <button
                    type="button"
                    id="btn-get-nomenclature"
                    class="primary-button"
                    onclick="getNomenclature()"
                    disabled
                >
                    @lang('iiko-integration::app.management.get-nomenclature')
                </button>
                <button
                    type="button"
                    id="btn-import-nomenclature"
                    class="primary-button"
                    onclick="importNomenclature()"
                    disabled
                >
                    @lang('iiko-integration::app.management.import-nomenclature')
                </button>
            </div>
            <div id="groups-select-container" class="mt-4 hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    @lang('iiko-integration::app.management.select-groups')
                </label>
                <select
                    id="groups-select"
                    multiple
                    size="10"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white bg-white"
                    onchange="onGroupsChange()"
                >
                </select>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    @lang('iiko-integration::app.management.select-groups-hint')
                </p>
            </div>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                @lang('iiko-integration::app.management.get-customer-by-phone')
            </h3>
            <div class="flex gap-2 mb-4">
                <input
                    type="text"
                    id="customer-phone-input"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white bg-white"
                    placeholder="@lang('iiko-integration::app.management.phone-placeholder')"
                />
                <button
                    type="button"
                    id="btn-get-customer-by-phone"
                    class="primary-button"
                    onclick="getCustomerByPhone()"
                    {{ !empty($savedOrganizations) ? '' : 'disabled' }}
                >
                    @lang('iiko-integration::app.management.get-customer-by-phone')
                </button>
            </div>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                @lang('iiko-integration::app.management.get-promotions')
            </h3>
            <button
                type="button"
                id="btn-get-promotions"
                class="primary-button"
                onclick="getPromotions(true)"
                {{ !empty($savedOrganizations) ? '' : 'disabled' }}
            >
                @lang('iiko-integration::app.management.get-promotions')
            </button>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                @lang('iiko-integration::app.management.get-payment-types')
            </h3>
            <button
                type="button"
                id="btn-get-payment-types"
                class="primary-button"
                onclick="getPaymentTypes(true)"
                {{ !empty($savedOrganizations) ? '' : 'disabled' }}
            >
                @lang('iiko-integration::app.management.get-payment-types')
            </button>
        </div>

        <div id="message-container" class="hidden"></div>
    </div>

    <!-- Request Results Section -->
    <div class="mt-7 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                @lang('iiko-integration::app.management.request-results')
            </h3>
            <button
                type="button"
                id="btn-clear-results"
                class="text-sm px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600"
                onclick="clearResults()"
            >
                @lang('iiko-integration::app.management.clear-results')
            </button>
        </div>
        <div
            id="request-results"
            class="w-full h-64 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-800 dark:text-gray-200 bg-gray-50 font-mono text-sm overflow-y-auto"
        ></div>
    </div>

        @push('scripts')
        <script>
            let selectedOrganizationId = null;
            let selectedTerminalId = null;
            let selectedMenuId = null;

            // Load saved organizations on page load
            document.addEventListener('DOMContentLoaded', function() {
                @if(!empty($savedOrganizations))
                    const select = document.getElementById('organizations-select');
                    if (select && select.options.length > 1) {
                        document.getElementById('organizations-select-container').classList.remove('hidden');
                        document.getElementById('btn-get-terminals').disabled = false;
                        document.getElementById('btn-get-customer-by-phone').disabled = false;
                        document.getElementById('btn-get-promotions').disabled = false;
                        document.getElementById('btn-get-payment-types').disabled = false;
                    }
                @endif
            });

            function showMessage(message, type = 'success') {
                const container = document.getElementById('message-container');
                container.className = type === 'success' 
                    ? 'mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4'
                    : 'mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4';
                container.innerHTML = `<p class="text-sm text-gray-800 dark:text-white">${message}</p>`;
                container.classList.remove('hidden');
                
                setTimeout(() => {
                    container.classList.add('hidden');
                }, 5000);
            }

            function setButtonLoading(buttonId, isLoading) {
                const button = document.getElementById(buttonId);
                if (isLoading) {
                    // Only change text, don't disable button to allow repeated requests
                    button.innerHTML = "@lang('iiko-integration::app.management.loading')";
                } else {
                    // Button stays enabled
                }
            }

            function restoreButtonText(buttonId, text) {
                const button = document.getElementById(buttonId);
                button.innerHTML = text;
            }

            function logRequest(action, requestData, responseData, error = null) {
                const resultsDiv = document.getElementById('request-results');
                const timestamp = new Date().toLocaleString('ru-RU');
                const separator = '='.repeat(80);
                
                let logEntry = '';
                
                // Create separator line
                logEntry += `<div style="border-top: 1px solid #e5e7eb; margin: 10px 0; padding-top: 10px;"></div>`;
                logEntry += `<div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 8px;">[${timestamp}] ${action}</div>`;
                
                if (requestData) {
                    logEntry += `<div style="margin: 8px 0;"><strong>REQUEST:</strong></div>`;
                    logEntry += `<pre style="background: #f3f4f6; padding: 8px; border-radius: 4px; overflow-x: auto; margin: 4px 0;">${JSON.stringify(requestData, null, 2)}</pre>`;
                }
                
                if (error) {
                    logEntry += `<div style="margin: 8px 0;"><strong style="color: #dc2626;">ERROR:</strong></div>`;
                    logEntry += `<div style="color: #dc2626; margin: 4px 0;">${error.message || error}</div>`;
                    if (error.stack) {
                        logEntry += `<pre style="background: #fee2e2; padding: 8px; border-radius: 4px; overflow-x: auto; margin: 4px 0; color: #991b1b;">${error.stack}</pre>`;
                    }
                } else if (responseData) {
                    // Special handling for nomenclature
                    if (action === 'Get Nomenclature' && responseData.success && responseData.data) {
                        logEntry += `<div style="margin: 8px 0;"><strong>RESPONSE:</strong></div>`;
                        
                        // Extract nomenclature data
                        const nomData = responseData.data;
                        const groups = nomData.groups || nomData.categories || [];
                        const items = nomData.items || nomData.products || [];
                        
                        // Separate products and modifiers
                        const products = items.filter(item => (item.type || 'Dish') !== 'Modifier');
                        const modifiers = items.filter(item => (item.type || '') === 'Modifier');
                        
                        // Display groups
                        if (groups.length > 0) {
                            logEntry += `<div style="margin: 12px 0;"><strong style="color: #059669;">Группы товаров (${groups.length}):</strong></div>`;
                            logEntry += `<div style="margin-left: 16px;">`;
                            groups.forEach(group => {
                                const id = group.id || group.externalId || 'N/A';
                                const name = group.name || 'Без названия';
                                logEntry += `<div style="margin: 4px 0;">• <span style="color: #6b7280;">ID:</span> ${id} | <span style="color: #6b7280;">Название:</span> ${name}</div>`;
                            });
                            logEntry += `</div>`;
                        }
                        
                        // Display products
                        if (products.length > 0) {
                            logEntry += `<div style="margin: 12px 0;"><strong style="color: #2563eb;">Товары (${products.length}):</strong></div>`;
                            logEntry += `<div style="margin-left: 16px;">`;
                            products.forEach(product => {
                                const id = product.id || product.externalId || 'N/A';
                                const name = product.name || 'Без названия';
                                logEntry += `<div style="margin: 4px 0;">• <span style="color: #6b7280;">ID:</span> ${id} | <span style="color: #6b7280;">Название:</span> ${name}</div>`;
                            });
                            logEntry += `</div>`;
                        }
                        
                        // Display modifiers
                        if (modifiers.length > 0) {
                            logEntry += `<div style="margin: 12px 0;"><strong style="color: #7c3aed;">Модификаторы (${modifiers.length}):</strong></div>`;
                            logEntry += `<div style="margin-left: 16px;">`;
                            modifiers.forEach(modifier => {
                                const id = modifier.id || modifier.externalId || 'N/A';
                                const name = modifier.name || 'Без названия';
                                logEntry += `<div style="margin: 4px 0;">• <span style="color: #6b7280;">ID:</span> ${id} | <span style="color: #6b7280;">Название:</span> ${name}</div>`;
                            });
                            logEntry += `</div>`;
                        }
                        
                        if (groups.length === 0 && products.length === 0 && modifiers.length === 0) {
                            logEntry += `<div style="color: #6b7280; margin: 8px 0;">Данные номенклатуры не найдены</div>`;
                        }
                    } else {
                        // Regular response display
                        logEntry += `<div style="margin: 8px 0;"><strong>RESPONSE:</strong></div>`;
                        logEntry += `<pre style="background: #f3f4f6; padding: 8px; border-radius: 4px; overflow-x: auto; margin: 4px 0;">${JSON.stringify(responseData, null, 2)}</pre>`;
                    }
                }
                
                resultsDiv.innerHTML += logEntry;
                resultsDiv.scrollTop = resultsDiv.scrollHeight;
            }

            function clearResults() {
                document.getElementById('request-results').innerHTML = '';
            }

            async function getOrganizations() {
                const button = document.getElementById('btn-get-organizations');
                const originalText = button.innerHTML;
                setButtonLoading('btn-get-organizations', true);

                const requestData = {
                    endpoint: "{{ route('admin.iiko.management.organizations') }}",
                    method: 'POST'
                };

                try {
                    const response = await fetch(requestData.endpoint, {
                        method: requestData.method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                    });

                    const data = await response.json();
                    logRequest('Get Organizations', requestData, data, response.ok ? null : new Error(`HTTP ${response.status}`));

                    if (data.success && data.data && data.data.length > 0) {
                        const select = document.getElementById('organizations-select');
                        select.innerHTML = '<option value="">@lang("iiko-integration::app.management.select-organization")</option>';
                        
                        data.data.forEach(org => {
                            const option = document.createElement('option');
                            option.value = org.id;
                            option.textContent = org.name;
                            select.appendChild(option);
                        });

                        document.getElementById('organizations-select-container').classList.remove('hidden');
                        document.getElementById('btn-get-terminals').disabled = false;
                        showMessage(data.message, 'success');
                    } else {
                        showMessage(data.message || "@lang('iiko-integration::app.management.error')", 'error');
                    }
                } catch (error) {
                    logRequest('Get Organizations', requestData, null, error);
                    showMessage("@lang('iiko-integration::app.management.error')", 'error');
                } finally {
                    restoreButtonText('btn-get-organizations', originalText);
                }
            }

            function onOrganizationChange() {
                const select = document.getElementById('organizations-select');
                selectedOrganizationId = select.value;
                
                if (selectedOrganizationId) {
                    document.getElementById('btn-get-terminals').disabled = false;
                    document.getElementById('btn-get-customer-by-phone').disabled = false;
                    document.getElementById('btn-get-promotions').disabled = false;
                    document.getElementById('btn-get-payment-types').disabled = false;
                    // Automatically load saved terminals for selected organization
                    loadTerminals(false);
                } else {
                    document.getElementById('btn-get-terminals').disabled = true;
                    document.getElementById('btn-get-customer-by-phone').disabled = true;
                    document.getElementById('btn-get-promotions').disabled = true;
                    document.getElementById('btn-get-payment-types').disabled = true;
                    document.getElementById('btn-import-terminal').disabled = true;
                    document.getElementById('terminals-select-container').classList.add('hidden');
                    document.getElementById('btn-get-menu').disabled = true;
                    document.getElementById('btn-get-nomenclature').disabled = true;
                    // Clear menu selection
                    selectedMenuId = null;
                    selectedTerminalId = null;
                    document.getElementById('menu-select-container').classList.add('hidden');
                    const menuSelect = document.getElementById('menu-select');
                    if (menuSelect) {
                        menuSelect.innerHTML = '<option value="">@lang("iiko-integration::app.management.select-menu")</option>';
                    }
                }
            }

            async function loadTerminals(forceRefresh = false) {
                if (!selectedOrganizationId) {
                    return;
                }

                const requestData = {
                    endpoint: "{{ route('admin.iiko.management.terminals') }}",
                    method: 'POST',
                    body: { 
                        organization_id: selectedOrganizationId,
                        force_refresh: forceRefresh
                    }
                };

                try {
                    const response = await fetch(requestData.endpoint, {
                        method: requestData.method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(requestData.body),
                    });

                    const data = await response.json();
                    logRequest('Load Terminals (auto)', requestData, data, response.ok ? null : new Error(`HTTP ${response.status}`));

                    if (data.success && data.data && data.data.length > 0) {
                        const select = document.getElementById('terminals-select');
                        select.innerHTML = '<option value="">@lang("iiko-integration::app.management.select-terminal")</option>';
                        
                        data.data.forEach(terminal => {
                            const option = document.createElement('option');
                            option.value = terminal.id;
                            option.textContent = terminal.name;
                            select.appendChild(option);
                        });

                        document.getElementById('terminals-select-container').classList.remove('hidden');
                        document.getElementById('btn-get-menu').disabled = false;
                        document.getElementById('btn-import-terminal').disabled = true;
                        selectedTerminalId = null;
                        
                        if (data.cached) {
                            // Silently load cached data, no message needed
                        } else {
                            showMessage(data.message || "@lang('iiko-integration::app.management.success')", 'success');
                        }
                    } else if (!forceRefresh) {
                        // If no cached data and not force refresh, silently fail
                        // User can click button to force refresh
                    }
                } catch (error) {
                    logRequest('Load Terminals (auto)', requestData, null, error);
                    console.error('Error loading terminals:', error);
                }
            }

            async function getTerminals(forceRefresh = false) {
                if (!selectedOrganizationId) {
                    showMessage("@lang('iiko-integration::app.management.select-organization')", 'error');
                    return;
                }

                const button = document.getElementById('btn-get-terminals');
                const originalText = button.innerHTML;
                setButtonLoading('btn-get-terminals', true);

                const requestData = {
                    endpoint: "{{ route('admin.iiko.management.terminals') }}",
                    method: 'POST',
                    body: { 
                        organization_id: selectedOrganizationId,
                        force_refresh: forceRefresh
                    }
                };

                try {
                    const response = await fetch(requestData.endpoint, {
                        method: requestData.method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(requestData.body),
                    });

                    const data = await response.json();
                    logRequest('Get Terminals', requestData, data, response.ok ? null : new Error(`HTTP ${response.status}`));

                    if (data.success && data.data && data.data.length > 0) {
                        const select = document.getElementById('terminals-select');
                        select.innerHTML = '<option value="">@lang("iiko-integration::app.management.select-terminal")</option>';
                        
                        data.data.forEach(terminal => {
                            const option = document.createElement('option');
                            option.value = terminal.id;
                            option.textContent = terminal.name;
                            select.appendChild(option);
                        });

                        document.getElementById('terminals-select-container').classList.remove('hidden');
                        document.getElementById('btn-get-menu').disabled = false;
                        document.getElementById('btn-import-terminal').disabled = true;
                        selectedTerminalId = null;
                        
                        const message = data.cached 
                            ? "@lang('iiko-integration::app.management.success') (cached)"
                            : data.message || "@lang('iiko-integration::app.management.success')";
                        showMessage(message, 'success');
                    } else {
                        showMessage(data.message || "@lang('iiko-integration::app.management.no-data')", 'error');
                    }
                } catch (error) {
                    logRequest('Get Terminals', requestData, null, error);
                    showMessage("@lang('iiko-integration::app.management.error')", 'error');
                } finally {
                    restoreButtonText('btn-get-terminals', originalText);
                }
            }

            async function getMenu() {
                if (!selectedOrganizationId) {
                    showMessage("@lang('iiko-integration::app.management.select-organization')", 'error');
                    return;
                }

                const button = document.getElementById('btn-get-menu');
                const originalText = button.innerHTML;
                setButtonLoading('btn-get-menu', true);

                const requestData = {
                    endpoint: "{{ route('admin.iiko.management.menu') }}",
                    method: 'POST',
                    body: { organization_id: selectedOrganizationId }
                };

                try {
                    const response = await fetch(requestData.endpoint, {
                        method: requestData.method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(requestData.body),
                    });

                    const data = await response.json();
                    logRequest('Get Menu', requestData, data, response.ok ? null : new Error(`HTTP ${response.status}`));

                    if (data.success && data.data && data.data.length > 0) {
                        const select = document.getElementById('menu-select');
                        select.innerHTML = '<option value="">@lang("iiko-integration::app.management.select-menu")</option>';
                        
                        data.data.forEach(menu => {
                            const option = document.createElement('option');
                            option.value = menu.id;
                            option.textContent = menu.name || menu.id;
                            select.appendChild(option);
                        });

                        document.getElementById('menu-select-container').classList.remove('hidden');
                        // Don't enable nomenclature button until menu is selected
                        document.getElementById('btn-get-nomenclature').disabled = true;
                        selectedMenuId = null;
                        showMessage(data.message, 'success');
                    } else if (data.success) {
                        // If no menus but success, enable nomenclature button (menu not required)
                        document.getElementById('menu-select-container').classList.add('hidden');
                        document.getElementById('btn-get-nomenclature').disabled = false;
                        selectedMenuId = null;
                        showMessage(data.message, 'success');
                    } else {
                        showMessage(data.message || "@lang('iiko-integration::app.management.error')", 'error');
                    }
                } catch (error) {
                    logRequest('Get Menu', requestData, null, error);
                    showMessage("@lang('iiko-integration::app.management.error')", 'error');
                } finally {
                    restoreButtonText('btn-get-menu', originalText);
                }
            }

            function onTerminalChange() {
                const select = document.getElementById('terminals-select');
                selectedTerminalId = select.value;
                
                if (selectedTerminalId) {
                    document.getElementById('btn-import-terminal').disabled = false;
                } else {
                    document.getElementById('btn-import-terminal').disabled = true;
                }
            }

            function onMenuChange() {
                const select = document.getElementById('menu-select');
                selectedMenuId = select.value;
                
                if (selectedMenuId) {
                    document.getElementById('btn-get-nomenclature').disabled = false;
                } else {
                    document.getElementById('btn-get-nomenclature').disabled = true;
                }
            }

            function onGroupsChange() {
                const select = document.getElementById('groups-select');
                const selectedOptions = Array.from(select.selectedOptions);
                const hasSelection = selectedOptions.length > 0;
                
                // Enable import button only if at least one group is selected
                document.getElementById('btn-import-nomenclature').disabled = !hasSelection;
            }

            async function getNomenclature() {
                if (!selectedOrganizationId) {
                    showMessage("@lang('iiko-integration::app.management.select-organization')", 'error');
                    return;
                }

                if (!selectedMenuId) {
                    showMessage("@lang('iiko-integration::app.management.external-menu-id-required')", 'error');
                    return;
                }

                const button = document.getElementById('btn-get-nomenclature');
                const originalText = button.innerHTML;
                setButtonLoading('btn-get-nomenclature', true);

                const requestBody = { 
                    organization_id: selectedOrganizationId,
                    external_menu_id: selectedMenuId
                };

                const requestData = {
                    endpoint: "{{ route('admin.iiko.management.nomenclature') }}",
                    method: 'POST',
                    body: requestBody
                };

                try {
                    const response = await fetch(requestData.endpoint, {
                        method: requestData.method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(requestData.body),
                    });

                    const data = await response.json();
                    logRequest('Get Nomenclature', requestData, data, response.ok ? null : new Error(`HTTP ${response.status}`));

                    if (data.success) {
                        showMessage(data.message, 'success');
                        
                        // Populate groups select if groups are available
                        if (data.groups && Array.isArray(data.groups) && data.groups.length > 0) {
                            const groupsSelect = document.getElementById('groups-select');
                            groupsSelect.innerHTML = '';
                            
                            data.groups.forEach(group => {
                                if (group.id) {
                                    const option = document.createElement('option');
                                    option.value = group.id;
                                    // Add "--" prefix for subcategories (groups with parentGroup)
                                    const displayName = group.parentGroup 
                                        ? '-- ' + (group.name || 'Unnamed Group')
                                        : (group.name || 'Unnamed Group');
                                    option.textContent = displayName;
                                    groupsSelect.appendChild(option);
                                }
                            });
                            
                            document.getElementById('groups-select-container').classList.remove('hidden');
                        } else {
                            document.getElementById('groups-select-container').classList.add('hidden');
                        }
                        
                        // Disable import button until groups are selected
                        document.getElementById('btn-import-nomenclature').disabled = true;
                    } else {
                        showMessage(data.message || "@lang('iiko-integration::app.management.error')", 'error');
                        document.getElementById('btn-import-nomenclature').disabled = true;
                        document.getElementById('groups-select-container').classList.add('hidden');
                    }
                } catch (error) {
                    logRequest('Get Nomenclature', requestData, null, error);
                    showMessage("@lang('iiko-integration::app.management.error')", 'error');
                    document.getElementById('btn-import-nomenclature').disabled = true;
                } finally {
                    restoreButtonText('btn-get-nomenclature', originalText);
                }
            }

            async function importNomenclature() {
                if (!selectedOrganizationId) {
                    showMessage("@lang('iiko-integration::app.management.select-organization')", 'error');
                    return;
                }

                // Get selected group IDs
                const groupsSelect = document.getElementById('groups-select');
                const selectedOptions = Array.from(groupsSelect.selectedOptions);
                const groupIds = selectedOptions.map(option => option.value);

                if (groupIds.length === 0) {
                    showMessage("@lang('iiko-integration::app.management.groups-required')", 'error');
                    return;
                }

                const button = document.getElementById('btn-import-nomenclature');
                const originalText = button.innerHTML;
                setButtonLoading('btn-import-nomenclature', true);

                const requestData = {
                    endpoint: "{{ route('admin.iiko.management.import-nomenclature') }}",
                    method: 'POST',
                    body: { 
                        organization_id: selectedOrganizationId,
                        group_ids: groupIds
                    }
                };

                try {
                    const response = await fetch(requestData.endpoint, {
                        method: requestData.method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(requestData.body),
                    });

                    const data = await response.json();
                    logRequest('Import Nomenclature', requestData, data, response.ok ? null : new Error(`HTTP ${response.status}`));

                    if (data.success) {
                        showMessage(data.message, 'success');
                    } else {
                        showMessage(data.message || "@lang('iiko-integration::app.management.import-error')", 'error');
                    }
                } catch (error) {
                    logRequest('Import Nomenclature', requestData, null, error);
                    showMessage("@lang('iiko-integration::app.management.import-error')", 'error');
                } finally {
                    restoreButtonText('btn-import-nomenclature', originalText);
                }
            }

            async function getCustomerByPhone() {
                if (!selectedOrganizationId) {
                    showMessage("@lang('iiko-integration::app.management.select-organization')", 'error');
                    return;
                }

                const phoneInput = document.getElementById('customer-phone-input');
                const phone = phoneInput.value.trim();

                if (!phone) {
                    showMessage("@lang('iiko-integration::app.management.phone-required')", 'error');
                    return;
                }

                const button = document.getElementById('btn-get-customer-by-phone');
                const originalText = button.innerHTML;
                setButtonLoading('btn-get-customer-by-phone', true);

                const requestData = {
                    endpoint: "{{ route('admin.iiko.management.customer-by-phone') }}",
                    method: 'POST',
                    body: {
                        phone: phone,
                        organization_id: selectedOrganizationId
                    }
                };

                try {
                    const response = await fetch(requestData.endpoint, {
                        method: requestData.method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(requestData.body),
                    });

                    const data = await response.json();
                    logRequest('Get Customer By Phone', requestData, data, response.ok ? null : new Error(`HTTP ${response.status}`));

                    if (data.success) {
                        showMessage(data.message, 'success');
                    } else {
                        showMessage(data.message || "@lang('iiko-integration::app.management.error')", 'error');
                    }
                } catch (error) {
                    logRequest('Get Customer By Phone', requestData, null, error);
                    showMessage("@lang('iiko-integration::app.management.error')", 'error');
                } finally {
                    restoreButtonText('btn-get-customer-by-phone', originalText);
                }
            }

            async function getPromotions(forceRefresh = false) {
                if (!selectedOrganizationId) {
                    showMessage("@lang('iiko-integration::app.management.select-organization')", 'error');
                    return;
                }

                const button = document.getElementById('btn-get-promotions');
                const originalText = button.innerHTML;
                setButtonLoading('btn-get-promotions', true);

                const requestData = {
                    endpoint: "{{ route('admin.iiko.management.promotions') }}",
                    method: 'POST',
                    body: {
                        organization_id: selectedOrganizationId,
                        force_refresh: forceRefresh
                    }
                };

                try {
                    const response = await fetch(requestData.endpoint, {
                        method: requestData.method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(requestData.body),
                    });

                    const data = await response.json();
                    logRequest('Get Promotions', requestData, data, response.ok ? null : new Error(`HTTP ${response.status}`));

                    if (data.success) {
                        const message = data.cached 
                            ? "@lang('iiko-integration::app.management.success') (cached)"
                            : data.message || "@lang('iiko-integration::app.management.success')";
                        showMessage(message, 'success');
                    } else {
                        showMessage(data.message || "@lang('iiko-integration::app.management.error')", 'error');
                    }
                } catch (error) {
                    logRequest('Get Promotions', requestData, null, error);
                    showMessage("@lang('iiko-integration::app.management.error')", 'error');
                } finally {
                    restoreButtonText('btn-get-promotions', originalText);
                }
            }

            async function getPaymentTypes(forceRefresh = false) {
                if (!selectedOrganizationId) {
                    showMessage("@lang('iiko-integration::app.management.select-organization')", 'error');
                    return;
                }

                const button = document.getElementById('btn-get-payment-types');
                const originalText = button.innerHTML;
                setButtonLoading('btn-get-payment-types', true);

                const requestData = {
                    endpoint: "{{ route('admin.iiko.management.payment-types') }}",
                    method: 'POST',
                    body: {
                        organization_id: selectedOrganizationId,
                        force_refresh: forceRefresh
                    }
                };

                try {
                    const response = await fetch(requestData.endpoint, {
                        method: requestData.method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(requestData.body),
                    });

                    const data = await response.json();
                    logRequest('Get Payment Types', requestData, data, response.ok ? null : new Error(`HTTP ${response.status}`));

                    if (data.success) {
                        const message = data.cached 
                            ? "@lang('iiko-integration::app.management.success') (cached)"
                            : data.message || "@lang('iiko-integration::app.management.success')";
                        showMessage(message, 'success');
                    } else {
                        showMessage(data.message || "@lang('iiko-integration::app.management.error')", 'error');
                    }
                } catch (error) {
                    logRequest('Get Payment Types', requestData, null, error);
                    showMessage("@lang('iiko-integration::app.management.error')", 'error');
                } finally {
                    restoreButtonText('btn-get-payment-types', originalText);
                }
            }

            async function importTerminal() {
                if (!selectedOrganizationId) {
                    showMessage("@lang('iiko-integration::app.management.select-organization')", 'error');
                    return;
                }

                if (!selectedTerminalId) {
                    showMessage("@lang('iiko-integration::app.management.terminal-id-required')", 'error');
                    return;
                }

                const button = document.getElementById('btn-import-terminal');
                const originalText = button.innerHTML;
                setButtonLoading('btn-import-terminal', true);

                const requestData = {
                    endpoint: "{{ route('admin.iiko.management.import-terminal') }}",
                    method: 'POST',
                    body: {
                        organization_id: selectedOrganizationId,
                        terminal_id: selectedTerminalId
                    }
                };

                try {
                    const response = await fetch(requestData.endpoint, {
                        method: requestData.method,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(requestData.body),
                    });

                    const data = await response.json();
                    logRequest('Import Terminal', requestData, data, response.ok ? null : new Error(`HTTP ${response.status}`));

                    if (data.success) {
                        showMessage(data.message, 'success');
                    } else if (data.skipped) {
                        showMessage(data.message, 'error');
                    } else {
                        showMessage(data.message || "@lang('iiko-integration::app.management.import-error')", 'error');
                    }
                } catch (error) {
                    logRequest('Import Terminal', requestData, null, error);
                    showMessage("@lang('iiko-integration::app.management.import-error')", 'error');
                } finally {
                    restoreButtonText('btn-import-terminal', originalText);
                }
            }
        </script>
    @endpush
</x-admin::layouts>
