<?php

namespace Webkul\Product\Type;

use Illuminate\Support\Str;
use Webkul\Admin\Validations\ConfigurableUniqueSku;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Checkout\Models\CartItem as CartItemModel;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\DataTypes\CartItemValidationResult;
use Webkul\Product\Facades\ProductImage;
use Webkul\Product\Helpers\Indexers\Price\ConfigurableConstructor as ConfigurableConstructorIndexer;
use Webkul\Product\Repositories\ProductAttributeValueRepository;
use Webkul\Product\Repositories\ProductConstructorRepository;
use Webkul\Product\Repositories\ProductCustomerGroupPriceRepository;
use Webkul\Product\Repositories\ProductImageRepository;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductVideoRepository;
use Webkul\Tax\Facades\Tax;

class ConfigurableConstructor extends AbstractType
{
    /**
     * Skip attribute for configurable constructor product type.
     *
     * @var array
     */
    protected $skipAttributes = [
        'price',
        'cost',
        'special_price',
        'special_price_from',
        'special_price_to',
        'length',
        'width',
        'height',
        'weight',
        'manage_stock',
    ];

    /**
     * These are the types which can be fillable when generating variant.
     *
     * @var array
     */
    protected $fillableVariantAttributeCodes = [
        'sku',
        'name',
        'url_key',
        'short_description',
        'description',
        'price',
        'weight',
        'status',
        'tax_category_id',
    ];

    /**
     * These are the types which can be fillable when generating variant.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $fillableVariantAttributes;

    /**
     * Is a composite product type.
     *
     * @var bool
     */
    protected $isComposite = true;

    /**
     * Show quantity box.
     *
     * @var bool
     */
    protected $showQuantityBox = true;

    /**
     * Product can be added to cart with options or not.
     *
     * @var bool
     */
    protected $canBeAddedToCartWithoutOptions = false;

    /**
     * Has child products i.e. variants.
     *
     * @var bool
     */
    protected $hasVariants = true;

    /**
     * Create a new product type instance.
     *
     * @return void
     */
    public function __construct(
        CustomerRepository $customerRepository,
        AttributeRepository $attributeRepository,
        ProductRepository $productRepository,
        ProductAttributeValueRepository $attributeValueRepository,
        ProductInventoryRepository $productInventoryRepository,
        ProductImageRepository $productImageRepository,
        ProductVideoRepository $productVideoRepository,
        ProductCustomerGroupPriceRepository $productCustomerGroupPriceRepository,
        protected ProductConstructorRepository $productConstructorRepository
    ) {
        parent::__construct(
            $customerRepository,
            $attributeRepository,
            $productRepository,
            $attributeValueRepository,
            $productInventoryRepository,
            $productImageRepository,
            $productVideoRepository,
            $productCustomerGroupPriceRepository
        );
    }

    /**
     * Create configurable constructor product.
     *
     * @return \Webkul\Product\Contracts\Product
     */
    public function create(array $data)
    {
        $product = parent::create($data);

        if (! isset($data['super_attributes'])) {
            return $product;
        }

        $this->fillableVariantAttributes = $this->attributeRepository->findWhereIn('code', $this->fillableVariantAttributeCodes);

        $superAttributes = [];

        foreach ($data['super_attributes'] as $attributeCode => $attributeOptions) {
            $attribute = $this->getAttributeByCode($attributeCode);

            $this->fillableVariantAttributes->push($attribute);

            $superAttributes[$attribute->code] = $attributeOptions;

            $product->super_attributes()->attach($attribute->id);
        }

        foreach (array_permutation($superAttributes) as $permutation) {
            $this->createVariant($product, $permutation, [
                'channel' => $data['channel'] ?? core()->getDefaultChannelCode(),
                'locale'  => $data['locale'] ?? core()->getDefaultLocaleCodeFromDefaultChannel(),
            ]);
        }

        return $product;
    }

