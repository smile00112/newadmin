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

    $totalQty = $product->inventories->sum('qty');
    $manageStock = old('manage_stock') ?? ($product->manage_stock ?? false);
    $inStock = $totalQty > 0;
@endphp

{!! view_render_event('bagisto.admin.catalog.product.edit.form.inventories.controls.before', ['product' => $product]) !!}

<v-product-inventory></v-product-inventory>

@pushOnce('scripts')
    <script type="text/x-template" id="v-product-inventory-template">
        <div v-if="manageStock" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-6">
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
                        <p class="text-sm text-gray-500">Статус наличия товара</p>
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

            <!-- Quantity Fields -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Количество товара на складах</span>
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
        </div>
    </script>

    <script type="module">
        app.component('v-product-inventory', {
            template: '#v-product-inventory-template',

            data() {
                return {
                    inStock: @json((bool) $inStock),
                    manageStock: @json((bool) $manageStock),
                    isToggling: false,
                    inventorySources: @json(array_values($productInventories)),
                    inventories: @json(collect($productInventories)->mapWithKeys(fn($item) => [$item['id'] => $item['qty']])->toArray()),
                };
            },

            computed: {
                totalQuantity() {
                    return Object.values(this.inventories).reduce((sum, qty) => sum + (parseInt(qty) || 0), 0);
                }
            },

            mounted() {
                // Listen to the standard Bagisto manage_stock checkbox
                const checkbox = document.querySelector('input[name="manage_stock"]');
                if (checkbox) {
                    this.manageStock = checkbox.checked;
                    checkbox.addEventListener('change', () => {
                        this.manageStock = checkbox.checked;
                    });
                }
            },

            methods: {
                async toggleStock() {
                    if (this.isToggling) return;

                    const previousInStock = this.inStock;
                    const newInStock = !this.inStock;
                    const newQty = newInStock ? 1 : 0;

                    this.inStock = newInStock;
                    this.isToggling = true;

                    const firstSourceId = Object.keys(this.inventories)[0];
                    if (firstSourceId) {
                        this.inventories[firstSourceId] = newQty;
                    }

                    this.updateHeaderDisplay(newQty);

                    const productId = {{ $product->id }};
                    const url = `{{ url('admin/catalog/products') }}/${productId}/toggle-stock`;

                    try {
                        const response = await this.$axios.post(url);
                        if (!response.data.success) {
                            this.rollback(previousInStock, firstSourceId);
                        }
                    } catch (error) {
                        this.rollback(previousInStock, firstSourceId);
                        this.$emitter.emit('add-flash', { type: 'error', message: 'Ошибка при изменении наличия' });
                    } finally {
                        this.isToggling = false;
                    }
                },

                rollback(previousInStock, firstSourceId) {
                    this.inStock = previousInStock;
                    if (firstSourceId) {
                        this.inventories[firstSourceId] = previousInStock ? 1 : 0;
                    }
                    this.updateHeaderDisplay(previousInStock ? 1 : 0);
                },

                updateHeaderDisplay(quantity) {
                    const headerAvailability = document.querySelector('[data-header-availability]');
                    const headerWrapper = document.querySelector('[data-header-availability-wrapper]');

                    if (!headerAvailability) return;

                    const isInStock = quantity > 0;
                    const dot = headerAvailability.querySelector('span:first-child');
                    const text = headerAvailability.querySelector('[data-availability-text]');

                    if (headerWrapper) {
                        headerWrapper.setAttribute('data-manage-stock', this.manageStock ? 'true' : 'false');
                    }

                    if (isInStock) {
                        headerAvailability.className = 'inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-500/30 backdrop-blur-sm rounded-full text-sm font-medium';
                        headerAvailability.setAttribute('data-in-stock', 'true');
                        if (dot) dot.className = 'w-2 h-2 bg-emerald-400 rounded-full animate-pulse';
                        if (text) text.textContent = this.manageStock ? 'В наличии ' + quantity + ' шт.' : 'В наличии';
                    } else {
                        headerAvailability.className = 'inline-flex items-center gap-1.5 px-3 py-1 bg-red-500/30 backdrop-blur-sm rounded-full text-sm font-medium';
                        headerAvailability.setAttribute('data-in-stock', 'false');
                        if (dot) dot.className = 'w-2 h-2 bg-red-400 rounded-full';
                        if (text) text.textContent = 'Нет в наличии';
                    }
                },

                onQuantityChange() {
                    if (this.manageStock) {
                        this.inStock = this.totalQuantity > 0;
                    }
                    this.$nextTick(() => this.updateHeaderDisplay(this.totalQuantity));
                }
            }
        });
    </script>
@endPushOnce

{!! view_render_event('bagisto.admin.catalog.product.edit.form.inventories.controls.after', ['product' => $product]) !!}
