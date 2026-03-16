<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.products.edit.title')
    </x-slot>

    @php
    // проброс ошибок в session, т.е. валидация через formRequest
    if(!empty($errors)){
        foreach ($errors->all() as $error) {
            session()->flash('error', $error);
            //echo $error . "<br>";
        }
    }
    @endphp
    {!! view_render_event('bagisto.admin.catalog.product.edit.before', ['product' => $product]) !!}

    <x-admin::form
        method="PUT"
        enctype="multipart/form-data"
    >
        {!! view_render_event('bagisto.admin.catalog.product.edit.actions.before', ['product' => $product]) !!}

        <!-- Page Header -->
        <div class="grid gap-2.5">
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 4px 15px rgba(245,158,11,0.3); min-width:44px;">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                            @lang('admin::app.catalog.products.edit.title')
                        </p>
                        <p class="text-xs text-gray-400">Редактирование товара</p>
                    </div>

                    @php
                        $typeLabels = [
                            'simple' => 'Простой',
                            'configurable' => 'Настроенный',
                            'grouped' => 'Группированный',
                            'bundle' => 'Набор',
                            'ingredient' => 'Ингредиент',
                            'constructor' => 'Конструктор',
                            'configurable_constructor' => 'Настроенный + Конструктор',
                        ];
                    @endphp
                </div>

                <div class="flex items-center gap-x-2.5">
                    <!-- Back Button -->
                    <a
                        href="{{ route('admin.catalog.products.index') }}"
                        style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #6b7280; background: #f3f4f6; transition: all 0.15s; text-decoration: none;"
                        onmouseenter="this.style.background='#e5e7eb'"
                        onmouseleave="this.style.background='#f3f4f6'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        @lang('admin::app.account.edit.back-btn')
                    </a>

                    <!-- Preview Button -->
                    @if (
                        $product->status
                        && $product->visible_individually
                        && $product->url_key
                    )
                        <a
                            href="{{ route('shop.product_or_category.index', $product->url_key) }}"
                            class="secondary-button"
                            target="_blank"
                        >
                            @lang('admin::app.catalog.products.edit.preview')
                        </a>
                    @endif

                    <!-- Delete Button -->
                    <button
                        type="button"
                        onclick="deleteProduct()"
                        style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:10px; font-size:13px; font-weight:600; color:#ef4444; background:#fef2f2; border:1px solid #fecaca; cursor:pointer; transition:all 0.15s;"
                        onmouseenter="this.style.background='#fee2e2'; this.style.borderColor='#fca5a5'"
                        onmouseleave="this.style.background='#fef2f2'; this.style.borderColor='#fecaca'"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Удалить
                    </button>

                    <!-- Save Button -->
                    <button class="primary-button">
                        @lang('admin::app.catalog.products.edit.save-btn')
                    </button>
                </div>
            </div>
        </div>

        @php
            $channels = core()->getAllChannels();

            $currentChannel = core()->getRequestedChannel();

            $currentLocale = core()->getRequestedLocale();
        @endphp

        <!-- Channel and Locale Switcher -->
        <div class="mt-7 flex items-center justify-between gap-4 max-md:flex-wrap">
            <div class="flex items-center gap-x-1">
                <!-- Channel Switcher -->
                <x-admin::dropdown :class="$channels->count() <= 1 ? 'hidden' : ''">
                    <!-- Dropdown Toggler -->
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="transparent-button px-1 py-1.5 hover:bg-gray-200 focus:bg-gray-200 dark:text-white dark:hover:bg-gray-800 dark:focus:bg-gray-800"
                        >
                            <span class="icon-store text-2xl"></span>

                            {{ $currentChannel->name }}

                            <input
                                type="hidden"
                                name="channel"
                                value="{{ $currentChannel->code }}"
                            />

                            <span class="icon-sort-down text-2xl"></span>
                        </button>
                    </x-slot>

                    <!-- Dropdown Content -->
                    <x-slot:content class="!p-0">
                        @foreach ($channels as $channel)
                            <a
                                href="?{{ Arr::query(['channel' => $channel->code, 'locale' => $channel->default_locale?->code ?? $currentLocale->code ]) }}"
                                class="flex cursor-pointer gap-2.5 px-5 py-2 text-base hover:bg-gray-100 dark:text-white dark:hover:bg-gray-950"
                            >
                                {{ $channel->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-admin::dropdown>

                <!-- Locale Switcher -->
                <x-admin::dropdown :class="$currentChannel->locales->count() <= 1 ? 'hidden' : ''">
                    <!-- Dropdown Toggler -->
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="transparent-button px-1 py-1.5 hover:bg-gray-200 focus:bg-gray-200 dark:text-white dark:hover:bg-gray-800 dark:focus:bg-gray-800"
                        >
                            <span class="icon-language text-2xl"></span>

                            {{ $currentLocale->name }}

                            <input
                                type="hidden"
                                name="locale"
                                value="{{ $currentLocale->code }}"
                            />

                            <span class="icon-sort-down text-2xl"></span>
                        </button>
                    </x-slot>

                    <!-- Dropdown Content -->
                    <x-slot:content class="!p-0">
                        @foreach ($currentChannel->locales->sortBy('name') as $locale)
                            <a
                                href="?{{ Arr::query(['channel' => $currentChannel->code, 'locale' => $locale->code]) }}"
                                class="flex gap-2.5 px-5 py-2 text-base cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-950 dark:text-white {{ $locale->code == $currentLocale->code ? 'bg-gray-100 dark:bg-gray-950' : ''}}"
                            >
                                {{ $locale->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-admin::dropdown>
            </div>
        </div>

        {!! view_render_event('bagisto.admin.catalog.product.edit.actions.after', ['product' => $product]) !!}

        <!-- Product Type Card -->
        <div id="edit-type-changer" class="mt-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900" style="max-width:360px;">
            <p class="mb-2 text-sm font-semibold text-gray-800 dark:text-white">Тип товара</p>
            <div style="position:relative;">
                <button
                    type="button"
                    id="edit-type-btn"
                    onclick="document.getElementById('edit-type-dropdown').classList.toggle('hidden')"
                    style="display:flex; width:100%; align-items:center; justify-content:space-between; padding:8px 14px; border-radius:10px; font-size:13px; background:#f9fafb; color:#374151; font-weight:600; border:1px solid #e5e7eb; cursor:pointer; transition:all 0.15s;"
                    onmouseenter="this.style.borderColor='#d1d5db'; this.style.background='#f3f4f6'"
                    onmouseleave="this.style.borderColor='#e5e7eb'; this.style.background='#f9fafb'"
                >
                    <span id="edit-type-label">{{ $typeLabels[$product->type] ?? $product->type }}</span>
                    <svg style="width:14px; height:14px; color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="edit-type-dropdown" class="hidden" style="position:absolute; top:100%; left:0; right:0; margin-top:4px; background:white; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.12); z-index:50; padding:4px; max-height:300px; overflow-y:auto;">
                    @foreach($typeLabels as $typeKey => $typeLabel)
                        <button
                            type="button"
                            data-type="{{ $typeKey }}"
                            onclick="changeProductTypeEdit('{{ $typeKey }}', '{{ $typeLabel }}')"
                            style="display:flex; width:100%; align-items:center; justify-content:space-between; padding:8px 12px; border-radius:8px; font-size:13px; color:#374151; background:transparent; border:none; cursor:pointer; transition:background 0.15s; text-align:left;"
                            onmouseenter="this.style.background='#f3f4f6'"
                            onmouseleave="this.style.background='transparent'"
                        >
                            <span>{{ $typeLabel }}</span>
                            @if($product->type === $typeKey)
                                <svg class="edit-type-check" style="width:16px; height:16px; color:#059669; flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- body content -->
        {!! view_render_event('bagisto.admin.catalog.product.edit.form.before', ['product' => $product]) !!}

        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            @php
                $groupedColumns = $product->attribute_family->attribute_groups->groupBy('column');

                $isSingleColumn = $groupedColumns->count() !== 2;
            @endphp

            @foreach ($groupedColumns as $column => $groups)

                {!! view_render_event("bagisto.admin.catalog.product.edit.form.column_{$column}.before", ['product' => $product]) !!}

                <div class="flex flex-col gap-2 {{ $column == 1 ? 'flex-1 max-xl:flex-auto' : 'w-[360px] max-w-full max-sm:w-full' }}">
                    @foreach ($groups as $group)
                        @php $customAttributes = $product->getEditableAttributes($group); @endphp
                            {{--    {{$group->code}} - {{$product->getTypeInstance()->isComposite()}} |--}}
                        @if (
                            $group->code === 'inventories'
                            &&
                            (
                                $product->getTypeInstance()->isComposite()
                                || $product->type === 'downloadable'
                            )
                        )
                            @continue
                        @endif

                        @if ($customAttributes->isNotEmpty())
                            <!--TODO - remove this-->
{{--                        {{$group->code}}--}}
{{--                            @if(in_array($group->code, ['shipping']))--}}
{{--                               @continue;--}}
{{--                            @endif--}}

{{--                            {{$group->code}}--}}
                            {!! view_render_event("bagisto.admin.catalog.product.edit.form.{$group->code}.before", ['product' => $product]) !!}

<!--                                    TODO - remove this -->
                            @php
                                $style_group = '';
                                if(in_array($group->code, ['meta_description', 'shipping'])){ //, 'settings'
                                    $style_group = 'display: none';

                                }
                            @endphp

                            <div class="box-shadow relative rounded bg-white p-4 dark:bg-gray-900" style="{{ $style_group }}">
                                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                                    {{ $group->name }} <!--{{$group->code}}-->
                                </p>

                                @if ($group->code == 'meta_description')
                                    <x-admin::seo />
                                @endif

                                @foreach ($customAttributes as $attribute)
{{--                                    {{$group->code}}--}}
<!--                                    TODO - remove this -->
{{--                                    @if(in_array($attribute->code, ['color', 'brand', 'size', 'length', 'width', 'height']))--}}
{{--                                        @continue--}}
{{--                                    @endif--}}

                                    {!! view_render_event("bagisto.admin.catalog.product.edit.form.{$group->code}.controls.before", ['product' => $product]) !!}


                                @php
                                    $style = '';
                                    if(in_array($attribute->code, ['sku', 'tax', 'product_number', 'url_key', 'tax_category_id', 'color', 'brand', 'size', 'length', 'width', 'height'])){
                                        $style = 'display: none';
                                    }

                                    $class = 'last:!mb-0';
                                    if(in_array($attribute->code, ['special_price_from', 'special_price_to'])){
                                       // $class = 'w-full lg:w-1/3';

                                    }


                                @endphp

                                    <x-admin::form.control-group class="{{$class}}" style="{{ $style }}" >
                                        <x-admin::form.control-group.label>
{{--                                            TODO remove this & find translation--}}
                                            @php
                                                $attribute_name = $attribute->admin_name;
                                                if($attribute_name === 'Статус')
                                                    $attribute_name = 'Активен';
                                            @endphp
                                            {!! $attribute_name . ($attribute->is_required ? '<span class="required"></span>' : '') !!}

                                            @if (
                                                $attribute->value_per_channel
                                                && $channels->count() > 1
                                            )
                                                <span class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600">
                                                    {{ $currentChannel->name }}
                                                </span>
                                            @endif

                                            @if ($attribute->value_per_locale)
                                                <span class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600">
                                                    {{ $currentLocale->name }}
                                                </span>
                                            @endif
                                        </x-admin::form.control-group.label>

                                        @include ('admin::catalog.products.edit.controls', [
                                            'attribute' => $attribute,
                                            'product'   => $product,
                                        ])

                                        <x-admin::form.control-group.error :control-name="$attribute->code . (in_array($attribute->type, ['multiselect', 'checkbox']) ? '[]' : '')" />
                                    </x-admin::form.control-group>

                                    {!! view_render_event("bagisto.admin.catalog.product.edit.form.{$group->code}.controls.after", ['product' => $product]) !!}
                                @endforeach

                                @includeWhen($group->code == 'price', 'admin::catalog.products.edit.price.group')

                                @includeWhen($group->code === 'inventories', 'admin::catalog.products.edit.inventories')
                            </div>

                            {!! view_render_event("bagisto.admin.catalog.product.edit.form.{$group->code}.after", ['product' => $product]) !!}
                        @endif
                    @endforeach

                    @if ($column == 1)
                        <!-- Category Image View Blade File -->
                        @include('admin::catalog.products.edit.category_image')

                        <!-- Images View Blade File -->
                        @include('admin::catalog.products.edit.images')

                        <!-- Videos View Blade File -->
                        @include('admin::catalog.products.edit.videos')

                        <!-- Product Type View Blade File -->
                        @includeIf('admin::catalog.products.edit.types.' . $product->type)

{{--                        TODO - refactor this--}}
                        @if($product->type !== 'ingredient')
                            <!-- Related, Cross Sells, Up Sells View Blade File -->
                            @include('admin::catalog.products.edit.links')
                        @else
                            <!-- Incompatibility Ingredients View Blade File -->
{{--                            @include('admin::catalog.products.edit.links_incompatibility_ingredients')--}}
                        @endif

                        <!-- Include Product Type Additional Blade Files If Any -->
                        @foreach ($product->getTypeInstance()->getAdditionalViews() as $view)
                            @includeIf($view)
                        @endforeach
                    @elseif (! $isSingleColumn)
                        <!-- Channels View Blade File -->
                        @include('admin::catalog.products.edit.channels')

                        <!-- Categories View Blade File -->
                        @include('admin::catalog.products.edit.categories')
                    @endif
                </div>

                @if ($isSingleColumn && ($column == 1 || $column == 2))
                    <div class="w-[360px] max-w-full max-sm:w-full">
                        @if ($column == 2)
                            <!-- Images View Blade File -->
                            @include('admin::catalog.products.edit.images')

                            <!-- Videos View Blade File -->
                            @include('admin::catalog.products.edit.videos')

                            <!-- Product Type View Blade File -->
                            @includeIf('admin::catalog.products.edit.types.' . $product->type)

    {{--                        TODO - refactor this--}}
                            @if($product->type !== 'ingredient')
                                <!-- Related, Cross Sells, Up Sells View Blade File -->
                                @include('admin::catalog.products.edit.links')
                            @else
                                <!-- Incompatibility Ingredients View Blade File -->
                                @include('admin::catalog.products.edit.links_incompatibility_ingredients')
                            @endif

                            <!-- Include Product Type Additional Blade Files If Any -->
                            @foreach ($product->getTypeInstance()->getAdditionalViews() as $view)
                                @includeIf($view)
                            @endforeach
                        @endif

                        <!-- Channels View Blade File -->
                        @include('admin::catalog.products.edit.channels')

                        <!-- Categories View Blade File -->
                        @include('admin::catalog.products.edit.categories')
                    </div>
                @endif

                {!! view_render_event("bagisto.admin.catalog.product.edit.form.column_{$column}.after", ['product' => $product]) !!}

            @endforeach
        </div>

        {!! view_render_event('bagisto.admin.catalog.product.edit.form.after', ['product' => $product]) !!}

    </x-admin::form>

    {!! view_render_event('bagisto.admin.catalog.product.edit.after', ['product' => $product]) !!}

    @pushOnce('scripts')
        <script>
            // Close type dropdown on click outside
            document.addEventListener('click', function(e) {
                var changer = document.getElementById('edit-type-changer');
                var dropdown = document.getElementById('edit-type-dropdown');
                if (changer && dropdown && !changer.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            // Change product type via AJAX then reload
            function changeProductTypeEdit(newType, newLabel) {
                var productId = {{ $product->id }};
                var currentType = '{{ $product->type }}';
                if (newType === currentType) {
                    document.getElementById('edit-type-dropdown').classList.add('hidden');
                    return;
                }

                var csrf = document.querySelector('meta[name="csrf-token"]')?.content
                         || document.querySelector('input[name="_token"]')?.value;

                fetch('/admin/catalog/products/quick-update/' + productId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ field: 'type', value: newType }),
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        document.getElementById('edit-type-label').textContent = newLabel;
                        document.getElementById('edit-type-dropdown').classList.add('hidden');
                        window.location.reload();
                    }
                })
                .catch(function() {
                    alert('Ошибка смены типа товара');
                });
            }
            // Delete product
            function deleteProduct() {
                // Find the Vue app instance to use emitter
                var vueApp = document.getElementById('app')?.__vue_app__;
                var emitter = vueApp?.config?.globalProperties?.$emitter;

                if (emitter) {
                    emitter.emit('open-confirm-modal', {
                        agree: function() {
                            var csrf = document.querySelector('meta[name="csrf-token"]')?.content
                                     || document.querySelector('input[name="_token"]')?.value;

                            fetch('/admin/catalog/products/edit/{{ $product->id }}', {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                window.location.href = '{{ route("admin.catalog.products.index") }}';
                            })
                            .catch(function() {
                                emitter.emit('add-flash', { type: 'error', message: 'Ошибка удаления товара' });
                            });
                        }
                    });
                } else {
                    // Fallback
                    if (!confirm('Вы уверены, что хотите удалить этот товар?')) return;

                    var csrf = document.querySelector('meta[name="csrf-token"]')?.content
                             || document.querySelector('input[name="_token"]')?.value;

                    fetch('/admin/catalog/products/edit/{{ $product->id }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        window.location.href = '{{ route("admin.catalog.products.index") }}';
                    })
                    .catch(function() {
                        alert('Ошибка удаления товара');
                    });
                }
            }
        </script>
    @endPushOnce

</x-admin::layouts>