    /**
     * Update configurable constructor product.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return \Webkul\Product\Contracts\Product
     */
    public function update(array $data, $id, $attributes = [])
    {
        $product = parent::update($data, $id, $attributes);

        if (! empty($attributes)) {
            return $product;
        }

        $this->fillableVariantAttributes = $this->attributeRepository->findWhereIn('code', $this->fillableVariantAttributeCodes);

        $previousVariantIds = $product->variants->pluck('id');

        foreach ($data['variants'] ?? [] as $variantId => $variantData) {
            if (Str::contains($variantId, 'variant_')) {
                $superAttributes = [];

                foreach ($product->super_attributes as $superAttribute) {
                    $superAttributes[$superAttribute->id] = $variantData[$superAttribute->code];

                    $this->fillableVariantAttributes->push($superAttribute);
                }

                $this->createVariant($product, $superAttributes, array_merge($variantData, [
                    'channel' => $data['channel'] ?? core()->getDefaultChannelCode(),
                    'locale'  => $data['locale'] ?? core()->getDefaultLocaleCodeFromDefaultChannel(),
                ]));
            } else {
                if (is_numeric($index = $previousVariantIds->search($variantId))) {
                    $previousVariantIds->forget($index);
                }

                $this->updateVariant(array_merge($variantData, [
                    'channel'         => $data['channel'] ?? core()->getDefaultChannelCode(),
                    'locale'          => $data['locale'] ?? core()->getDefaultLocaleCodeFromDefaultChannel(),
                    'tax_category_id' => $data['tax_category_id'] ?? null,
                ]), $variantId);
            }
        }

        foreach ($previousVariantIds as $variantId) {
            $this->productRepository->delete($variantId);
        }

        if (isset($data['constructor']) && is_array($data['constructor']) && ! empty($data['constructor'])) {
            $this->productConstructorRepository->saveConstructor($data, $product);
        } else {
            $product->constructor()->delete();
        }

        $this->ensureInventoryRecord($product);

        return $product;
    }

    /**
     * Create variant.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @param  array  $superAttributes
     * @param  array  $data
     * @return \Webkul\Product\Contracts\Product
     */
    public function createVariant($product, $superAttributes, $data = [])
    {
        $sku = $product->sku.'-variant-'.implode('-', $superAttributes);

        $data = array_merge([
            'sku'               => $sku,
            'name'              => 'Variant '.implode(' ', $superAttributes),
            'price'             => 0,
            'weight'            => 0,
            'status'            => 1,
            'tax_category_id'   => '',
            'url_key'           => $sku,
            'short_description' => $sku,
            'description'       => $sku,
            'inventories'       => [],
        ], $data);

        $variant = parent::create([
            'type'                => 'simple',
            'sku'                 => $sku,
            'attribute_family_id' => $product->attribute_family_id,
            'parent_id'           => $product->id,
        ]);

        foreach ($superAttributes as $attributeCode => $optionId) {
            $data[$attributeCode] = $optionId;
        }

        $this->attributeValueRepository->saveValues($data, $variant, $this->fillableVariantAttributes);

        $this->productInventoryRepository->saveInventories($data, $variant);

        $this->productImageRepository->upload($data, $variant, 'images');

        return $variant;
    }

    /**
     * Update variant.
     *
     * @param  int  $id
     * @return \Webkul\Product\Contracts\Product
     */
    public function updateVariant(array $data, $id)
    {
        $variant = $this->productRepository->find($id);

        $variant->update(['sku' => $data['sku']]);

        $this->attributeValueRepository->saveValues($data, $variant, $this->fillableVariantAttributes);

        $this->productInventoryRepository->saveInventories($data, $variant);

        $this->productImageRepository->upload($data, $variant, 'images');

        $variant->channels()->sync($variant->parent->channels->pluck('id')->toArray());

        return $variant;
    }

