<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.sales.orders.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600" style="min-width:40px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.sales.orders.index.title')
                </p>
                <p class="text-xs text-gray-400">@lang('admin::app.sales.orders.index.title')</p>
            </div>
        </div>

        <div class="flex items-center gap-x-2.5">
            <x-admin::datagrid.export src="{{ route('admin.sales.orders.index') }}" />

            {!! view_render_event('bagisto.admin.sales.orders.create.before') !!}

            @if (bouncer()->hasPermission('sales.orders.create'))
                <button
                    class="primary-button"
                    @click="$refs.selectCustomerComponent.openDrawer()"
                >
                    @lang('admin::app.sales.orders.index.create-btn')
                </button>
            @endif

            {!! view_render_event('bagisto.admin.sales.orders.create.after') !!}
        </div>
    </div>

    <!-- Status Filter Cards -->
    <v-order-status-filter :status-filters='@json($statusFilters)'></v-order-status-filter>

    <v-customer-search ref="selectCustomerComponent"></v-customer-search>

    <!-- Mass Action Component -->
    <v-order-mass-action></v-order-mass-action>

    <x-admin::datagrid :src="route('admin.sales.orders.index')" :isMultiRow="true">
        <template #header="{
            isLoading,
            available,
            applied,
            selectAll,
            sort,
            performAction
        }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.head :isMultiRow="true" />
            </template>

            <template v-else>
                <!-- Modern Grid Header -->
                <div class="flex items-center border-b border-gray-100 dark:border-gray-800 px-5 py-3 gap-6" style="background: linear-gradient(135deg, #f8f7ff 0%, #f0f4ff 100%);border-radius: 16px 16px 0 0;">
                    <!-- Select All Checkbox -->
                    <div class="flex items-center justify-center w-10 flex-shrink-0">
                        <input 
                            type="checkbox" 
                            id="select-all-orders"
                            class="w-5 h-5 rounded border-2 border-gray-300 text-violet-600 focus:ring-violet-500 cursor-pointer"
                            onclick="handleSelectAllOrders(this.checked)"
                        />
                    </div>
                    
                    <!-- Column Headers -->
                    <div class="flex-1" style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem;">
                        <div
                            class="flex select-none items-center gap-2"
                            v-for="(columnGroup, index) in [['increment_id'], ['base_grand_total', 'method'], ['full_name', 'customer_email'], ['items']]"
                        >
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                <span>
                                    <template v-for="(column, idx) in columnGroup">
                                        <span
                                            class="transition-colors duration-200"
                                            :class="{
                                                'text-violet-600 dark:text-violet-400': applied.sort.column == column,
                                                'cursor-pointer hover:text-violet-600 dark:hover:text-violet-400': available.columns.find(columnTemp => columnTemp.index === column)?.sortable,
                                            }"
                                            @click="
                                                available.columns.find(columnTemp => columnTemp.index === column)?.sortable ? sort(available.columns.find(columnTemp => columnTemp.index === column)) : {}
                                            "
                                        >
                                            @{{ available.columns.find(columnTemp => columnTemp.index === column)?.label }}
                                        </span>
                                        <span v-if="idx < columnGroup.length - 1" class="mx-1 text-gray-300 dark:text-gray-600">/</span>
                                    </template>
                                </span>

                                <i
                                    class="align-text-bottom text-sm text-violet-600 dark:text-violet-400 ltr:ml-1 rtl:mr-1 transition-transform duration-200"
                                    :class="[applied.sort.order === 'asc' ? 'icon-down-stat': 'icon-up-stat']"
                                    v-if="columnGroup.includes(applied.sort.column)"
                                >
                                </i>
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </template>

        <template #body="{
            isLoading,
            available,
            applied,
            selectAll,
            sort,
            performAction
        }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.body :isMultiRow="true" />
            </template>

            <template v-else>
                <!-- Modern Order Cards -->
                <div class="divide-y divide-gray-100 dark:divide-gray-800" style="border-radius: 0 0 16px 16px; overflow: visible;">
                    <div
                        class="group flex items-start gap-4 md:gap-6 px-5 py-4 transition-all duration-300 hover:bg-gradient-to-r hover:from-violet-50/60 hover:to-transparent dark:hover:from-violet-900/10 relative cursor-pointer"
                        v-for="(record, index) in available.records"
                        :key="record.id"
                        style="border-left: 3px solid transparent; transition: border-color 0.3s, background 0.3s;"
                        @mouseenter="$event.currentTarget.style.borderLeftColor='#8b5cf6'; $emitter.emit('preload-order', record)"
                        @mouseleave="$event.currentTarget.style.borderLeftColor='transparent'"
                        @click="$emitter.emit('open-order-drawer', record)"
                    >
                        <!-- Checkbox -->
                        <div class="flex items-center justify-center w-10 flex-shrink-0 pt-3" @click.stop>
                            <input 
                                type="checkbox" 
                                :value="record.id"
                                :data-order-id="record.id"
                                class="order-checkbox w-5 h-5 rounded border-2 border-gray-300 text-violet-600 focus:ring-violet-500 cursor-pointer"
                                onclick="handleOrderCheckbox(this)"
                            />
                        </div>
                        
                        <!-- Main Content Grid -->
                        <div class="flex-1" style="display: grid; grid-template-columns: 2fr 1.5fr 1.5fr 2fr; gap: 1rem; align-items: center;">
                            <!-- Order Id, Created, Status Section -->
                            <div class="flex items-start gap-4">
                                <!-- Order Number Badge -->
                                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center shadow-lg shadow-violet-500/20 group-hover:shadow-violet-500/40 group-hover:scale-105 transition-all duration-300">
                                    <span class="text-white font-bold text-sm">#@{{ index + 1 }}</span>
                                </div>
                                
                                <div class="flex flex-col gap-1.5 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-base font-bold text-gray-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors duration-200">
                                            #@{{ record.increment_id }}
                                        </p>
                                        <v-inline-order-status
                                            :order-id="record.id"
                                            :initial-status-code="record.status_code"
                                            :initial-status-html="record.status"
                                        ></v-inline-order-status>
                                    </div>

                                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span v-html="$formatRelativeDate(record.created_at)"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Amount, Pay Via -->
                            <div class="flex flex-col justify-center gap-1.5">
                                <p class="text-lg font-bold text-gray-900 dark:text-white" style="letter-spacing: -0.02em;">
                                    @{{ $admin.formatPrice(record.base_grand_total) }}
                                </p>

                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full" style="background: #f1f0ff;">
                                        <svg class="w-3 h-3" style="color:#7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        <span style="color:#7c3aed; font-weight:500;">@{{ record.method }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer, Email Section -->
                            <div class="flex items-center gap-3">
                                <!-- Customer Avatar -->
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center text-white font-semibold text-sm shadow-md">
                                    @{{ record.full_name ? record.full_name.charAt(0).toUpperCase() : '?' }}
                                </div>
                                
                                <div class="flex flex-col gap-0.5 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                        @{{ record.full_name }}
                                    </p>

                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        @{{ record.customer_email }}
                                    </p>
                                </div>
                            </div>

                            <!-- Images + Actions Section -->
                            <div class="flex items-center justify-between gap-3">
                                <div
                                    class="flex items-center"
                                    v-html="record.items"
                                >
                                </div>

                                <div class="flex items-center gap-1.5" @click.stop>
                                    <!-- Quick View Button -->
                                    <a 
                                        href="javascript:void(0)"
                                        @click.stop="$emitter.emit('open-order-drawer', record)"
                                        class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-200"
                                        style="background:#f5f3ff;"
                                        @mouseenter="$event.currentTarget.style.background='#ede9fe'"
                                        @mouseleave="$event.currentTarget.style.background='#f5f3ff'"
                                        title="Быстрый просмотр"
                                    >
                                        <svg class="w-4 h-4" style="color:#7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    <!-- Open Order Button -->
                                    <a 
                                        :href="`{{ route('admin.sales.orders.view', '') }}/${record.id}`"
                                        class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-200"
                                        style="background:#f0fdf4;"
                                        @mouseenter="$event.currentTarget.style.background='#dcfce7'"
                                        @mouseleave="$event.currentTarget.style.background='#f0fdf4'"
                                        title="Открыть заказ"
                                    >
                                        <svg class="w-4 h-4" style="color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>

    <!-- Order Slide-Out Drawer -->
    <v-order-drawer></v-order-drawer>

    @include('admin::customers.customers.index.create')

    @pushOnce('scripts')
        <script type="module">
            // Register formatRelativeDate as Vue global property so it's accessible in templates
            app.config.globalProperties.$formatRelativeDate = window.formatRelativeDate;
        </script>

        <!-- Global functions for order selection -->
        <script>
            // Relative date formatter — also registered as Vue global property below
            function formatRelativeDate(dateStr) {
                if (!dateStr) return '';
                
                // Parse date format "d M Y h:i A" or similar formats from Bagisto datagrid
                let date;
                try {
                    date = new Date(dateStr);
                    if (isNaN(date.getTime())) {
                        // Try to parse common Bagisto format: "27 Jan 2025 12:30 PM"
                        const parts = dateStr.match(/(\d{1,2})\s+(\w+)\s+(\d{4})\s+(\d{1,2}):(\d{2})\s*(AM|PM)?/i);
                        if (parts) {
                            const months = {jan:0,feb:1,mar:2,apr:3,may:4,jun:5,jul:6,aug:7,sep:8,oct:9,nov:10,dec:11};
                            let hour = parseInt(parts[4]);
                            if (parts[6] && parts[6].toUpperCase() === 'PM' && hour < 12) hour += 12;
                            if (parts[6] && parts[6].toUpperCase() === 'AM' && hour === 12) hour = 0;
                            date = new Date(parseInt(parts[3]), months[parts[2].toLowerCase().substring(0,3)], parseInt(parts[1]), hour, parseInt(parts[5]));
                        }
                    }
                } catch(e) {
                    return dateStr;
                }
                
                if (!date || isNaN(date.getTime())) return dateStr;

                const now = new Date();
                const diffMs = now - date;
                const diffMin = Math.floor(diffMs / 60000);
                const diffHrs = diffMs / 3600000;

                const hh = String(date.getHours()).padStart(2, '0');
                const mi = String(date.getMinutes()).padStart(2, '0');
                const timeStr = hh + ':' + mi;

                // Check if the order is from today
                const isToday = date.getDate() === now.getDate()
                    && date.getMonth() === now.getMonth()
                    && date.getFullYear() === now.getFullYear();

                let line1 = '';
                if (isToday) {
                    if (diffMin < 1) {
                        line1 = 'только что';
                    } else if (diffMin < 60) {
                        line1 = diffMin + ' мин. назад';
                    } else {
                        line1 = diffHrs.toFixed(1).replace('.0', '') + ' ч. назад';
                    }
                } else {
                    const dd = String(date.getDate()).padStart(2, '0');
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const yyyy = date.getFullYear();
                    line1 = dd + '.' + mm + '.' + yyyy;
                }

                return '<span>' + line1 + '</span><br><span style="opacity:.6">' + timeStr + '</span>';
            }

            // Store for selected orders
            window.selectedOrderIds = [];
            
            function handleSelectAllOrders(checked) {
                const checkboxes = document.querySelectorAll('.order-checkbox');
                window.selectedOrderIds = [];
                
                checkboxes.forEach(cb => {
                    cb.checked = checked;
                    if (checked) {
                        const orderId = parseInt(cb.dataset.orderId);
                        if (orderId && !window.selectedOrderIds.includes(orderId)) {
                            window.selectedOrderIds.push(orderId);
                        }
                    }
                });
                
                // Dispatch event for Vue component
                window.dispatchEvent(new CustomEvent('orders-selection-changed', { 
                    detail: { orderIds: window.selectedOrderIds } 
                }));
            }
            
            function handleOrderCheckbox(checkbox) {
                // Ensure array is initialized
                if (!window.selectedOrderIds) {
                    window.selectedOrderIds = [];
                }
                
                const orderId = parseInt(checkbox.dataset.orderId);
                console.log('handleOrderCheckbox called, orderId:', orderId, 'checked:', checkbox.checked);
                
                if (checkbox.checked) {
                    if (!window.selectedOrderIds.includes(orderId)) {
                        window.selectedOrderIds.push(orderId);
                    }
                } else {
                    window.selectedOrderIds = window.selectedOrderIds.filter(id => id !== orderId);
                    // Uncheck select-all if any checkbox is unchecked
                    const selectAll = document.getElementById('select-all-orders');
                    if (selectAll) selectAll.checked = false;
                }
                
                console.log('Current selectedOrderIds:', window.selectedOrderIds);
                
                // Dispatch event for Vue component
                const event = new CustomEvent('orders-selection-changed', { 
                    detail: { orderIds: [...window.selectedOrderIds] } 
                });
                console.log('Dispatching event:', event);
                window.dispatchEvent(event);
            }
        </script>
    
        <!-- Order Mass Action Component -->
        <script type="text/x-template" id="v-inline-order-status-template">
            <div class="relative" :style="{ zIndex: isOpen ? 10020 : 1 }" @click.stop>
                <button
                    type="button"
                    @click="toggle"
                    class="inline-flex items-center gap-1"
                    :disabled="isLoading"
                    style="cursor:pointer;"
                >
                    <span v-html="statusHtml"></span>
                    <svg
                        class="w-3.5 h-3.5 text-gray-500 transition-transform duration-200"
                        :style="{ transform: isOpen ? 'rotate(180deg)' : 'rotate(0deg)' }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div
                    v-if="isOpen"
                    class="absolute z-[10020] mt-2 min-w-[210px] rounded-xl border border-gray-200 bg-white p-1 shadow-xl dark:border-gray-700 dark:bg-gray-900"
                >
                    <button
                        v-for="s in allStatuses"
                        :key="s.code"
                        type="button"
                        @click="changeStatus(s.code)"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                        :disabled="isLoading || s.code === currentStatusCode"
                    >
                        <span class="flex items-center gap-2">
                            <span
                                class="inline-block h-2.5 w-2.5 rounded-full"
                                :style="{ backgroundColor: s.color || '#6b7280' }"
                            ></span>
                            <span class="text-gray-700 dark:text-gray-200">@{{ s.name }}</span>
                        </span>

                        <svg
                            v-if="s.code === currentStatusCode"
                            class="h-4 w-4 text-emerald-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-inline-order-status', {
                template: '#v-inline-order-status-template',

                props: {
                    orderId: {
                        type: [String, Number],
                        required: true,
                    },

                    initialStatusCode: {
                        type: String,
                        default: '',
                    },

                    initialStatusHtml: {
                        type: String,
                        default: '',
                    },
                },

                data() {
                    return {
                        isOpen: false,
                        isLoading: false,
                        currentStatusCode: this.initialStatusCode,
                        statusHtml: this.initialStatusHtml,
                        allStatuses: @json(\Webkul\Sales\Models\OrderStatus::allForJs()),
                    };
                },

                mounted() {
                    document.addEventListener('click', this.handleOutsideClick);
                },

                beforeUnmount() {
                    document.removeEventListener('click', this.handleOutsideClick);
                },

                methods: {
                    toggle() {
                        if (this.isLoading) return;

                        this.isOpen = !this.isOpen;
                    },

                    handleOutsideClick(event) {
                        if (!this.$el.contains(event.target)) {
                            this.isOpen = false;
                        }
                    },

                    buildBadgeHtml(code) {
                        const status = this.allStatuses.find((item) => item.code === code);

                        if (!status) {
                            return this.statusHtml;
                        }

                        const color = status.color || '#6b7280';

                        return `<span style="display:inline-block;padding:2px 10px;border-radius:9999px;font-size:12px;font-weight:600;background:${color}1a;color:${color};">${status.name}</span>`;
                    },

                    async changeStatus(code) {
                        if (!code || code === this.currentStatusCode || this.isLoading) {
                            this.isOpen = false;
                            return;
                        }

                        this.isLoading = true;

                        try {
                            const url = '{{ route('admin.sales.orders.update_status', ['id' => '__ID__']) }}'.replace('__ID__', this.orderId);

                            const response = await this.$axios.post(url, { status: code });

                            this.currentStatusCode = code;
                            this.statusHtml = this.buildBadgeHtml(code);
                            this.isOpen = false;

                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data?.message || 'Статус заказа обновлён',
                            });

                            this.$emitter.emit('datagrid:refresh');
                        } catch (error) {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || 'Не удалось обновить статус заказа',
                            });
                        } finally {
                            this.isLoading = false;
                        }
                    },
                },
            });
        </script>

        <script type="text/x-template" id="v-order-mass-action-template">
            <transition name="slide-up">
                <div v-if="selectedOrders.length > 0" 
                     class="fixed bottom-4 left-1/2 -translate-x-1/2 z-[9999] flex items-center gap-3 px-5 py-3 bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700"
                     style="box-shadow: 0 -4px 30px rgba(0,0,0,0.15);">
                    
                    <!-- Selected count -->
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/30">
                            <span class="text-base font-bold text-violet-600 dark:text-violet-400">@{{ selectedOrders.length }}</span>
                        </div>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">
                            выбрано
                        </span>
                    </div>
                    
                    <div class="w-px h-6 bg-gray-200 dark:bg-gray-700"></div>
                    
                    <!-- Status select -->
                    <div class="flex items-center gap-2">
                        <select 
                            v-model="selectedStatus" 
                            class="px-3 py-1.5 text-sm font-medium border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                        >
                            <option value="">Статус</option>
                            <option v-for="s in allStatuses" :key="s.code" :value="s.code">@{{ s.name }}</option>
                        </select>
                    </div>
                    
                    <!-- Apply button -->
                    <button 
                        @click="applyMassAction"
                        :disabled="!selectedStatus || isLoading"
                        class="flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold text-white bg-gradient-to-r from-violet-500 to-violet-600 rounded-lg hover:from-violet-600 hover:to-violet-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                    >
                        <svg v-if="isLoading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>OK</span>
                    </button>
                    
                    <!-- Clear selection -->
                    <button 
                        @click="clearSelection"
                        class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200"
                        title="Сбросить выбор"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </transition>
        </script>

        <script type="module">
            app.component('v-order-mass-action', {
                template: '#v-order-mass-action-template',

                data() {
                    return {
                        selectedOrders: [],
                        selectedStatus: '',
                        isLoading: false,
                        allStatuses: @json(\Webkul\Sales\Models\OrderStatus::ordered()->toArray()),
                    }
                },

                mounted() {
                    // Listen to global selection event
                    window.addEventListener('orders-selection-changed', this.handleSelectionChange);
                },
                
                beforeUnmount() {
                    window.removeEventListener('orders-selection-changed', this.handleSelectionChange);
                },

                methods: {
                    handleSelectionChange(event) {
                        this.selectedOrders = event.detail.orderIds || [];
                    },
                    
                    clearSelection() {
                        this.selectedOrders = [];
                        this.selectedStatus = '';
                        window.selectedOrderIds = [];
                        document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = false);
                        const selectAllCb = document.getElementById('select-all-orders');
                        if (selectAllCb) selectAllCb.checked = false;
                    },
                    
                    async applyMassAction() {
                        if (!this.selectedStatus || this.selectedOrders.length === 0) return;
                        
                        this.isLoading = true;
                        
                        try {
                            const response = await this.$axios.post('{{ route("admin.sales.orders.mass_update_status") }}', {
                                order_ids: this.selectedOrders,
                                status: this.selectedStatus
                            });
                            
                            if (response.data.success) {
                                this.$emitter.emit('add-flash', { 
                                    type: 'success', 
                                    message: response.data.message 
                                });
                                
                                // Reload the page to refresh data
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            }
                        } catch (error) {
                            this.$emitter.emit('add-flash', { 
                                type: 'error', 
                                message: error.response?.data?.message || 'Произошла ошибка' 
                            });
                        } finally {
                            this.isLoading = false;
                        }
                    }
                }
            });
        </script>
        
        <style>
            .slide-up-enter-active,
            .slide-up-leave-active {
                transition: all 0.3s ease;
            }
            .slide-up-enter-from,
            .slide-up-leave-to {
                opacity: 0;
                transform: translate(-50%, 20px);
            }
        </style>

        <!-- Order Slide-Out Drawer Component -->
        <script type="text/x-template" id="v-order-drawer-template">
            <teleport to="body">
                <!-- Always in DOM — visibility controlled via CSS for instant reopen -->
                <div
                    :style="{
                        position: 'fixed',
                        inset: 0,
                        zIndex: 9998,
                        visibility: isOpen ? 'visible' : 'hidden',
                        pointerEvents: isOpen ? 'auto' : 'none',
                    }"
                >
                    <!-- Backdrop -->
                    <div
                        @click="closeDrawer"
                        :style="{
                            position: 'absolute',
                            inset: 0,
                            background: 'rgba(0,0,0,0.3)',
                            backdropFilter: 'blur(2px)',
                            transition: 'opacity 0.3s ease',
                            opacity: panelVisible ? 1 : 0,
                        }"
                    ></div>

                    <!-- Slide-out Panel -->
                    <div
                        ref="drawerPanel"
                        style="position:absolute; top:0; right:0; bottom:0; width:calc(100vw - 270px); max-width:calc(100vw - 270px); background:#f8f9fb; box-shadow:-8px 0 40px rgba(0,0,0,0.15); transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1); overflow:hidden; display:flex; flex-direction:column;"
                        :style="{ transform: panelVisible ? 'translateX(0)' : 'translateX(100%)' }"
                    >
                        <!-- Drawer Header -->
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:11px 24px; background:white; border-bottom:1px solid #e5e7eb; flex-shrink:0; overflow:hidden;">
                            <div style="display:flex; align-items:center; gap:14px; min-width:0; flex:1; overflow:hidden;">
                                <!-- Close Button -->
                                <button
                                    @click="closeDrawer"
                                    style="display:flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:12px; background:#f3f4f6; border:none; cursor:pointer; transition:all 0.2s;"
                                    @mouseenter="$event.currentTarget.style.background='#e5e7eb'; $event.currentTarget.style.transform='scale(1.05)'"
                                    @mouseleave="$event.currentTarget.style.background='#f3f4f6'; $event.currentTarget.style.transform='scale(1)'"
                                    title="Закрыть (Esc)"
                                >
                                    <svg style="width:18px; height:18px; color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>

                                <!-- Order Info -->
                                <div>
                                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                        <span style="font-size:18px; font-weight:800; color:#1f2937; letter-spacing:-0.02em;">
                                            Заказ #@{{ orderRecord.increment_id }}
                                        </span>
                                        <span
                                            v-if="orderRecord.status"
                                            v-html="orderRecord.status"
                                        ></span>
                                    </div>
                                    <div style="display:flex; align-items:center; gap:8px; margin-top:3px;">
                                        <span style="font-size:13px; color:#6b7280; font-weight:500;">
                                            @{{ orderRecord.full_name || 'Гость' }}
                                        </span>
                                        <span v-if="orderRecord.base_grand_total" style="font-size:13px; color:#9ca3af;">•</span>
                                        <span v-if="orderRecord.base_grand_total" style="font-size:13px; font-weight:700; color:#7c3aed;">
                                            @{{ $admin.formatPrice(orderRecord.base_grand_total) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Open Full Page -->
                            <a
                                :href="`{{ route('admin.sales.orders.view', '') }}/${orderRecord.id}`"
                                style="display:flex; align-items:center; gap:6px; padding:8px 16px; font-size:13px; font-weight:700; color:white; background:linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); border-radius:12px; text-decoration:none; box-shadow:0 2px 8px rgba(124,58,237,0.3); transition:all 0.2s; flex-shrink:0; white-space:nowrap;"
                                @mouseenter="$event.currentTarget.style.boxShadow='0 4px 16px rgba(124,58,237,0.4)'"
                                @mouseleave="$event.currentTarget.style.boxShadow='0 2px 8px rgba(124,58,237,0.3)'"
                            >
                                <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Открыть
                            </a>
                        </div>

                        <!-- Loading Overlay -->
                        <div v-if="isLoading && isOpen" style="flex:1; display:flex; align-items:center; justify-content:center; background:rgba(248,249,251,0.9); position:absolute; top:60px; left:0; right:0; bottom:0; z-index:2;">
                            <div style="display:flex; flex-direction:column; align-items:center; gap:12px;">
                                <div style="width:48px; height:48px; border-radius:16px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); animation:pulse 1.5s infinite;">
                                    <svg style="width:24px; height:24px; color:white; animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                                        <circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <p style="font-size:14px; color:#9ca3af; font-weight:500;">Загрузка заказа...</p>
                            </div>
                        </div>

                        <!-- Iframe Content — kept alive across open/close for instant reopen -->
                        <iframe
                            v-if="iframeSrc"
                            :src="iframeSrc"
                            ref="orderIframe"
                            @load="onIframeLoad"
                            style="width:100%; border:none; flex:1; margin:0; padding:0; display:block;"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>
            </teleport>
        </script>

        <script type="module">
            app.component('v-order-drawer', {
                template: '#v-order-drawer-template',

                data() {
                    return {
                        isOpen: false,
                        panelVisible: false,
                        isLoading: false,
                        iframeSrc: '',
                        orderRecord: {},
                        currentOrderId: null,
                        hoverTimer: null,
                    };
                },

                mounted() {
                    // Listen for open events from row clicks
                    this.$emitter.on('open-order-drawer', (record) => {
                        this.openDrawer(record);
                    });

                    // Listen for preload events from row hover
                    this.$emitter.on('preload-order', (record) => {
                        this.preloadOrder(record);
                    });

                    // Listen for postMessage from iframe (close, status update, etc.)
                    window.addEventListener('message', this.handleMessage);

                    // Close on Escape key
                    window.addEventListener('keydown', this.handleKeyDown);
                },

                beforeUnmount() {
                    window.removeEventListener('message', this.handleMessage);
                    window.removeEventListener('keydown', this.handleKeyDown);
                    clearTimeout(this.hoverTimer);
                },

                methods: {
                    /**
                     * Start preloading an order's view-panel on hover.
                     * The iframe loads in the background (drawer hidden via CSS).
                     * If user clicks the same order, it opens instantly.
                     */
                    preloadOrder(record) {
                        // Don't preload if drawer is already open
                        if (this.isOpen) return;
                        // Don't preload if already loaded/loading this order
                        if (this.currentOrderId === record.id) return;

                        // Debounce: only preload if mouse stays 120ms (prevents rapid-fire on scroll)
                        clearTimeout(this.hoverTimer);
                        this.hoverTimer = setTimeout(() => {
                            this.currentOrderId = record.id;
                            this.orderRecord = record;
                            this.isLoading = true;
                            this.iframeSrc = window.location.origin + '/admin/sales/orders/view-panel/' + record.id;
                        }, 120);
                    },

                    openDrawer(record) {
                        clearTimeout(this.hoverTimer);
                        this.orderRecord = record;

                        const alreadyLoaded = (this.currentOrderId === record.id && !this.isLoading);
                        const alreadyLoading = (this.currentOrderId === record.id && this.isLoading);

                        if (!alreadyLoaded && !alreadyLoading) {
                            // New order — start loading from scratch
                            this.currentOrderId = record.id;
                            this.isLoading = true;
                            this.iframeSrc = window.location.origin + '/admin/sales/orders/view-panel/' + record.id;
                        }
                        // If alreadyLoaded: isLoading is false -> no spinner, instant!
                        // If alreadyLoading: spinner continues until iframe fires @load

                        this.isOpen = true;

                        // Animate panel in
                        this.$nextTick(() => {
                            requestAnimationFrame(() => {
                                this.panelVisible = true;
                            });
                        });

                        // Blur the sidebar
                        this.toggleSidebarBlur(true);

                        // Prevent body scroll
                        document.body.style.overflow = 'hidden';
                    },

                    closeDrawer() {
                        this.panelVisible = false;

                        // Wait for slide-out animation to finish
                        setTimeout(() => {
                            this.isOpen = false;
                            // Keep iframeSrc & currentOrderId alive!
                            // Reopening same order = instant.

                            // Remove sidebar blur
                            this.toggleSidebarBlur(false);

                            // Restore body scroll
                            document.body.style.overflow = '';
                        }, 350);
                    },

                    onIframeLoad() {
                        this.isLoading = false;
                    },

                    handleMessage(event) {
                        if (!event.data || typeof event.data !== 'object') return;

                        switch (event.data.type) {
                            case 'close-order-panel':
                                this.closeDrawer();
                                break;
                            case 'order-status-updated':
                            case 'order-items-updated':
                                // Refresh the datagrid to reflect changes
                                this.$emitter.emit('datagrid:refresh');
                                break;
                        }
                    },

                    handleKeyDown(e) {
                        if (e.key === 'Escape' && this.isOpen) {
                            this.closeDrawer();
                        }
                    },

                    toggleSidebarBlur(blur) {
                        // Find the sidebar element in the layout
                        const sidebar = document.querySelector('.lg\\:fixed.lg\\:top-\\[58px\\]');
                        if (sidebar) {
                            sidebar.style.transition = 'filter 0.3s ease';
                            sidebar.style.filter = blur ? 'blur(4px)' : 'none';
                            sidebar.style.pointerEvents = blur ? 'none' : '';
                        }
                    },
                },
            });
        </script>

        <style>
            @keyframes spin { to { transform: rotate(360deg); } }
            @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .7; } }
        </style>

        <!-- Order Status Filter Component -->
        <script type="text/x-template" id="v-order-status-filter-template">
            <div style="margin-top:16px; margin-bottom:24px;">
                <!-- Status Cards Row -->
                <div style="display:flex; gap:14px; overflow-x:auto; padding-bottom:6px;">
                    <div
                        v-for="status in statusFilters"
                        :key="status.key"
                        @click="filterByStatus(status.key)"
                        style="min-width:155px; flex:1; background:#fff; border-radius:16px; overflow:hidden; cursor:pointer; transition:all 0.3s ease;"
                        :style="{
                            boxShadow: activeStatus === status.key
                                ? '0 8px 28px ' + hexToRgba(status.color, 0.3) + ', 0 0 0 2px ' + status.color
                                : '0 1px 6px rgba(0,0,0,0.06)',
                            transform: activeStatus === status.key ? 'translateY(-3px)' : 'translateY(0)'
                        }"
                        @mouseenter="$event.currentTarget.style.boxShadow = activeStatus === status.key ? '0 8px 28px ' + hexToRgba(status.color, 0.3) + ', 0 0 0 2px ' + status.color : '0 6px 20px ' + hexToRgba(status.color, 0.18); $event.currentTarget.style.transform = 'translateY(-3px)'"
                        @mouseleave="$event.currentTarget.style.boxShadow = activeStatus === status.key ? '0 8px 28px ' + hexToRgba(status.color, 0.3) + ', 0 0 0 2px ' + status.color : '0 1px 6px rgba(0,0,0,0.06)'; $event.currentTarget.style.transform = activeStatus === status.key ? 'translateY(-3px)' : 'translateY(0)'"
                    >
                        <!-- Top color bar -->
                        <div style="height:4px;" :style="{ background: 'linear-gradient(90deg, ' + status.color + ', ' + lightenColor(status.color, 30) + ')' }"></div>

                        <!-- Card content -->
                        <div style="padding:14px 16px 16px;">
                            <!-- Icon + Label -->
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                                <div style="width:38px; height:38px; border-radius:11px; display:flex; align-items:center; justify-content:center; flex-shrink:0;"
                                     :style="{ background: hexToRgba(status.color, 0.13) }">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" :style="{ color: status.color }">
                                        <path :d="getIconPath(status.icon)" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                              :fill="getIconFill(status.icon) ? 'currentColor' : 'none'"/>
                                    </svg>
                                </div>
                                <span style="font-size:12px; font-weight:600; color:#6b7280; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">@{{ status.label }}</span>
                            </div>
                            <!-- Count number -->
                            <div style="font-size:28px; font-weight:800; line-height:1;" :style="{ color: status.color }">
                                @{{ status.count }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clear Filter Button -->
                <div v-if="activeStatus" style="margin-top:12px; display:flex; justify-content:flex-end;">
                    <button
                        @click="clearFilter"
                        style="display:flex; align-items:center; gap:6px; padding:6px 14px; font-size:13px; font-weight:500; color:#7c3aed; background:#f5f3ff; border:none; border-radius:10px; cursor:pointer; transition:all 0.2s;"
                        @mouseenter="$event.currentTarget.style.background='#ede9fe'"
                        @mouseleave="$event.currentTarget.style.background='#f5f3ff'"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Сбросить фильтр
                    </button>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-order-status-filter', {
                template: '#v-order-status-filter-template',

                props: {
                    statusFilters: {
                        type: Array,
                        required: true
                    }
                },

                data() {
                    return {
                        activeStatus: null,
                    }
                },

                mounted() {
                    const savedFilter = localStorage.getItem('orders_filter_status');
                    if (savedFilter) {
                        localStorage.removeItem('orders_filter_status');
                        setTimeout(() => {
                            this.filterByStatus(savedFilter);
                        }, 500);
                    }
                },

                methods: {
                    filterByStatus(status) {
                        if (this.activeStatus === status) {
                            this.clearFilter();
                            return;
                        }

                        this.activeStatus = status;

                        this.$emitter.emit('datagrid:filter', {
                            columns: [{
                                index: 'status',
                                value: [status]
                            }]
                        });
                    },

                    clearFilter() {
                        this.activeStatus = null;

                        this.$emitter.emit('datagrid:filter', {
                            columns: [{
                                index: 'status',
                                value: []
                            }]
                        });
                    },

                    hexToRgba(hex, alpha) {
                        if (!hex) return 'rgba(107,114,128,' + alpha + ')';
                        const h = hex.replace('#', '');
                        const r = parseInt(h.substring(0, 2), 16);
                        const g = parseInt(h.substring(2, 4), 16);
                        const b = parseInt(h.substring(4, 6), 16);
                        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
                    },

                    lightenColor(hex, percent) {
                        if (!hex) return '#a0a0a0';
                        const h = hex.replace('#', '');
                        let r = parseInt(h.substring(0, 2), 16);
                        let g = parseInt(h.substring(2, 4), 16);
                        let b = parseInt(h.substring(4, 6), 16);
                        r = Math.min(255, r + Math.round((255 - r) * percent / 100));
                        g = Math.min(255, g + Math.round((255 - g) * percent / 100));
                        b = Math.min(255, b + Math.round((255 - b) * percent / 100));
                        return '#' + [r, g, b].map(c => c.toString(16).padStart(2, '0')).join('');
                    },

                    getIconFill(icon) {
                        return ['check-circle-fill'].includes(icon);
                    },

                    getIconPath(icon) {
                        const paths = {
                            'hourglass-top':       'M12 2v4l-3 3 3 3v4M8 2h8M8 22h8M12 18v4',
                            'receipt':             'M4 2v20l3-2 3 2 3-2 3 2V2l-3 2-3-2-3 2-3-2zM8 7h8M8 11h8M8 15h4',
                            'credit-card':         'M1 5a2 2 0 012-2h18a2 2 0 012 2v14a2 2 0 01-2 2H3a2 2 0 01-2-2V5zm0 4h22M5 14h4',
                            'arrow-repeat':        'M17 1l4 4-4 4M3 11V9a4 4 0 014-4h14M7 23l-4-4 4-4M21 13v2a4 4 0 01-4 4H3',
                            'fire':                'M12 23c-4.97 0-8-3.58-8-8 0-4 2.5-7.5 5-10 .5 2 2 3.5 3.5 3.5C11 8.5 10 5 12 2c2 4 6 6 6 10 0 4.42-3.03 8-6 11z',
                            'check2-circle':       'M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm-1.5-6l-3.5-3.5 1.414-1.414L10.5 13.172l5.586-5.586L17.5 9l-7 7z',
                            'check-circle-fill':   'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z',
                            'x-circle':            'M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zM15 9l-6 6M9 9l6 6',
                            'lock':                'M19 11H5a2 2 0 00-2 2v7a2 2 0 002 2h14a2 2 0 002-2v-7a2 2 0 00-2-2zM7 11V7a5 5 0 0110 0v4',
                            'pause-circle':        'M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zM10 15V9M14 15V9',
                            'exclamation-triangle': 'M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0zM12 9v4M12 17h.01',
                            'lightning':           'M13 2L3 14h9l-1 8 10-12h-9l1-8',
                            'truck':               'M1 3h15v13H1zM16 8h4l3 3v5h-7V8zM5.5 21a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM18.5 21a2.5 2.5 0 100-5 2.5 2.5 0 000 5z',
                            'bag-check':           'M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4H6zM3 6h18M16 10a4 4 0 01-8 0',
                            'clock':               'M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zM12 6v6l4 2',
                        };
                        return paths[icon] || paths['clock'];
                    },
                }
            });
        </script>

        <script
            type="text/x-template"
            id="v-customer-search-template"
        >
            <div class="">
                <!-- Search Drawer -->
                <x-admin::drawer
                    ref="searchCustomerDrawer"
                    @close="searchTerm = ''; searchedCustomers = [];"
                >
                    <!-- Drawer Header -->
                    <x-slot:header>
                        <div class="grid gap-3">
                            <p class="py-2 text-xl font-medium dark:text-white">
                                @lang('admin::app.sales.orders.index.search-customer.title')
                            </p>

                            <div class="relative w-full">
                                <input
                                    type="text"
                                    class="block w-full rounded-lg border bg-white py-1.5 leading-6 text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 ltr:pl-3 ltr:pr-10 rtl:pl-10 rtl:pr-3"
                                    placeholder="@lang('admin::app.sales.orders.index.search-customer.search-by')"
                                    v-model.lazy="searchTerm"
                                    v-debounce="500"
                                />

                                <template v-if="isSearching">
                                    <img
                                        class="absolute top-2.5 h-5 w-5 animate-spin ltr:right-3 rtl:left-3"
                                        src="{{ bagisto_asset('images/spinner.svg') }}"
                                    />
                                </template>

                                <template v-else>
                                    <span class="icon-search pointer-events-none absolute top-1.5 flex items-center text-2xl ltr:right-3 rtl:left-3"></span>
                                </template>
                            </div>
                        </div>
                    </x-slot>

                    <!-- Drawer Content -->
                    <x-slot:content class="!p-0">
                        <div
                            class="grid max-h-[400px] overflow-y-auto"
                            v-if="searchedCustomers.length"
                        >
                            <div
                                class="grid cursor-pointer place-content-start gap-1.5 border-b border-slate-300 p-4 last:border-b-0 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-gray-950"
                                v-for="customer in searchedCustomers"
                                @click="createCart(customer)"
                            >
                                <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                                    @{{ customer.first_name + ' ' + customer.last_name }}
                                </p>

                                <p class="text-gray-500">
                                    @{{ customer.email }}
                                </p>
                            </div>
                        </div>

                        <!-- For Empty Variations -->
                        <div
                            class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                            v-else
                        >
                            <!-- Placeholder Image -->
                            <img
                                src="{{ bagisto_asset('images/empty-placeholders/customers.svg') }}"
                                class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                            />

                            <!-- Add Variants Information -->
                            <div class="flex flex-col items-center gap-1.5">
                                <p class="text-base font-semibold text-gray-400">
                                    @lang('admin::app.sales.orders.index.search-customer.empty-title')
                                </p>

                                <p class="text-gray-400">
                                    @lang('admin::app.sales.orders.index.search-customer.empty-info')
                                </p>

                                <button
                                    class="secondary-button"
                                    @click="$refs.searchCustomerDrawer.close(); $refs.createCustomerComponent.openModal()"
                                >
                                    @lang('admin::app.sales.orders.index.search-customer.create-btn')
                                </button>
                            </div>
                        </div>
                    </x-slot>
                </x-admin::drawer>

                <v-create-customer-form
                    ref="createCustomerComponent"
                    @customer-created="createCart"
                ></v-create-customer-form>
            </div>
        </script>

        <script type="module">
            app.component('v-customer-search', {
                template: '#v-customer-search-template',

                data() {
                    return {
                        searchTerm: '',

                        searchedCustomers: [],

                        isSearching: false,
                    }
                },

                watch: {
                    searchTerm: function(newVal, oldVal) {
                        this.search();
                    }
                },

                methods: {
                    openDrawer() {
                        this.$refs.searchCustomerDrawer.open();
                    },

                    search() {
                        if (this.searchTerm.length <= 1) {
                            this.searchedCustomers = [];

                            return;
                        }

                        this.isSearching = true;

                        let self = this;

                        this.$axios.get("{{ route('admin.customers.customers.search') }}", {
                                params: {
                                    query: this.searchTerm,
                                }
                            })
                            .then(function(response) {
                                self.isSearching = false;

                                self.searchedCustomers = response.data.data;
                            })
                            .catch(function (error) {
                            });
                    },

                    createCart(customer) {
                        this.$axios.post("{{ route('admin.sales.cart.store') }}", {customer_id: customer.id})
                            .then(function(response) {
                                window.location.href = response.data.redirect_url;
                            })
                            .catch(function (error) {
                                this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                            });
                    },
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
