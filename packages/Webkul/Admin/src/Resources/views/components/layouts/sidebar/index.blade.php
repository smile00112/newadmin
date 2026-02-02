<!-- Larkon-style Sidebar with Vue Component -->
<!-- Static sidebar placeholder (shows immediately while Vue loads) -->
<div id="sidebar-placeholder" class="fixed top-14 z-[1000] h-full bg-white dark:bg-gray-900 transition-all duration-300 max-lg:hidden border-r border-gray-200/60 dark:border-gray-800/60 {{ request()->cookie('sidebar_collapsed') ?? 0 ? 'w-[70px]' : 'w-[260px]' }}">
    <div class="h-[calc(100vh-120px)] overflow-y-auto overflow-x-hidden custom-scroll px-3 pt-4">
        @if (!(request()->cookie('sidebar_collapsed') ?? 0))
        <div class="mb-4">
            <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                Основное
            </p>
        </div>
        @endif
        
        <nav class="grid w-full gap-0.5">
            @foreach (menu()->getItems('admin') as $index => $menuItem)
                @php
                    $menuKey = $menuItem->getKey();
                    $isGeneral = in_array($menuKey, ['dashboard', 'sales', 'catalog']);
                    $isUsers = in_array($menuKey, ['customers', 'marketing']);
                    $isCollapsed = request()->cookie('sidebar_collapsed') ?? 0;
                @endphp
                
                @if ($index > 0 && !$isCollapsed)
                    @php
                        $prevItem = menu()->getItems('admin')[$index - 1] ?? null;
                        $prevKey = $prevItem ? $prevItem->getKey() : '';
                        $prevIsGeneral = in_array($prevKey, ['dashboard', 'sales', 'catalog']);
                        $prevIsUsers = in_array($prevKey, ['customers', 'marketing']);
                    @endphp
                    
                    @if (!$isGeneral && $prevIsGeneral)
                        <div class="my-3">
                            <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                Пользователи
                            </p>
                        </div>
                    @elseif (!$isUsers && !$isGeneral && $prevIsUsers)
                        <div class="my-3">
                            <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                Настройки
                            </p>
                        </div>
                    @endif
                @endif
                
                <div class="menu-item">
                    @if ($menuItem->haveChildren())
                        @php $hasActiveChild = collect($menuItem->getChildren())->contains(fn($child) => $child->isActive()); @endphp
                        <div class="flex items-center gap-3 px-3 py-2.5 cursor-pointer rounded-xl transition-all duration-200 group {{ $hasActiveChild ? 'bg-gradient-to-r from-violet-500 to-purple-500 shadow-lg shadow-violet-500/25' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                            <span class="{{ $menuItem->getIcon() }} text-lg transition-colors {{ $hasActiveChild ? 'text-white' : 'text-gray-500 dark:text-gray-400' }}"></span>
                            @if (!$isCollapsed)
                            <span class="flex-1 text-sm font-medium transition-colors {{ $hasActiveChild ? 'text-white' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $menuItem->getName() }}
                            </span>
                            <svg class="w-4 h-4 transition-transform duration-200 {{ $hasActiveChild ? 'rotate-90 text-white' : 'text-gray-400' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            @endif
                        </div>
                        
                        @if (!$isCollapsed && $hasActiveChild)
                        <div class="mt-1 ml-4 pl-4 border-l-2 border-gray-100 dark:border-gray-800 space-y-0.5">
                            @foreach ($menuItem->getChildren() as $subMenuItem)
                                <a href="{{ $subMenuItem->getUrl() }}"
                                   class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-all duration-200 {{ $subMenuItem->isActive() ? 'text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-900/20 font-medium' : 'text-gray-600 dark:text-gray-400 hover:text-violet-600 hover:bg-gray-50' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $subMenuItem->isActive() ? 'bg-violet-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                    {{ $subMenuItem->getName() }}
                                </a>
                            @endforeach
                        </div>
                        @endif
                    @else
                        <a href="{{ $menuItem->getUrl() }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ $menuItem->isActive() == 'active' ? 'bg-gradient-to-r from-violet-500 to-purple-500 shadow-lg shadow-violet-500/25' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                            <span class="{{ $menuItem->getIcon() }} text-lg {{ $menuItem->isActive() == 'active' ? 'text-white' : 'text-gray-500 dark:text-gray-400' }}"></span>
                            @if (!$isCollapsed)
                            <span class="text-sm font-medium {{ $menuItem->isActive() == 'active' ? 'text-white' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $menuItem->getName() }}
                            </span>
                            @endif
                        </a>
                    @endif
                </div>
            @endforeach
        </nav>
    </div>
</div>

<!-- Vue component replaces placeholder when loaded -->
<v-sidebar-menu></v-sidebar-menu>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-sidebar-menu-template"
    >
        <div class="fixed top-14 z-[1000] h-full bg-white dark:bg-gray-900 transition-all duration-300 max-lg:hidden border-r border-gray-200/60 dark:border-gray-800/60"
             :class="[isCollapsed ? 'w-[70px]' : 'w-[260px]']">
            
            <div class="h-[calc(100vh-120px)] overflow-y-auto overflow-x-hidden custom-scroll px-3 pt-4">
                <!-- GENERAL Section -->
                <div class="mb-4" v-if="!isCollapsed">
                    <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        Основное
                    </p>
                </div>
                
                <nav class="grid w-full gap-0.5">
                    @foreach (menu()->getItems('admin') as $index => $menuItem)
                        @php
                            $menuKey = $menuItem->getKey();
                            $isGeneral = in_array($menuKey, ['dashboard', 'sales', 'catalog']);
                            $isUsers = in_array($menuKey, ['customers', 'marketing']);
                            $isSettings = in_array($menuKey, ['settings', 'configuration', 'gdpr', 'data-transfer', 'magic-ai', 'cms', 'reporting']);
                        @endphp
                        
                        @if ($index > 0)
                            @php
                                $prevItem = menu()->getItems('admin')[$index - 1] ?? null;
                                $prevKey = $prevItem ? $prevItem->getKey() : '';
                                $prevIsGeneral = in_array($prevKey, ['dashboard', 'sales', 'catalog']);
                                $prevIsUsers = in_array($prevKey, ['customers', 'marketing']);
                            @endphp
                            
                            @if (!$isGeneral && $prevIsGeneral)
                                <div class="my-3" v-if="!isCollapsed">
                                    <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                        Пользователи
                                    </p>
                                </div>
                            @elseif (!$isUsers && !$isGeneral && $prevIsUsers)
                                <div class="my-3" v-if="!isCollapsed">
                                    <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                        Настройки
                                    </p>
                                </div>
                            @endif
                        @endif
                        
                        <div class="menu-item">
                            @if ($menuItem->haveChildren())
                                <!-- Menu with children - expandable -->
                                <div 
                                    class="flex items-center gap-3 px-3 py-2.5 cursor-pointer rounded-xl transition-all duration-200 group"
                                    :class="[
                                        expandedMenus.includes('{{ $menuKey }}') 
                                            ? 'bg-gradient-to-r from-violet-500 to-purple-500 shadow-lg shadow-violet-500/25' 
                                            : 'hover:bg-gray-50 dark:hover:bg-gray-800/50'
                                    ]"
                                    @click="toggleMenu('{{ $menuKey }}')"
                                >
                                    <span class="{{ $menuItem->getIcon() }} text-lg transition-colors"
                                          :class="[
                                              expandedMenus.includes('{{ $menuKey }}')
                                                  ? 'text-white' 
                                                  : 'text-gray-500 dark:text-gray-400 group-hover:text-violet-600 dark:group-hover:text-violet-400'
                                          ]"></span>
                                    
                                    <span v-if="!isCollapsed" 
                                          class="flex-1 text-sm font-medium transition-colors"
                                          :class="[
                                              expandedMenus.includes('{{ $menuKey }}')
                                                  ? 'text-white' 
                                                  : 'text-gray-700 dark:text-gray-300 group-hover:text-violet-600 dark:group-hover:text-violet-400'
                                          ]">
                                        {{ $menuItem->getName() }}
                                    </span>
                                    
                                    <svg v-if="!isCollapsed" 
                                         class="w-4 h-4 transition-transform duration-200"
                                         :class="[
                                             expandedMenus.includes('{{ $menuKey }}') ? 'rotate-90 text-white' : 'text-gray-400'
                                         ]"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                                
                                <!-- Submenu -->
                                <div v-if="!isCollapsed" 
                                     v-show="expandedMenus.includes('{{ $menuKey }}')"
                                     class="mt-1 ml-4 pl-4 border-l-2 border-gray-100 dark:border-gray-800 space-y-0.5">
                                    @foreach ($menuItem->getChildren() as $subMenuItem)
                                        <a href="{{ $subMenuItem->getUrl() }}"
                                           class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-all duration-200 {{ $subMenuItem->isActive() ? 'text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-900/20 font-medium' : 'text-gray-600 dark:text-gray-400 hover:text-violet-600 hover:bg-gray-50 dark:hover:bg-gray-800/50 dark:hover:text-violet-400' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $subMenuItem->isActive() ? 'bg-violet-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                            {{ $subMenuItem->getName() }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <!-- Simple menu item without children -->
                                <a href="{{ $menuItem->getUrl() }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ $menuItem->isActive() == 'active' ? 'bg-gradient-to-r from-violet-500 to-purple-500 shadow-lg shadow-violet-500/25' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                                    <span class="{{ $menuItem->getIcon() }} text-lg {{ $menuItem->isActive() == 'active' ? 'text-white' : 'text-gray-500 dark:text-gray-400 group-hover:text-violet-600 dark:group-hover:text-violet-400' }}"></span>
                                    
                                    <span v-if="!isCollapsed" 
                                          class="text-sm font-medium {{ $menuItem->isActive() == 'active' ? 'text-white' : 'text-gray-700 dark:text-gray-300 group-hover:text-violet-600 dark:group-hover:text-violet-400' }}">
                                        {{ $menuItem->getName() }}
                                    </span>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </nav>
            </div>

            <!-- Collapse Button -->
            <div class="absolute bottom-0 left-0 right-0 border-t border-gray-200/60 dark:border-gray-800/60 bg-white dark:bg-gray-900">
                <div class="flex items-center justify-center p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                     @click="toggleCollapse">
                    <svg class="w-5 h-5 text-gray-500 transition-transform duration-300"
                         :class="[isCollapsed ? 'rotate-180' : '']"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                    <span v-if="!isCollapsed" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Свернуть</span>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-sidebar-menu', {
            template: '#v-sidebar-menu-template',

            data() {
                return {
                    isCollapsed: {{ request()->cookie('sidebar_collapsed') ?? 0 }},
                    expandedMenus: @json(
                        collect(menu()->getItems('admin'))
                            ->filter(fn($item) => $item->isActive() === 'active' && $item->haveChildren())
                            ->map(fn($item) => $item->getKey())
                            ->values()
                            ->toArray()
                    ),
                }
            },

            mounted() {
                // Скрываем статический placeholder, показываем Vue sidebar
                const placeholder = document.getElementById('sidebar-placeholder');
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
                
                // Автоматически раскрываем меню с активными элементами
                this.initExpandedMenus();
            },

            methods: {
                initExpandedMenus() {
                    // Проверяем все меню и раскрываем те, где есть активные подпункты
                    @foreach (menu()->getItems('admin') as $menuItem)
                        @if ($menuItem->haveChildren())
                            @foreach ($menuItem->getChildren() as $subMenuItem)
                                @if ($subMenuItem->isActive())
                                    if (!this.expandedMenus.includes('{{ $menuItem->getKey() }}')) {
                                        this.expandedMenus.push('{{ $menuItem->getKey() }}');
                                    }
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                },

                toggleMenu(key) {
                    const index = this.expandedMenus.indexOf(key);
                    if (index > -1) {
                        this.expandedMenus.splice(index, 1);
                    } else {
                        this.expandedMenus.push(key);
                    }
                },

                toggleCollapse() {
                    this.isCollapsed = !this.isCollapsed;
                    
                    var expiryDate = new Date();
                    expiryDate.setMonth(expiryDate.getMonth() + 1);
                    document.cookie = 'sidebar_collapsed=' + (this.isCollapsed ? 1 : 0) + '; path=/; expires=' + expiryDate.toGMTString();
                    
                    this.$root.$refs.appLayout.classList.toggle('sidebar-collapsed');
                },
            },
        });
    </script>
    
    <style>
        .custom-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 4px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }
        .dark .custom-scroll::-webkit-scrollbar-thumb {
            background: #374151;
        }
        .dark .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #4b5563;
        }
    </style>
@endpushOnce