    /**
     * Copy relationships.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return void
     */
    protected function copyRelationships($product)
    {
        parent::copyRelationships($product);

        $attributesToSkip = config('products.skipAttributesOnCopy') ?? [];

        if (! in_array('super_attributes', $attributesToSkip) && ! in_array('variants', $attributesToSkip)) {
            foreach ($this->product->super_attributes as $superAttribute) {
                $product->super_attributes()->save($superAttribute);
            }

            foreach ($this->product->variants as $variant) {
                $newVariant = $variant->getTypeInstance()->copy();

                $newVariant->parent_id = $product->id;

                $newVariant->save();
            }
        }

        if (! in_array('constructor', $attributesToSkip)) {
            foreach ($this->product->constructor as $constructor) {
                $newConstructor = $constructor->replicate();
                $newConstructor->parent_id = $product->id;
                $newConstructor->save();

                foreach ($constructor->groups as $group) {
                    $newGroup = $group->replicate();
                    $newGroup->parent_id = $newConstructor->id;
                    $newGroup->save();

                    $products = [];
                    foreach ($group->products as $groupProduct) {
                        $products[$groupProduct->id] = [
                            'sort'      => $groupProduct->pivot->sort ?? 0,
                            'default'   => $groupProduct->pivot->default ?? false,
                            'parent_id' => $product->id,
                        ];
                    }
                    $newGroup->products()->sync($products);
                }
            }
        }
    }

    /**
     * Returns children ids.
     *
     * @return array
     */
    public function getChildrenIds()
    {
        $childrenIds = $this->product->variants()->pluck('id')->toArray();

        foreach ($this->product->constructor as $constructor) {
            foreach ($constructor->groups as $group) {
                $childrenIds = array_merge($childrenIds, $group->products->pluck('id')->toArray());
            }
        }

        return array_unique($childrenIds);
    }

    /**
     * Is item have quantity.
     *
     * @param  \Webkul\Checkout\Contracts\CartItem  $cartItem
     * @return bool
     */
    public function isItemHaveQuantity($cartItem)
    {
        $variantId = $cartItem->additional['selected_configurable_option'] ?? null;

        if (! $variantId) {
            return false;
        }

        $variantChild = $cartItem->children->firstWhere('product_id', $variantId);

        if (! $variantChild) {
            return false;
        }

        return $variantChild->getTypeInstance()->haveSufficientQuantity($cartItem->quantity);
    }

    /**
     * Return validation rules.
     *
     * @return array
     */
    public function getTypeValidationRules()
    {
        $rules = [
            'variants.*.name'   => 'required',
            'variants.*.sku'    => [
                'required',
                new ConfigurableUniqueSku($this->getChildrenIds()),
            ],
            'variants.*.price'  => 'required',
            'variants.*.weight' => 'required',
        ];

        $constructorRules = [
            'constructor_options'    => 'array',
            'constructor_options.*'  => 'array',
            'constructor_options.*.*' => 'integer|min:0',
        ];

        return array_merge($rules, $constructorRules);
    }

    /**
     * Return true if item can be moved to cart from wishlist.
     *
     * @param  \Webkul\Customer\Contracts\Wishlist  $item
     * @return bool
     */
    public function canBeMovedFromWishlistToCart($item)
    {
        return isset($item->additional['selected_configurable_option'])
            && isset($item->additional['constructor_options']);
    }

    /**
     * Get product prices.
     *
     * @return array
     */
    public function getProductPrices()
    {
        $minPrice = $this->getMinimalPrice();

        return [
            'regular' => [
                'price'           => $minPrice,
                'formatted_price' => core()->currency($minPrice),
            ],
        ];
    }

    /**
     * Get product minimal price.
     *
     * @return string
     */
    public function getPriceHtml()
    {
        return view('shop::products.prices.configurable', [
            'product' => $this->product,
            'prices'  => $this->getProductPrices(),
        ])->render();
    }

