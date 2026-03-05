<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.cms.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%); box-shadow: 0 4px 15px rgba(100,116,139,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.cms.index.title')
                </p>
                <p class="text-xs text-gray-400">Страницы CMS</p>
            </div>
        </div>

        <div class="flex items-center gap-x-2.5">
            <!-- Export Modal -->
            <x-admin::datagrid.export :src="route('admin.cms.index')" />

            <!-- Create New Pages Button -->
            @if (bouncer()->hasPermission('cms.create'))
                <a
                    href="{{ route('admin.cms.create') }}"
                    class="primary-button"
                >
                    @lang('admin::app.cms.index.create-btn')
                </a>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.admin.cms.pages.list.before') !!}

    <x-admin::datagrid :src="route('admin.cms.index')" />
    
    {!! view_render_event('bagisto.admin.cms.pages.list.after') !!}

</x-admin::layouts>
