@php
    $contactGroupId = $contactGroupId ?? null;
    // Debug: log contactGroupId in PHP
    \Log::debug('contacts-table component loaded', ['contactGroupId' => $contactGroupId]);
@endphp

<div id="contacts-table-container" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <!-- Search and Filters -->
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <input type="text"
                       id="search-input"
                       placeholder="{{ __('newsletters::app.admin.contacts.search-placeholder') }}"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                       onkeyup="handleSearch(event)">
            </div>
            @if(!empty($contactGroupId))
            <button type="button"
                    id="clear_group_contacts"
                    onclick="clearGroupContacts()"
                    class="px-4 py-2 text-sm font-medium text-red-500 bg-red-600 border border-red-200 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-700 dark:hover:bg-red-800">
                {{ __('newsletters::app.admin.contacts.clear-group') }}
            </button>
            @else
            <!-- Debug: contactGroupId is empty. Value: {{ var_export($contactGroupId, true) }} -->
            @endif
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loading-indicator-cl" class="hidden text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('newsletters::app.common.messages.loading') }}</p>
    </div>

    <!-- Table -->
    <div id="table-wrapper" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider sortable-column"
                        data-sort="id"
                        onclick="handleSort('id')">
                        <div class="flex items-center cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                            {{ __('newsletters::app.common.fields.id') }}
                            <span id="sort-indicator-id" class="ml-1"></span>
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider sortable-column"
                        data-sort="full_name"
                        onclick="handleSort('full_name')">
                        <div class="flex items-center cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                            {{ __('newsletters::app.admin.contacts.table.full-name') }}
                            <span id="sort-indicator-full_name" class="ml-1"></span>
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider sortable-column"
                        data-sort="phone"
                        onclick="handleSort('phone')">
                        <div class="flex items-center cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                            {{ __('newsletters::app.admin.contacts.table.phone') }}
                            <span id="sort-indicator-phone" class="ml-1"></span>
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider sortable-column"
                        data-sort="gender"
                        onclick="handleSort('gender')">
                        <div class="flex items-center cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                            {{ __('newsletters::app.admin.contacts.table.gender') }}
                            <span id="sort-indicator-gender" class="ml-1"></span>
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider sortable-column"
                        data-sort="total_check"
                        onclick="handleSort('total_check')">
                        <div class="flex items-center cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                            {{ __('newsletters::app.admin.contacts.table.total-check') }}
                            <span id="sort-indicator-total_check" class="ml-1"></span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody id="contacts-tbody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Contacts will be loaded here via AJAX -->
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="pagination-wrapper" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
        <!-- Pagination will be loaded here via AJAX -->
    </div>
</div>

