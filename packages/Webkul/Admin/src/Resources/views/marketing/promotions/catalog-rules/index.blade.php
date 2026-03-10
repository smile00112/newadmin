<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.marketing.promotions.catalog-rules.index.title')
    </x-slot>

    <div class="mt-3 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 15px rgba(16,185,129,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.marketing.promotions.catalog-rules.index.title')
                </p>
                <p class="text-xs text-gray-400">Правила каталога</p>
            </div>
        </div>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('marketing.promotions.catalog_rules.create'))
                <a 
                    href="{{ route('admin.marketing.promotions.catalog_rules.create') }}"
                    class="primary-button"
                >
                    @lang('admin::app.marketing.promotions.catalog-rules.index.create-btn')
                </a>
            @endif
        </div>
    </div>
    
    {!! view_render_event('bagisto.admin.marketing.promotions.catalog_rules.list.before') !!}

    <x-admin::datagrid :src="route('admin.marketing.promotions.catalog_rules.index')" />

    {!! view_render_event('bagisto.admin.marketing.promotions.catalog_rules.list.after') !!}

</x-admin::layouts>