    /**
     * Add product. Returns error message if can't prepare product.
     *
     * @param  array  $data
     * @return array|string
     */
    public function prepareForCart($data)
    {
        $data['quantity'] = parent::handleQuantity((int) ($data['quantity'] ?? 1));

        if (empty($data['selected_configurable_option'])) {
            return trans('product::app.checkout.cart.missing-options');
        }

        $constructorOptions = $data['constructor_options'] ?? [];
        if (! is_array($constructorOptions)) {
            $constructorOptions = [];
        }

        $data = $this->getQtyRequest($data);

        $childProduct = $this->productRepository->find($data['selected_configurable_option']);

        if (! $childProduct || $childProduct->parent_id != $this->product->id) {
            return trans('product::app.checkout.cart.missing-options');
        }

        if (! $childProduct->haveSufficientQuantity($data['quantity'])) {
            return trans('product::app.checkout.cart.inventory-warning');
        }

        $variantPrice = $childProduct->getTypeInstance()->getFinalPrice();
        $baseTotal = $variantPrice * $data['quantity'];
        $basePrice = $variantPrice;
        $weight = $childProduct->weight ?? 0;

        $products = [
            [
                'product_id'          => $this->product->id,
                'sku'                 => $this->product->sku,
                'name'                => $this->product->name,
                'type'                => $this->product->type,
                'quantity'            => $data['quantity'],
                'price'               => core()->convertPrice($basePrice),
                'price_incl_tax'      => core()->convertPrice($basePrice),
                'base_price'          => $basePrice,
                'base_price_incl_tax' => $basePrice,
                'total'               => core()->convertPrice($baseTotal),
                'total_incl_tax'      => core()->convertPrice($baseTotal),
                'base_total'          => $baseTotal,
                'base_total_incl_tax' => $baseTotal,
                'weight'              => $weight,
                'total_weight'        => $weight * $data['quantity'],
                'base_total_weight'   => $weight * $data['quantity'],
                'additional'          => $this->getAdditionalOptions($data),
            ],
            [
                'parent_id'  => $this->product->id,
                'product_id' => (int) $data['selected_configurable_option'],
                'sku'        => $childProduct->sku,
                'name'       => $childProduct->name,
                'type'       => $childProduct->type,
                'additional' => [
                    'product_id' => (int) $data['selected_configurable_option'],
                    'parent_id'  => $this->product->id,
                ],
            ],
        ];

        $cartProductsList = [$products[1]];
        $ingredientsTotal = 0;

        foreach ($constructorOptions as $groupId => $selectedProducts) {
            if (! is_array($selectedProducts) || empty($selectedProducts)) {
                continue;
            }

            foreach ($selectedProducts as $productId => $qty) {
                if (! $qty) {
                    continue;
                }

                $ingredientProduct = $this->productRepository->find($productId);

                if (! $ingredientProduct || ! in_array($ingredientProduct->type, ['simple', 'ingredient'])) {
                    return trans('product::app.checkout.cart.selected-products-simple');
                }

                if (! $ingredientProduct->getTypeInstance()->haveSufficientQuantity($qty * $data['quantity'])) {
                    return trans('product::app.checkout.cart.inventory-warning');
                }

                if (! $ingredientProduct->getTypeInstance()->isSaleable()) {
                    continue;
                }

                // Per-unit constructor qty × parent line qty (e.g. 2 drinks → 2× milk).
                $ingredientLineQty = (int) $qty * (int) $data['quantity'];

                $cartProduct = $ingredientProduct->getTypeInstance()->prepareForCart([
                    'product_id' => $productId,
                    'quantity'   => $ingredientLineQty,
                    'parent_id'  => $this->product->id,
                ]);

                if (is_string($cartProduct)) {
                    return $cartProduct;
                }

                $cartProduct[0]['parent_id'] = $this->product->id;

                $cartProductsList[] = $cartProduct[0];
                $ingredientsTotal += $cartProduct[0]['base_total'];
            }
        }

        $products[0]['base_total'] += $ingredientsTotal;
        $products[0]['base_total_incl_tax'] += $ingredientsTotal;
        $products[0]['total'] = core()->convertPrice($products[0]['base_total']);
        $products[0]['total_incl_tax'] = core()->convertPrice($products[0]['base_total_incl_tax']);

        if ($data['quantity'] > 0) {
            $products[0]['base_price'] = $products[0]['base_total'] / $data['quantity'];
            $products[0]['base_price_incl_tax'] = $products[0]['base_total_incl_tax'] / $data['quantity'];
            $products[0]['price'] = $products[0]['total'] / $data['quantity'];
            $products[0]['price_incl_tax'] = $products[0]['total_incl_tax'] / $data['quantity'];
        }

        foreach (array_slice($cartProductsList, 1) as $ingredientItem) {
            $products[0]['weight'] += $ingredientItem['total_weight'] ?? 0;
        }
        $products[0]['total_weight'] = $products[0]['base_total_weight'] = $products[0]['weight'] * $products[0]['quantity'];

        $allChildren = array_merge(
            [$products[1]],
            array_slice($cartProductsList, 1)
        );

        return array_merge([$products[0]], $allChildren);
    }

