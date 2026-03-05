<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.roles.index.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.settings.roles.list.before') !!}

    {{-- Hidden: register tree view JS components --}}
    <div class="hidden">
        <x-admin::tree.view
            input-type="checkbox"
            :items="json_encode([])"
        />
    </div>

    <v-roles>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); box-shadow: 0 4px 15px rgba(244,63,94,0.3); min-width:44px;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.roles.index.title')
                    </p>
                    <p class="text-xs text-gray-400">Роли и права доступа</p>
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                @if (bouncer()->hasPermission('settings.roles.create'))
                    <button type="button" class="primary-button">
                        @lang('admin::app.settings.roles.index.create-btn')
                    </button>
                @endif
            </div>
        </div>

        <x-admin::shimmer.datagrid />
    </v-roles>

    {!! view_render_event('bagisto.admin.settings.roles.list.after') !!}

    @pushOnce('scripts')
        <script type="text/x-template" id="v-roles-template">
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); box-shadow: 0 4px 15px rgba(244,63,94,0.3); min-width:44px;">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.roles.index.title')
                        </p>
                        <p class="text-xs text-gray-400">Роли и права доступа</p>
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    @if (bouncer()->hasPermission('settings.roles.create'))
                        <button type="button" class="primary-button" @click="openDrawer('create')">
                            @lang('admin::app.settings.roles.index.create-btn')
                        </button>
                    @endif
                </div>
            </div>

            <x-admin::datagrid
                :src="route('admin.settings.roles.index')"
                ref="datagrid"
            >
                <template #body="{
                    isLoading,
                    available,
                    applied,
                    selectAll,
                    sort,
                    performAction
                }">
                    <template v-if="isLoading">
                        <x-admin::shimmer.datagrid.table.body />
                    </template>

                    <template v-else>
                        <div
                            v-for="record in available.records"
                            class="row grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950"
                            :style="`grid-template-columns: repeat(${gridsCount}, minmax(0, 1fr))`"
                            style="cursor: pointer;"
                            @click="openDrawer('edit', record)"
                            @mouseenter="preloadRecord(record)"
                        >
                            <p>@{{ record.id }}</p>
                            <p>@{{ record.name }}</p>
                            <p>@{{ record.permission_type }}</p>

                            <div class="flex justify-end">
                                @if (bouncer()->hasPermission('settings.roles.edit'))
                                    <a @click.stop="openDrawer('edit', record)">
                                        <span
                                            :class="record.actions.find(action => action.index === 'edit')?.icon"
                                            class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                        ></span>
                                    </a>
                                @endif

                                @if (bouncer()->hasPermission('settings.roles.delete'))
                                    <a @click.stop="performAction(record.actions.find(action => action.index === 'delete'))">
                                        <span
                                            :class="record.actions.find(action => action.index === 'delete')?.icon"
                                            class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                        ></span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </template>
                </template>
            </x-admin::datagrid>

            <!-- Drawer -->
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

                    <div
                        style="position:absolute; top:0; right:0; bottom:0; width:calc(100vw - 270px); max-width:calc(100vw - 270px); background:#f8f9fb; box-shadow:-8px 0 40px rgba(0,0,0,0.15); transition:transform 0.35s cubic-bezier(0.16,1,0.3,1); overflow:hidden; display:flex; flex-direction:column;"
                        :style="{ transform: drawerVisible ? 'translateX(0)' : 'translateX(100%)' }"
                    >
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 20px; background:white; border-bottom:1px solid #e5e7eb; flex-shrink:0;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <button @click="closeDrawer" style="display:flex; align-items:center; justify-content:center; width:36px; height:36px; min-width:36px; border-radius:10px; background:#f3f4f6; cursor:pointer; border:none; transition:all 0.2s;" onmouseenter="this.style.background='#e5e7eb'" onmouseleave="this.style.background='#f3f4f6'">
                                    <svg style="width:18px; height:18px; color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                                <div>
                                    <span v-if="drawerMode === 'edit' && currentRecord" style="font-size:14px; font-weight:600; color:#111827;">
                                        @{{ currentRecord.name }}
                                        <span style="font-size:12px; color:#6b7280; margin-left:6px; font-weight:400;">@{{ currentRecord.permission_type }}</span>
                                    </span>
                                    <span v-else style="font-size:14px; font-weight:600; color:#111827;">
                                        @lang('admin::app.settings.roles.create.title')
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-if="isDrawerLoading && isDrawerOpen" style="position:absolute; top:52px; left:0; right:0; bottom:0; display:flex; align-items:center; justify-content:center; background:rgba(248,249,251,0.9); z-index:5;">
                            <div style="display:flex; flex-direction:column; align-items:center; gap:12px;">
                                <svg style="width:36px; height:36px; color:#6366f1; animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="32" stroke-dashoffset="10" /></svg>
                                <span style="font-size:13px; color:#6b7280;">Загрузка...</span>
                            </div>
                        </div>

                        <iframe v-if="iframeSrc" :src="iframeSrc" ref="panelIframe" @load="onIframeLoad" style="width:100%; border:none; flex:1; margin:0; padding:0; display:block;" allowfullscreen></iframe>
                    </div>
                </div>
            </teleport>
        </script>

        <script type="module">
            app.component('v-roles', {
                template: '#v-roles-template',
                data() {
                    return {
                        isDrawerOpen: false,
                        drawerVisible: false,
                        isDrawerLoading: false,
                        iframeSrc: '',
                        currentRecord: null,
                        currentRecordId: null,
                        drawerMode: 'edit',
                        hoverTimer: null,
                    };
                },
                computed: {
                    gridsCount() {
                        let count = this.$refs.datagrid.available.columns.length;
                        if (this.$refs.datagrid.available.actions.length) ++count;
                        if (this.$refs.datagrid.available.massActions.length) ++count;
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
                    clearTimeout(this.hoverTimer);
                },
                methods: {
                    openDrawer(mode, record = null) {
                        clearTimeout(this.hoverTimer);
                        this.drawerMode = mode;
                        this.currentRecord = record;
                        let targetUrl, targetId;
                        if (mode === 'create') {
                            targetUrl = window.location.origin + '/admin/settings/roles/create-panel';
                            targetId = 'create';
                        } else {
                            targetUrl = window.location.origin + '/admin/settings/roles/edit-panel/' + record.id;
                            targetId = record.id;
                        }
                        const alreadyLoaded = (this.currentRecordId === targetId && !this.isDrawerLoading);
                        const alreadyLoading = (this.currentRecordId === targetId && this.isDrawerLoading);
                        if (!alreadyLoaded && !alreadyLoading) {
                            this.currentRecordId = targetId;
                            this.isDrawerLoading = true;
                            this.iframeSrc = targetUrl;
                        }
                        this.isDrawerOpen = true;
                        this.$nextTick(() => { requestAnimationFrame(() => { this.drawerVisible = true; }); });
                        this.toggleSidebarBlur(true);
                        document.body.style.overflow = 'hidden';
                    },
                    closeDrawer() {
                        this.drawerVisible = false;
                        setTimeout(() => {
                            this.isDrawerOpen = false;
                            this.toggleSidebarBlur(false);
                            document.body.style.overflow = '';
                        }, 350);
                    },
                    preloadRecord(record) {
                        if (this.isDrawerOpen) return;
                        if (this.currentRecordId === record.id) return;
                        clearTimeout(this.hoverTimer);
                        this.hoverTimer = setTimeout(() => {
                            this.currentRecordId = record.id;
                            this.currentRecord = record;
                            this.drawerMode = 'edit';
                            this.isDrawerLoading = true;
                            this.iframeSrc = window.location.origin + '/admin/settings/roles/edit-panel/' + record.id;
                        }, 150);
                    },
                    onIframeLoad() { this.isDrawerLoading = false; },
                    handleMessage(event) {
                        if (!event.data || typeof event.data !== 'object') return;
                        switch (event.data.type) {
                            case 'panel-saved':
                                this.closeDrawer();
                                this.iframeSrc = '';
                                this.currentRecordId = null;
                                this.$emitter.emit('add-flash', { type: 'success', message: event.data.message });
                                this.$refs.datagrid.get();
                                break;
                            case 'panel-closed':
                                this.closeDrawer();
                                break;
                        }
                    },
                    handleKeyDown(e) { if (e.key === 'Escape' && this.isDrawerOpen) this.closeDrawer(); },
                    toggleSidebarBlur(blur) {
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
        </style>
    @endPushOnce
</x-admin::layouts>
