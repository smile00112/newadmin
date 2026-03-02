@php
    $inventorySources = app(\Webkul\Inventory\Repositories\InventorySourceRepository::class)->findWhere(['status' => 1]);
    $productInventories = [];
    foreach ($inventorySources as $source) {
        $qty = old('inventories.' . $source->id)
            ?: ($product->inventories->where('inventory_source_id', $source->id)->pluck('qty')->first() ?? 0);
        $productInventories[$source->id] = [
            'id' => $source->id,
            'name' => $source->name,
            'qty' => (int)$qty
        ];
    }

    // Calculate total quantity
    $totalQty = $product->inventories->sum('qty');

    // Get manage_stock value
    $manageStock = old('manage_stock') ?? ($product->manage_stock ?? false);

    // Determine in_stock based on inventory qty (товар в наличии если qty > 0)
    $inStock = $totalQty > 0;
@endphp

{!! view_render_event('bagisto.admin.catalog.product.edit.form.inventories.controls.before', ['product' => $product]) !!}

<!-- Product Inventory Component Template -->
<script type="text/x-template" id="v-product-inventory-template">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200" data-block-id="product-inventory">
        <div class="p-6 space-y-6">
            <!-- In Stock Toggle -->
            <div class="flex items-center justify-between gap-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
                <div class="flex items-center gap-3">
                    <div :class="[
                        'w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300',
                        inStock ? 'bg-green-100' : 'bg-red-100'
                    ]">
                        <svg v-if="inStock" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg v-else class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">@{{ inStock ? 'В наличии' : 'Нет в наличии' }}</p>
                        <p class="text-sm text-gray-500">Отображается покупателям в каталоге</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer group" @click="toggleStock">
                    <input type="hidden" name="in_stock" :value="inStock ? 1 : 0">
                    <div :class="[
                        'relative rounded-full cursor-pointer transition-all duration-200',
                        isToggling ? 'bg-gray-400' : (inStock ? 'bg-green-500 shadow-lg shadow-green-500/30' : 'bg-gray-300')
                    ]" style="width: 52px; height: 28px;">
                        <span
                            class="absolute bg-white rounded-full shadow-md group-hover:scale-105 group-active:scale-95"
                            :style="{
                                width: '22px',
                                height: '22px',
                                top: '3px',
                                left: '3px',
                                transform: inStock ? 'translateX(24px)' : 'translateX(0)',
                                transition: 'transform 0.2s cubic-bezier(0.68, -0.30, 0.32, 1.30)'
                            }"
                        ></span>
                    </div>
                </label>
            </div>

            <!-- Manage Stock Toggle -->
            <div class="flex items-center justify-between gap-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
                <div class="flex items-center gap-3">
                    <div :class="[
                        'w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300',
                        manageStock ? 'bg-blue-100' : 'bg-gray-200'
                    ]">
                        <svg class="w-5 h-5" :class="manageStock ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">Учитывать количество на складе</p>
                        <p class="text-sm text-gray-500">Автоматически менять статус при 0 шт.</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer group" @click="manageStock = !manageStock">
                    <input type="hidden" name="manage_stock" :value="manageStock ? 1 : 0">
                    <div :class="[
                        'relative rounded-full cursor-pointer transition-all duration-200',
                        manageStock ? 'bg-blue-500 shadow-lg shadow-blue-500/30' : 'bg-gray-300'
                    ]" style="width: 52px; height: 28px;">
                        <span
                            class="absolute bg-white rounded-full shadow-md group-hover:scale-105 group-active:scale-95"
                            :style="{
                                width: '22px',
                                height: '22px',
                                top: '3px',
                                left: '3px',
                                transform: manageStock ? 'translateX(24px)' : 'translateX(0)',
                                transition: 'transform 0.2s cubic-bezier(0.68, -0.30, 0.32, 1.30)'
                            }"
                        ></span>
                    </div>
                </label>
            </div>

            <!-- Quantity Fields (shown when manageStock is true) -->
            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="manageStock" class="space-y-4">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Укажите количество товара на каждом складе</span>
                    </div>

                    <div v-for="source in inventorySources" :key="source.id" class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            @{{ source.name }}
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                :name="'inventories[' + source.id + ']'"
                                v-model.number="inventories[source.id]"
                                min="0"
                                class="w-full px-4 py-3 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all bg-white"
                                placeholder="0"
                                @input="onQuantityChange"
                            >
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-gray-400">шт.</span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Всего на складах:</span>
                        <span :class="[
                            'text-lg font-bold',
                            totalQuantity > 0 ? 'text-green-600' : 'text-red-600'
                        ]">
                            @{{ totalQuantity }} шт.
                        </span>
                    </div>
                </div>
            </transition>

            <!-- Hidden fields for inventories when manageStock is false -->
            <template v-if="!manageStock">
                <input
                    v-for="source in inventorySources"
                    :key="'hidden-' + source.id"
                    type="hidden"
                    :name="'inventories[' + source.id + ']'"
                    :value="inventories[source.id] || 0"
                >
            </template>
        </div>
    </div>
