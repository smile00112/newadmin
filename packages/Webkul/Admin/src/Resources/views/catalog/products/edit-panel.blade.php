<x-admin::layouts :hideNavigation="true">
    <x-slot:title>
        @lang('admin::app.catalog.products.edit.title') — {{ $product->name ?? $product->sku }}
    </x-slot>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .7; } }
        body { background: #f8f9fb !important; }
        .panel-header-block { display: none !important; }
        body.in-iframe { padding: 0 !important; margin: 0 !important; }
        body.in-iframe > div { padding: 8px 16px !important; min-height: auto !important; }
    </style>

    <script>
        if (window !== window.parent) {
            document.documentElement.classList.add('in-iframe');
            document.addEventListener('DOMContentLoaded', function() {
                document.body.classList.add('in-iframe');
            });
        }
    </script>

    @php
        if(!empty($errors)){
            foreach ($errors->all() as $error) {
                session()->flash('error', $error);
            }
        }
    @endphp

    {!! view_render_event('bagisto.admin.catalog.product.edit.before', ['product' => $product]) !!}

    <x-admin::form
        method="PUT"
        enctype="multipart/form-data"
    >
        <!-- Panel Header -->
        <div class="panel-header-block" style="display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:16px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <button
                    type="button"
                    onclick="window.parent.postMessage({type:'close-product-panel'}, '*');"
                    style="display:flex; align-items:center; justify-content:center; width:40px; height:40px; min-width:40px; border-radius:12px; background:#f3f4f6; cursor:pointer; border:none; transition:all 0.2s;"
                    onmouseenter="this.style.background='#e5e7eb'; this.style.transform='scale(1.05)'"
                    onmouseleave="this.style.background='#f3f4f6'; this.style.transform='scale(1)'"
                    title="Закрыть панель"
                >
                    <svg style="width:20px; height:20px; color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div style="display:flex; align-items:center; justify-content:center; width:44px; height:44px; min-width:44px; border-radius:12px; background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow:0 4px 15px rgba(245,158,11,0.3);">
                    <svg style="width:20px; height:20px; color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>

                <div>
                    <p style="font-size:18px; font-weight:700; color:#1f2937; margin:0;">
                        {{ $product->name ?? 'Новый товар' }}
                    </p>
                    <p style="font-size:13px; color:#9ca3af; margin:2px 0 0;">
                        SKU: {{ $product->sku }} &bull; ID: {{ $product->id }}
                    </p>
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

                    if (! in_array($product->type, ['bundle', 'grouped'], true)) {
                        unset($typeLabels['bundle'], $typeLabels['grouped']);
                    }
                @endphp
            </div>

            <div style="display:flex; align-items:center; gap:8px;">
                <!-- Delete Button -->
                <button
                    type="button"
                    onclick="confirmDeleteProduct({{ $product->id }})"
                    style="display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:10px; font-size:13px; font-weight:600; color:#ef4444; background:#fef2f2; border:1px solid #fecaca; cursor:pointer; transition:all 0.15s;"
                    onmouseenter="this.style.background='#fee2e2'; this.style.borderColor='#fca5a5'"
                    onmouseleave="this.style.background='#fef2f2'; this.style.borderColor='#fecaca'"
                    title="Удалить товар"
                >
                    <svg style="width:15px; height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Удалить
                </button>

                @if ($product->status && $product->visible_individually && $product->url_key)
                    <a
                        href="{{ route('shop.product_or_category.index', $product->url_key) }}"
                        target="_blank"
                        style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:10px; font-size:13px; font-weight:600; color:#6b7280; background:#f3f4f6; transition:all 0.15s; text-decoration:none;"
                        onmouseenter="this.style.background='#e5e7eb'"
                        onmouseleave="this.style.background='#f3f4f6'"
                    >
                        <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        Просмотр
                    </a>
                @endif

                <button type="submit" class="primary-button">
                    @lang('admin::app.catalog.products.edit.save-btn')
                </button>
            </div>
        </div>

        @php
            $channels = core()->getAllChannels();
            $currentChannel = core()->getRequestedChannel();
            $currentLocale = core()->getRequestedLocale();
        @endphp

        <!-- Channel/Locale Switcher (compact) -->
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
            <x-admin::dropdown :class="$channels->count() <= 1 ? 'hidden' : ''">
                <x-slot:toggle>
                    <button type="button" class="transparent-button px-1 py-1.5 hover:bg-gray-200 focus:bg-gray-200 dark:text-white dark:hover:bg-gray-800 dark:focus:bg-gray-800">
                        <span class="icon-store text-2xl"></span>
                        {{ $currentChannel->name }}
                        <input type="hidden" name="channel" value="{{ $currentChannel->code }}" />
                        <span class="icon-sort-down text-2xl"></span>
                    </button>
                </x-slot>
                <x-slot:content class="!p-0">
                    @foreach ($channels as $channel)
                        <a href="?{{ Arr::query(['channel' => $channel->code, 'locale' => $channel->default_locale?->code ?? $currentLocale->code]) }}" class="flex cursor-pointer gap-2.5 px-5 py-2 text-base hover:bg-gray-100 dark:text-white dark:hover:bg-gray-950">
                            {{ $channel->name }}
                        </a>
                    @endforeach
                </x-slot>
            </x-admin::dropdown>

            <x-admin::dropdown :class="$currentChannel->locales->count() <= 1 ? 'hidden' : ''">
                <x-slot:toggle>
                    <button type="button" class="transparent-button px-1 py-1.5 hover:bg-gray-200 focus:bg-gray-200 dark:text-white dark:hover:bg-gray-800 dark:focus:bg-gray-800">
                        <span class="icon-language text-2xl"></span>
                        {{ $currentLocale->name }}
                        <input type="hidden" name="locale" value="{{ $currentLocale->code }}" />
                        <span class="icon-sort-down text-2xl"></span>
                    </button>
                </x-slot>
                <x-slot:content class="!p-0">
                    @foreach ($currentChannel->locales->sortBy('name') as $locale)
                        <a href="?{{ Arr::query(['channel' => $currentChannel->code, 'locale' => $locale->code]) }}" class="flex gap-2.5 px-5 py-2 text-base cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-950 dark:text-white {{ $locale->code == $currentLocale->code ? 'bg-gray-100 dark:bg-gray-950' : ''}}">
                            {{ $locale->name }}
                        </a>
                    @endforeach
                </x-slot>
            </x-admin::dropdown>
        </div>

        <!-- Product Type Card -->
        <div id="panel-type-changer" style="max-width:320px; margin-bottom:12px; padding:12px 16px; background:white; border:1px solid #e5e7eb; border-radius:12px;">
            <p style="font-size:13px; font-weight:600; color:#374151; margin:0 0 8px;">Тип товара</p>
            <div style="position:relative;">
                <button
                    type="button"
                    onclick="document.getElementById('panel-type-dropdown').classList.toggle('hidden')"
                    style="display:flex; width:100%; align-items:center; justify-content:space-between; padding:8px 14px; border-radius:10px; font-size:13px; background:#f9fafb; color:#374151; font-weight:600; border:1px solid #e5e7eb; cursor:pointer; transition:all 0.15s;"
                    onmouseenter="this.style.borderColor='#d1d5db'; this.style.background='#f3f4f6'"
                    onmouseleave="this.style.borderColor='#e5e7eb'; this.style.background='#f9fafb'"
                >
                    <span id="panel-type-label">{{ $typeLabels[$product->type] ?? $product->type }}</span>
                    <svg style="width:14px; height:14px; color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="panel-type-dropdown" class="hidden" style="position:absolute; top:100%; left:0; right:0; margin-top:4px; background:white; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.12); z-index:50; padding:4px; max-height:280px; overflow-y:auto;">
                    @foreach($typeLabels as $typeKey => $typeLabel)
                        <button
                            type="button"
                            data-type="{{ $typeKey }}"
                            onclick="changeProductType('{{ $typeKey }}', '{{ $typeLabel }}')"
                            style="display:flex; width:100%; align-items:center; justify-content:space-between; padding:8px 12px; border-radius:8px; font-size:13px; color:#374151; background:transparent; border:none; cursor:pointer; transition:background 0.15s; text-align:left;"
                            onmouseenter="this.style.background='#f3f4f6'"
                            onmouseleave="this.style.background='transparent'"
                        >
                            <span>{{ $typeLabel }}</span>
                            @if($product->type === $typeKey)
                                <svg style="width:16px; height:16px; color:#059669; flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div id="panel-configurable-modal" class="hidden" style="position:fixed; inset:0; background:rgba(17,24,39,0.45); z-index:1200; align-items:center; justify-content:center; padding:16px;">
            <div style="width:100%; max-width:760px; max-height:85vh; overflow:auto; background:white; border-radius:16px; box-shadow:0 18px 48px rgba(0,0,0,0.2);">
                <div style="padding:18px 20px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; gap:8px;">
                    <p style="margin:0; font-size:18px; font-weight:700; color:#111827;">Атрибуты конфигурируемого продукта</p>
                    <button type="button" onclick="closePanelConfigurableModal()" style="border:none; background:transparent; font-size:24px; line-height:1; color:#6b7280; cursor:pointer;">&times;</button>
                </div>

                <div id="panel-configurable-modal-content" style="padding:20px;"></div>

                <div style="padding:16px 20px; border-top:1px solid #e5e7eb; display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closePanelConfigurableModal()" style="padding:8px 14px; border-radius:10px; border:1px solid #d1d5db; background:#fff; color:#374151; font-weight:600; cursor:pointer;">Отмена</button>
                    <button id="panel-configurable-save-btn" type="button" onclick="confirmPanelTypeWithAttributes()" style="padding:8px 16px; border-radius:10px; border:none; background:#7c3aed; color:white; font-weight:700; cursor:pointer;" disabled>Применить</button>
                </div>
            </div>
        </div>

        <!-- Body Content -->
        <div class="flex gap-2.5 max-xl:flex-wrap">
            @php
                $groupedColumns = $product->attribute_family->attribute_groups->groupBy('column');
                $isSingleColumn = $groupedColumns->count() !== 2;
            @endphp

            @foreach ($groupedColumns as $column => $groups)

                <div class="flex flex-col gap-2 {{ $column == 1 ? 'flex-1 max-xl:flex-auto' : 'w-[360px] max-w-full max-sm:w-full' }}">
                    @foreach ($groups as $group)
                        @php $customAttributes = $product->getEditableAttributes($group); @endphp

                        @if (
                            $group->code === 'inventories'
                            && (
                                $product->getTypeInstance()->isComposite()
                                || $product->type === 'downloadable'
                            )
                        )
                            @continue
                        @endif

                        @if ($customAttributes->isNotEmpty())
                            @php
                                $style_group = '';
                                if(in_array($group->code, ['meta_description', 'shipping'])){
                                    $style_group = 'display: none';
                                }
                            @endphp

                            <div class="box-shadow relative rounded bg-white p-4 dark:bg-gray-900" style="{{ $style_group }}">
                                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                                    {{ $group->name }}
                                </p>

                                @if ($group->code == 'meta_description')
                                    <x-admin::seo />
                                @endif

                                @foreach ($customAttributes as $attribute)
                                    @php
                                        $style = '';
                                        if(in_array($attribute->code, ['sku', 'tax', 'product_number', 'url_key', 'tax_category_id', 'color', 'brand', 'size', 'length', 'width', 'height'])){
                                            $style = 'display: none';
                                        }
                                        $class = 'last:!mb-0';
                                    @endphp

                                    <x-admin::form.control-group class="{{ $class }}" style="{{ $style }}">
                                        <x-admin::form.control-group.label>
                                            @php
                                                $attribute_name = $attribute->admin_name;
                                                if($attribute_name === 'Статус')
                                                    $attribute_name = 'Активен';
                                            @endphp
                                            {!! $attribute_name . ($attribute->is_required ? '<span class="required"></span>' : '') !!}

                                            @if ($attribute->value_per_channel && $channels->count() > 1)
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
                                @endforeach

                                @includeWhen($group->code == 'price', 'admin::catalog.products.edit.price.group')
                                @includeWhen($group->code === 'inventories', 'admin::catalog.products.edit.inventories')
                            </div>
                        @endif
                    @endforeach

                    @if ($column == 1)
                        @include('admin::catalog.products.edit.category_image')
                        @include('admin::catalog.products.edit.images')
                        @include('admin::catalog.products.edit.videos')
                        @includeIf('admin::catalog.products.edit.types.' . $product->type)

                        @if($product->type !== 'ingredient')
                            @include('admin::catalog.products.edit.links')
                        @endif

                        @foreach ($product->getTypeInstance()->getAdditionalViews() as $view)
                            @includeIf($view)
                        @endforeach
                    @elseif (! $isSingleColumn)
                        @include('admin::catalog.products.edit.channels')
                        @include('admin::catalog.products.edit.categories')
                    @endif
                </div>

                @if ($isSingleColumn && ($column == 1 || $column == 2))
                    <div class="w-[360px] max-w-full max-sm:w-full">
                        @if ($column == 2)
                            @include('admin::catalog.products.edit.images')
                            @include('admin::catalog.products.edit.videos')
                            @includeIf('admin::catalog.products.edit.types.' . $product->type)

                            @if($product->type !== 'ingredient')
                                @include('admin::catalog.products.edit.links')
                            @else
                                @include('admin::catalog.products.edit.links_incompatibility_ingredients')
                            @endif

                            @foreach ($product->getTypeInstance()->getAdditionalViews() as $view)
                                @includeIf($view)
                            @endforeach
                        @endif

                        @include('admin::catalog.products.edit.channels')
                        @include('admin::catalog.products.edit.categories')
                    </div>
                @endif

            @endforeach
        </div>

    </x-admin::form>

    {!! view_render_event('bagisto.admin.catalog.product.edit.after', ['product' => $product]) !!}

    @pushOnce('scripts')
        <script>
            const panelVariantTypes = ['configurable', 'configurable_constructor'];
            let panelPendingType = null;
            let panelPendingLabel = null;
            let panelFetchedAttributes = [];

            // Notify parent when product is saved (form submit intercept)
            document.addEventListener('DOMContentLoaded', function() {
                @if(session('success'))
                    if (window !== window.parent) {
                        window.parent.postMessage({type: 'product-updated'}, '*');
                    }
                @endif
            });

            // Close type dropdown on click outside
            document.addEventListener('click', function(e) {
                const changer = document.getElementById('panel-type-changer');
                const dropdown = document.getElementById('panel-type-dropdown');
                if (changer && dropdown && !changer.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });

            // Change product type via AJAX
            async function changeProductType(newType, newLabel) {
                const productId = {{ $product->id }};
                const currentType = '{{ $product->type }}';
                if (newType === currentType) {
                    document.getElementById('panel-type-dropdown').classList.add('hidden');
                    return;
                }

                document.getElementById('panel-type-dropdown').classList.add('hidden');

                if (panelVariantTypes.includes(newType)) {
                    panelPendingType = newType;
                    panelPendingLabel = newLabel;
                    await openPanelConfigurableModal(productId);

                    return;
                }

                await submitPanelTypeUpdate(newType, null, newLabel);
            }

            async function openPanelConfigurableModal(productId) {
                const modal = document.getElementById('panel-configurable-modal');
                const content = document.getElementById('panel-configurable-modal-content');
                const saveButton = document.getElementById('panel-configurable-save-btn');

                content.innerHTML = 'Загрузка атрибутов...';
                saveButton.disabled = true;
                modal.style.display = 'flex';
                modal.classList.remove('hidden');

                try {
                    const response = await fetch(`/admin/catalog/products/${productId}/configurable-attributes`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const payload = await response.json();

                    panelFetchedAttributes = payload?.data?.attributes ?? [];

                    if (!Array.isArray(panelFetchedAttributes) || !panelFetchedAttributes.length) {
                        content.innerHTML = '<p style="margin:0; color:#b45309;">Для выбранного семейства нет конфигурируемых атрибутов.</p>';
                        return;
                    }

                    renderPanelConfigurableAttributes(content, panelFetchedAttributes);
                    updatePanelConfigurableSaveState();
                } catch (error) {
                    content.innerHTML = '<p style="margin:0; color:#dc2626;">Не удалось загрузить атрибуты. Повторите попытку.</p>';
                }
            }

            function renderPanelConfigurableAttributes(container, attributes) {
                const blocks = attributes.map((attribute, index) => {
                    const options = (attribute.options || []).map((option) => {
                        return `
                            <label style="display:flex; align-items:center; gap:8px; padding:6px 0; cursor:pointer;">
                                <input type="checkbox" class="panel-super-attribute-option" data-attribute-code="${attribute.code}" value="${option.id}" checked onchange="updatePanelConfigurableSaveState()">
                                <span style="font-size:14px; color:#374151;">${option.name}</span>
                            </label>
                        `;
                    }).join('');

                    return `
                        <div style="${index ? 'margin-top:14px; padding-top:14px; border-top:1px solid #f3f4f6;' : ''}">
                            <p style="margin:0 0 6px; font-size:14px; font-weight:700; color:#111827;">${attribute.name}</p>
                            ${options || '<p style="margin:0; font-size:13px; color:#9ca3af;">Нет опций</p>'}
                        </div>
                    `;
                }).join('');

                container.innerHTML = blocks;
            }

            function closePanelConfigurableModal() {
                const modal = document.getElementById('panel-configurable-modal');
                modal.classList.add('hidden');
                modal.style.display = 'none';
                panelPendingType = null;
                panelPendingLabel = null;
                panelFetchedAttributes = [];
            }

            function collectPanelSuperAttributes() {
                const map = {};

                document.querySelectorAll('.panel-super-attribute-option:checked').forEach((input) => {
                    const code = input.getAttribute('data-attribute-code');
                    if (!map[code]) {
                        map[code] = [];
                    }

                    map[code].push(parseInt(input.value, 10));
                });

                return map;
            }

            function updatePanelConfigurableSaveState() {
                const saveButton = document.getElementById('panel-configurable-save-btn');
                const superAttributes = collectPanelSuperAttributes();
                const hasValidSelection = Object.keys(superAttributes).length > 0
                    && Object.values(superAttributes).every((options) => Array.isArray(options) && options.length > 0);

                saveButton.disabled = !hasValidSelection;
            }

            async function confirmPanelTypeWithAttributes() {
                if (!panelPendingType) {
                    return;
                }

                const superAttributes = collectPanelSuperAttributes();
                await submitPanelTypeUpdate(panelPendingType, superAttributes, panelPendingLabel);
            }

            async function submitPanelTypeUpdate(newType, superAttributes = null, newLabel = null) {
                const productId = {{ $product->id }};
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                    || document.querySelector('input[name="_token"]')?.value;

                const payload = { field: 'type', value: newType };

                if (superAttributes) {
                    payload.super_attributes = superAttributes;
                }

                try {
                    const response = await fetch('/admin/catalog/products/quick-update/' + productId, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        if (data?.errors) {
                            const firstError = Object.values(data.errors).flat()[0];
                            alert(firstError || 'Ошибка смены типа товара');
                            return;
                        }

                        alert(data?.message || 'Ошибка смены типа товара');
                        return;
                    }

                    if (data.success) {
                        if (newLabel) {
                            document.getElementById('panel-type-label').textContent = newLabel;
                        }
                        closePanelConfigurableModal();
                        window.location.reload();
                    }
                } catch (error) {
                    alert('Ошибка смены типа товара');
                }
            }
            // Delete product with confirm modal
            function confirmDeleteProduct(productId) {
                const emitter = document.getElementById('app')?.__vue_app__?.config?.globalProperties?.$emitter;
                if (emitter) {
                    emitter.emit('open-confirm-modal', {
                        agree: () => {
                            const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                                       || document.querySelector('input[name="_token"]')?.value;
                            fetch('/admin/catalog/products/edit/' + productId, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                            })
                            .then(r => r.json())
                            .then(data => {
                                emitter.emit('add-flash', { type: 'success', message: data.message || 'Товар удалён' });
                                if (window !== window.parent) {
                                    window.parent.postMessage({ type: 'product-deleted' }, '*');
                                } else {
                                    window.location.href = '{{ route("admin.catalog.products.index") }}';
                                }
                            })
                            .catch(() => {
                                emitter.emit('add-flash', { type: 'error', message: 'Ошибка удаления' });
                            });
                        }
                    });
                }
            }
        </script>


    @endPushOnce
</x-admin::layouts>
