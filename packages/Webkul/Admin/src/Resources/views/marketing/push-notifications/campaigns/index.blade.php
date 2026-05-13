<x-admin::layouts>
    <x-slot:title>
        Пуш-рассылки
    </x-slot>

    <v-push-campaigns>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); box-shadow: 0 4px 15px rgba(99,102,241,0.3); min-width:44px;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">Пуш-рассылки</p>
                    <p class="text-xs text-gray-400">Управление кампаниями пуш-уведомлений</p>
                </div>
            </div>
            <div class="flex items-center gap-x-2.5">
                <div class="primary-button">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Создать рассылку
                </div>
            </div>
        </div>
        <x-admin::shimmer.datagrid />
    </v-push-campaigns>

    @pushOnce('scripts')
    <script type="text/x-template" id="v-push-campaigns-template">
        <div>
            <!-- Header -->
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap mb-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); box-shadow: 0 4px 15px rgba(99,102,241,0.3); min-width:44px;">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800 dark:text-white">Пуш-рассылки</p>
                        <p class="text-xs text-gray-400">Управление кампаниями пуш-уведомлений</p>
                    </div>
                </div>
                <div class="flex items-center gap-x-2.5">
                    <button @click="openDrawer('create')" class="primary-button flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                        Создать рассылку
                    </button>
                </div>
            </div>

            <!-- DataGrid -->
            <x-admin::datagrid
                src="{{ route('admin.marketing.push_notifications.campaigns.index') }}"
                ref="datagrid"
            >
                <template #body="{ isLoading, available, applied, selectAll, sort, performAction }">
                    <template v-if="isLoading">
                        <x-admin::shimmer.datagrid.table.body />
                    </template>
                    <template v-else>
                        <div
                            v-for="record in available.records"
                            class="row grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950"
                            :style="`grid-template-columns: repeat(${gridsCount}, minmax(0, 1fr))`"
                        >
                            <p class="text-gray-500">#@{{ record.id }}</p>
                            <div>
                                <p class="font-semibold text-gray-800 dark:text-white">@{{ record.name }}</p>
                            </div>
                            <div v-html="record.status"></div>
                            <p>@{{ record.total_recipients.toLocaleString() }}</p>
                            <p>@{{ record.delivered_count.toLocaleString() }}</p>
                            <p>@{{ record.opened_count.toLocaleString() }}</p>
                            <p class="font-semibold" :style="{ color: record.conversion_rate > 0 ? '#16a34a' : '#6b7280' }">
                                @{{ record.conversion_rate }}
                            </p>
                            <p class="text-sm text-gray-400">@{{ record.created_at }}</p>
                            <div class="flex justify-end gap-1 items-center">
                                <a
                                    :href="`{{ url('admin/marketing/push-notifications/campaigns/show') }}/${record.id}`"
                                    title="Открыть кампанию"
                                    class="rounded-md px-2 py-1 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-300"
                                >
                                    Открыть
                                </a>
                                <a
                                    v-if="record.actions.find(a => a.index === 'view')"
                                    :href="record.actions.find(a => a.index === 'view').url"
                                    title="Статистика"
                                    class="cursor-pointer rounded-md p-1.5 text-xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-eye"
                                ></a>
                                <a
                                    v-if="record.actions.find(a => a.index === 'send') && ['draft','failed'].includes(record.status_raw ?? record.status)"
                                    @click.prevent="sendCampaign(record)"
                                    title="Отправить"
                                    class="cursor-pointer rounded-md p-1.5 text-xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-send"
                                    style="cursor:pointer;"
                                ></a>
                                <a
                                    @click.prevent="openDrawer('edit', record)"
                                    title="Редактировать"
                                    class="cursor-pointer rounded-md p-1.5 text-xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-edit"
                                ></a>
                                <a
                                    @click.prevent="deleteCampaign(record)"
                                    title="Удалить"
                                    class="cursor-pointer rounded-md p-1.5 text-xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 icon-delete"
                                ></a>
                            </div>
                        </div>
                    </template>
                </template>
            </x-admin::datagrid>

            <!-- Drawer (slide-in panel) -->
            <teleport to="body">
                <div :style="{
                    position: 'fixed', inset: 0, zIndex: 9998,
                    visibility: isDrawerOpen ? 'visible' : 'hidden',
                    pointerEvents: isDrawerOpen ? 'auto' : 'none',
                }">
                    <div @click="closeDrawer" :style="{
                        position: 'absolute', inset: 0, background: 'rgba(0,0,0,0.3)',
                        backdropFilter: 'blur(2px)', transition: 'opacity 0.3s ease',
                        opacity: drawerVisible ? 1 : 0,
                    }"></div>

                    <div style="position:absolute;top:0;right:0;bottom:0;width:calc(100vw - 270px);max-width:780px;background:#f8f9fb;box-shadow:-8px 0 40px rgba(0,0,0,0.15);transition:transform 0.35s cubic-bezier(0.16,1,0.3,1);overflow:hidden;display:flex;flex-direction:column;"
                        :style="{ transform: drawerVisible ? 'translateX(0)' : 'translateX(100%)' }"
                    >
                        <!-- Drawer header -->
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;background:white;border-bottom:1px solid #e5e7eb;flex-shrink:0;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <button @click="closeDrawer" style="display:flex;align-items:center;justify-content:center;width:36px;height:36px;min-width:36px;border-radius:10px;background:#f3f4f6;cursor:pointer;border:none;" onmouseenter="this.style.background='#e5e7eb'" onmouseleave="this.style.background='#f3f4f6'">
                                    <svg style="width:18px;height:18px;color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                                <span style="font-size:14px;font-weight:600;color:#111827;">
                                    @{{ drawerMode === 'create' ? 'Новая рассылка' : 'Редактировать рассылку' }}
                                </span>
                            </div>
                        </div>

                        <!-- Loading shimmer -->
                        <div v-if="isDrawerLoading" style="position:absolute;top:53px;left:0;right:0;bottom:0;background:#f8f9fb;z-index:5;padding:20px;">
                            <div v-for="i in 5" :key="i" style="margin-bottom:16px;">
                                <div style="height:13px;width:25%;background:#e5e7eb;border-radius:4px;margin-bottom:6px;animation:shimmer 1.5s infinite;"></div>
                                <div style="height:38px;background:#e5e7eb;border-radius:8px;animation:shimmer 1.5s infinite;"></div>
                            </div>
                        </div>

                        <iframe
                            v-if="iframeSrc"
                            :src="iframeSrc"
                            ref="panelIframe"
                            @load="onIframeLoad"
                            style="width:100%;border:none;flex:1;margin:0;padding:0;display:block;"
                        ></iframe>
                    </div>
                </div>
            </teleport>
        </div>
    </script>

    <script type="module">
        app.component('v-push-campaigns', {
            template: '#v-push-campaigns-template',
            data() {
                return {
                    isDrawerOpen: false,
                    drawerVisible: false,
                    isDrawerLoading: false,
                    iframeSrc: '',
                    currentRecord: null,
                    currentRecordId: null,
                    drawerMode: 'create',
                };
            },
            computed: {
                gridsCount() {
                    if (!this.$refs.datagrid) return 9;
                    let count = this.$refs.datagrid.available.columns.length;
                    if (this.$refs.datagrid.available.actions.length) ++count;
                    return count;
                },
            },
            mounted() {
                window.addEventListener('message', this.handleMessage);
                window.addEventListener('keydown', this.handleKeyDown);
            },
            beforeUnmount() {
                window.removeEventListener('message', this.handleMessage);
                window.removeEventListener('keydown', this.handleKeyDown);
            },
            methods: {
                openDrawer(mode, record = null) {
                    this.drawerMode = mode;
                    this.currentRecord = record;
                    let targetUrl, targetId;
                    if (mode === 'create') {
                        targetUrl = '{{ route('admin.marketing.push_notifications.campaigns.create_panel') }}';
                        targetId = 'create';
                    } else {
                        targetUrl = '{{ url('admin/marketing/push-notifications/campaigns/edit-panel') }}/' + record.id;
                        targetId = record.id;
                    }
                    if (this.currentRecordId !== targetId) {
                        this.currentRecordId = targetId;
                        this.isDrawerLoading = true;
                        this.iframeSrc = targetUrl;
                    }
                    this.isDrawerOpen = true;
                    this.$nextTick(() => requestAnimationFrame(() => { this.drawerVisible = true; }));
                },
                closeDrawer() {
                    this.drawerVisible = false;
                    setTimeout(() => { this.isDrawerOpen = false; }, 350);
                },
                onIframeLoad() { this.isDrawerLoading = false; },
                handleMessage(event) {
                    if (!event.data || typeof event.data !== 'object') return;
                    if (event.data.type === 'panel-saved') {
                        this.closeDrawer();
                        this.iframeSrc = '';
                        this.currentRecordId = null;
                        this.$emitter.emit('add-flash', { type: 'success', message: event.data.message });
                        this.$refs.datagrid.get();
                    } else if (event.data.type === 'panel-closed') {
                        this.closeDrawer();
                    }
                },
                handleKeyDown(e) {
                    if (e.key === 'Escape' && this.isDrawerOpen) this.closeDrawer();
                },
                sendCampaign(record) {
                    if (!confirm(`Отправить рассылку "${record.name}"?`)) return;
                    fetch('{{ url('admin/marketing/push-notifications/campaigns/send') }}/' + record.id, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                    })
                    .then(async r => {
                        const data = await r.json().catch(() => ({}));

                        if (!r.ok) {
                            throw new Error(data.message || 'Ошибка запуска рассылки');
                        }

                        return data;
                    })
                    .then(data => {
                        this.$emitter.emit('add-flash', { type: 'success', message: data.message || 'Рассылка запущена' });
                        this.$refs.datagrid.get();
                    })
                    .catch((error) => {
                        this.$emitter.emit('add-flash', { type: 'error', message: error.message || 'Ошибка запуска рассылки' });
                    });
                },
                deleteCampaign(record) {
                    if (!confirm(`Удалить рассылку "${record.name}"?`)) return;

                    fetch('{{ url('admin/marketing/push-notifications/campaigns/edit') }}/' + record.id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                    })
                    .then(async r => {
                        const data = await r.json().catch(() => ({}));

                        if (!r.ok) {
                            throw new Error(data.message || 'Ошибка удаления рассылки');
                        }

                        this.$emitter.emit('add-flash', { type: 'success', message: data.message || 'Рассылка удалена' });
                        this.$refs.datagrid.get();
                    })
                    .catch((error) => {
                        this.$emitter.emit('add-flash', { type: 'error', message: error.message || 'Ошибка удаления рассылки' });
                    });
                },
            },
        });
    </script>

    <style>
        @keyframes shimmer {
            0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; }
        }
    </style>
    @endPushOnce
</x-admin::layouts>
