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
            <div id="organizations-select-container" class="mt-4 hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    @lang('iiko-integration::app.management.select-organization')
                </label>
                <select
                    id="organizations-select"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white bg-white dark:bg-gray-700"
                    onchange="onOrganizationChange()"
                >
                    <option value="">@lang('iiko-integration::app.management.select-organization')</option>
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
                onclick="getTerminals()"
                disabled
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

    @push('scripts')
        <script>
            let selectedOrganizationId = null;
            let selectedTerminalId = null;

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
                    button.disabled = true;
                    button.innerHTML = "@lang('iiko-integration::app.management.loading')";
                } else {
                    button.disabled = false;
                }
            }

            function restoreButtonText(buttonId, text) {
                const button = document.getElementById(buttonId);
                button.innerHTML = text;
            }

            async function getOrganizations() {
                const button = document.getElementById('btn-get-organizations');
                setButtonLoading('btn-get-organizations', true);

                try {
                    const response = await fetch("{{ route('admin.iiko.management.organizations') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                    });

                    const data = await response.json();

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
                    showMessage("@lang('iiko-integration::app.management.error')", 'error');
                } finally {
                    restoreButtonText('btn-get-organizations', "@lang('iiko-integration::app.management.get-organizations')");
                }
            }

            function onOrganizationChange() {
                const select = document.getElementById('organizations-select');
                selectedOrganizationId = select.value;
                
                if (selectedOrganizationId) {
                    document.getElementById('btn-get-terminals').disabled = false;
                } else {
                    document.getElementById('btn-get-terminals').disabled = true;
                    document.getElementById('terminals-select-container').classList.add('hidden');
                    document.getElementById('btn-get-menu').disabled = true;
                    document.getElementById('btn-get-nomenclature').disabled = true;
                }
            }

            async function getTerminals() {
                if (!selectedOrganizationId) {
                    showMessage("@lang('iiko-integration::app.management.select-organization')", 'error');
                    return;
                }

                setButtonLoading('btn-get-terminals', true);

                try {
                    const response = await fetch("{{ route('admin.iiko.management.terminals') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ organization_id: selectedOrganizationId }),
                    });

                    const data = await response.json();

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
                        showMessage(data.message, 'success');
                    } else {
                        showMessage(data.message || "@lang('iiko-integration::app.management.no-data')", 'error');
                    }
                } catch (error) {
                    showMessage("@lang('iiko-integration::app.management.error')", 'error');
                } finally {
                    restoreButtonText('btn-get-terminals', "@lang('iiko-integration::app.management.get-terminals')");
                }
            }

            async function getMenu() {
                if (!selectedOrganizationId) {
                    showMessage("@lang('iiko-integration::app.management.select-organization')", 'error');
                    return;
                }

                setButtonLoading('btn-get-menu', true);

                try {
                    const response = await fetch("{{ route('admin.iiko.management.menu') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ organization_id: selectedOrganizationId }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        document.getElementById('btn-get-nomenclature').disabled = false;
                        showMessage(data.message, 'success');
                    } else {
                        showMessage(data.message || "@lang('iiko-integration::app.management.error')", 'error');
                    }
                } catch (error) {
                    showMessage("@lang('iiko-integration::app.management.error')", 'error');
                } finally {
                    restoreButtonText('btn-get-menu', "@lang('iiko-integration::app.management.get-menu')");
                }
            }

            async function getNomenclature() {
                if (!selectedOrganizationId) {
                    showMessage("@lang('iiko-integration::app.management.select-organization')", 'error');
                    return;
                }

                setButtonLoading('btn-get-nomenclature', true);

                try {
                    const response = await fetch("{{ route('admin.iiko.management.nomenclature') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ organization_id: selectedOrganizationId }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        showMessage(data.message, 'success');
                    } else {
                        showMessage(data.message || "@lang('iiko-integration::app.management.error')", 'error');
                    }
                } catch (error) {
                    showMessage("@lang('iiko-integration::app.management.error')", 'error');
                } finally {
                    restoreButtonText('btn-get-nomenclature', "@lang('iiko-integration::app.management.get-nomenclature')");
                }
            }
        </script>
    @endpush
</x-admin::layouts>