<script>
    let currentPage = 1;
    let currentSortBy = 'id';
    let currentSortDir = 'desc';
    let currentSearch = '';
    // contactGroupId should be declared before this script (in edit.blade.php)
    // If not defined, assign it (for standalone usage of this component)
    if (typeof contactGroupId === 'undefined') {
        contactGroupId = @if(isset($contactGroupId) && $contactGroupId) {{ $contactGroupId }} @else null @endif;
    }
    let searchTimeout;

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Debug: log contactGroupId value
        console.log('Contact Group ID:', contactGroupId);

        // Ensure all elements exist before loading
        const loadingIndicator = document.getElementById('loading-indicator-cl');
        const tableWrapper = document.getElementById('table-wrapper');
        const tbody = document.getElementById('contacts-tbody');

        if (!loadingIndicator || !tableWrapper || !tbody) {
            console.error('Required elements not found');
            return;
        }

        loadContacts();
    });

    function loadContacts(page = 1) {
        currentPage = page;

        const loadingIndicator = document.getElementById('loading-indicator-cl');
        const tableWrapper = document.getElementById('table-wrapper');
        const tbody = document.getElementById('contacts-tbody');

        if (!loadingIndicator || !tableWrapper || !tbody) {
            console.error('Required elements not found in loadContacts');
            return;
        }

        loadingIndicator.classList.remove('hidden');
        tableWrapper.classList.add('opacity-50');

        const params = new URLSearchParams({
            page: currentPage,
            sort_by: currentSortBy,
            sort_dir: currentSortDir,
        });

        if (currentSearch) {
            params.append('search', currentSearch);
        }

        if (contactGroupId !== null && contactGroupId !== undefined) {
            params.append('contact_group_id', contactGroupId);
        }

        const url = '{{ route('admin.newsletters.contacts.get') }}?' + params.toString();
        console.log('Fetching contacts from:', url);

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(response => {
            console.log('Response status:', response.status, response.statusText);
            console.log('Response headers:', response.headers.get('content-type'));

            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error response:', text);
                    throw new Error(`HTTP error! status: ${response.status}`);
                });
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.error('Non-JSON response:', text.substring(0, 500));
                    throw new Error('Server returned non-JSON response. Check if you are logged in.');
                });
            }

            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            if (data && data.success) {
                renderContacts(data.contacts || []);
                if (data.pagination) {
                    renderPagination(data.pagination);
                }
                updateSortIndicators();
            } else {
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.contacts.no-contacts') }}</td></tr>';
                }
            }
        })
        .catch(error => {
            console.error('Error loading contacts:', error);
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-red-500">{{ __('newsletters::app.common.messages.error') }}: ' + (error.message || 'Unknown error') + '</td></tr>';
            }
        })
        .finally(() => {
            try {
                const loadingIndicator = document.getElementById('loading-indicator-cl');

                if (loadingIndicator) {
                    loadingIndicator.classList.add('hidden');
                }

                if (tableWrapper) {
                    tableWrapper.classList.remove('opacity-50');
                }

            } catch (e) {
                console.error('Error hiding loading indicator:', e);
            }
        });
    }

    function renderContacts(contacts) {
        const tbody = document.getElementById('contacts-tbody');

        if (contacts.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('newsletters::app.admin.contacts.no-contacts') }}</td></tr>';
            return;
        }

        tbody.innerHTML = contacts.map(contact => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${contact.id}</td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">${escapeHtml(contact.full_name || '-')}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${escapeHtml(contact.phone || '-')}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${getGenderLabel(contact.gender)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${formatNumber(contact.total_check)}</td>
            </tr>
        `).join('');
    }

    function renderPagination(pagination) {
        const wrapper = document.getElementById('pagination-wrapper');

        if (pagination.last_page <= 1) {
            wrapper.innerHTML = '';
            return;
        }

        let html = '<div class="flex items-center justify-between">';
        html += `<div class="text-sm text-gray-700 dark:text-gray-300">`;
        html += `{{ __('newsletters::app.admin.contacts.showing') }} ${((pagination.current_page - 1) * pagination.per_page) + 1} - ${Math.min(pagination.current_page * pagination.per_page, pagination.total)} {{ __('newsletters::app.admin.contacts.of') }} ${pagination.total}`;
        html += `</div>`;
        html += '<div class="flex gap-2">';

        // Previous button
        const prevText = '{{ __('newsletters::app.common.actions.previous') }}';
        if (pagination.current_page > 1) {
            html += `<button onclick="loadContacts(${pagination.current_page - 1})" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">${prevText}</button>`;
        }

        // Page numbers
        for (let i = 1; i <= pagination.last_page; i++) {
            if (i === 1 || i === pagination.last_page || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
                if (i === pagination.current_page) {
                    html += `<span class="px-3 py-2 text-sm font-medium text-red bg-indigo-600 border border-indigo-600 rounded-md">${i}</span>`;
                } else {
                    html += `<button onclick="loadContacts(${i})" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">${i}</button>`;
                }
            } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
                html += `<span class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">...</span>`;
            }
        }

        // Next button
        const nextText = '{{ __('newsletters::app.common.actions.next') }}';
        if (pagination.current_page < pagination.last_page) {
            html += `<button onclick="loadContacts(${pagination.current_page + 1})" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">${nextText}</button>`;
        }

        html += '</div></div>';
        wrapper.innerHTML = html;
    }

    function handleSort(column) {
        if (currentSortBy === column) {
            currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            currentSortBy = column;
            currentSortDir = 'asc';
        }
        loadContacts(1);
    }

    function updateSortIndicators() {
        // Reset all indicators
        document.querySelectorAll('[id^="sort-indicator-"]').forEach(el => {
            el.textContent = '';
        });

        // Set active indicator
        const indicator = document.getElementById(`sort-indicator-${currentSortBy}`);
        if (indicator) {
            indicator.textContent = currentSortDir === 'asc' ? '↑' : '↓';
        }
    }

    function handleSearch(event) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentSearch = event.target.value;
            loadContacts(1);
        }, 500);
    }

    function escapeHtml(text) {
        if (!text) return '-';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getGenderLabel(gender) {
        if (!gender) return '-';
        const labels = {
            'male': '{{ __('newsletters::app.admin.contacts.gender-male') }}',
            'female': '{{ __('newsletters::app.admin.contacts.gender-female') }}',
            'other': '{{ __('newsletters::app.admin.contacts.gender-other') }}',
        };
        return labels[gender] || gender;
    }

    function formatNumber(value) {
        if (!value) return '0.00';
        return parseFloat(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function clearGroupContacts() {
        if (!contactGroupId) {
            alert('{{ __('newsletters::app.admin.contacts.no-group-selected') }}');
            return;
        }

        if (!confirm('{{ __('newsletters::app.admin.contacts.clear-confirm') }}')) {
            return;
        }

        const loadingIndicator = document.getElementById('loading-indicator-cl');
        const tableWrapper = document.getElementById('table-wrapper');

        loadingIndicator.classList.remove('hidden');
        tableWrapper.classList.add('opacity-50');

        fetch('{{ route('admin.newsletters.contacts.clear-group') }}', {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                contact_group_id: contactGroupId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || '{{ __('newsletters::app.admin.contacts.clear-success-default') }}');
                loadContacts(1);
            } else {
                alert(data.message || '{{ __('newsletters::app.common.messages.error') }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __('newsletters::app.common.messages.error') }}');
        })
        .finally(() => {
            const loadingIndicator = document.getElementById('loading-indicator-cl');

            loadingIndicator.classList.add('hidden');
            tableWrapper.classList.remove('opacity-50');
        });
    }
</script>

<style>
    .sortable-column:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .dark .sortable-column:hover {
        background-color: rgba(0, 0, 0, 0.2);
    }
</style>

