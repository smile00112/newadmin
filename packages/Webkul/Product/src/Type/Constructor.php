<?php

namespace Webkul\Product\Type;

use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Helpers\Indexers\Price\Grouped as GroupedIndexer;
use Webkul\Product\Repositories\ProductAttributeValueRepository;
use Webkul\Product\Repositories\ProductConstructorRepository;
use Webkul\Product\Repositories\ProductCustomerGroupPriceRepository;
use Webkul\Product\Repositories\ProductImageRepository;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductVideoRepository;

class Constructor extends AbstractType
{
    /**
     * Skip attribute for constructor product type.
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
        'depth',
        'manage_stock',
    ];

    /**
     * Is a composite product type.
     *
     * @var bool
     */
    protected $isComposite = true;

    /**
     * Product can be added to cart with options or not.
     *
     * @var bool
     */
    protected $canBeAddedToCartWithoutOptions = false;

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
     * Update.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return \Webkul\Product\Contracts\Product
     */
    public function update(array $data, $id, $attributes = [])
    {
        $product = parent::update($data, $id);

        if (! empty($attributes)) {
            return $product;
        }

        // Only save constructor data if it exists and has content
        if (isset($data['constructor']) && is_array($data['constructor']) && !empty($data['constructor'])) {
            $this->productConstructorRepository->saveConstructor($data, $product);
        } else {
            // If no constructor data, remove existing constructors
            $product->constructor()->delete();
        }

        // Ensure constructor products have inventory records for admin display
        $this->ensureInventoryRecord($product);

        // Update product price based on constructor configuration
        $this->updateProductPrice($product);

        return $product;
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

        if (in_array('constructor', $attributesToSkip)) {
            return;
        }

        foreach ($this->product->constructor as $constructor) {
            $newConstructor = $constructor->replicate();
            $newConstructor->parent_id = $product->id;
            $newConstructor->save();

            foreach ($constructor->groups as $group) {
                $newGroup = $group->replicate();
                $newGroup->parent_id = $newConstructor->id;
                $newGroup->save();

                $newGroup->products()->sync($group->products->pluck('id'));
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
        $childrenIds = [];

        foreach ($this->product->constructor as $constructor) {
            foreach ($constructor->groups as $group) {
                $childrenIds = array_merge($childrenIds, $group->products->pluck('id')->toArray());
            }
        }

        return array_unique($childrenIds);
    }

    /**
     * Check if catalog rule can be applied.
     *
     * @return bool
     */
    public function priceRuleCanBeApplied()
    {
        return false;
    }

    /**
     * Is saleable.
     *
     * @return bool
     */
    public function isSaleable()
    {
        if (! $this->product->status) {
            return false;
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
     * Is product have sufficient quantity.
     */
    public function haveSufficientQuantity(int $qty): bool
    {
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
     * Add product. Returns error message if can't prepare product.
     *
     * @param  array  $data
     * @return array|string
     */
    public function prepareForCart($data)
    {
        if (
            ! isset($data['constructor_options'])
            || ! is_array($data['constructor_options'])
        ) {
            return trans('product::app.checkout.cart.missing-options');
        }

        $cartProductsList = [];

        foreach ($data['constructor_options'] as $groupId => $selectedProducts) {
            if (! is_array($selectedProducts) || empty($selectedProducts)) {
                continue;
            }

            foreach ($selectedProducts as $productId => $qty) {
                if (! $qty) {
                    continue;
                }

                $product = $this->productRepository->find($productId);

                if ($product->type !== 'simple') {
                    return trans('product::app.checkout.cart.selected-products-simple');
                }

                $cartProducts = $product->getTypeInstance()->prepareForCart([
                    'product_id' => $productId,
                    'quantity'   => $qty,
                ]);

                if (is_string($cartProducts)) {
                    return $cartProducts;
                }

                $cartProductsList[] = $cartProducts;
            }
        }

        $products = array_merge(...$cartProductsList);

        if (! count($products)) {
            return trans('product::app.checkout.cart.integrity.qty-missing');
        }

        return $products;
    }

    /**
     * Returns price indexer class for a specific product type
     *
     * @return string
     */
    public function getPriceIndexer()
    {
        return app(GroupedIndexer::class);
    }

    /**
     * Returns validation rules.
     *
     * @return array
     */
    public function getTypeValidationRules()
    {
        return [
            'constructor_options' => 'array',
            'constructor_options.*' => 'array',
            'constructor_options.*.*' => 'integer|min:0',
        ];
    }

    /**
     * Ensure constructor product has inventory record for admin display.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return void
     */
    protected function ensureInventoryRecord($product)
    {
        // Check if product has any inventory records
        if ($product->inventories->count() == 0) {
            // Create a default inventory record for admin display
            $this->productInventoryRepository->create([
                'product_id' => $product->id,
                'vendor_id' => 0, // Default vendor
                'inventory_source_id' => 1, // Default inventory source
                'qty' => 999, // High quantity to show as available
            ]);
        } else {
            // Update existing inventory record to ensure it shows as available
            foreach ($product->inventories as $inventory) {
                if ($inventory->qty <= 0) {
                    $inventory->update(['qty' => 999]);
                }
            }
        }
    }


    /**
     * Update product price based on constructor configuration.
     *
     * @param  \Webkul\Product\Models\Product  $product
     * @return void
     */
    protected function updateProductPrice($product)
    {
        // Refresh the product to get latest constructor data
        $product->load('constructor.groups.products');

        // Calculate the minimal price from default products
        $minimalPrice = $this->calculateMinimalPrice();

        // Update the product's price attribute
//        $product->update(['price' => $minimalPrice]);
//
//        $product->product_flats()->update([
//            'price' => $minimalPrice
//        ]);
        //TODO refactor
        $affectedRows = DB::table('product_flat')
            ->where('product_id', $product->id)
            ->update(['price' => $minimalPrice]);
//dd($affectedRows);

    }

    /**
     * Calculate minimal price from default products.
     *
     * @return float
     */
    protected function calculateMinimalPrice()
    {
        $totalPrice = 1;
        $hasDefaultProducts = false;

        // Ensure we have the latest constructor data
        $this->product->load('constructor.groups.products');

        foreach ($this->product->constructor as $constructor) {
            foreach ($constructor->groups as $group) {
//dump($group);
                // Get all products from this group and filter for default ones
                $allProducts = $group->products;
                $defaultProducts = $allProducts->filter(function ($product) {
                    return $product->pivot->default == true || $product->pivot->default == 1;
                });
//dump($defaultProducts);
                if ($defaultProducts->count() > 0) {
                    $hasDefaultProducts = true;
                    // Get the cheapest default product from this group
                    $cheapestProduct = $defaultProducts->min(function ($product) {
                        return $product->getTypeInstance()->getMinimalPrice();
                    });

//dump($cheapestProduct);
                    $totalPrice += $cheapestProduct;
                }
            }
        }
//dd(($hasDefaultProducts ? $totalPrice : 0));
        return $hasDefaultProducts ? $totalPrice : 0;
    }
}