    /**
     * Compare options.
     *
     * @param  array  $options1
     * @param  array  $options2
     * @return bool
     */
    public function compareOptions($options1, $options2)
    {
        if ($this->product->id != ($options2['product_id'] ?? null)) {
            return false;
        }

        $variant1 = $options1['selected_configurable_option'] ?? null;
        $variant2 = $options2['selected_configurable_option'] ?? null;

        if ($variant1 !== $variant2) {
            return false;
        }

        $constructor1 = $options1['constructor_options'] ?? [];
        $constructor2 = $options2['constructor_options'] ?? [];

        return $constructor1 === $constructor2;
    }

    /**
     * Return additional information for items.
     *
     * @param  array  $data
     * @return array
     */
    public function getAdditionalOptions($data)
    {
        $childProduct = $this->productRepository->find($data['selected_configurable_option'] ?? 0);

        if (! $childProduct) {
            return $data;
        }

        foreach ($this->product->super_attributes as $attribute) {
            $option = $attribute->options()->where('id', $childProduct->{$attribute->code})->first();

            if ($option) {
                $data['attributes'][$attribute->code] = [
                    'attribute_name' => $attribute->name ?: $attribute->admin_name,
                    'option_id'      => $option->id,
                    'option_label'   => $option->label ?: $option->admin_name,
                ];
            }
        }

        if (! empty($data['constructor_options'])) {
            $data['constructor_options'] = $data['constructor_options'];
        }

        return $data;
    }

    /**
     * Get actual ordered item.
     *
     * @param  \Webkul\Checkout\Contracts\CartItem  $item
     * @return \Webkul\Checkout\Contracts\CartItem|\Webkul\Sales\Contracts\OrderItem|\Webkul\Sales\Contracts\InvoiceItem|\Webkul\Sales\Contracts\ShipmentItem|\Webkul\Customer\Contracts\Wishlist
     */
    public function getOrderedItem($item)
    {
        $variantId = $item->additional['selected_configurable_option'] ?? null;

        if ($variantId) {
            $variantChild = $item->children->firstWhere('product_id', $variantId);

            if ($variantChild) {
                return $variantChild;
            }
        }

        return $item->children->first();
    }

    /**
     * Get product base image.
     *
     * @param  \Webkul\Customer\Contracts\Wishlist|\Webkul\Checkout\Contracts\CartItem  $item
     * @return array
     */
    public function getBaseImage($item)
    {
        $product = $item->product;

        if ($item instanceof \Webkul\Customer\Contracts\Wishlist) {
            if (isset($item->additional['selected_configurable_option'])) {
                $variantProduct = $this->productRepository->find($item->additional['selected_configurable_option']);
                if ($variantProduct) {
                    $product = $variantProduct;
                }
            }
        } else {
            $variantId = $item->additional['selected_configurable_option'] ?? null;
            if ($variantId) {
                $variantChild = $item->children->firstWhere('product_id', $variantId);
                if ($variantChild && $variantChild->product && count($variantChild->product->images ?? [])) {
                    $product = $variantChild->product;
                }
            }
        }

        return ProductImage::getProductBaseImage($product);
    }

