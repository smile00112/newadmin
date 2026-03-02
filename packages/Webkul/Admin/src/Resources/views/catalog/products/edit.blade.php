<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.products.edit.title')
    </x-slot>

    @php
        $currentLocale = core()->getRequestedLocale();
        $currentChannel = core()->getRequestedChannel();
        $productPrice = $product->price ?? 0;
        $productQty = $product->totalQuantity() ?? 0;
    @endphp

    {!! view_render_event('bagisto.admin.catalog.products.edit.before', ['product' => $product]) !!}

    <!-- Современный хедер -->
    <div class="mb-6">
        <!-- Верхняя панель -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.catalog.products.index') }}"
                   class="flex items-center justify-center w-10 h-10 bg-white rounded-xl shadow-sm border border-gray-200 hover:bg-gray-50 hover:border-violet-300 transition-all duration-200">
                    <i class="icon-arrow-left text-gray-600 text-xl"></i>
                </a>

                <div>
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                        <a href="{{ route('admin.catalog.products.index') }}" class="hover:text-violet-600 transition-colors">
                            @lang('admin::app.catalog.products.index.title')
                        </a>
                        <i class="icon-chevron-right text-xs"></i>
                        <span class="text-gray-700">{{ $product->name ?? 'Товар #' . $product->id }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        @lang('admin::app.catalog.products.edit.title')
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Селекторы канала и локали -->
                <x-admin::dropdown position="bottom-right">
                    <x-slot:toggle>
                        <button type="button" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-violet-300 hover:bg-violet-50 transition-all duration-200 shadow-sm">
                            <i class="icon-store text-violet-500"></i>
                            <span>{{ $currentChannel->name }}</span>
                            <i class="icon-chevron-down text-xs text-gray-400"></i>
                        </button>
                    </x-slot>

                    <x-slot:menu class="!p-2 shadow-xl rounded-xl border border-gray-100">
                        @foreach (core()->getAllChannels() as $channel)
                            <a href="?{{ Arr::query(['channel' => $channel->code, 'locale' => $currentLocale->code]) }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $currentChannel->code == $channel->code ? 'bg-violet-50 text-violet-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                                @if ($currentChannel->code == $channel->code)
                                    <i class="icon-done text-violet-600"></i>
                                @else
                                    <span class="w-4"></span>
                                @endif
                                {{ $channel->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-admin::dropdown>

                <x-admin::dropdown position="bottom-right">
                    <x-slot:toggle>
                        <button type="button" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-violet-300 hover:bg-violet-50 transition-all duration-200 shadow-sm">
                            <i class="icon-language text-violet-500"></i>
                            <span>{{ $currentLocale->name }}</span>
                            <i class="icon-chevron-down text-xs text-gray-400"></i>
                        </button>
                    </x-slot>

                    <x-slot:menu class="!p-2 shadow-xl rounded-xl border border-gray-100">
                        @foreach ($currentChannel->locales()->orderBy('name')->get() as $locale)
                            <a href="?{{ Arr::query(['channel' => $currentChannel->code, 'locale' => $locale->code]) }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ $currentLocale->code == $locale->code ? 'bg-violet-50 text-violet-700' : 'text-gray-600 hover:bg-gray-50' }} transition-colors">
                                @if ($currentLocale->code == $locale->code)
                                    <i class="icon-done text-violet-600"></i>
                                @else
                                    <span class="w-4"></span>
                                @endif
                                {{ $locale->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-admin::dropdown>

                <!-- Кнопка настройки расположения блоков -->
                <button id="product-drag-toggle"
                        type="button"
                        onclick="toggleProductDragMode()"
                        class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-violet-300 hover:bg-violet-50 transition-all duration-200 shadow-sm"
                        title="Настроить расположение блоков">
                    <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                    </svg>
                    <span class="drag-mode-text">Настроить</span>
                </button>

                <!-- Кнопка сохранения -->
                <button type="submit"
                        form="product-edit-form"
                        onclick="document.getElementById('product-edit-form').submit();"
                        class="flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-violet-600 to-violet-700 text-white font-semibold rounded-xl hover:from-violet-700 hover:to-violet-800 transition-all duration-200 shadow-lg shadow-violet-500/30 hover:shadow-violet-500/40">
                    <i class="icon-save text-lg"></i>
                    @lang('admin::app.catalog.products.edit.save-btn')
                </button>
            </div>
        </div>

        <!-- Информационная панель товара -->
        <div class="bg-gradient-to-r from-violet-500 via-violet-600 to-purple-600 rounded-2xl p-5 text-white shadow-xl shadow-violet-500/20">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-5">
                    <!-- Превью изображения -->
                    <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center overflow-hidden">
                        @if ($product->images->isNotEmpty())
                            <img src="{{ $product->images->first()->url }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <i class="icon-product text-white/60 text-2xl"></i>
                        @endif
                    </div>

                    <div>
                        <h2 class="text-xl font-bold mb-1">{{ $product->name ?? 'Без названия' }}</h2>
                        <div class="flex items-center gap-4 text-white/80 text-sm">
                            <span class="flex items-center gap-1.5">
                                <i class="icon-attribute text-xs"></i>
                                {{ $product->type }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i class="icon-attribute text-xs"></i>
                                {{ $product->attribute_family->name }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-8">
                    <!-- Наличие -->
                    @php
                        $manageStock = $product->manage_stock ?? false;
                    @endphp
                    <div class="text-center" data-header-availability-wrapper data-manage-stock="{{ $manageStock ? 'true' : 'false' }}">
                        <div class="text-white/60 text-xs uppercase tracking-wider mb-1">Наличие</div>
                        @if ($productQty > 0)
                            <span data-header-availability class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-500/30 backdrop-blur-sm rounded-full text-sm font-medium" data-in-stock="true">
                                <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                                <span data-availability-text>В наличии{{ $manageStock ? ' ' . $productQty . ' шт.' : '' }}</span>
                            </span>
                        @else
                            <span data-header-availability class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-500/30 backdrop-blur-sm rounded-full text-sm font-medium" data-in-stock="false">
                                <span class="w-2 h-2 bg-red-400 rounded-full"></span>
                                <span data-availability-text>Нет в наличии</span>
                            </span>
                        @endif
                    </div>

                    <!-- SKU -->
                    <div class="text-center">
                        <div class="text-white/60 text-xs uppercase tracking-wider mb-1">SKU</div>
                        <div class="font-mono font-semibold">{{ $product->sku }}</div>
                    </div>

                    <!-- Цена -->
                    <div class="text-center">
                        <div class="text-white/60 text-xs uppercase tracking-wider mb-1">Цена</div>
                        <div class="text-xl font-bold">{{ core()->formatPrice($productPrice) }}</div>
                    </div>

                    <!-- ID -->
                    <div class="text-center">
                        <div class="text-white/60 text-xs uppercase tracking-wider mb-1">ID</div>
                        <div class="font-mono text-lg font-semibold">#{{ $product->id }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Форма редактирования -->
    <x-admin::form
        id="product-edit-form"
        method="PUT"
        :action="route('admin.catalog.products.update', $product->id)"
        enctype="multipart/form-data"
    >
        @php
            $icons = [
                'general' => 'icon-information',
                'description' => 'icon-description',
                'meta_description' => 'icon-search',
                'price' => 'icon-money',
                'shipping' => 'icon-shipping',
                'settings' => 'icon-settings',
                'inventories' => 'icon-inventory',
                'variations' => 'icon-variants',
            ];
        @endphp

        {!! view_render_event('bagisto.admin.catalog.products.edit.actions.before', ['product' => $product]) !!}

        <input type="hidden" name="channel" value="{{ $currentChannel->code }}">
        <input type="hidden" name="locale" value="{{ $currentLocale->code }}">

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
            <!-- Левая колонка - основной контент -->
            <div class="xl:col-span-2 space-y-6 draggable-column" data-column="left">
                @foreach ($product->attribute_family->attribute_groups->groupBy('column') as $column => $groups)
                    @if ($column == 1)
                        @foreach ($groups as $group)
                            @php
                                $customAttributes = $product->getEditableAttributes($group);
                            @endphp

                            @if ($customAttributes->isNotEmpty())
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                                    <!-- Заголовок группы -->
                                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/30">
                                                <i class="{{ $icons[Str::slug($group->name)] ?? 'icon-attribute' }} text-white text-lg"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-800">{{ $group->name }}</h3>
                                                <p class="text-sm text-gray-500">Заполните информацию о товаре</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Поля группы -->
                                    <div class="p-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            @foreach ($customAttributes as $attribute)
                                                @php
                                                    $colSpan = in_array($attribute->type, ['textarea', 'textarea_visual']) ? 'md:col-span-2' : '';
                                                @endphp

                                                <div class="{{ $colSpan }}">
                                                    <x-admin::form.control-group>
                                                        <x-admin::form.control-group.label :for="$attribute->code" class="{{ $attribute->is_required ? 'required' : '' }}">
                                                            {{ $attribute->admin_name ?: $attribute->name ?: $attribute->code }}
                                                        </x-admin::form.control-group.label>

                                                        @include ('admin::catalog.products.edit.controls', [
                                                            'attribute' => $attribute,
                                                            'product'   => $product,
                                                        ])

                                                        <x-admin::form.control-group.error :control-name="$attribute->code" />
                                                    </x-admin::form.control-group>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                <!-- Типо-специфичные атрибуты -->
                @foreach ($product->attribute_family->attribute_groups->groupBy('column') as $column => $groups)
                    @if ($column == 2)
                        @foreach ($groups->sortBy('position') as $group)
                            @php
                                $customAttributes = $product->getEditableAttributes($group);
                                // Пропускаем группу Inventories - у нас есть свой компонент для управления наличием
                                $isInventoriesGroup = Str::slug($group->name) === 'inventories' || $group->code === 'inventories';
                            @endphp

                            @if ($customAttributes->isNotEmpty() && !$isInventoriesGroup)
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/30">
                                                <i class="{{ $icons[Str::slug($group->name)] ?? 'icon-attribute' }} text-white text-lg"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-800">{{ $group->name }}</h3>
                                                <p class="text-sm text-gray-500">Дополнительные параметры</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            @foreach ($customAttributes as $attribute)
                                                @php
                                                    $colSpan = in_array($attribute->type, ['textarea', 'textarea_visual']) ? 'md:col-span-2' : '';
                                                @endphp

                                                <div class="{{ $colSpan }}">
                                                    <x-admin::form.control-group>
                                                        <x-admin::form.control-group.label :for="$attribute->code" class="{{ $attribute->is_required ? 'required' : '' }}">
                                                            {{ $attribute->admin_name ?: $attribute->name ?: $attribute->code }}
                                                        </x-admin::form.control-group.label>

                                                        @include ('admin::catalog.products.edit.controls', [
                                                            'attribute' => $attribute,
                                                            'product'   => $product,
                                                        ])

                                                        <x-admin::form.control-group.error :control-name="$attribute->code" />
                                                    </x-admin::form.control-group>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            <!-- Правая колонка - сайдбар -->
            <div class="flex flex-col gap-6 draggable-column" data-column="right">
                {{-- Блок выбора типа товара --}}
                @include('admin::catalog.products.edit.product-type-selector')

                {!! view_render_event('bagisto.admin.catalog.products.edit.form.types.before', ['product' => $product]) !!}

                {{-- Типо-специфичные компоненты --}}
                <v-product-type-blocks :current-type="'{{ $product->type }}'"></v-product-type-blocks>

                {!! view_render_event('bagisto.admin.catalog.products.edit.form.types.after', ['product' => $product]) !!}

                <!-- Карточка изображений -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/30">
                                <i class="icon-image text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">@lang('admin::app.catalog.products.edit.images.title')</h3>
                                <p class="text-sm text-gray-500">Фотографии товара</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <x-admin::media.images
                            name="images[files]"
                            allow-multiple="true"
                            show-placeholders="true"
                            :uploaded-images="$product->images"
                        />
                        <x-admin::form.control-group.error control-name='images.files[0]' />
                    </div>
                </div>

                <!-- Карточка видео -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/30">
                                <i class="icon-video text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">@lang('admin::app.catalog.products.edit.videos.title')</h3>
                                <p class="text-sm text-gray-500">Видео товара</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <x-admin::media.videos
                            name="videos[files]"
                            :allow-multiple="true"
                            :uploaded-videos="$product->videos"
                        />
                        <x-admin::form.control-group.error control-name='videos.files[0]' />
                    </div>
                </div>

                <!-- Наличие товара -->
                @if (! $product->getTypeInstance()->isComposite())
                    <v-product-inventory :product='@json($product)'></v-product-inventory>
                @endif

                <!-- Связанные товары -->
                @if ($product->type != 'grouped')
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/30">
                                    <i class="icon-product text-white text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">@lang('admin::app.catalog.products.edit.links.title')</h3>
                                    <p class="text-sm text-gray-500">Связи с другими товарами</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            @include('admin::catalog.products.edit.links')
                        </div>
                    </div>
                @endif

                <!-- Категории -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/30">
                                <i class="icon-category text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">@lang('admin::app.catalog.products.edit.categories.title')</h3>
                                <p class="text-sm text-gray-500">Выберите категории</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <v-product-categories>
                            <x-admin::shimmer.tree />
                        </v-product-categories>
                    </div>
                </div>

                <!-- Фото для категории -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/30">
                                <i class="icon-image text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">@lang('admin::app.catalog.products.edit.category_image.title')</h3>
                                <p class="text-sm text-gray-500">Изображение товара в категории</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        @php
                            $categoryImageUrl = $product->category_image ? \Illuminate\Support\Facades\Storage::url($product->category_image) : null;
                        @endphp

                        <div class="flex gap-4 items-start">
                            @if ($product->category_image)
                                <div class="relative">
                                    <img
                                        src="{{ $categoryImageUrl }}"
                                        class="h-[120px] w-[120px] overflow-hidden rounded-xl border border-gray-200 object-cover hover:border-violet-400 transition-colors"
                                        alt="Category Image"
                                    />
                                    <input type="hidden" name="category_image" value="{{ $product->category_image }}" />
                                </div>
                            @endif

                            <div class="flex-1">
                                <v-field
                                    type="file"
                                    class="w-full"
                                    name="category_image"
                                    v-slot="{ handleChange, handleBlur }"
                                    label="@lang('admin::app.catalog.products.edit.category_image.title')"
                                >
                                    <input
                                        type="file"
                                        id="category_image"
                                        accept="image/*"
                                        :class="[errors['category_image'] ? 'border border-red-600 hover:border-red-600' : '']"
                                        class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-violet-400 focus:border-violet-500 dark:border-gray-800 dark:text-gray-300"
                                        name="category_image"
                                        @change="handleChange"
                                        @blur="handleBlur"
                                    >
                                </v-field>

                                @if ($product->category_image)
                                    <div class="mt-3 flex items-center gap-2">
                                        <x-admin::form.control-group.control
                                            type="checkbox"
                                            id="category_image_delete"
                                            name="category_image_delete"
                                            value="1"
                                            for="category_image_delete"
                                        />
                                        <label for="category_image_delete" class="cursor-pointer text-sm text-gray-600 hover:text-red-500 transition-colors">
                                            @lang('admin::app.catalog.products.edit.remove')
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <x-admin::form.control-group.error control-name='category_image' />
                    </div>
                </div>
            </div>
        </div>

        {!! view_render_event('bagisto.admin.catalog.products.edit.actions.after', ['product' => $product]) !!}

    </x-admin::form>

    {!! view_render_event('bagisto.admin.catalog.products.edit.after', ['product' => $product]) !!}

    @pushOnce('scripts')
        @include('admin::catalog.products.edit.inventories')

        @include('admin::catalog.products.edit.types.configurable')
        @include('admin::catalog.products.edit.types.grouped')
        @include('admin::catalog.products.edit.types.bundle')
        @include('admin::catalog.products.edit.types.downloadable')
        @include('admin::catalog.products.edit.types.constructor')

        {{-- Компонент динамических блоков по типу товара --}}
        <script type="text/x-template" id="v-product-type-blocks-template">
            <div v-if="hasContent" class="contents">
                {{-- Конфигурируемый товар --}}
                <template v-if="activeType === 'configurable'">
                    <v-product-variations :errors="errors"></v-product-variations>
                </template>

                {{-- Групповой товар --}}
                <template v-if="activeType === 'grouped'">
                    <v-group-products :errors="errors"></v-group-products>
                </template>

                {{-- Комплект --}}
                <template v-if="activeType === 'bundle'">
                    <v-bundle-options :errors="errors"></v-bundle-options>
                </template>

                {{-- Конструктор --}}
                <template v-if="activeType === 'constructor'">
                    <v-constructor-options :errors="errors"></v-constructor-options>
                </template>
            </div>
        </script>

        <script type="module">
            app.component('v-product-type-blocks', {
                template: '#v-product-type-blocks-template',

                props: {
                    currentType: {
                        type: String,
                        default: 'simple'
                    }
                },

                inject: ['$emitter'],

                data() {
                    return {
                        activeType: this.currentType,
                        errors: {}
                    };
                },

                computed: {
                    hasContent() {
                        // Показываем только для типов с дополнительным контентом
                        return ['configurable', 'grouped', 'bundle', 'constructor'].includes(this.activeType);
                    }
                },

                created() {
                    this.$emitter.on('product-type-changed', (type) => {
                        this.activeType = type;
                    });
                }
            });
        </script>

        <!-- Categories Component -->
        <script
            type="text/x-template"
            id="v-product-categories-template"
        >
            <div>
                <template v-if="isLoading">
                    <x-admin::shimmer.tree />
                </template>

                <template v-else>
                    <x-admin::tree.view
                        input-type="checkbox"
                        selection-type="individual"
                        name-field="categories"
                        id-field="id"
                        value-field="id"
                        ::items="categories"
                        :value="json_encode($product->categories->pluck('id'))"
                        :fallback-locale="config('app.fallback_locale')"
                    />
                </template>
            </div>
        </script>

        <script type="module">
            app.component('v-product-categories', {
                template: '#v-product-categories-template',

                data() {
                    return {
                        isLoading: true,

                        categories: [],
                    }
                },

                mounted() {
                    this.get();
                },

                methods: {
                    get() {
                        axios.get("{{ route('admin.catalog.categories.tree') }}", {
                                params: {
                                    channel: "{{ $currentChannel->code }}",
                                }
                            })
                            .then(response => {
                                this.isLoading = false;

                                this.categories = response.data.data;
                            }).catch(error => {
                                console.log(error);
                            });
                    }
                }
            });
        </script>

        <!-- Block Drag & Drop Script -->
        <script>
        // Global variables for product drag & drop
        var productDragState = {
            enabled: false,
            draggedElement: null,
            placeholder: null,
            storageKey: 'product-edit-block-order'
        };

        // Helper function to find all draggable blocks
        function findDraggableBlocks() {
            var columns = document.querySelectorAll('.draggable-column');
            var blocks = [];
            var blockCounter = 0;

            columns.forEach(function(column) {
                // Находим ВСЕ прямые потомки с border или shadow (карточки)
                column.querySelectorAll(':scope > *').forEach(function(block) {
                    // Пропускаем служебные элементы
                    if (block.classList.contains('contents')) return;
                    if (block.tagName === 'SCRIPT') return;
                    if (block.tagName === 'TEMPLATE') return;
                    if (!block.offsetParent && !block.dataset.blockId) return; // скрытые элементы без block-id

                    // Проверяем что это карточка: имеет data-block-id ИЛИ rounded класс ИЛИ shadow/border стиль
                    var hasBlockId = !!block.dataset.blockId;
                    var hasRounded = Array.from(block.classList).some(function(cls) { return cls.includes('rounded'); });

                    // Также проверяем computed styles
                    var style = window.getComputedStyle(block);
                    var hasBorder = style.borderWidth && style.borderWidth !== '0px' && style.borderStyle !== 'none';
                    var hasShadow = style.boxShadow && style.boxShadow !== 'none';

                    if (hasBlockId || hasRounded || hasBorder || hasShadow) {
                        // Назначаем ID если нет
                        if (!block.dataset.blockId) {
                            var heading = block.querySelector('h3, h2, [class*="font-semibold"]');
                            if (heading) {
                                block.dataset.blockId = heading.textContent.trim().toLowerCase().replace(/[^a-zа-яё0-9]/gi, '-').substring(0, 50);
                            } else {
                                block.dataset.blockId = 'block-' + blockCounter++;
                            }
                        }
                        blocks.push(block);
                        console.log('Found block:', block.dataset.blockId, 'classes:', block.className);
                    }
                });
            });

            console.log('Total draggable blocks found:', blocks.length);
            return blocks;
        }

        // Global toggle function (called by onclick)
        function toggleProductDragMode() {
            console.log('toggleProductDragMode called');

            var state = productDragState;
            var toggleBtn = document.getElementById('product-drag-toggle');

            if (!toggleBtn) {
                console.error('Toggle button not found');
                return;
            }

            state.enabled = !state.enabled;
            console.log('Product drag mode:', state.enabled ? 'ON' : 'OFF');

            // Get all blocks
            var blocks = findDraggableBlocks();

            if (state.enabled) {
                // Enable drag mode
                toggleBtn.className = 'flex items-center gap-2 px-4 py-2.5 bg-violet-500 border border-violet-500 rounded-xl text-sm font-medium text-white hover:bg-violet-600 transition-all duration-200 shadow-sm';
                toggleBtn.querySelector('.drag-mode-text').textContent = 'Готово';

                blocks.forEach(function(block) {
                    block.setAttribute('draggable', 'true');
                    block.classList.add('cursor-move');
                    block.style.transition = 'transform 0.2s, box-shadow 0.2s';

                    // Добавляем индикатор перетаскивания
                    if (!block.querySelector('.drag-indicator')) {
                        var indicator = document.createElement('div');
                        indicator.className = 'drag-indicator absolute top-2 right-2 w-6 h-6 bg-violet-100 rounded flex items-center justify-center z-10';
                        indicator.innerHTML = '<svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>';
                        block.style.position = 'relative';
                        block.appendChild(indicator);
                    }
                });
            } else {
                // Disable drag mode and save
                toggleBtn.className = 'flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-violet-300 hover:bg-violet-50 transition-all duration-200 shadow-sm';
                toggleBtn.querySelector('.drag-mode-text').textContent = 'Настроить';

                blocks.forEach(function(block) {
                    block.removeAttribute('draggable');
                    block.classList.remove('cursor-move');
                    block.style.transition = '';

                    var indicator = block.querySelector('.drag-indicator');
                    if (indicator) indicator.remove();
                });

                // Save block order
                saveProductBlockOrder();
            }
        }

        function saveProductBlockOrder() {
            var columns = document.querySelectorAll('.draggable-column');
            var order = {};

            columns.forEach(function(column) {
                var columnId = column.dataset.column;
                order[columnId] = [];

                column.querySelectorAll(':scope > *').forEach(function(block) {
                    if (block.classList.contains('contents')) return;
                    if (block.tagName === 'SCRIPT' || block.tagName === 'TEMPLATE') return;
                    if (!block.offsetParent && block.style.display !== 'contents') return;

                    if (block.dataset.blockId) {
                        order[columnId].push(block.dataset.blockId);
                    }
                });
            });

            localStorage.setItem(productDragState.storageKey, JSON.stringify(order));
            console.log('Product block order saved:', order);
        }

        function loadProductBlockOrder() {
            var savedOrder = localStorage.getItem(productDragState.storageKey);
            if (!savedOrder) {
                console.log('No saved product order found');
                return;
            }

            try {
                var order = JSON.parse(savedOrder);
                console.log('Loading saved product order:', order);

                // Сначала соберём все блоки с ID через нашу функцию
                var allBlocks = {};
                findDraggableBlocks().forEach(function(block) {
                    if (block.dataset.blockId) {
                        allBlocks[block.dataset.blockId] = block;
                    }
                });

                console.log('Found blocks for reorder:', Object.keys(allBlocks));

                var columns = document.querySelectorAll('.draggable-column');

                // Now place blocks in correct columns according to saved order
                columns.forEach(function(column) {
                    var columnId = column.dataset.column;
                    if (!order[columnId]) return;

                    order[columnId].forEach(function(blockId) {
                        if (allBlocks[blockId]) {
                            // Move block to this column
                            column.appendChild(allBlocks[blockId]);
                        }
                    });
                });

                console.log('Product block order restored');
            } catch (e) {
                console.error('Error loading product block order:', e);
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Ждём пока Vue отрендерит компоненты
            setTimeout(function() {
                // Назначаем ID всем блокам через нашу функцию
                findDraggableBlocks();

                // Load saved order
                loadProductBlockOrder();

                console.log('Product Drag & Drop initialized (global settings)');
            }, 500);

            // Drag event handlers
            document.addEventListener('dragstart', function(e) {
                if (!productDragState.enabled) return;

                var draggedBlock = e.target.closest('[data-block-id]');
                if (!draggedBlock) {
                    // Fallback - ищем любой rounded элемент
                    draggedBlock = e.target.closest('[class*="rounded"]');
                }
                if (!draggedBlock || !draggedBlock.closest('.draggable-column')) return;
                if (draggedBlock.classList.contains('contents')) return;

                productDragState.draggedElement = draggedBlock;

                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', draggedBlock.dataset.blockId || '');

                productDragState.placeholder = document.createElement('div');
                productDragState.placeholder.className = 'drag-placeholder border-2 border-dashed border-violet-400 bg-violet-50 rounded-2xl';
                productDragState.placeholder.style.height = draggedBlock.offsetHeight + 'px';
                productDragState.placeholder.style.marginBottom = '24px';

                setTimeout(function() {
                    if (productDragState.draggedElement) {
                        productDragState.draggedElement.style.opacity = '0.5';
                        productDragState.draggedElement.style.transform = 'scale(1.02)';
                    }
                }, 0);
            });

            document.addEventListener('dragend', function(e) {
                if (!productDragState.enabled || !productDragState.draggedElement) return;

                productDragState.draggedElement.style.opacity = '';
                productDragState.draggedElement.style.transform = '';

                if (productDragState.placeholder && productDragState.placeholder.parentNode) {
                    productDragState.placeholder.parentNode.removeChild(productDragState.placeholder);
                }

                productDragState.draggedElement = null;
                productDragState.placeholder = null;
            });

            document.addEventListener('dragover', function(e) {
                if (!productDragState.enabled || !productDragState.draggedElement) return;
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';

                var column = e.target.closest('.draggable-column');
                if (!column) return;

                var draggableElements = Array.from(column.querySelectorAll(':scope > [data-block-id]:not(.dragging)'));
                var afterElement = draggableElements.reduce(function(closest, child) {
                    var box = child.getBoundingClientRect();
                    var offset = e.clientY - box.top - box.height / 2;
                    if (offset < 0 && offset > closest.offset) {
                        return { offset: offset, element: child };
                    }
                    return closest;
                }, { offset: Number.NEGATIVE_INFINITY }).element;

                if (productDragState.placeholder && productDragState.placeholder.parentNode) {
                    productDragState.placeholder.parentNode.removeChild(productDragState.placeholder);
                }

                if (afterElement == null) {
                    column.appendChild(productDragState.placeholder);
                } else {
                    column.insertBefore(productDragState.placeholder, afterElement);
                }
            });

            document.addEventListener('drop', function(e) {
                if (!productDragState.enabled || !productDragState.draggedElement) return;
                e.preventDefault();

                var column = e.target.closest('.draggable-column');
                if (!column) return;

                if (productDragState.placeholder && productDragState.placeholder.parentNode) {
                    productDragState.placeholder.parentNode.insertBefore(productDragState.draggedElement, productDragState.placeholder);
                    productDragState.placeholder.parentNode.removeChild(productDragState.placeholder);
                }

                productDragState.draggedElement.style.opacity = '';
                productDragState.draggedElement.style.transform = '';

                saveProductBlockOrder();
            });
        });
        </script>
    @endPushOnce
</x-admin::layouts>
