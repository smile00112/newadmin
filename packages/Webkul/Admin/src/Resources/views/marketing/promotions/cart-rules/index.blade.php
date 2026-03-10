<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.marketing.promotions.cart-rules.index.title')
    </x-slot>

    <div class="mt-3 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 4px 15px rgba(245,158,11,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" /></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.marketing.promotions.cart-rules.index.title')
                </p>
                <p class="text-xs text-gray-400">Правила корзины</p>
            </div>
        </div>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('marketing.promotions.cart_rules.create'))
                <a 
                    href="{{ route('admin.marketing.promotions.cart_rules.create') }}"
                    class="primary-button"
                >
                    @lang('admin::app.marketing.promotions.cart-rules.index.create-btn')
                </a>
            @endif
        </div>
    </div>
    
    {!! view_render_event('bagisto.admin.marketing.promotions.cart-rules.list.before') !!}

    <x-admin::datagrid :src="route('admin.marketing.promotions.cart_rules.index')" />

    {!! view_render_event('bagisto.admin.marketing.promotions.cart-rules.list.after') !!}

</x-admin::layouts>