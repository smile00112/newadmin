{{-- Компонент выбора типа товара --}}
<v-product-type-selector></v-product-type-selector>

@pushOnce('scripts')
    <script type="text/x-template" id="v-product-type-selector-template">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-white to-gray-50 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300" data-block-id="product-type-selector">
            {{-- Декоративный фон --}}
            <div class="absolute top-0 right-0 w-32 h-32 opacity-[0.03]" :style="{ background: `radial-gradient(circle, ${gradientColor} 0%, transparent 70%)` }"></div>
            
            <div class="relative p-5">
                <div class="flex items-center gap-4">
                    {{-- Иконка типа --}}
                    <div 
                        class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300 hover:scale-105"
                        :style="{ 
                            background: `linear-gradient(135deg, ${typeColorStart[selectedType]} 0%, ${typeColorEnd[selectedType]} 100%)`,
                            boxShadow: `0 8px 24px -4px ${shadowColor}` 
                        }"
                    >
                        <component :is="getTypeIcon(selectedType)" class="w-7 h-7 text-white drop-shadow-sm"></component>
                    </div>
                    
                    {{-- Селект с кастомным стилем --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Тип</span>
                            <div class="flex-1 h-px bg-gradient-to-r from-gray-200 to-transparent"></div>
                        </div>
                        <input type="hidden" name="type" :value="selectedType">
                        <div class="relative">
                            <select 
                                v-model="selectedType"
                                @change="onTypeChange"
                                class="w-full appearance-none pl-4 pr-10 py-3 bg-white border-2 border-gray-100 rounded-xl text-base font-semibold text-gray-800 focus:outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-100 transition-all duration-200 cursor-pointer hover:border-gray-300"
                            >
                                <option v-for="productType in availableTypes" :key="productType.key" :value="productType.key">
                                    @{{ productType.name }}
                                </option>
                            </select>
                            {{-- Кастомная стрелка --}}
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Уведомление о переключении --}}
                <transition name="slide-fade">
                    <div v-if="showWarning" class="mt-4 p-4 rounded-xl" style="background: linear-gradient(to right, #fffbeb, #fff7ed); border: 1px solid rgba(251, 191, 36, 0.3);">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%); box-shadow: 0 8px 16px -4px rgba(251, 191, 36, 0.4);">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold" style="color: #78350f;">Тип будет изменён</p>
                                <p class="text-xs mt-0.5" style="color: #b45309;">Сохраните товар для применения</p>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </script>
    
    <script type="module">
        app.component('v-product-type-selector', {
            template: '#v-product-type-selector-template',
            
            data() {
                return {
                    selectedType: '{{ $product->type }}',
                    originalType: '{{ $product->type }}',
                    showWarning: false,
                    availableTypes: [
                        { key: 'simple', name: 'Простой товар' },
                        { key: 'configurable', name: 'Конфигурируемый' },
                        { key: 'configurable_constructor', name: 'Конфигурируемый + Конструктор' },
                        { key: 'grouped', name: 'Группа товаров' },
                        { key: 'bundle', name: 'Комплект' },
                        { key: 'constructor', name: 'Конструктор' },
                        { key: 'ingredient', name: 'Ингредиент' },
                    ],
                    typeColorStart: {
                        simple: '#8b5cf6',
                        configurable: '#3b82f6',
                        configurable_constructor: '#a855f7',
                        grouped: '#10b981',
                        bundle: '#f59e0b',
                        constructor: '#ec4899',
                        ingredient: '#06b6d4',
                    },
                    typeColorEnd: {
                        simple: '#7c3aed',
                        configurable: '#06b6d4',
                        configurable_constructor: '#ec4899',
                        grouped: '#14b8a6',
                        bundle: '#f97316',
                        constructor: '#f43f5e',
                        ingredient: '#3b82f6',
                    },
                    typeShadows: {
                        simple: 'rgba(139, 92, 246, 0.35)',
                        configurable: 'rgba(59, 130, 246, 0.35)',
                        configurable_constructor: 'rgba(168, 85, 247, 0.35)',
                        grouped: 'rgba(16, 185, 129, 0.35)',
                        bundle: 'rgba(245, 158, 11, 0.35)',
                        constructor: 'rgba(236, 72, 153, 0.35)',
                        ingredient: 'rgba(6, 182, 212, 0.35)',
                    },
                    typeColors: {
                        simple: '#8b5cf6',
                        configurable: '#3b82f6',
                        configurable_constructor: '#a855f7',
                        grouped: '#10b981',
                        bundle: '#f59e0b',
                        constructor: '#ec4899',
                        ingredient: '#06b6d4',
                    }
                };
            },
            
            computed: {
                shadowColor() {
                    return this.typeShadows[this.selectedType] || 'rgba(139, 92, 246, 0.35)';
                },
                gradientColor() {
                    return this.typeColors[this.selectedType] || '#8b5cf6';
                }
            },
            
            methods: {
                onTypeChange() {
                    this.showWarning = this.selectedType !== this.originalType;
                    this.$emitter.emit('product-type-changed', this.selectedType);
                },
                
                getTypeIcon(type) {
                    const icons = {
                        simple: {
                            template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>`
                        },
                        configurable: {
                            template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`
                        },
                        grouped: {
                            template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>`
                        },
                        bundle: {
                            template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>`
                        },
                        constructor: {
                            template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>`
                        },
                        ingredient: {
                            template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>`
                        },
                        configurable_constructor: {
                            template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>`
                        }
                    };
                    return icons[type] || icons.simple;
                }
            }
        });
    </script>
    
    <style>
        .slide-fade-enter-active {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .slide-fade-leave-active {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .slide-fade-enter-from,
        .slide-fade-leave-to {
            opacity: 0;
            transform: translateY(-10px);
        }
    </style>
@endPushOnce
