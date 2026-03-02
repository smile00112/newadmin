@php
    $tabs = menu()->getCurrentActiveMenu('admin')?->getChildren();
@endphp

@if (
    $tabs
    && $tabs->isNotEmpty()
)
    <div class="tabs">
        <div class="mb-6 flex gap-2 border-b border-gray-100 pt-2 dark:border-gray-800 max-sm:hidden">
            @foreach ($tabs as $tab)
                <a href="{{ $tab->getUrl() }}">
                    <div class="{{ $tab->isActive() ? "-mb-px border-indigo-500 border-b-2 text-indigo-600 dark:text-indigo-400" : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }} pb-3 px-4 text-sm font-medium cursor-pointer transition-all duration-200 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 rounded-t-lg">
                        {{ $tab->getName() }}
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
