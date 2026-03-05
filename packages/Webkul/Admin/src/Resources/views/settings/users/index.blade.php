<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.users.index.title')
    </x-slot>

    <v-users>
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); box-shadow: 0 4px 15px rgba(124,58,237,0.3); min-width:44px;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.users.index.title')
                    </p>
                    <p class="text-xs text-gray-400">Администраторы системы</p>
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                @if (bouncer()->hasPermission('settings.users.users.create'))
                    <button type="button" class="primary-button">
                        @lang('admin::app.settings.users.index.create.title')
                    </button>
                @endif
            </div>
        </div>

        <x-admin::shimmer.datagrid />
    </v-users>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-users-template">
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); box-shadow: 0 4px 15px rgba(124,58,237,0.3); min-width:44px;">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.users.index.title')
                        </p>
                        <p class="text-xs text-gray-400">Администраторы системы</p>
                    </div>
                </div>

                <div class="flex items-center gap-x-2.5">
                    @if (bouncer()->hasPermission('settings.users.users.create'))
                        <button type="button" class="primary-button" @click="openDrawer('create')">
                            @lang('admin::app.settings.users.index.create.title')
                        </button>
                    @endif
                </div>
            </div>

            <x-admin::datagrid
                :src="route('admin.settings.users.index')"
                ref="datagrid"
            >
                @php
                    $hasPermission = bouncer()->hasPermission('settings.users.users.edit') || bouncer()->hasPermission('settings.users.users.delete');
                @endphp

                <template #header="{
                    isLoading,
                    available,
                    applied,
                    selectAll,
                    sort,
                    performAction
                }">
                    <div class="row grid grid-cols-{{ $hasPermission ? '6' : '5' }} grid-rows-1 gap-2.5 items-center px-4 py-2.5 border-b dark:border-gray-800 text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900 font-semibold">
                        <div
                            class="flex cursor-pointer gap-2.5"
                            v-for="(columnGroup, index) in ['user_id', 'user_name', 'status', 'email', 'role_name']"
                        >
                            <p class="text-gray-600 dark:text-gray-300">
                                <span class="[&>*]:after:content-['_/_']">
                                    <span
                                        class="after:content-['/'] last:after:content-['']"
                                        :class="{
                                            'text-gray-800 dark:text-white font-medium': applied.sort.column == columnGroup,
                                            'cursor-pointer hover:text-gray-800 dark:hover:text-white': available.columns.find(columnTemp => columnTemp.index === columnGroup)?.sortable,
                                        }"
                                        @click="
                                            available.columns.find(columnTemp => columnTemp.index === columnGroup)?.sortable ? sort(available.columns.find(columnTemp => columnTemp.index === columnGroup)): {}
                                        "
                                    >
                                        @{{ available.columns.find(columnTemp => columnTemp.index === columnGroup)?.label }}
                                    </span>
                                </span>

                                <i
                                    class="align-text-bottom text-base text-gray-800 dark:text-white ltr:ml-1.5 rtl:mr-1.5"
                                    :class="[applied.sort.order === 'asc' ? 'icon-down-stat': 'icon-up-stat']"
                                    v-if="columnGroup.includes(applied.sort.column)"
                                ></i>
                            </p>
                        </div>

                        @if ($hasPermission)
                            <p class="flex justify-end gap-2.5">
                                @lang('admin::app.components.datagrid.table.actions')
                            </p>
                        @endif
                    </div>
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
                        <x-admin::shimmer.datagrid.table.body />
                    </template>

                    <template v-else>
                        <div
                            v-for="record in available.records"
                            class="row grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950"
                            :style="'grid-template-columns: repeat(' + (record.actions.length ? 6 : 5) + ', minmax(150px, 1fr));'"
                            style="cursor: pointer;"
                            @click="openDrawer('edit', record)"
                            @mouseenter="preloadRecord(record)"
                        >
                            <p>@{{ record.user_id }}</p>

                            <p>
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="border-3 mr-2 inline-block h-9 w-9 overflow-hidden rounded-full border-gray-800 text-center align-middle"
                                        v-if="record.user_img"
                                    >
                                        <img class="h-9 w-9" :src="record.user_img" alt="record.user_name" />
                                    </div>

                                    <div class="profile-info-icon" v-else>
                                        <button class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-blue-400 text-sm font-semibold leading-6 text-white transition-all hover:bg-blue-500 focus:bg-blue-500">
                                            @{{ record.user_name[0].toUpperCase() }}
                                        </button>
                                    </div>

                                    <div class="text-sm break-all">
                                        @{{ record.user_name }}
                                    </div>
                                </div>
                            </p>

                            <p>@{{ record.status }}</p>
                            <p class="break-words">@{{ record.email }}</p>
                            <p>@{{ record.role_name }}</p>

                            <div class="flex justify-end">
                                <a @click.stop="openDrawer('edit', record)">
                                    <span
                                        :class="record.actions.find(action => action.index === 'edit')?.icon"
                                        class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                    ></span>
                                </a>

                                <a @click.stop="performAction(record.actions.find(action => action.index === 'delete'))">
                                    <span
                                        :class="record.actions.find(action => action.index === 'delete')?.icon"
                                        class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                    ></span>
                                </a>
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
                                        @{{ currentRecord.user_name }}
                                        <span style="font-size:12px; color:#6b7280; margin-left:6px; font-weight:400;">@{{ currentRecord.email }}</span>
                                    </span>
                                    <span v-else style="font-size:14px; font-weight:600; color:#111827;">
                                        @lang('admin::app.settings.users.index.create.title')
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
            app.component('v-users', {
                template: '#v-users-template',
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
                            targetUrl = window.location.origin + '/admin/settings/users/create-panel';
                            targetId = 'create';
                        } else {
                            targetUrl = window.location.origin + '/admin/settings/users/edit-panel/' + record.user_id;
                            targetId = record.user_id;
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
                        if (this.currentRecordId === record.user_id) return;
                        clearTimeout(this.hoverTimer);
                        this.hoverTimer = setTimeout(() => {
                            this.currentRecordId = record.user_id;
                            this.currentRecord = record;
                            this.drawerMode = 'edit';
                            this.isDrawerLoading = true;
                            this.iframeSrc = window.location.origin + '/admin/settings/users/edit-panel/' + record.user_id;
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
