<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.channels.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); box-shadow: 0 4px 15px rgba(99,102,241,0.3); min-width:44px;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.channels.index.title')
                </p>
                <p class="text-xs text-gray-400">Каналы продаж</p>
            </div>
        </div>
        
        <div class="flex items-center gap-x-2.5">
            <!-- Create New Channel Button -->
            @if (bouncer()->hasPermission('settings.channels.create'))
                <a 
                    href="{{ route('admin.settings.channels.create') }}"
                    class="primary-button"
                >
                    @lang('admin::app.settings.channels.index.create-btn')
                </a>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.settings.channels.list.before') !!}
    
    <x-admin::datagrid :src="route('admin.settings.channels.index')" />

    {!! view_render_event('bagisto.settings.channels.list.after') !!}

</x-admin::layouts>