    /**
     * Validate cart item product price.
     *
     * @param  \Webkul\Product\Type\CartItem  $item
     */
    public function validateCartItem(CartItemModel $item): CartItemValidationResult
    {
        $validation = new CartItemValidationResult;

        if ($this->isCartItemInactive($item)) {
            $validation->itemIsInactive();

            return $validation;
        }

        $variantId = $item->additional['selected_configurable_option'] ?? null;

        if (! $variantId) {
            return $validation;
        }

        $variant = $this->productRepository->find($variantId);

        if (! $variant) {
            return $validation;
        }

        $baseTotal = $variant->getTypeInstance()->getFinalPrice($item->quantity) * $item->quantity;

        $constructorOptions = $item->additional['constructor_options'] ?? [];

        if (is_array($constructorOptions)) {
            foreach ($constructorOptions as $selectedProducts) {
                if (! is_array($selectedProducts)) {
                    continue;
                }

                foreach ($selectedProducts as $productId => $qty) {
                    $qty = (int) $qty;

                    if ($qty <= 0) {
                        continue;
                    }

                    $ingredient = $this->productRepository->find((int) $productId);

                    if (! $ingredient || ! in_array($ingredient->type, ['simple', 'ingredient'])) {
                        continue;
                    }

                    $baseTotal += $ingredient->getTypeInstance()->getFinalPrice($qty * $item->quantity) * $qty * $item->quantity;
                }
            }
        }

        foreach ($item->children as $childItem) {
            if ((int) $childItem->product_id === (int) $variantId) {
                continue;
            }

            $childValidation = $childItem->getTypeInstance()->validateCartItem($childItem);

            if ($childValidation->isItemInactive()) {
                $validation->itemIsInactive();
            }

            if ($childValidation->isCartInvalid()) {
                $validation->cartIsInvalid();
            }

        }

        $baseTotal = round($baseTotal, 4);

        if (Tax::isInclusiveTaxProductPrices()) {
            $itemBaseTotal = $item->base_total_incl_tax;
        } else {
            $itemBaseTotal = $item->base_total;
        }

        if ($baseTotal == $itemBaseTotal) {
            return $validation;
        }

        $basePrice = $item->quantity > 0 ? $baseTotal / $item->quantity : 0;

        $item->base_total = $baseTotal;
        $item->base_total_incl_tax = $baseTotal;
        $item->base_price = $basePrice;
        $item->base_price_incl_tax = $basePrice;

        $item->price = core()->convertPrice($basePrice);
        $item->price_incl_tax = $item->price;
        $item->total = core()->convertPrice($baseTotal);
        $item->total_incl_tax = $item->total;

        $item->save();

        return $validation;
    }

    /**
     * Is product have sufficient quantity.
     */
    public function haveSufficientQuantity(int $qty): bool
    {
        $hasVariantStock = false;

        foreach ($this->product->variants as $variant) {
            if ($variant->haveSufficientQuantity($qty)) {
                $hasVariantStock = true;
                break;
            }
        }

        if (! $hasVariantStock) {
            return (bool) core()->getConfigData('catalog.inventory.stock_options.back_orders');
        }

        if ($this->product->constructor->isEmpty()) {
            return true;
        }

        foreach ($this->product->constructor as $constructor) {
            foreach ($constructor->groups as $group) {
                foreach ($group->products as $product) {
                    if ($product->getTypeInstance()->haveSufficientQuantity($qty)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Return true if this product type is saleable.
     *
     * @return bool
     */
    public function isSaleable()
    {
        $hasSaleableVariant = false;

        foreach ($this->product->variants as $variant) {
            if ($variant->isSaleable()) {
                $hasSaleableVariant = true;
                break;
            }
        }

        if (! $hasSaleableVariant) {
            return false;
        }

        if ($this->product->constructor->isEmpty()) {
            return true;
        }

        foreach ($this->product->constructor as $constructor) {
            foreach ($constructor->groups as $group) {
                foreach ($group->products as $product) {
                    if ($product->isSaleable()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Return total quantity.
     *
     * @return int
     */
    public function totalQuantity()
    {
        $total = 0;

        foreach ($this->product->variants as $variant) {
            $total += $variant->totalQuantity();
        }

        return $total;
    }

    /**
     * Returns price indexer class for a specific product type.
     *
     * @return \Webkul\Product\Helpers\Indexers\Price\ConfigurableConstructor
     */
    public function getPriceIndexer()
    {
        return app(ConfigurableConstructorIndexer::class);
    }

    /**
     * Ensure product has inventory record for admin display.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return void
     */
    protected function ensureInventoryRecord($product)
    {
        if ($product->inventories->count() == 0) {
            $this->productInventoryRepository->create([
                'product_id'          => $product->id,
                'vendor_id'           => 0,
                'inventory_source_id' => 1,
                'qty'                 => 999,
            ]);
        } else {
            foreach ($product->inventories as $inventory) {
                if ($inventory->qty <= 0) {
                    $inventory->update(['qty' => 999]);
                }
            }
        }
    }
}
