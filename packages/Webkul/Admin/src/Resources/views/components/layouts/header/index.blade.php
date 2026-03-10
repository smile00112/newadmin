@php
    $admin = auth()->guard('admin')->user();
@endphp

<header class="sticky top-0 z-[10001] flex items-center justify-between border-b border-gray-200/60 bg-white/90 backdrop-blur-2xl px-4 py-3 dark:border-gray-700/40 dark:bg-gray-900/95 sm:px-6 sm:py-3.5 shadow-[0_1px_3px_rgba(0,0,0,0.05),0_1px_2px_rgba(0,0,0,0.03)] dark:shadow-[0_1px_3px_rgba(0,0,0,0.3)]">
    <div class="flex items-center gap-2 sm:gap-3">
        <!-- Hamburger Menu -->
        <button
            class="flex items-center justify-center rounded-xl p-2.5 text-gray-600 transition-all duration-200 hover:bg-gradient-to-br hover:from-violet-50 hover:to-indigo-50 hover:text-violet-600 hover:shadow-sm active:scale-95 dark:text-gray-400 dark:hover:bg-gray-800/80 dark:hover:text-violet-400 lg:hidden"
            @click="$refs.sidebarMenuDrawer.open()"
        >
            <i class="icon-menu text-xl sm:text-2xl"></i>
        </button>

        <!-- Logo -->
        <a href="{{ route('admin.dashboard.index') }}" class="flex-shrink-0 transition-all duration-300 hover:scale-105 hover:opacity-80">
            @if ($logo = core()->getConfigData('general.design.admin_logo.logo_image'))
                <img
                    class="h-9 w-auto sm:h-10"
                    src="{{ Storage::url($logo) }}"
                    alt="{{ config('app.name') }}"
                />
            @else
                <img
                    src="{{ request()->cookie('dark_mode') ? bagisto_asset('images/dark-logo.svg') : bagisto_asset('images/logo.svg') }}"
                    class="h-9 w-auto sm:h-10"
                    id="logo-image"
                    alt="{{ config('app.name') }}"
                />
            @endif
        </a>

        <!-- Mega Search Bar Vue Component -->
        <v-mega-search class="hidden sm:block">
            <div class="relative flex w-[220px] items-center sm:w-[320px] md:w-[420px] lg:w-[500px] xl:max-w-[520px] ltr:ml-4 rtl:mr-4 sm:ltr:ml-5 sm:rtl:mr-5">
                <i class="icon-search absolute top-1/2 -translate-y-1/2 text-gray-400 text-lg ltr:left-4 rtl:right-4 sm:text-xl sm:ltr:left-4 sm:rtl:right-4"></i>

                <input
                    type="text"
                    class="block w-full rounded-2xl border-0 bg-gray-50/90 px-11 py-2.5 text-sm leading-6 text-gray-700 shadow-inner ring-1 ring-gray-200/80 transition-all duration-300 placeholder:text-gray-400 hover:bg-gray-100/80 hover:ring-violet-300/50 focus:bg-white focus:shadow-lg focus:shadow-violet-500/10 focus:ring-2 focus:ring-violet-500 dark:bg-gray-800/90 dark:text-gray-200 dark:ring-gray-700/80 dark:placeholder:text-gray-500 dark:hover:bg-gray-800 dark:hover:ring-violet-500/40 dark:focus:bg-gray-800 dark:focus:ring-violet-500 sm:px-12 sm:py-3 sm:text-base"
                    placeholder="@lang('admin::app.components.layouts.header.mega-search.title')"
                >
            </div>
        </v-mega-search>
    </div>

    <div class="flex items-center gap-1.5 sm:gap-2">
        <!-- Dark mode Switcher -->
        <v-dark>
            <div class="flex">
                <button
                    class="group flex items-center justify-center rounded-xl p-2.5 text-gray-500 transition-all duration-300 hover:bg-gradient-to-br hover:from-amber-50 hover:to-orange-50 hover:text-amber-500 hover:shadow-sm active:scale-95 dark:text-gray-400 dark:hover:bg-gray-800/80 dark:hover:text-amber-400"
                    title="Toggle theme"
                >
                    <span class="{{ request()->cookie('dark_mode') ? 'icon-light' : 'icon-dark' }} text-xl transition-transform duration-300 group-hover:rotate-12 sm:text-2xl"></span>
                </button>
            </div>
        </v-dark>

        <!-- Visit Shop Link -->
        <a
            href="{{ route('shop.home.index') }}"
            target="_blank"
            class="group hidden sm:flex items-center justify-center rounded-xl p-2.5 text-gray-500 transition-all duration-300 hover:bg-gradient-to-br hover:from-emerald-50 hover:to-teal-50 hover:text-emerald-600 hover:shadow-sm active:scale-95 dark:text-gray-400 dark:hover:bg-gray-800/80 dark:hover:text-emerald-400"
            title="@lang('admin::app.components.layouts.header.visit-shop')"
        >
            <span class="icon-store text-xl transition-transform duration-300 group-hover:scale-110 sm:text-2xl"></span>
        </a>

       <!-- Notification Component -->
        <v-notifications {{ $attributes }}>
            <span class="group relative flex items-center justify-center rounded-xl p-2.5 text-gray-500 transition-all duration-300 hover:bg-gradient-to-br hover:from-rose-50 hover:to-pink-50 hover:text-rose-500 hover:shadow-sm active:scale-95 dark:text-gray-400 dark:hover:bg-gray-800/80 dark:hover:text-rose-400">
                <span
                    class="icon-notification text-xl transition-transform duration-300 group-hover:rotate-12 sm:text-2xl"
                    title="@lang('admin::app.components.layouts.header.notifications')"
                ></span>
            </span>
        </v-notifications>

        <!-- Sound Alert Toggle for New Orders -->
        <v-sound-alert-toggle></v-sound-alert-toggle>

        <!-- AI Assistant Button -->
        <v-ai-assistant-header></v-ai-assistant-header>

        <!-- Admin profile -->
        <x-admin::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'right' : 'left' }}">
            <x-slot:toggle>
                @if ($admin->image)
                    <button class="group flex h-10 w-10 cursor-pointer overflow-hidden rounded-2xl ring-2 ring-gray-100 transition-all duration-300 hover:ring-violet-300 hover:ring-offset-2 hover:shadow-lg focus:ring-violet-500 focus:ring-offset-2 active:scale-95 dark:ring-gray-700 dark:hover:ring-violet-500 sm:h-11 sm:w-11">
                        <img
                            src="{{ $admin->image_url }}"
                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                        />
                    </button>
                @else
                    <button class="group flex h-10 w-10 cursor-pointer items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 via-purple-500 to-indigo-600 text-sm font-bold leading-6 text-white shadow-lg shadow-violet-500/30 ring-2 ring-transparent transition-all duration-300 hover:shadow-xl hover:shadow-violet-500/40 hover:ring-violet-300 hover:ring-offset-2 focus:ring-violet-500 focus:ring-offset-2 active:scale-95 dark:shadow-violet-500/20 sm:h-11 sm:w-11 sm:text-base">
                        <span class="transition-transform duration-300 group-hover:scale-110">{{ substr($admin->name, 0, 1) }}</span>
                    </button>
                @endif
            </x-slot>

            <!-- Admin Dropdown -->
            <x-slot:content class="!p-0 overflow-hidden rounded-2xl shadow-2xl shadow-gray-200/50 dark:shadow-gray-950/50 border border-gray-100 dark:border-gray-800 animate-fade-in">
                <div class="flex items-center gap-3 bg-gradient-to-br from-violet-500 via-purple-500 to-indigo-600 px-5 py-5 text-white">
                    @if ($admin->image)
                        <img
                            src="{{ $admin->image_url }}"
                            class="h-12 w-12 rounded-xl object-cover ring-2 ring-white/40 shadow-lg"
                        />
                    @else
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm text-base font-bold shadow-lg">
                            {{ substr($admin->name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <p class="font-semibold text-base">{{ $admin->name }}</p>
                        <p class="text-sm text-violet-100/90">{{ $admin->email }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-3 dark:border-gray-800">
                    <img
                        src="{{ url('cache/logo/bagisto.png') }}"
                        class="h-5 w-5"
                        width="20"
                        height="20"
                    />
                    <p class="text-xs text-gray-400">
                        @lang('admin::app.components.layouts.header.app-version', ['version' => 'v' . core()->version()])
                    </p>
                </div>

                <div class="p-2">
                    <a
                        class="flex items-center gap-3 cursor-pointer rounded-lg px-4 py-2.5 text-sm text-gray-700 transition-colors hover:bg-indigo-50 hover:text-indigo-600 dark:text-gray-200 dark:hover:bg-gray-800 dark:hover:text-indigo-400"
                        href="{{ route('admin.account.edit') }}"
                    >
                        <span class="icon-customer text-lg"></span>
                        @lang('admin::app.components.layouts.header.my-account')
                    </a>

                    <!--Admin logout-->
                    <x-admin::form
                        method="DELETE"
                        action="{{ route('admin.session.destroy') }}"
                        id="adminLogout"
                    >
                    </x-admin::form>

                    <a
                        class="flex items-center gap-3 cursor-pointer rounded-lg px-4 py-2.5 text-sm text-rose-600 transition-colors hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-gray-800"
                        href="{{ route('admin.session.destroy') }}"
                        onclick="event.preventDefault(); document.getElementById('adminLogout').submit();"
                    >
                        <span class="icon-exit text-lg"></span>
                        @lang('admin::app.components.layouts.header.logout')
                    </a>
                </div>
            </x-slot>
        </x-admin::dropdown>
    </div>
</header>

<!-- Menu Sidebar Drawer -->
<x-admin::drawer
    position="left"
    width="270px"
    ref="sidebarMenuDrawer"
>
    <!-- Drawer Header -->
    <x-slot:header>
        <div class="flex items-center justify-between">
            @if ($logo = core()->getConfigData('general.design.admin_logo.logo_image'))
                <img
                    src="{{ Storage::url($logo) }}"
                    class="h-8 w-auto sm:h-10"
                    alt="{{ config('app.name') }}"
                />
            @else
                <img
                    src="{{ request()->cookie('dark_mode') ? bagisto_asset('images/dark-logo.svg') : bagisto_asset('images/logo.svg') }}"
                    class="h-8 w-auto sm:h-10"
                    id="logo-image"
                    alt="{{ config('app.name') }}"
                />
            @endif
        </div>
    </x-slot>

    <!-- Drawer Content -->
    <x-slot:content class="p-3 sm:p-4">
        <div class="journal-scroll h-[calc(100vh-100px)] overflow-auto">
            <nav class="grid w-full gap-1.5 sm:gap-2">
                <!-- Navigation Menu -->
                @foreach (menu()->getItems('admin') as $menuItem)
                    <div class="group/item relative">
                        <a
                            href="{{ $menuItem->getUrl() }}"
                            class="flex items-center gap-2 p-1.5 cursor-pointer hover:rounded-lg {{ $menuItem->isActive() == 'active' ? 'bg-blue-600 rounded-lg' : ' hover:bg-gray-100 hover:dark:bg-gray-950' }} peer sm:gap-2.5"
                        >
                            <span class="{{ $menuItem->getIcon() }} text-xl {{ $menuItem->isActive() ? 'text-white' : ''}} sm:text-2xl"></span>

                            <p class="font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap text-sm group-[.sidebar-collapsed]/container:hidden {{ $menuItem->isActive() ? 'text-white' : ''}} sm:text-base">
                                {{ $menuItem->getName() }}
                            </p>
                        </a>

                        @if ($menuItem->haveChildren())
                            <div class="{{ $menuItem->isActive() ? ' !grid bg-gray-100 dark:bg-gray-950' : '' }} hidden min-w-[180px] ltr:pl-8 rtl:pr-8 pb-2 rounded-b-lg z-[100] sm:ltr:pl-10 sm:rtl:pr-10">
                                @foreach ($menuItem->getChildren() as $subMenuItem)
                                    <a
                                        href="{{ $subMenuItem->getUrl() }}"
                                        class="text-xs text-{{ $subMenuItem->isActive() ? 'blue':'gray' }}-600 dark:text-{{ $subMenuItem->isActive() ? 'blue':'gray' }}-300 whitespace-nowrap py-1 group-[.sidebar-collapsed]/container:px-4 group-[.sidebar-collapsed]/container:py-2 group-[.inactive]/item:px-4 group-[.inactive]/item:py-2 hover:text-blue-600 dark:hover:bg-gray-950 sm:text-sm sm:group-[.sidebar-collapsed]/container:px-5 sm:group-[.sidebar-collapsed]/container:py-2.5 sm:group-[.inactive]/item:px-5 sm:group-[.inactive]/item:py-2.5"
                                    >
                                        {{ $subMenuItem->getName() }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </nav>
        </div>
    </x-slot>
</x-admin::drawer>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-mega-search-template"
    >
        <div class="relative flex w-[200px] items-center sm:w-[300px] md:w-[400px] lg:w-[525px] xl:max-w-[525px] ltr:ml-2 rtl:mr-2 sm:ltr:ml-2.5 sm:rtl:mr-2.5">
            <i class="icon-search absolute top-1.5 flex items-center text-xl ltr:left-2 rtl:right-2 sm:text-2xl sm:ltr:left-3 sm:rtl:right-3"></i>

            <input
                type="text"
                class="peer block w-full rounded-lg border bg-white px-8 py-1.5 text-sm leading-6 text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400 sm:px-10 sm:text-base"
                :class="{'border-gray-400': isDropdownOpen}"
                placeholder="@lang('admin::app.components.layouts.header.mega-search.title')"
                v-model.lazy="searchTerm"
                @click="searchTerm.length >= 2 ? isDropdownOpen = true : {}"
                v-debounce="500"
            >

            <div
                class="absolute top-8 z-10 w-full rounded-lg border bg-white shadow-[0px_0px_0px_0px_rgba(0,0,0,0.10),0px_1px_3px_0px_rgba(0,0,0,0.10),0px_5px_5px_0px_rgba(0,0,0,0.09),0px_12px_7px_0px_rgba(0,0,0,0.05),0px_22px_9px_0px_rgba(0,0,0,0.01),0px_34px_9px_0px_rgba(0,0,0,0.00)] dark:border-gray-800 dark:bg-gray-900 sm:top-10"
                v-if="isDropdownOpen"
            >
                <!-- Search Tabs -->
                <div class="flex border-b text-xs text-gray-600 dark:border-gray-800 dark:text-gray-300 sm:text-sm">
                    <div
                        class="cursor-pointer p-2 hover:bg-gray-100 dark:hover:bg-gray-950 sm:p-4"
                        :class="{ 'border-b-2 border-blue-600': activeTab == tab.key }"
                        v-for="tab in tabs"
                        @click="activeTab = tab.key; search();"
                    >
                        @{{ tab.title }}
                    </div>
                </div>

                <!-- Searched Results -->
                <template v-if="activeTab == 'products'">
                    <template v-if="isLoading">
                        <x-admin::shimmer.header.mega-search.products />
                    </template>

                    <template v-else>
                        <div class="grid max-h-[300px] overflow-y-auto sm:max-h-[400px]">
                            <a
                                :href="'{{ route('admin.catalog.products.edit', ':id') }}'.replace(':id', product.id)"
                                class="flex cursor-pointer justify-between gap-2 border-b border-slate-300 p-3 last:border-b-0 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-gray-950 sm:gap-2.5 sm:p-4"
                                v-for="product in searchedResults.products.data"
                            >
                                <!-- Left Information -->
                                <div class="flex gap-2 sm:gap-2.5">
                                    <!-- Image -->
                                    <div
                                        class="relative h-10 max-h-10 w-full max-w-10 overflow-hidden rounded sm:h-[60px] sm:max-h-[60px] sm:max-w-[60px]"
                                        :class="{'overflow-hidden rounded border border-dashed border-gray-300 dark:border-gray-800 dark:mix-blend-exclusion dark:invert': ! product.images.length}"
                                    >
                                        <template v-if="! product.images.length">
                                            <img src="{{ bagisto_asset('images/product-placeholders/front.svg') }}" class="h-full w-full object-cover">

                                            <p class="absolute bottom-0.5 w-full text-center text-[4px] font-semibold text-gray-400 sm:bottom-1.5 sm:text-[6px]">
                                                @lang('admin::app.catalog.products.edit.types.grouped.image-placeholder')
                                            </p>
                                        </template>

                                        <template v-else>
                                            <img :src="product.images[0].url" class="h-full w-full object-cover">
                                        </template>
                                    </div>

                                    <!-- Details -->
                                    <div class="grid place-content-start gap-1 sm:gap-1.5">
                                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 sm:text-base">
                                            @{{ product.name }}
                                        </p>

                                        <p class="text-xs text-gray-500 sm:text-sm">
                                            @{{ translations.sku.replace(':sku', product.sku) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Right Information -->
                                <div class="grid place-content-center gap-1 text-right">
                                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 sm:text-base">
                                        @{{ product.formatted_price }}
                                    </p>
                                </div>
                            </a>
                        </div>

                        <div class="flex border-t p-2 dark:border-gray-800 sm:p-3">
                            <a
                                :href="'{{ route('admin.catalog.products.index') }}?search=:query'.replace(':query', searchTerm)"
                                class="cursor-pointer text-xs font-semibold text-blue-600 transition-all hover:underline"
                                v-if="searchedResults.products.data.length"
                            >
                                @{{ translations.exploreProducts.replace(':query', searchTerm).replace(':count', searchedResults.products.meta.total) }}
                            </a>

                            <a
                                href="{{ route('admin.catalog.products.index') }}"
                                class="cursor-pointer text-xs font-semibold text-blue-600 transition-all hover:underline"
                                v-else
                            >
                                @lang('admin::app.components.layouts.header.mega-search.explore-all-products')
                            </a>
                        </div>
                    </template>
                </template>

                <template v-if="activeTab == 'orders'">
                    <template v-if="isLoading">
                        <x-admin::shimmer.header.mega-search.orders />
                    </template>

                    <template v-else>
                        <div class="grid max-h-[300px] overflow-y-auto sm:max-h-[400px]">
                            <a
                                :href="'{{ route('admin.sales.orders.view', ':id') }}'.replace(':id', order.id)"
                                class="grid cursor-pointer place-content-start gap-1 border-b border-slate-300 p-3 last:border-b-0 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-gray-950 sm:gap-1.5 sm:p-4"
                                v-for="order in searchedResults.orders.data"
                            >
                                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 sm:text-base">
                                    #@{{ order.increment_id }}
                                </p>

                                <p class="text-xs text-gray-500 dark:text-gray-300 sm:text-sm">
                                    @{{ order.formatted_created_at + ', ' + order.status_label + ', ' + order.customer_full_name }}
                                </p>
                            </a>
                        </div>

                        <div class="flex border-t p-2 dark:border-gray-800 sm:p-3">
                            <a
                                :href="'{{ route('admin.sales.orders.index') }}?search=:query'.replace(':query', searchTerm)"
                                class="cursor-pointer text-xs font-semibold text-blue-600 transition-all hover:underline"
                                v-if="searchedResults.orders.data.length"
                            >
                                @{{ translations.exploreOrders.replace(':query', searchTerm).replace(':count', searchedResults.orders.total) }}
                            </a>

                            <a
                                href="{{ route('admin.sales.orders.index') }}"
                                class="cursor-pointer text-xs font-semibold text-blue-600 transition-all hover:underline"
                                v-else
                            >
                                @lang('admin::app.components.layouts.header.mega-search.explore-all-orders')
                            </a>
                        </div>
                    </template>
                </template>

                <template v-if="activeTab == 'categories'">
                    <template v-if="isLoading">
                        <x-admin::shimmer.header.mega-search.categories />
                    </template>

                    <template v-else>
                        <div class="grid max-h-[300px] overflow-y-auto sm:max-h-[400px]">
                            <a
                                :href="'{{ route('admin.catalog.categories.edit', ':id') }}'.replace(':id', category.id)"
                                class="cursor-pointer border-b p-3 text-xs font-semibold text-gray-600 last:border-b-0 hover:bg-gray-100 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950 sm:p-4 sm:text-sm"
                                v-for="category in searchedResults.categories.data"
                            >
                                @{{ category.name }}
                            </a>
                        </div>

                        <div class="flex border-t p-2 dark:border-gray-800 sm:p-3">
                            <a
                                :href="'{{ route('admin.catalog.categories.index') }}?search=:query'.replace(':query', searchTerm)"
                                class="cursor-pointer text-xs font-semibold text-blue-600 transition-all hover:underline"
                                v-if="searchedResults.categories.data.length"
                            >
                                @{{ translations.exploreCategories.replace(':query', searchTerm).replace(':count', searchedResults.categories.total) }}
                            </a>

                            <a
                                href="{{ route('admin.catalog.categories.index') }}"
                                class="cursor-pointer text-xs font-semibold text-blue-600 transition-all hover:underline"
                                v-else
                            >
                                @lang('admin::app.components.layouts.header.mega-search.explore-all-categories')
                            </a>
                        </div>
                    </template>
                </template>

                <template v-if="activeTab == 'customers'">
                    <template v-if="isLoading">
                        <x-admin::shimmer.header.mega-search.customers />
                    </template>

                    <template v-else>
                        <div class="grid max-h-[300px] overflow-y-auto sm:max-h-[400px]">
                            <a
                                :href="'{{ route('admin.customers.customers.view', ':id') }}'.replace(':id', customer.id)"
                                class="grid cursor-pointer place-content-start gap-1 border-b border-slate-300 p-3 last:border-b-0 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-gray-950 sm:gap-1.5 sm:p-4"
                                v-for="customer in searchedResults.customers.data"
                            >
                                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 sm:text-base">
                                    @{{ customer.first_name + ' ' + customer.last_name }}
                                </p>

                                <p class="text-xs text-gray-500 sm:text-sm">
                                    @{{ customer.email }}
                                </p>
                            </a>
                        </div>

                        <div class="flex border-t p-2 dark:border-gray-800 sm:p-3">
                            <a
                                :href="'{{ route('admin.customers.customers.index') }}?search=:query'.replace(':query', searchTerm)"
                                class="cursor-pointer text-xs font-semibold text-blue-600 transition-all hover:underline"
                                v-if="searchedResults.customers.data.length"
                            >
                                @{{ translations.exploreCustomers.replace(':query', searchTerm).replace(':count', searchedResults.customers.total) }}
                            </a>

                            <a
                                href="{{ route('admin.customers.customers.index') }}"
                                class="cursor-pointer text-xs font-semibold text-blue-600 transition-all hover:underline"
                                v-else
                            >
                                @lang('admin::app.components.layouts.header.mega-search.explore-all-customers')
                            </a>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-mega-search', {
            template: '#v-mega-search-template',

            data() {
                return {
                    activeTab: 'products',

                    isDropdownOpen: false,

                    tabs: {
                        products: {
                            key: 'products',
                            title: @json(__('admin::app.components.layouts.header.mega-search.products')),
                            is_active: true,
                            endpoint: "{{ route('admin.catalog.products.search') }}"
                        },

                        orders: {
                            key: 'orders',
                            title: @json(__('admin::app.components.layouts.header.mega-search.orders')),
                            endpoint: "{{ route('admin.sales.orders.search') }}"
                        },

                        categories: {
                            key: 'categories',
                            title: @json(__('admin::app.components.layouts.header.mega-search.categories')),
                            endpoint: "{{ route('admin.catalog.categories.search') }}"
                        },

                        customers: {
                            key: 'customers',
                            title: @json(__('admin::app.components.layouts.header.mega-search.customers')),
                            endpoint: "{{ route('admin.customers.customers.search') }}"
                        }
                    },

                    isLoading: false,

                    searchTerm: '',

                    searchedResults: {
                        products: [],
                        orders: [],
                        categories: [],
                        customers: []
                    },

                    translations: {
                        sku: @json(__('admin::app.components.layouts.header.mega-search.sku')),
                        exploreProducts: @json(__('admin::app.components.layouts.header.mega-search.explore-all-matching-products')),
                        exploreOrders: @json(__('admin::app.components.layouts.header.mega-search.explore-all-matching-orders')),
                        exploreCategories: @json(__('admin::app.components.layouts.header.mega-search.explore-all-matching-categories')),
                        exploreCustomers: @json(__('admin::app.components.layouts.header.mega-search.explore-all-matching-customers')),
                    },
                }
            },

            watch: {
                searchTerm: function(newVal, oldVal) {
                    this.search()
                }
            },

            created() {
                window.addEventListener('click', this.handleFocusOut);
            },

            beforeDestroy() {
                window.removeEventListener('click', this.handleFocusOut);
            },

            methods: {
                search() {
                    if (this.searchTerm.length <= 1) {
                        this.searchedResults[this.activeTab] = [];

                        this.isDropdownOpen = false;

                        return;
                    }

                    this.isDropdownOpen = true;

                    let self = this;

                    this.isLoading = true;

                    this.$axios.get(this.tabs[this.activeTab].endpoint, {
                            params: {query: this.searchTerm}
                        })
                        .then(function(response) {
                            self.searchedResults[self.activeTab] = response.data;

                            self.isLoading = false;
                        })
                        .catch(function (error) {
                        })
                },

                handleFocusOut(e) {
                    if (! this.$el.contains(e.target)) {
                        this.isDropdownOpen = false;
                    }
                },
            }
        });
    </script>

    <script
        type="text/x-template"
        id="v-notification-item-template"
    >
        <div class="group/item">
            <div
                class="relative flex items-center gap-3 notification-card"
                :class="containerClasses"
                style="padding: 12px 14px; margin: 6px 10px; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); transition: all 0.15s ease;"
            >
                <a
                    :href="href"
                    class="flex min-w-0 flex-1 items-center gap-3 text-left"
                    style="padding-right: 32px;"
                >
                    <div
                        class="flex flex-shrink-0 items-center justify-center"
                        :class="iconWrapperClasses"
                        :style="iconWrapperStyle"
                    >
                        <span
                            class="text-base"
                            :class="iconClass"
                        ></span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p
                            class="truncate text-[#111827] dark:text-gray-100"
                            :class="notification.read ? 'font-semibold' : 'font-semibold'"
                            style="font-size: 14px; font-weight: 600; line-height: 1.4;"
                        >
                            @{{ title }}
                        </p>

                        <p
                            class="truncate text-[#6b7280] dark:text-gray-400"
                            style="font-size: 13px; line-height: 1.4; margin-top: 2px;"
                        >
                            @{{ meta }}
                        </p>

                        <p
                            class="text-[#9ca3af] dark:text-gray-500"
                            style="font-size: 12px; line-height: 1.4; margin-top: 2px;"
                        >
                            @{{ timestamp }}
                        </p>
                    </div>
                </a>

                <span
                    v-if="! notification.read"
                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-[#ef4444] group-hover/item:hidden"
                    style="width: 8px; height: 8px; box-shadow: 0 0 0 3px rgba(239,68,68,0.15);"
                ></span>

                <button
                    v-if="! notification.read"
                    type="button"
                    class="absolute right-2.5 top-1/2 hidden h-6 w-6 -translate-y-1/2 items-center justify-center rounded-md text-gray-400 transition-colors duration-100 hover:bg-gray-200 hover:text-gray-600 group-hover/item:inline-flex dark:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                    :class="isBusy ? 'pointer-events-none !inline-flex' : ''"
                    :title="dismissTitle"
                    @click.stop.prevent="$emit('mark-as-read', notification)"
                >
                    <span
                        class="text-xs"
                        :class="isBusy ? 'icon-loader animate-spin' : 'icon-cross'"
                    ></span>
                </button>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-notification-item', {
            template: '#v-notification-item-template',

            props: {
                notification: {
                    type: Object,
                    required: true,
                },

                iconClass: {
                    type: String,
                    default: 'icon-notification',
                },

                iconWrapperClasses: {
                    type: String,
                    default: '',
                },

                iconWrapperStyle: {
                    type: String,
                    default: 'width: 40px; height: 40px; border-radius: 50%; background: #f3f4f6;',
                },

                title: {
                    type: String,
                    default: '',
                },

                meta: {
                    type: String,
                    default: '',
                },

                timestamp: {
                    type: String,
                    default: '',
                },

                href: {
                    type: String,
                    required: true,
                },

                isBusy: {
                    type: Boolean,
                    default: false,
                },

                dismissTitle: {
                    type: String,
                    default: 'Mark as read',
                },
            },

            computed: {
                containerClasses() {
                    return this.notification.read
                        ? 'bg-[#fafafa] hover:bg-[#f8fafc] hover:-translate-y-px dark:bg-gray-800/50 dark:hover:bg-gray-800'
                        : 'bg-[#fafafa] hover:bg-[#f8fafc] hover:-translate-y-px dark:bg-gray-800/50 dark:hover:bg-gray-800';
                },
            },
        });
    </script>

    <script
        type="text/x-template"
        id="v-notifications-template"
    >
        <div class="relative inline-flex shrink-0" ref="notificationsRoot">
            <div
                class="relative cursor-pointer"
                @click.stop="toggleNotifications()"
            >
                <slot>
                    <span class="group relative flex items-center justify-center rounded-xl p-2.5 text-gray-500 transition-all duration-300 hover:bg-gradient-to-br hover:from-rose-50 hover:to-pink-50 hover:text-rose-500 hover:shadow-sm active:scale-95 dark:text-gray-400 dark:hover:bg-gray-800/80 dark:hover:text-rose-400">
                        <span
                            class="icon-notification text-xl transition-transform duration-300 group-hover:rotate-12 sm:text-2xl"
                            title="@lang('admin::app.components.layouts.header.notifications')"
                        ></span>
                    </span>
                </slot>

                <span
                    v-if="totalUnRead"
                    class="pointer-events-none absolute flex items-center justify-center text-white"
                    style="top: -5px; right: -6px; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px; background: #ef4444; font-size: 11px; font-weight: 600; border: 2px solid white;"
                >
                    @{{ totalUnRead }}
                </span>
            </div>

            <transition
                enter-active-class="transform transition duration-150 ease-out"
                enter-from-class="-translate-y-1.5 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transform transition duration-150 ease-in"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="-translate-y-1.5 opacity-0"
            >
                <div
                    v-if="isNotificationsOpen"
                    class="absolute z-[1000] w-[380px] max-w-[calc(100vw-24px)] overflow-hidden bg-white dark:bg-gray-900"
                    style="top: 46px; right: 0; border-radius: 16px; border: 1px solid #eee; box-shadow: 0 10px 30px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.04);"
                    aria-label="@lang('admin::app.components.layouts.header.notifications')"
                    @click.stop
                >
                    <div class="w-full">
                        <!-- Header -->
                        <div class="flex items-center justify-between gap-3 dark:border-gray-800" style="padding: 16px; border-bottom: 1px solid #f1f5f9; background: #fafafa;">
                            <div class="min-w-0 flex-1">
                                <p class="text-gray-900 dark:text-gray-100" style="font-size: 15px; font-weight: 600;">
                                    Уведомления
                                </p>
                            </div>

                            <button
                                v-if="notifications.length && totalUnRead"
                                type="button"
                                class="group inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-[#f4f2ff] text-[#8b80f9] transition-all duration-150 hover:bg-[#ebe7ff] hover:text-[#786cf6] dark:bg-gray-800 dark:text-violet-300 dark:hover:bg-gray-700"
                                :class="isMarkingAllRead ? 'pointer-events-none opacity-70' : ''"
                                :title="readAllTitleText"
                                @click.stop="readAll()"
                            >
                                <span v-if="isMarkingAllRead" class="icon-loader animate-spin text-base"></span>

                                <span v-else class="relative block h-4 w-4">
                                    <span class="icon-done absolute -left-0.5 top-0 text-[11px]"></span>
                                    <span class="icon-done absolute left-[6px] top-0 text-[11px]"></span>
                                </span>
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="overflow-y-auto py-1 notifications-scroll" style="max-height: 520px;">
                            <transition-group
                                tag="div"
                                enter-from-class="translate-y-2 opacity-0"
                                enter-active-class="transform transition duration-200 ease-out"
                                enter-to-class="translate-y-0 opacity-100"
                                leave-from-class="translate-y-0 opacity-100"
                                leave-active-class="transform transition duration-200 ease-in"
                                leave-to-class="-translate-y-1 opacity-0"
                                move-class="transition duration-200"
                                class="grid"
                            >
                                <v-notification-item
                                    v-for="notification in notifications"
                                    :key="notification.id"
                                    :notification="notification"
                                    :icon-class="getNotificationIcon(notification)"
                                    :icon-wrapper-classes="getNotificationIconWrapperClasses(notification)"
                                    :icon-wrapper-style="getNotificationIconBgStyle(notification)"
                                    :title="getNotificationTitle(notification)"
                                    :meta="getNotificationMeta(notification)"
                                    :timestamp="getNotificationTimestamp(notification)"
                                    :href="getNotificationHref(notification)"
                                    :is-busy="Boolean(processingNotificationIds[notification.id])"
                                    :dismiss-title="singleReadTitleText"
                                    @mark-as-read="markAsRead"
                                ></v-notification-item>
                            </transition-group>

                            <div
                                v-if="! notifications.length && ! isLoading"
                                class="flex min-h-[10rem] flex-col items-center justify-center bg-[#fafafa] px-6 text-center dark:bg-gray-800/50"
                                style="margin: 6px 10px; border-radius: 12px;"
                            >
                                <span class="icon-notification text-3xl text-gray-300 dark:text-gray-600"></span>

                                <p class="mt-3 text-[13px] font-medium text-gray-500 dark:text-gray-400">
                                    Нет уведомлений
                                </p>
                            </div>

                            <div
                                v-if="isLoading"
                                class="py-2"
                            >
                                <div
                                    v-for="index in 4"
                                    :key="`skeleton-${index}`"
                                    class="flex items-center gap-3 bg-[#fafafa] dark:bg-gray-800/50"
                                    style="padding: 12px 14px; margin: 6px 10px; border-radius: 12px;"
                                >
                                    <div class="flex-shrink-0 animate-pulse rounded-full bg-gray-200 dark:bg-gray-700" style="width: 40px; height: 40px;"></div>
                                    <div class="flex-1">
                                        <div class="h-3.5 w-3/4 animate-pulse rounded bg-gray-200 dark:bg-gray-700"></div>
                                        <div class="mt-2 h-3 w-1/2 animate-pulse rounded bg-gray-100 dark:bg-gray-700/60"></div>
                                        <div class="mt-1.5 h-2.5 w-1/4 animate-pulse rounded bg-gray-100 dark:bg-gray-700/40"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </script>

    <script type="module">
        app.component('v-notifications', {
            template: '#v-notifications-template',

                props: [
                    'getReadAllUrl',
                    'readAllTitle',
                ],

                data() {
                    return {
                        notifications: [],

                        isNotificationsOpen: false,

                        totalUnRead: 0,

                        isLoading: true,

                        isMarkingAllRead: false,

                        processingNotificationIds: {},

                        orderTypeMessages: @json(
                            \Webkul\Sales\Models\OrderStatus::orderBy('sort_order')->get()->mapWithKeys(function($status) {
                                $translationKey = 'admin::app.notifications.order-status-messages.' . $status->code;
                                $translation = trans($translationKey);
                                return [$status->code => $translation !== $translationKey ? $translation : $status->name];
                            })->toArray()
                        ),

                        singleReadTitleText: 'Отметить как прочитанное',

                        readAllTitleText: @json(__('admin::app.notifications.read-all')),
                    }
                },

                computed: {
                    notificationStatusIcon() {
                        return {
                            pending: 'icon-information',
                            pending_payment: 'icon-information',
                            preparing: 'icon-information',
                            ready: 'icon-cart',
                            closed: 'icon-cross',
                            completed: 'icon-cart',
                            canceled: 'icon-cross',
                            processing: 'icon-cart',
                            failed: 'icon-cross',
                            fraud: 'icon-cross',
                        };
                    },

                    notificationStatusWrapperClasses() {
                        return {
                            pending: 'text-[#D97706] dark:text-amber-300',
                            pending_payment: 'text-[#D97706] dark:text-amber-300',
                            preparing: 'text-[#D97706] dark:text-amber-300',
                            ready: 'text-[#059669] dark:text-emerald-300',
                            closed: 'text-[#DC2626] dark:text-red-300',
                            completed: 'text-[#059669] dark:text-emerald-300',
                            canceled: 'text-[#DC2626] dark:text-red-300',
                            processing: 'text-[#059669] dark:text-emerald-300',
                            failed: 'text-[#DC2626] dark:text-red-300',
                            fraud: 'text-[#DC2626] dark:text-red-300',
                        };
                    },

                    notificationStatusBgStyles() {
                        return {
                            pending: 'background: linear-gradient(135deg, #FEF3C7, #FDE68A);',
                            pending_payment: 'background: linear-gradient(135deg, #FEF3C7, #FDE68A);',
                            preparing: 'background: linear-gradient(135deg, #FEF3C7, #FDE68A);',
                            ready: 'background: linear-gradient(135deg, #DCFCE7, #BBF7D0);',
                            closed: 'background: linear-gradient(135deg, #FEE2E2, #FECACA);',
                            completed: 'background: linear-gradient(135deg, #DCFCE7, #BBF7D0);',
                            canceled: 'background: linear-gradient(135deg, #FEE2E2, #FECACA);',
                            processing: 'background: linear-gradient(135deg, #DCFCE7, #BBF7D0);',
                            failed: 'background: linear-gradient(135deg, #FEE2E2, #FECACA);',
                            fraud: 'background: linear-gradient(135deg, #FEE2E2, #FECACA);',
                        };
                    },

                    headerSubtitle() {
                        if (! this.notifications.length) {
                            return 'Новые события появятся здесь';
                        }

                        if (! this.totalUnRead) {
                            return 'Все просмотрено';
                        }

                        return `${this.totalUnRead} непрочитанных`;
                    },
                },

                mounted() {
                    this.getNotification();

                    window.addEventListener('click', this.handleNotificationsFocusOut);
                },

                beforeDestroy() {
                    window.removeEventListener('click', this.handleNotificationsFocusOut);
                },

                methods: {
                    toggleNotifications() {
                        this.isNotificationsOpen = ! this.isNotificationsOpen;
                    },

                    closeNotifications() {
                        this.isNotificationsOpen = false;
                    },

                    handleNotificationsFocusOut(event) {
                        if (! this.$refs.notificationsRoot || ! this.$refs.notificationsRoot.contains(event.target)) {
                            this.closeNotifications();
                        }
                    },

                    getOrderData(notification) {
                        return notification && notification.order
                            ? notification.order
                            : {};
                    },

                    normalizeNotification(notification) {
                        return {
                            ...notification,
                            read: Boolean(notification.read),
                        };
                    },

                    getNotification() {
                        this.isLoading = true;

                        this.$axios.get('{{ route('admin.notification.get_notification') }}', {
                                params: {
                                    limit: 50,
                                }
                            })
                            .then((response) => {
                                this.notifications = response.data.search_results.data.map((notification) => this.normalizeNotification(notification));

                                this.totalUnRead = response.data.total_unread;

                                this.isLoading = false;
                            })
                            .catch(error => {
                                this.isLoading = false;
                            })
                    },

                    getNotificationHref(notification) {
                        return '{{ route('admin.notification.viewed_notification', ':orderId') }}'.replace(':orderId', notification.order_id);
                    },

                    getNotificationIcon(notification) {
                        const order = this.getOrderData(notification);

                        return this.notificationStatusIcon[order.status] || 'icon-notification text-gray-500 dark:!text-gray-400';
                    },

                    getNotificationIconWrapperClasses(notification) {
                        const order = this.getOrderData(notification);

                        return this.notificationStatusWrapperClasses[order.status] || 'text-gray-500 dark:text-gray-400';
                    },

                    getNotificationIconBgStyle(notification) {
                        const order = this.getOrderData(notification);
                        const bg = this.notificationStatusBgStyles[order.status] || 'background: #f3f4f6;';

                        return 'width: 40px; height: 40px; border-radius: 50%; box-shadow: inset 0 0 0 1px rgba(255,255,255,0.6); ' + bg;
                    },

                    getNotificationTitle(notification) {
                        const order = this.getOrderData(notification);
                        const orderNumber = order.increment_id || order.id || notification.order_id;
                        const statusLabel = this.orderTypeMessages[order.status] || order.status || 'Обновление заказа';

                        return `${statusLabel} #${orderNumber}`;
                    },

                    getNotificationMeta(notification) {
                        const order = this.getOrderData(notification);
                        const amount = this.formatPrice(order.grand_total, order.order_currency_code);
                        const customerName = [order.customer_first_name, order.customer_last_name]
                            .filter(Boolean)
                            .join(' ') || 'Гость';

                        return `${amount} • ${customerName}`;
                    },

                    getNotificationTimestamp(notification) {
                        const order = this.getOrderData(notification);

                        return order.datetime || '';
                    },

                    formatPrice(amount, currencyCode) {
                        const numericAmount = Number(amount || 0);

                        try {
                            return new Intl.NumberFormat(document.documentElement.lang || undefined, {
                                style: 'currency',
                                currency: currencyCode || 'USD',
                                maximumFractionDigits: 0,
                            }).format(numericAmount);
                        } catch (error) {
                            return `${numericAmount} ${currencyCode || ''}`.trim();
                        }
                    },

                    markAsRead(notification) {
                        if (notification.read || this.processingNotificationIds[notification.id]) {
                            return;
                        }

                        this.processingNotificationIds = {
                            ...this.processingNotificationIds,
                            [notification.id]: true,
                        };

                        this.$axios.post('{{ route('admin.notification.read', ':notificationId') }}'.replace(':notificationId', notification.id))
                            .then((response) => {
                                this.notifications = this.notifications.filter((item) => item.id !== notification.id);

                                this.totalUnRead = response.data.total_unread;

                                if (! this.notifications.length) {
                                    this.closeNotifications();
                                }
                            })
                            .catch((error) => {})
                            .finally(() => {
                                const processingNotificationIds = {
                                    ...this.processingNotificationIds,
                                };

                                delete processingNotificationIds[notification.id];

                                this.processingNotificationIds = processingNotificationIds;
                            });
                    },

                    readAll() {
                        if (! this.notifications.length || this.isMarkingAllRead) {
                            return;
                        }

                        this.isMarkingAllRead = true;

                        this.$axios.post('{{ route('admin.notification.read_all') }}')
                            .then((response) => {
                                this.notifications = this.notifications.map((notification) => ({
                                    ...notification,
                                    read: true,
                                }));

                                this.totalUnRead = response.data.total_unread;

                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.success_message });
                            })
                            .catch((error) => {})
                            .finally(() => {
                                this.isMarkingAllRead = false;
                            });
                    },
            },
        });
    </script>

    <script
        type="text/x-template"
        id="v-dark-template"
    >
        <div class="flex">
            <span
                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                :class="[isDarkMode ? 'icon-light' : 'icon-dark']"
                @click="toggle"
            ></span>
        </div>
    </script>

    <script type="module">
        app.component('v-dark', {
            template: '#v-dark-template',

            data() {
                return {
                    isDarkMode: {{ request()->cookie('dark_mode') ?? 0 }},

                    logo: "{{ bagisto_asset('images/logo.svg') }}",

                    dark_logo: "{{ bagisto_asset('images/dark-logo.svg') }}",
                };
            },

            methods: {
                toggle() {
                    this.isDarkMode = parseInt(this.isDarkModeCookie()) ? 0 : 1;

                    var expiryDate = new Date();

                    expiryDate.setMonth(expiryDate.getMonth() + 1);

                    document.cookie = 'dark_mode=' + this.isDarkMode + '; path=/; expires=' + expiryDate.toGMTString();

                    document.documentElement.classList.toggle('dark', this.isDarkMode === 1);

                    if (this.isDarkMode) {
                        this.$emitter.emit('change-theme', 'dark');

                        document.getElementById('logo-image').src = this.dark_logo;
                    } else {
                        this.$emitter.emit('change-theme', 'light');

                        document.getElementById('logo-image').src = this.logo;
                    }
                },

                isDarkModeCookie() {
                    const cookies = document.cookie.split(';');

                    for (const cookie of cookies) {
                        const [name, value] = cookie.trim().split('=');

                        if (name === 'dark_mode') {
                            return value;
                        }
                    }

                    return 0;
                },
            },
        });
    </script>
@endPushOnce

@pushOnce('styles')
    <style>
        .notifications-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .notifications-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .notifications-scroll::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 6px;
        }
        .notifications-scroll::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        .notifications-scroll {
            scrollbar-width: thin;
            scrollbar-color: #d1d5db transparent;
        }
        .notification-card:hover {
            box-shadow: 0 4px 10px rgba(0,0,0,0.06) !important;
        }
    </style>
@endPushOnce
