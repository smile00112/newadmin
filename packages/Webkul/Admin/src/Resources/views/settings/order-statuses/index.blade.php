<x-admin::layouts>
    <x-slot:title>
        Статусы заказов
    </x-slot>

    @pushOnce('styles')
        <style>
            .os-drag-handle { cursor: grab; }
            .os-drag-handle:active { cursor: grabbing; }
            .os-sortable-ghost { opacity: 0.4; background: #f5f3ff !important; }
            .os-pipeline-arrow {
                display: inline-flex; align-items: center; justify-content: center;
                width: 20px; color: #c4b5fd; font-size: 16px; flex-shrink: 0;
            }
        </style>
    @endPushOnce

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settings.locales.index') }}"
               class="flex items-center justify-center w-10 h-10 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
               title="Назад">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="flex items-center justify-center w-11 h-11 rounded-xl"
                 style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); box-shadow: 0 4px 15px rgba(124,58,237,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">Статусы заказов</p>
                <p class="text-xs text-gray-400">Единые настройки для всех приложений: админка, менеджер, мобильное приложение, сайт</p>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <v-order-status-settings
            initial-statuses='@json($statuses)'
            initial-settings='@json($workflowSettings)'
            system-shipping='@json($systemShippingMethods)'
            system-payments='@json($systemPaymentMethods)'
            save-url="{{ route('admin.settings.order_statuses.save') }}"
        ></v-order-status-settings>
    </div>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-order-status-settings-template">
            <div>
                <!-- Save Button (sticky) -->
                <div class="flex justify-end mb-5">
                    <button
                        @click="saveAll"
                        :disabled="isSaving"
                        class="flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white rounded-xl transition-all"
                        style="background: linear-gradient(135deg, #7c3aed, #6d28d9); box-shadow: 0 4px 15px rgba(124,58,237,0.3);"
                    >
                        <svg v-if="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        @{{ isSaving ? 'Сохраняю...' : 'Сохранить' }}
                    </button>
                </div>

                <!-- ============ SECTION 1: STATUSES ============ -->
                <div class="box-shadow rounded-2xl bg-white dark:bg-gray-900 overflow-hidden mb-5">
                    <div class="flex items-center gap-2.5 px-5 py-4" style="border-bottom: 1px solid #f3f4f6;">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg" style="background:#f1f0ff;">
                            <svg class="w-4 h-4" style="color:#7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-base font-bold text-gray-800 dark:text-white">Статусы</p>
                            <p class="text-xs text-gray-400">Перетаскивайте для изменения порядка. Статус с меткой «системный» нельзя удалить.</p>
                        </div>
                    </div>

                    <div class="p-4">
                        <div ref="statusList">
                            <div
                                v-for="(status, idx) in statuses"
                                :key="status.code"
                                class="flex items-center gap-3 p-3 mb-2 rounded-xl transition-colors"
                                style="background: #fafafa; border: 1px solid #f0f0f0;"
                                :data-idx="idx"
                            >
                                <!-- Drag handle -->
                                <div class="os-drag-handle flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                    </svg>
                                </div>

                                <!-- Code -->
                                <input
                                    v-model="status.code"
                                    :readonly="status.is_system"
                                    class="w-28 px-2.5 py-1.5 text-xs font-mono rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300"
                                    :class="{ 'bg-gray-100 cursor-not-allowed': status.is_system }"
                                    placeholder="code"
                                />

                                <!-- Name -->
                                <input
                                    v-model="status.name"
                                    class="flex-1 px-2.5 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 min-w-[120px]"
                                    placeholder="Название"
                                />

                                <!-- Icon select -->
                                <select
                                    v-model="status.icon"
                                    class="w-44 px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300"
                                >
                                    <option v-for="ic in iconOptions" :key="ic" :value="ic">@{{ ic }}</option>
                                </select>

                                <!-- System badge -->
                                <span
                                    v-if="status.is_system"
                                    class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full flex-shrink-0"
                                    style="background:#e0e7ff; color:#4338ca;"
                                >системный</span>

                                <!-- Delete button -->
                                <button
                                    v-if="!status.is_system"
                                    @click="removeStatus(idx)"
                                    class="flex items-center justify-center w-8 h-8 rounded-lg transition-colors flex-shrink-0"
                                    style="background:#ffe4e6;"
                                    title="Удалить"
                                    @mouseenter="$event.currentTarget.style.background='#fecdd3'"
                                    @mouseleave="$event.currentTarget.style.background='#ffe4e6'"
                                >
                                    <svg class="w-4 h-4" style="color:#be123c;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                <div v-else class="w-8 flex-shrink-0"></div>
                            </div>
                        </div>

                        <button
                            @click="addStatus"
                            class="flex items-center gap-2 mt-3 px-4 py-2 text-sm font-semibold rounded-xl transition-colors"
                            style="background:#f5f3ff; color:#7c3aed;"
                            @mouseenter="$event.currentTarget.style.background='#ede9fe'"
                            @mouseleave="$event.currentTarget.style.background='#f5f3ff'"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Добавить статус
                        </button>
                    </div>
                </div>

                <!-- ============ SECTION 2: NEW ORDER STATUS ============ -->
                <div class="box-shadow rounded-2xl bg-white dark:bg-gray-900 overflow-hidden mb-5">
                    <div class="flex items-center gap-2.5 px-5 py-4" style="border-bottom: 1px solid #f3f4f6;">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg" style="background:#fef3c7;">
                            <svg class="w-4 h-4" style="color:#b45309;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-base font-bold text-gray-800 dark:text-white">Статус «Новый заказ»</p>
                            <p class="text-xs text-gray-400">Какой статус считать новым заказом? При его появлении админка и менеджер проигрывают звук.</p>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex flex-wrap gap-2">
                            <label
                                v-for="s in statuses"
                                :key="'nos-' + s.code"
                                class="flex items-center gap-2 px-3 py-2 rounded-xl cursor-pointer transition-all text-sm"
                                :style="{
                                    background: newOrderStatus === s.code ? '#f5f3ff' : '#f9fafb',
                                    border: newOrderStatus === s.code ? '2px solid #7c3aed' : '2px solid transparent',
                                    fontWeight: newOrderStatus === s.code ? '700' : '500',
                                    color: newOrderStatus === s.code ? '#5b21b6' : '#6b7280',
                                }"
                            >
                                <input type="radio" :value="s.code" v-model="newOrderStatus" class="hidden"/>
                                <span
                                    class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                                    :style="{
                                        borderColor: newOrderStatus === s.code ? '#7c3aed' : '#d1d5db',
                                    }"
                                >
                                    <span
                                        v-if="newOrderStatus === s.code"
                                        class="w-2 h-2 rounded-full"
                                        style="background:#7c3aed;"
                                    ></span>
                                </span>
                                @{{ s.name }}
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ============ SECTION 3: PIPELINES ============ -->
                <div class="box-shadow rounded-2xl bg-white dark:bg-gray-900 overflow-hidden mb-5">
                    <div class="flex items-center gap-2.5 px-5 py-4" style="border-bottom: 1px solid #f3f4f6;">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg" style="background:#d1fae5;">
                            <svg class="w-4 h-4" style="color:#047857;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-base font-bold text-gray-800 dark:text-white">Этапы заказа (пайплайны)</p>
                            <p class="text-xs text-gray-400">Настройте последовательность статусов для каждой комбинации тип доставки × способ оплаты.</p>
                        </div>
                    </div>

                    <div class="p-5">
                        <div
                            v-for="(pipe, pIdx) in pipelines"
                            :key="'pipe-' + pIdx"
                            class="mb-4 p-4 rounded-2xl"
                            style="background: #fafafa; border: 1px solid #f0f0f0;"
                        >
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-200">
                                    @{{ getDeliveryName(pipe.delivery_type) }} + @{{ getPaymentName(pipe.payment_type) }}
                                </span>
                                <button
                                    @click="removePipeline(pIdx)"
                                    class="ml-auto flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                    style="background: #ffe4e6;"
                                    title="Удалить пайплайн"
                                    @mouseenter="$event.currentTarget.style.background='#fecdd3'"
                                    @mouseleave="$event.currentTarget.style.background='#ffe4e6'"
                                >
                                    <svg class="w-3.5 h-3.5" style="color:#be123c;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="flex items-center flex-wrap gap-1.5">
                                <template v-for="(stepCode, sIdx) in pipe.steps" :key="'ps-' + pIdx + '-' + sIdx">
                                    <div class="flex items-center gap-1.5">
                                        <select
                                            v-model="pipe.steps[sIdx]"
                                            class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-700 dark:text-gray-300"
                                        >
                                            <option v-for="s in statuses" :key="s.code" :value="s.code">@{{ s.name }}</option>
                                        </select>
                                        <button
                                            @click="pipe.steps.splice(sIdx, 1)"
                                            class="flex items-center justify-center w-5 h-5 rounded text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                            title="Убрать"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <span v-if="sIdx < pipe.steps.length - 1" class="os-pipeline-arrow">→</span>
                                </template>

                                <button
                                    @click="pipe.steps.push(statuses[0]?.code || 'pending')"
                                    class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg transition-colors"
                                    style="background:#f5f3ff; color:#7c3aed;"
                                    @mouseenter="$event.currentTarget.style.background='#ede9fe'"
                                    @mouseleave="$event.currentTarget.style.background='#f5f3ff'"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    добавить
                                </button>
                            </div>
                        </div>

                        <!-- Add pipeline -->
                        <div class="flex items-center gap-2 mt-3">
                            <select v-model="newPipeDelivery" class="px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-700 dark:text-gray-300">
                                <option v-for="d in shippingMethods" :key="d.code" :value="d.code">@{{ d.title }}</option>
                            </select>
                            <span class="text-gray-400 font-bold">+</span>
                            <select v-model="newPipePayment" class="px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-700 dark:text-gray-300">
                                <option v-for="p in paymentMethods" :key="p.code" :value="p.code">@{{ p.title }}</option>
                            </select>
                            <button
                                @click="addPipeline"
                                class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-xl transition-colors"
                                style="background:#d1fae5; color:#047857;"
                                @mouseenter="$event.currentTarget.style.background='#a7f3d0'"
                                @mouseleave="$event.currentTarget.style.background='#d1fae5'"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Добавить пайплайн
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ============ SECTION 4: TAB GROUPS ============ -->
                <div class="box-shadow rounded-2xl bg-white dark:bg-gray-900 overflow-hidden mb-5">
                    <div class="flex items-center gap-2.5 px-5 py-4" style="border-bottom: 1px solid #f3f4f6;">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg" style="background:#dbeafe;">
                            <svg class="w-4 h-4" style="color:#2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-base font-bold text-gray-800 dark:text-white">Группы табов (фильтры заказов)</p>
                            <p class="text-xs text-gray-400">Табы KPI на странице заказов в админке и приложении менеджера. Один таб может объединять несколько статусов.</p>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex flex-wrap gap-3">
                            <div
                                v-for="(tab, tIdx) in tabGroups"
                                :key="'tab-' + tIdx"
                                class="p-3 rounded-2xl min-w-[160px]"
                                style="background:#fafafa; border: 1px solid #f0f0f0;"
                            >
                                <div class="flex items-center gap-2 mb-2">
                                    <input
                                        v-model="tab.name"
                                        class="flex-1 px-2.5 py-1 text-sm font-semibold rounded-lg border border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700 text-gray-800 dark:text-white min-w-[80px]"
                                    />
                                    <button
                                        @click="tabGroups.splice(tIdx, 1)"
                                        class="flex items-center justify-center w-6 h-6 rounded text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors flex-shrink-0"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="(sc, scIdx) in tab.statuses"
                                        :key="'ts-' + tIdx + '-' + scIdx"
                                        class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-lg"
                                        style="background:#e0e7ff; color:#4338ca;"
                                    >
                                        @{{ getStatusName(sc) }}
                                        <button @click="tab.statuses.splice(scIdx, 1)" class="hover:text-red-500 ml-0.5">×</button>
                                    </span>
                                    <select
                                        @change="addTabStatus(tIdx, $event)"
                                        class="px-2 py-1 text-xs rounded-lg border border-dashed border-gray-300 bg-white dark:bg-gray-800 text-gray-500 cursor-pointer"
                                    >
                                        <option value="">+</option>
                                        <option v-for="s in statuses" :key="s.code" :value="s.code">@{{ s.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <button
                                @click="addTabGroup"
                                class="flex items-center justify-center min-w-[120px] p-3 rounded-2xl"
                                style="background: #f5f3ff; border: 2px dashed #c4b5fd; color:#7c3aed;"
                                @mouseenter="$event.currentTarget.style.background='#ede9fe'"
                                @mouseleave="$event.currentTarget.style.background='#f5f3ff'"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ============ SECTION 5: DELIVERY & PAYMENT TYPES (from system config) ============ -->
                <div class="box-shadow rounded-2xl bg-white dark:bg-gray-900 overflow-hidden mb-5">
                    <div class="flex items-center gap-2.5 px-5 py-4" style="border-bottom: 1px solid #f3f4f6;">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg" style="background:#fce7f3;">
                            <svg class="w-4 h-4" style="color:#be185d;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-base font-bold text-gray-800 dark:text-white">Типы доставки и способы оплаты</p>
                            <p class="text-xs text-gray-400">Выберите, какие методы использовать в пайплайнах. Данные подгружаются из системных настроек.</p>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Delivery Types (from system carriers config) -->
                            <div>
                                <p class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-3">Доставка <span class="font-normal text-gray-400">(из конфигурации Shipping)</span></p>
                                <div v-for="sm in shippingMethods" :key="'sm-' + sm.code" class="flex items-center gap-3 mb-2 px-3 py-2 rounded-xl" style="background:#f9fafb;">
                                    <label class="flex items-center gap-2 cursor-pointer flex-1">
                                        <input type="checkbox" :value="sm.code" v-model="selectedDeliveryTypes" class="w-4 h-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500"/>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">@{{ sm.title }}</span>
                                        <span class="text-xs font-mono text-gray-400">(@{{ sm.code }})</span>
                                    </label>
                                    <span v-if="sm.active" class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#d1fae5;color:#047857;">Активен</span>
                                    <span v-else class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#f3f4f6;color:#6b7280;">Откл.</span>
                                </div>
                                <p v-if="!shippingMethods.length" class="text-sm text-gray-400 italic">Нет настроенных методов доставки</p>
                            </div>

                            <!-- Payment Types (from system payment_methods config) -->
                            <div>
                                <p class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-3">Оплата <span class="font-normal text-gray-400">(из конфигурации Payment)</span></p>
                                <div v-for="pm in paymentMethods" :key="'pm-' + pm.code" class="flex items-center gap-3 mb-2 px-3 py-2 rounded-xl" style="background:#f9fafb;">
                                    <label class="flex items-center gap-2 cursor-pointer flex-1">
                                        <input type="checkbox" :value="pm.code" v-model="selectedPaymentTypes" class="w-4 h-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500"/>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">@{{ pm.title }}</span>
                                        <span class="text-xs font-mono text-gray-400">(@{{ pm.code }})</span>
                                    </label>
                                    <span v-if="pm.active" class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#d1fae5;color:#047857;">Активен</span>
                                    <span v-else class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#f3f4f6;color:#6b7280;">Откл.</span>
                                </div>
                                <p v-if="!paymentMethods.length" class="text-sm text-gray-400 italic">Нет настроенных методов оплаты</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button (bottom) -->
                <div class="flex justify-end">
                    <button
                        @click="saveAll"
                        :disabled="isSaving"
                        class="flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white rounded-xl transition-all"
                        style="background: linear-gradient(135deg, #7c3aed, #6d28d9); box-shadow: 0 4px 15px rgba(124,58,237,0.3);"
                    >
                        <svg v-if="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        @{{ isSaving ? 'Сохраняю...' : 'Сохранить' }}
                    </button>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-order-status-settings', {
                template: '#v-order-status-settings-template',
                props: ['initialStatuses', 'initialSettings', 'saveUrl', 'systemShipping', 'systemPayments'],
                data() {
                    const settings = JSON.parse(this.initialSettings || '{}');
                    const shipping = (() => { try { return typeof this.systemShipping === 'string' ? JSON.parse(this.systemShipping) : (this.systemShipping || []); } catch(e) { return []; } })();
                    const payments = (() => { try { return typeof this.systemPayments === 'string' ? JSON.parse(this.systemPayments) : (this.systemPayments || []); } catch(e) { return []; } })();
                    const savedDelivery = settings.delivery_types || [];
                    const savedPayment  = settings.payment_types || [];
                    return {
                        statuses: JSON.parse(this.initialStatuses || '[]'),
                        shippingMethods: shipping,
                        paymentMethods: payments,
                        newOrderStatus: settings.new_order_status || 'pending',
                        pipelines: settings.pipelines || [],
                        tabGroups: settings.tab_groups || [],
                        selectedDeliveryTypes: savedDelivery.map(d => d.code || d),
                        selectedPaymentTypes: savedPayment.map(p => p.code || p),
                        isSaving: false,
                        newPipeDelivery: '',
                        newPipePayment: '',
                        iconOptions: [
                            'hourglass-top', 'receipt', 'file-earmark-check', 'arrow-repeat',
                            'check-circle', 'fire', 'check2-circle', 'truck', 'check-circle-fill',
                            'x-circle', 'pause-circle', 'arrow-counterclockwise', 'exclamation-triangle',
                            'clock', 'bag-check', 'box-seam', 'shield-check', 'star', 'lightning',
                            'cart-check', 'envelope-check', 'cash-coin', 'credit-card', 'wallet2',
                            'telephone', 'geo-alt', 'building', 'person-check', 'hand-thumbs-up', 'emoji-smile',
                        ],
                    };
                },
                mounted() {
                    if (this.shippingMethods.length) this.newPipeDelivery = this.shippingMethods[0].code;
                    if (this.paymentMethods.length) this.newPipePayment = this.paymentMethods[0].code;
                    this.initSortable();
                },
                methods: {
                    initSortable() {
                        const el = this.$refs.statusList;
                        if (!el) return;
                        // Simple drag-and-drop using HTML5 drag events
                        let dragIdx = null;
                        el.addEventListener('dragstart', (e) => {
                            const item = e.target.closest('[data-idx]');
                            if (!item) return;
                            dragIdx = parseInt(item.dataset.idx);
                            item.style.opacity = '0.4';
                        });
                        el.addEventListener('dragend', (e) => {
                            const item = e.target.closest('[data-idx]');
                            if (item) item.style.opacity = '1';
                        });
                        el.addEventListener('dragover', (e) => {
                            e.preventDefault();
                        });
                        el.addEventListener('drop', (e) => {
                            e.preventDefault();
                            const target = e.target.closest('[data-idx]');
                            if (!target || dragIdx === null) return;
                            const dropIdx = parseInt(target.dataset.idx);
                            if (dragIdx === dropIdx) return;
                            const item = this.statuses.splice(dragIdx, 1)[0];
                            this.statuses.splice(dropIdx, 0, item);
                            dragIdx = null;
                        });
                        // Make items draggable via handle
                        el.querySelectorAll('.os-drag-handle').forEach((handle, idx) => {
                            const parent = handle.closest('[data-idx]');
                            if (parent) parent.setAttribute('draggable', 'true');
                        });
                    },
                    addStatus() {
                        this.statuses.push({
                            code: '',
                            name: '',
                            icon: 'clock',
                            color: null,
                            sort_order: this.statuses.length,
                            is_system: false,
                        });
                        this.$nextTick(() => this.initSortable());
                    },
                    removeStatus(idx) {
                        if (this.statuses[idx].is_system) return;
                        this.statuses.splice(idx, 1);
                    },
                    getStatusName(code) {
                        const s = this.statuses.find(s => s.code === code);
                        return s ? s.name : code;
                    },
                    getDeliveryName(code) {
                        const d = this.shippingMethods.find(d => d.code === code);
                        return d ? d.title : code;
                    },
                    getPaymentName(code) {
                        const p = this.paymentMethods.find(p => p.code === code);
                        return p ? p.title : code;
                    },
                    addPipeline() {
                        if (!this.newPipeDelivery || !this.newPipePayment) return;
                        this.pipelines.push({
                            delivery_type: this.newPipeDelivery,
                            payment_type: this.newPipePayment,
                            steps: ['pending', 'processing', 'completed'],
                        });
                    },
                    removePipeline(idx) {
                        this.pipelines.splice(idx, 1);
                    },
                    addTabStatus(tIdx, event) {
                        const code = event.target.value;
                        if (!code) return;
                        if (!this.tabGroups[tIdx].statuses.includes(code)) {
                            this.tabGroups[tIdx].statuses.push(code);
                        }
                        event.target.value = '';
                    },
                    addTabGroup() {
                        this.tabGroups.push({
                            name: 'Новый таб',
                            statuses: [],
                        });
                    },
                    saveAll() {
                        this.isSaving = true;

                        const payload = {
                            statuses: this.statuses.map((s, i) => ({
                                code: s.code,
                                name: s.name,
                                icon: s.icon,
                                color: s.color,
                                sort_order: i,
                                is_system: s.is_system,
                            })),
                            new_order_status: this.newOrderStatus,
                            pipelines: this.pipelines,
                            tab_groups: this.tabGroups,
                            delivery_types: this.selectedDeliveryTypes.map(code => {
                                const sm = this.shippingMethods.find(s => s.code === code);
                                return { code, name: sm ? sm.title : code };
                            }),
                            payment_types: this.selectedPaymentTypes.map(code => {
                                const pm = this.paymentMethods.find(p => p.code === code);
                                return { code, name: pm ? pm.title : code };
                            }),
                        };

                        this.$axios.post(this.saveUrl, payload, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then((response) => {
                            this.isSaving = false;
                            this.$emitter.emit('add-flash', {
                                type: response.data.success ? 'success' : 'error',
                                message: response.data.message || 'Сохранено',
                            });
                        })
                        .catch((error) => {
                            this.isSaving = false;
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || 'Ошибка при сохранении',
                            });
                        });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
