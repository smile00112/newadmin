<x-admin::layouts>
    <v-customer-view>
        <!-- Shimmer Effect -->
        <x-admin::shimmer.customers.view />
    </v-customer-view>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-customer-view-template"
        >
            <!-- Page Title -->
            <x-slot:title>
                @lang('admin::app.customers.customers.view.title')
            </x-slot>

            <div class="grid">
                <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                    <div class="flex items-center gap-3">
                        <!-- Gradient avatar -->
                        <div class="flex items-center justify-center rounded-xl" style="width: 48px; height: 48px; min-width: 48px; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); box-shadow: 0 4px 15px rgba(6,182,212,0.3);">
                            <template v-if="customer">
                                <span style="font-size: 20px; font-weight: 700; color: #fff;" v-text="customer.first_name ? customer.first_name.charAt(0).toUpperCase() : '?'"></span>
                            </template>
                            <template v-else>
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </template>
                        </div>

                        <template
                            v-if="! customer"
                            class="flex gap-5"
                        >
                            <p class="shimmer w-32 p-2.5"></p>

                            <p class="shimmer w-14 p-2.5"></p>
                        </template>

                        <template v-else>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h1
                                        v-if="customer"
                                        class="text-xl font-bold leading-6 text-gray-800 dark:text-white"
                                        v-text="`${customer.first_name} ${customer.last_name}`"
                                    ></h1>

                                    <span
                                        v-if="customer.status"
                                        style="display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;"
                                    >
                                        @lang('admin::app.customers.customers.view.active')
                                    </span>

                                    <span
                                        v-else
                                        style="display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;"
                                    >
                                        @lang('admin::app.customers.customers.view.inactive')
                                    </span>

                                    <span
                                        v-if="customer.is_suspended"
                                        style="display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; background: #fef3c7; color: #d97706; border: 1px solid #fde68a;"
                                    >
                                        @lang('admin::app.customers.customers.view.suspended')
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5" v-if="customer" v-text="customer.email"></p>
                            </div>
                        </template>
                    </div>

                    <!-- Back Button -->
                    <a
                        href="{{ route('admin.customers.customers.index') }}"
                        style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #6b7280; background: #f3f4f6; transition: all 0.15s;"
                        @mouseenter="$event.currentTarget.style.background='#e5e7eb'"
                        @mouseleave="$event.currentTarget.style.background='#f3f4f6'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        @lang('admin::app.customers.customers.view.back-btn')
                    </a>
                </div>
            </div>

            {!! view_render_event('bagisto.admin.customers.customers.view.filters.before') !!}

            <!-- Filters -->
            <div class="mt-5 flex flex-wrap items-center gap-2">
                <!-- Create Order button -->
                @if (bouncer()->hasPermission('sales.orders.create'))
                    <div
                        style="display: inline-flex; cursor: pointer; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #7c3aed; background: #ede9fe; transition: all 0.15s;"
                        @mouseenter="$event.currentTarget.style.background='#ddd6fe'"
                        @mouseleave="$event.currentTarget.style.background='#ede9fe'"
                        @click="$emitter.emit('open-confirm-modal', {
                            message: '@lang('admin::app.customers.customers.view.order-create-confirmation')',

                            agree: () => {
                                this.$refs['create-order'].submit()
                            }
                        })"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" /></svg>

                        @lang('admin::app.customers.customers.view.create-order')

                        <!-- Create Order Form -->
                        <form
                            method="post"
                            action="{{ route('admin.customers.customers.cart.store', $customer->id) }}"
                            ref="create-order"
                        >
                            @csrf
                        </form>
                    </div>
                @endif

                <a
                    style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #2563eb; background: #dbeafe; transition: all 0.15s; text-decoration: none;"
                    href="{{ route('admin.customers.customers.login_as_customer', $customer->id) }}"
                    target="_blank"
                    @mouseenter="$event.currentTarget.style.background='#bfdbfe'"
                    @mouseleave="$event.currentTarget.style.background='#dbeafe'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>

                    @lang('admin::app.customers.customers.view.login-as-customer')
                </a>
                
                <!-- Account Delete button -->
                @if (bouncer()->hasPermission('customers.customers.delete'))
                    <div
                        style="display: inline-flex; cursor: pointer; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #dc2626; background: #fef2f2; transition: all 0.15s;"
                        @mouseenter="$event.currentTarget.style.background='#fecaca'"
                        @mouseleave="$event.currentTarget.style.background='#fef2f2'"
                        @click="$emitter.emit('open-confirm-modal', {
                            message: '@lang('admin::app.customers.customers.view.account-delete-confirmation')',

                            agree: () => {
                                this.$refs['delete-account'].submit()
                            }
                        })"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>

                        @lang('admin::app.customers.customers.view.delete-account')

                        <!-- Delete Customer Account -->
                        <form
                            method="post"
                            action="{{ route('admin.customers.customers.delete', $customer->id) }}"
                            ref="delete-account"
                        >
                            @csrf
                        </form>
                    </div>
                @endif
            </div>

            {!! view_render_event('bagisto.admin.customers.customers.view.filters.after') !!}

            <!-- Content -->
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left Component -->
                <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                    {!! view_render_event('bagisto.admin.customers.customers.view.card.orders.before') !!}

                    @include('admin::customers.customers.view.orders')

                    {!! view_render_event('bagisto.admin.customers.customers.view.card.orders.after') !!}

                    {!! view_render_event('bagisto.admin.customers.customers.view.card.invoices.before') !!}

                    @include('admin::customers.customers.view.invoices')

                    {!! view_render_event('bagisto.admin.customers.customers.view.card.invoices.after') !!}

                    {!! view_render_event('bagisto.admin.customers.customers.view.card.reviews.before') !!}

                    @include('admin::customers.customers.view.reviews')

                    {!! view_render_event('bagisto.admin.customers.customers.view.card.reviews.after') !!}

                    {!! view_render_event('bagisto.admin.customers.customers.view.card.notes.before') !!}

                    @include('admin::customers.customers.view.notes')

                    {!! view_render_event('bagisto.admin.customers.customers.view.card.notes.after') !!}
                </div>

                <!-- Right Component -->
                <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">

                    {!! view_render_event('bagisto.admin.customers.customers.view.card.accordion.customer.before') !!}

                    <!-- Information -->
                    {!! view_render_event('bagisto.admin.customers.customers.view.card.accordion.customer.after') !!}

                    <template v-if="! customer">
                        <x-admin::shimmer.accordion class="h-[271px] w-[360px]"/>
                    </template>

                    <template v-else>
                        <x-admin::accordion>
                            <x-slot:header>
                                <div class="flex w-full items-center">
                                    <div class="flex items-center gap-2 w-full p-2.5">
                                        <div class="flex items-center justify-center rounded-lg" style="width: 28px; height: 28px; min-width: 28px; background: #ede9fe;">
                                            <svg class="w-4 h-4" style="color: #7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        </div>
                                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                                            @lang('admin::app.customers.customers.view.customer')
                                        </p>
                                    </div>

                                    <!--Customer Edit Component -->
                                    @include('admin::customers.customers.view.edit')
                                </div>
                            </x-slot:header>

                            <x-slot:content>
                                <div class="grid gap-y-2.5">
                                    <p
                                        class="break-all font-semibold text-gray-800 dark:text-white"
                                        v-text="`${customer.first_name} ${customer.last_name}`"
                                    >
                                    </p>

                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('admin::app.customers.customers.view.email')".replace(':email', customer.email ?? 'N/A') }}
                                    </p>

                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('admin::app.customers.customers.view.phone')".replace(':phone', customer.phone ?? 'N/A') }}
                                    </p>

                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('admin::app.customers.customers.view.gender')".replace(':gender', customer.gender ?? 'N/A') }}
                                    </p>

                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('admin::app.customers.customers.view.date-of-birth')".replace(':dob', customer.date_of_birth ?? 'N/A') }}
                                    </p>

                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ "@lang('admin::app.customers.customers.view.group')".replace(':group_code', customer.group?.name ?? 'N/A') }}
                                    </p>
                                </div>
                            </x-slot:content>
                        </x-admin::accordion>
                    </template>

                    {!! view_render_event('bagisto.admin.customers.customers.view.card.accordion.address.before') !!}

                    <template v-if="! customer">
                        <x-admin::shimmer.accordion class="h-[271px] w-[360px]"/>
                    </template>

                    <template v-else>
                        <!-- Addresses listing-->
                        <x-admin::accordion>
                            <x-slot:header>
                                <div class="flex w-full items-center">
                                    <div class="flex items-center gap-2 w-full p-2.5">
                                        <div class="flex items-center justify-center rounded-lg" style="width: 28px; height: 28px; min-width: 28px; background: #dbeafe;">
                                            <svg class="w-4 h-4" style="color: #2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        </div>
                                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                                            @{{ "@lang('admin::app.customers.customers.view.address.count')".replace(':count', customer.addresses.length) }}
                                        </p>
                                    </div>

                                    <!-- Address Create component -->
                                    @include('admin::customers.customers.view.address.create')
                                </div>
                            </x-slot>

                            <x-slot:content>
                                <template v-if="customer.addresses.length">
                                    <div
                                        class="grid gap-y-2.5"
                                        v-for="(address, index) in customer.addresses"
                                    >
                                        <p
                                            class="label-pending"
                                            v-if="address.default_address"
                                        >
                                            @lang('admin::app.customers.customers.view.default-address')
                                        </p>

                                        <p class="break-all font-semibold text-gray-800 dark:text-white">
                                            @{{ `${address.first_name} ${address.last_name}` }}

                                            <template v-if="address.company_name">
                                                (@{{ address.company_name }})
                                            </template>
                                        </p>

                                        <p class="text-gray-600 dark:text-gray-300">
                                            <template v-if="address.address">
                                                @{{ address.address.split('\n').join(', ') }},
                                            </template>

                                            @{{ address.city }},
                                            @{{ address.state }},
                                            @{{ address.country }},
                                            @{{ address.postcode }}
                                        </p>

                                        <p class="text-gray-600 dark:text-gray-300">
                                            @{{ '@lang('admin::app.customers.customers.view.phone')'.replace(':phone', address.phone ?? 'N/A') }}
                                        </p>

                                        <!-- E-mail -->
                                        <p class="text-gray-600 dark:text-gray-300">
                                            @{{ '@lang('admin::app.customers.customers.view.email')'.replace(':email', address.email ?? 'N/A') }}
                                        </p>

                                        <div class="flex items-center gap-2.5">
                                            <!-- Edit Address -->
                                            @include('admin::customers.customers.view.address.edit')

                                            <!-- Delete Address -->
                                            @if (bouncer()->hasPermission('customers.addresses.delete'))
                                                <p
                                                    class="cursor-pointer text-red-600 transition-all hover:underline"
                                                    @click="deleteAddress(address.id)"
                                                >
                                                    @lang('admin::app.customers.customers.view.delete')
                                                </p>
                                            @endif

                                            <!-- Set Default Address -->
                                            <template v-if="! address.default_address">
                                                <x-admin::button
                                                    button-type="button"
                                                    class="flex cursor-pointer justify-center text-sm text-blue-600 transition-all hover:underline"
                                                    :title="trans('admin::app.customers.customers.view.set-as-default')"
                                                    ::loading="isUpdating[index]"
                                                    ::disabled="isUpdating[index]"
                                                    @click="setAsDefault(address, index)"
                                                />
                                            </template>
                                        </div>

                                        <span
                                            v-if="index != customer?.addresses.length - 1"
                                            class="mb-4 mt-4 block w-full border-b dark:border-gray-800"
                                        ></span>
                                    </div>
                                </template>

                                <template v-else>
                                    <!-- Empty Address Container -->
                                    <div class="flex items-center gap-5 py-2.5">
                                        <img
                                            src="{{ bagisto_asset('images/settings/address.svg') }}"
                                            class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                                        />

                                        <div class="flex flex-col gap-1.5">
                                            <p class="text-base font-semibold text-gray-400">
                                                @lang('admin::app.customers.customers.view.empty-title')
                                            </p>

                                            <p class="text-gray-400">
                                                @lang('admin::app.customers.customers.view.empty-description')
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </x-slot>
                        </x-admin::accordion>
                    </template>

                    {!! view_render_event('bagisto.admin.customers.customers.view.card.accordion.address.after') !!}
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-customer-view', {
                template: '#v-customer-view-template',

                data() {
                    return {
                        customer: @json($customer),

                        isUpdating: {},
                    };
                },

                methods: {
                    deleteAddress(id) {
                        this.$emitter.emit('open-confirm-modal', {
                            message: '@lang('admin::app.customers.customers.view.address-delete-confirmation')',

                            agree: () => {
                                this.$axios.post(`{{ route('admin.customers.customers.addresses.delete', '') }}/${id}`)
                                    .then((response) => {
                                        this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                        this.customer.addresses = this.customer.addresses.filter(address => address.id !== id);
                                    })
                                    .catch((error) => {});
                            },
                        });
                    },

                    setAsDefault(address, index) {
                        this.isUpdating[index] = true;

                        this.$axios.post(`{{ route('admin.customers.customers.addresses.set_default', '') }}/${this.customer.id}`, {
                            set_as_default: address.id,
                        })
                            .then((response) => {
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                this.customer.addresses = this.customer.addresses.map(address => ({
                                    ...address,
                                    default_address: address.id === response.data.data.id
                                        ? response.data.data.default_address
                                        : false,
                                }));

                                this.isUpdating[index] = false;
                            })
                            .catch((error) => this.isUpdating[index] = false);
                    },

                    updateCustomer(data) {
                        this.customer = {
                            ...this.customer,
                            ...data.customer,
                            group: {
                                ...data.group
                            },
                        };
                    },

                    addressCreated(address) {
                        if (address.default_address) {
                            this.customer.addresses.forEach(address => address.default_address = false);
                        }

                        this.customer.addresses.push({
                            ...address,
                            address: address.address.join('\n'),
                        });
                    },

                    addressUpdated(updatedAddress) {
                        if (updatedAddress.default_address) {
                            this.customer.addresses.forEach(address => address.default_address = false);
                        }

                        this.customer.addresses =this.customer.addresses.map(address => {
                            if (address.id === updatedAddress.id) {
                                return {
                                    ...updatedAddress,
                                    address: updatedAddress.address.join('\n'),
                                };
                            }

                            return address;
                        });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>