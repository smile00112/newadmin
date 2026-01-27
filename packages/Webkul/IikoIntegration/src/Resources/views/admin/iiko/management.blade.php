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
            <button
                type="button"
                id="btn-get-terminals"
                class="primary-button"
                onclick="getTerminals(true)"
                {{ !empty($savedOrganizations) ? '' : 'disabled' }}
            >
                @lang('iiko-integration::app.management.get-terminals')
            </button>
            <div id="terminals-select-container" class="mt-4 hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    @lang('iiko-integration::app.management.select-terminal')
                </label>
                <select
                    id="terminals-select"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white bg-white dark:bg-gray-700"
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
            <button
                type="button"
                id="btn-get-nomenclature"
                class="primary-button"
                onclick="getNomenclature()"
                disabled
            >
                @lang('iiko-integration::app.management.get-nomenclature')
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
        <textarea
            id="request-results"
            readonly
            class="w-full h-64 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-200 bg-gray-50 font-mono text-sm resize-none"
            placeholder="@lang('iiko-integration::app.management.request-results-placeholder')"
        ></textarea>
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
                const resultsTextarea = document.getElementById('request-results');
                const timestamp = new Date().toLocaleString('ru-RU');
                const separator = '='.repeat(80);
                
                let logEntry = `\n${separator}\n`;
                logEntry += `[${timestamp}] ${action}\n`;
                logEntry += `${separator}\n`;
                
                if (requestData) {
                    logEntry += `REQUEST:\n${JSON.stringify(requestData, null, 2)}\n\n`;
                }
                
                if (error) {
                    logEntry += `ERROR:\n${error.message || error}\n`;
                    if (error.stack) {
                        logEntry += `\nStack:\n${error.stack}\n`;
                    }
                } else if (responseData) {
                    logEntry += `RESPONSE:\n${JSON.stringify(responseData, null, 2)}\n`;
                }
                
                logEntry += `\n${separator}\n`;
                
                resultsTextarea.value += logEntry;
                resultsTextarea.scrollTop = resultsTextarea.scrollHeight;
            }

            function clearResults() {
                document.getElementById('request-results').value = '';
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
                    // Automatically load saved terminals for selected organization
                    loadTerminals(false);
                } else {
                    document.getElementById('btn-get-terminals').disabled = true;
                    document.getElementById('terminals-select-container').classList.add('hidden');
                    document.getElementById('btn-get-menu').disabled = true;
                    document.getElementById('btn-get-nomenclature').disabled = true;
                    // Clear menu selection
                    selectedMenuId = null;
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

            function onMenuChange() {
                const select = document.getElementById('menu-select');
                selectedMenuId = select.value;
                
                if (selectedMenuId) {
                    document.getElementById('btn-get-nomenclature').disabled = false;
                } else {
                    document.getElementById('btn-get-nomenclature').disabled = true;
                }
            }

            async function getNomenclature() {
                if (!selectedOrganizationId) {
                    showMessage("@lang('iiko-integration::app.management.select-organization')", 'error');
                    return;
                }

                const button = document.getElementById('btn-get-nomenclature');
                const originalText = button.innerHTML;
                setButtonLoading('btn-get-nomenclature', true);

                const requestBody = { organization_id: selectedOrganizationId };
                if (selectedMenuId) {
                    requestBody.external_menu_id = selectedMenuId;
                }

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
                    } else {
                        showMessage(data.message || "@lang('iiko-integration::app.management.error')", 'error');
                    }
                } catch (error) {
                    logRequest('Get Nomenclature', requestData, null, error);
                    showMessage("@lang('iiko-integration::app.management.error')", 'error');
                } finally {
                    restoreButtonText('btn-get-nomenclature', originalText);
                }
            }
        </script>
    @endpush
</x-admin::layouts>