</script>

<script type="module">
    app.component('v-product-inventory', {
        template: '#v-product-inventory-template',

        props: {
            product: {
                type: Object,
                required: true
            }
        },

        data() {
            return {
                inStock: {{ $inStock ? 'true' : 'false' }},
                manageStock: {{ $manageStock ? 'true' : 'false' }},
                isToggling: false,
                inventorySources: @json(array_values($productInventories)),
                inventories: @json(collect($productInventories)->mapWithKeys(fn($item) => [$item['id'] => $item['qty']])->toArray())
            };
        },

        computed: {
            totalQuantity() {
                return Object.values(this.inventories).reduce((sum, qty) => sum + (parseInt(qty) || 0), 0);
            }
        },

        watch: {
            manageStock(newVal) {
                if (newVal) {
                    this.updateStockStatus();
                }
                // Update header display when manageStock changes
                this.$nextTick(() => {
                    this.updateHeaderDisplay(this.totalQuantity);
                });
            }
        },

        methods: {
            async toggleStock() {
                if (this.isToggling) return;

                // Оптимистичное обновление UI - сразу меняем состояние
                const previousInStock = this.inStock;
                const newInStock = !this.inStock;
                const newQty = newInStock ? 1 : 0;

                this.inStock = newInStock;
                this.isToggling = true;

                // Сразу обновляем локальные данные
                const firstSourceId = Object.keys(this.inventories)[0];
                if (firstSourceId) {
                    this.inventories[firstSourceId] = newQty;
                }

                // Сразу обновляем header
                this.updateHeaderDisplay(newQty);

                const productId = {{ $product->id }};
                const url = `{{ url('admin/catalog/products') }}/${productId}/toggle-stock`;

                try {
                    const response = await this.$axios.post(url);

                    if (!response.data.success) {
                        // Откатываем при ошибке
                        this.inStock = previousInStock;
                        if (firstSourceId) {
                            this.inventories[firstSourceId] = previousInStock ? 1 : 0;
                        }
                        this.updateHeaderDisplay(previousInStock ? 1 : 0);
                    }
                } catch (error) {
                    // Откатываем при ошибке
                    this.inStock = previousInStock;
                    if (firstSourceId) {
                        this.inventories[firstSourceId] = previousInStock ? 1 : 0;
                    }
                    this.updateHeaderDisplay(previousInStock ? 1 : 0);
                    this.$emitter.emit('add-flash', {
                        type: 'error',
                        message: 'Ошибка при изменении наличия'
                    });
                } finally {
                    this.isToggling = false;
                }
            },

            updateHeaderDisplay(quantity) {
                // Update the header availability badge
                const headerAvailability = document.querySelector('[data-header-availability]');
                const headerWrapper = document.querySelector('[data-header-availability-wrapper]');

                if (headerAvailability) {
                    const isInStock = quantity > 0;
                    const dot = headerAvailability.querySelector('span:first-child');
                    const text = headerAvailability.querySelector('[data-availability-text]');

                    // Update wrapper's manage-stock attribute
                    if (headerWrapper) {
                        headerWrapper.setAttribute('data-manage-stock', this.manageStock ? 'true' : 'false');
                    }

                    if (isInStock) {
                        headerAvailability.className = 'inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-500/30 backdrop-blur-sm rounded-full text-sm font-medium';
                        headerAvailability.setAttribute('data-in-stock', 'true');
                        if (dot) {
                            dot.className = 'w-2 h-2 bg-emerald-400 rounded-full animate-pulse';
                        }
                        if (text) {
                            // Show quantity only if manageStock is enabled
                            text.textContent = this.manageStock ? 'В наличии ' + quantity + ' шт.' : 'В наличии';
                        }
                    } else {
                        headerAvailability.className = 'inline-flex items-center gap-1.5 px-3 py-1 bg-red-500/30 backdrop-blur-sm rounded-full text-sm font-medium';
                        headerAvailability.setAttribute('data-in-stock', 'false');
                        if (dot) {
                            dot.className = 'w-2 h-2 bg-red-400 rounded-full';
                        }
                        if (text) {
                            text.textContent = 'Нет в наличии';
                        }
                    }
                }
            },

            onQuantityChange() {
                this.updateStockStatus();
                // Update header with new total quantity
                this.$nextTick(() => {
                    this.updateHeaderDisplay(this.totalQuantity);
                });
            },

            updateStockStatus() {
                if (this.manageStock) {
                    this.inStock = this.totalQuantity > 0;
                }
            }
        }
    });
</script>

{!! view_render_event('bagisto.admin.catalog.product.edit.form.inventories.controls.after', ['product' => $product]) !!}
