<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.sales.orders.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="py-3 text-xl font-bold text-gray-800 dark:text-white">
            @lang('admin::app.sales.orders.index.title')
        </p>

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
                <div class="flex items-center bg-gray-50/80 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800 px-5 py-3.5 rounded-t-xl gap-6">
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
                    <div class="flex-1 grid grid-cols-4 gap-4">
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
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div
                        class="group flex items-start gap-4 md:gap-6 px-5 py-4 transition-all duration-300 hover:bg-gradient-to-r hover:from-violet-50/50 hover:to-transparent dark:hover:from-violet-900/10 relative"
                        v-for="(record, index) in available.records"
                        :key="record.id"
                    >
                        <!-- Checkbox -->
                        <div class="flex items-center justify-center w-10 flex-shrink-0 pt-3">
                            <input 
                                type="checkbox" 
                                :value="record.id"
                                :data-order-id="record.id"
                                class="order-checkbox w-5 h-5 rounded border-2 border-gray-300 text-violet-600 focus:ring-violet-500 cursor-pointer"
                                onclick="handleOrderCheckbox(this)"
                            />
                        </div>
                        
                        <!-- Hover indicator -->
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-violet-500 rounded-r opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        
                        <!-- Main Content Grid -->
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Order Id, Created, Status Section -->
                            <a :href="`{{ route('admin.sales.orders.view', '') }}/${record.id}`" class="flex items-start gap-4 no-underline">
                                <!-- Order Number Badge -->
                                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center shadow-lg shadow-violet-500/20 group-hover:shadow-violet-500/40 group-hover:scale-105 transition-all duration-300">
                                    <span class="text-white font-bold text-sm">#@{{ index + 1 }}</span>
                                </div>
                                
                                <div class="flex flex-col gap-1.5 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-base font-bold text-gray-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors duration-200">
                                            #@{{ record.increment_id }}
                                        </p>
                                        <span v-html="record.status"></span>
                                    </div>

                                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>@{{ record.created_at }}</span>
                                    </div>
                                </div>
                            </a>

                            <!-- Total Amount, Pay Via -->
                            <div class="flex flex-col justify-center gap-1.5">
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    @{{ $admin.formatPrice(record.base_grand_total) }}
                                </p>

                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center gap-1.5 px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded-lg">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        <span>@{{ record.method }}</span>
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

                            <!-- Images Section -->
                            <div class="flex items-center justify-between gap-3">
                                <div
                                    class="flex items-center"
                                    v-html="record.items"
                                >
                                </div>

                                <a 
                                    :href="`{{ route('admin.sales.orders.view', '') }}/${record.id}`"
                                    class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center group-hover:bg-violet-100 dark:group-hover:bg-violet-900/30 transition-colors duration-200"
                                >
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>

    @include('admin::customers.customers.index.create')

    @pushOnce('scripts')
        <!-- Global functions for order selection -->
        <script>
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
                            <option value="pending">Новый</option>
                            <option value="pending_payment">Ожидает оплаты</option>
                            <option value="processing">В обработке</option>
                            <option value="preparing">Готовится</option>
                            <option value="ready">Готов</option>
                            <option value="completed">Завершен</option>
                            <option value="canceled">Отменен</option>
                            <option value="closed">Закрыт</option>
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
                        isLoading: false
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

        <!-- Order Status Filter Component -->
        <script type="text/x-template" id="v-order-status-filter-template">
            <div class="mt-4 mb-6">
                <!-- Status Cards Grid - Larkon Style -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-8 gap-3">
                    <div
                        v-for="status in statusFilters"
                        :key="status.key"
                        @click="filterByStatus(status.key)"
                        class="relative flex flex-col p-4 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 cursor-pointer transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group overflow-hidden"
                        :class="{
                            'ring-2 ring-violet-500 border-violet-500 shadow-lg shadow-violet-500/20': activeStatus === status.key,
                            'hover:border-violet-200 dark:hover:border-violet-800': activeStatus !== status.key
                        }"
                    >
                        <!-- Background decoration -->
                        <div class="absolute top-0 right-0 w-20 h-20 rounded-full opacity-10 -mr-6 -mt-6 transition-transform duration-300 group-hover:scale-150"
                             :class="getStatusBgClass(status.key)"></div>
                        
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl font-bold text-gray-800 dark:text-white">@{{ status.count }}</span>
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 group-hover:scale-110"
                                 :class="getStatusIconBgClass(status.key)">
                                <i :class="[status.icon, getStatusIconColorClass(status.key)]" class="text-lg"></i>
                            </div>
                        </div>
                        
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">@{{ status.label }}</span>
                        
                        <!-- Progress indicator -->
                        <div class="mt-2 h-1 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500"
                                 :class="getStatusBarClass(status.key)"
                                 :style="{ width: getProgressWidth(status.key) + '%' }"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Clear Filter Button -->
                <div v-if="activeStatus" class="mt-4 flex justify-end">
                    <button
                        @click="clearFilter"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-violet-600 hover:text-violet-800 dark:text-violet-400 dark:hover:text-violet-300 bg-violet-50 dark:bg-violet-900/20 rounded-xl transition-all duration-200 hover:bg-violet-100 dark:hover:bg-violet-900/30"
                    >
                        <i class="icon-cancel text-lg"></i>
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
                        maxCount: 0
                    }
                },

                mounted() {
                    this.maxCount = Math.max(...this.statusFilters.map(s => s.count), 1);
                    
                    // Check if we need to apply a filter from notification panel
                    const savedFilter = localStorage.getItem('orders_filter_status');
                    if (savedFilter) {
                        localStorage.removeItem('orders_filter_status');
                        // Apply filter after a short delay to ensure datagrid is ready
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
                            columns: [
                                {
                                    index: 'status',
                                    value: [status]
                                }
                            ]
                        });
                    },

                    clearFilter() {
                        this.activeStatus = null;
                        
                        this.$emitter.emit('datagrid:filter', {
                            columns: [
                                {
                                    index: 'status',
                                    value: []
                                }
                            ]
                        });
                    },
                    
                    getProgressWidth(status) {
                        // Fixed progress values based on order workflow stage
                        const progressMap = {
                            'pending': 5,
                            'pending_payment': 5,
                            'processing': 15,
                            'preparing': 40,
                            'ready': 70,
                            'completed': 100,
                            'canceled': 100,
                            'closed': 100
                        };
                        return progressMap[status] || 5;
                    },
                    
                    getStatusBgClass(status) {
                        const classes = {
                            'pending': 'bg-amber-500',
                            'pending_payment': 'bg-amber-500',
                            'processing': 'bg-blue-500',
                            'completed': 'bg-emerald-500',
                            'canceled': 'bg-rose-500',
                            'closed': 'bg-gray-500',
                            'fraud': 'bg-red-500'
                        };
                        return classes[status] || 'bg-violet-500';
                    },
                    
                    getStatusIconBgClass(status) {
                        const classes = {
                            'pending': 'bg-amber-50 dark:bg-amber-900/30',
                            'pending_payment': 'bg-amber-50 dark:bg-amber-900/30',
                            'processing': 'bg-blue-50 dark:bg-blue-900/30',
                            'completed': 'bg-emerald-50 dark:bg-emerald-900/30',
                            'canceled': 'bg-rose-50 dark:bg-rose-900/30',
                            'closed': 'bg-gray-100 dark:bg-gray-800',
                            'fraud': 'bg-red-50 dark:bg-red-900/30'
                        };
                        return classes[status] || 'bg-violet-50 dark:bg-violet-900/30';
                    },
                    
                    getStatusIconColorClass(status) {
                        const classes = {
                            'pending': 'text-amber-500',
                            'pending_payment': 'text-amber-500',
                            'processing': 'text-blue-500',
                            'completed': 'text-emerald-500',
                            'canceled': 'text-rose-500',
                            'closed': 'text-gray-500',
                            'fraud': 'text-red-500'
                        };
                        return classes[status] || 'text-violet-500';
                    },
                    
                    getStatusBarClass(status) {
                        const classes = {
                            'pending': 'bg-amber-500',
                            'pending_payment': 'bg-amber-500',
                            'processing': 'bg-blue-500',
                            'completed': 'bg-emerald-500',
                            'canceled': 'bg-rose-500',
                            'closed': 'bg-gray-500',
                            'fraud': 'bg-red-500'
                        };
                        return classes[status] || 'bg-violet-500';
                    }
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
