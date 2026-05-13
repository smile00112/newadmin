<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.customers.customers.index.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); box-shadow: 0 4px 15px rgba(6,182,212,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.customers.customers.index.title')
                </p>
                <p class="text-xs text-gray-400">Управление клиентами</p>
            </div>
        </div>

        <div class="flex items-center gap-x-2.5">
            <!-- Export Modal -->
            <x-admin::datagrid.export src="{{ route('admin.customers.customers.index') }}" />

            <div class="flex items-center gap-x-2.5">
                <!-- Included customer create blade file -->
                @if (bouncer()->hasPermission('customers.customers.create'))
                    {!! view_render_event('bagisto.admin.customers.customers.create.before') !!}

                    @include('admin::customers.customers.index.create')

                    <v-create-customer-form
                        ref="createCustomerComponent"
                        @customer-created="$refs.customerDatagrid.get()"
                    ></v-create-customer-form>

                    {!! view_render_event('bagisto.admin.customers.customers.create.after') !!}

                    <button
                        class="primary-button"
                        @click="$refs.createCustomerComponent.openModal()"
                    >
                        @lang('admin::app.customers.customers.index.create.create-btn')
                    </button>
                @endif
            </div>
        </div>
    </div>

    {!! view_render_event('bagisto.admin.customers.customers.list.before') !!}

    <x-admin::datagrid
        :src="route('admin.customers.customers.index')"
        ref="customerDatagrid"
        :isMultiRow="true"
    >
        @php
            $hasPermission = bouncer()->hasPermission('customers.customers.edit') || bouncer()->hasPermission('customers.customers.delete');
        @endphp

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
                <div class="row grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr] grid-rows-1 gap-1 items-center border-b px-4 py-2.5 dark:border-gray-800 min-w-full">
                    <div
                        class="flex select-none items-center gap-2.5"
                        v-for="(columnGroup, index) in [['full_name', 'email', 'phone'], ['status', 'gender', 'group', 'customer_id'], ['revenue', 'order_count', 'address_count']]"
                    >
                        @if ($hasPermission)
                            <label
                                class="flex w-max cursor-pointer select-none items-center gap-1"
                                for="mass_action_select_all_records"
                                v-if="! index"
                            >
                                <input
                                    type="checkbox"
                                    name="mass_action_select_all_records"
                                    id="mass_action_select_all_records"
                                    class="peer hidden"
                                    :checked="['all', 'partial'].includes(applied.massActions.meta.mode)"
                                    @change="selectAll"
                                >

                                <span
                                    class="icon-uncheckbox cursor-pointer rounded-md text-2xl"
                                    :class="[
                                        applied.massActions.meta.mode === 'all' ? 'peer-checked:icon-checked peer-checked:text-blue-600' : (
                                            applied.massActions.meta.mode === 'partial' ? 'peer-checked:icon-checkbox-partial peer-checked:text-blue-600' : ''
                                        ),
                                    ]"
                                >
                                </span>
                            </label>
                        @endif

                        <p class="text-gray-600 dark:text-gray-300">
                            <span class="[&>*]:after:content-['_/_']">
                                <template v-for="column in columnGroup">
                                    <span
                                        class="after:content-['/'] last:after:content-['']"
                                        :class="{
                                            'font-medium text-gray-800 dark:text-white': applied.sort.column == column,
                                            'cursor-pointer hover:text-gray-800 dark:hover:text-white': available.columns.find(columnTemp => columnTemp.index === column)?.sortable,
                                        }"
                                        @click="
                                            available.columns.find(columnTemp => columnTemp.index === column)?.sortable ? sort(available.columns.find(columnTemp => columnTemp.index === column)): {}
                                        "
                                    >
                                        @{{ available.columns.find(columnTemp => columnTemp.index === column)?.label }}
                                    </span>
                                </template>
                            </span>

                            <i
                                class="align-text-bottom text-base text-gray-800 dark:text-white ltr:ml-1.5 rtl:mr-1.5"
                                :class="[applied.sort.order === 'asc' ? 'icon-down-stat': 'icon-up-stat']"
                                v-if="columnGroup.includes(applied.sort.column)"
                            ></i>
                        </p>
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
                <div
                    class="row grid grid-cols-1 gap-2 md:grid-cols-[minmax(150px,_2fr)_1fr_1fr] md:gap-0 border-b px-4 py-2.5 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950 min-w-full"
                    v-for="record in available.records"
                    style="cursor: pointer;"
                    @mouseenter="$event.currentTarget.style.boxShadow='inset 3px 0 0 #06b6d4'"
                    @mouseleave="$event.currentTarget.style.boxShadow='none'"
                    @click="!$event.target.closest('a, button, label, input') && (window.location.href = `{{ route('admin.customers.customers.view', '') }}/${record.customer_id}`)"
                >
                    <div class="flex gap-2.5 items-center">
                        @if ($hasPermission)
                            <input
                                type="checkbox"
                                :name="`mass_action_select_record_${record.customer_id}`"
                                :id="`mass_action_select_record_${record.customer_id}`"
                                :value="record.customer_id"
                                class="peer hidden"
                                v-model="applied.massActions.indices"
                                @change="setCurrentSelectionMode"
                            >

                            <label
                                class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"
                                :for="`mass_action_select_record_${record.customer_id}`"
                            >
                            </label>
                        @endif

                        <!-- Avatar -->
                        <div class="flex items-center justify-center rounded-full" style="width: 40px; height: 40px; min-width: 40px; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); font-weight: 600; font-size: 15px; color: #fff;">
                            @{{ record.full_name ? record.full_name.charAt(0).toUpperCase() : '?' }}
                        </div>

                        <div class="flex flex-col gap-1">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @{{ record.full_name }}
                            </p>

                            <p class="text-gray-500 dark:text-gray-400" style="font-size: 13px;">
                                @{{ record.email }}
                            </p>

                            <p v-if="record.phone" class="text-gray-400 dark:text-gray-500" style="font-size: 12px;">
                                @{{ record.phone }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5 ps-8 md:ps-0 justify-center">
                        <div class="flex gap-1.5 flex-wrap">
                            <span
                                style="display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;"
                                :style="record.status === 1
                                    ? 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;'
                                    : 'background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;'"
                            >
                                @{{ record.status ? '@lang('admin::app.customers.customers.index.datagrid.active')' : '@lang('admin::app.customers.customers.index.datagrid.inactive')' }}
                            </span>

                            <span
                                v-if="record.is_suspended === 1"
                                style="display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; background: #fef3c7; color: #d97706; border: 1px solid #fde68a;"
                            >
                                @lang('admin::app.customers.customers.index.datagrid.suspended')
                            </span>
                        </div>

                        <p class="text-gray-600 dark:text-gray-300" style="font-size: 13px;">
                            @{{ record.gender ?? '—' }}
                        </p>

                        <p v-if="record.group" style="display: inline-flex; align-items: center; padding: 1px 8px; border-radius: 6px; font-size: 11px; background: #ede9fe; color: #7c3aed; font-weight: 500; width: fit-content;">
                            @{{ record.group }}
                        </p>

                        <p class="text-gray-400 dark:text-gray-500" style="font-size: 11px;">
                            ID: @{{ record.customer_id }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between gap-x-4 ps-8 md:ps-0">
                        <div class="flex flex-col gap-1.5">
                            <p class="text-base font-bold" style="color: #059669;">
                                @{{ $admin.formatPrice(record.revenue) }}
                            </p>

                            <div class="flex items-center gap-1.5">
                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 8px; font-size: 12px; background: #eef2ff; color: #4f46e5; font-weight: 500;">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                    @{{ record.order_count }}
                                </span>
                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 8px; font-size: 12px; background: #fef3c7; color: #d97706; font-weight: 500;">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    @{{ record.address_count }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <a
                                style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; transition: all 0.15s;"
                                :href="`{{ route('admin.customers.customers.login_as_customer', '') }}/${record.customer_id}`"
                                target="_blank"
                                @mouseenter="$event.currentTarget.style.background='#ede9fe'"
                                @mouseleave="$event.currentTarget.style.background='transparent'"
                                title="Войти как клиент"
                            >
                                <svg class="w-4 h-4" style="color: #7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                            </a>

                            <a
                                style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; transition: all 0.15s;"
                                :href="`{{ route('admin.customers.customers.view', '') }}/${record.customer_id}`"
                                @mouseenter="$event.currentTarget.style.background='#dbeafe'"
                                @mouseleave="$event.currentTarget.style.background='transparent'"
                                title="Просмотр"
                            >
                                <svg class="w-4 h-4" style="color: #2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>

    {!! view_render_event('bagisto.admin.customers.customers.list.after') !!}
</x-admin::layouts>
