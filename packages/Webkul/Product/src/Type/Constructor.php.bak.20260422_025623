<?php

namespace Webkul\Product\Type;

use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Checkout\Models\CartItem;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\DataTypes\CartItemValidationResult;
use Webkul\Product\Helpers\Indexers\Price\Constructor as ConstructorIndexer;
use Webkul\Product\Repositories\ProductAttributeValueRepository;
use Webkul\Product\Repositories\ProductConstructorRepository;
use Webkul\Product\Repositories\ProductCustomerGroupPriceRepository;
use Webkul\Product\Repositories\ProductImageRepository;
use Webkul\Product\Repositories\ProductInventoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductVideoRepository;
use Webkul\Tax\Facades\Tax;

class Constructor extends AbstractType
{
    /**
     * Skip attribute for constructor product type.
     *
     * @var array
     */
    protected $skipAttributes = [
        //'price',
        //'cost',
        //        'special_price',
        //        'special_price_from',
        //        'special_price_to',
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

                // Sync products with parent_id
                $products = [];
                foreach ($group->products as $groupProduct) {
                    $products[$groupProduct->id] = [
                        'sort' => $groupProduct->pivot->sort ?? 0,
                        'default' => $groupProduct->pivot->default ?? false,
                        'parent_id' => $product->id
                    ];
                }
                $newGroup->products()->sync($products);
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
     * Get product minimal price.
     *
     * For constructor products, return the base price from the price field.
     *
     * @return float
     */
    public function getMinimalPrice()
    {
        // For constructor products, base price = price field
        return (float) ($this->product->price ?? 0);
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

        $constructorQuantity = parent::handleQuantity((int) ($data['quantity'] ?? 1));

        // First, create the parent constructor product cart item
        $products = parent::prepareForCart($data);

        if (is_string($products)) {
            return $products;
        }

        // Override base price of constructor product with price field value
        $basePrice = (float) ($this->product->price ?? 0);
        $products[0]['base_price'] = $basePrice;
        $products[0]['base_price_incl_tax'] = $basePrice;
        $products[0]['price'] = core()->convertPrice($basePrice);
        $products[0]['price_incl_tax'] = $products[0]['price'];

        // Recalculate totals for constructor base price
        $products[0]['base_total'] = $basePrice * $constructorQuantity;
        $products[0]['base_total_incl_tax'] = $basePrice * $constructorQuantity;
        $products[0]['total'] = $products[0]['price'] * $constructorQuantity;
        $products[0]['total_incl_tax'] = $products[0]['price'] * $constructorQuantity;

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

                if (! in_array($product->type, ['simple', 'ingredient'])) {
                    return trans('product::app.checkout.cart.selected-products-simple');
                }

                /* need to check each individual quantity as well if don't have then show error */
                if (! $product->getTypeInstance()->haveSufficientQuantity($qty * $constructorQuantity)) {
                    return trans('product::app.checkout.cart.inventory-warning');
                }

                if (! $product->getTypeInstance()->isSaleable()) {
                    continue;
                }

                $cartProduct = $product->getTypeInstance()->prepareForCart([
                    'product_id' => $productId,
                    'quantity'   => $qty,
                    'parent_id'  => $this->product->id,
                ]);

                if (is_string($cartProduct)) {
                    return $cartProduct;
                }

                // Set parent_id for the cart product
                $cartProduct[0]['parent_id'] = $this->product->id;

                $cartProductsList[] = $cartProduct;

                // Accumulate totals (not prices) to parent constructor product
                // Each ingredient's total is for ONE dish, multiply by constructorQuantity for all dishes
                $products[0]['total'] += $cartProduct[0]['total'] * $constructorQuantity;
                $products[0]['total_incl_tax'] += $cartProduct[0]['total'] * $constructorQuantity;
                $products[0]['base_total'] += $cartProduct[0]['base_total'] * $constructorQuantity;
                $products[0]['base_total_incl_tax'] += $cartProduct[0]['base_total'] * $constructorQuantity;
                $products[0]['weight'] += $cartProduct[0]['total_weight'];
            }
        }

        // Recalculate per-unit prices after adding all ingredients
        // Price per unit = total / quantity
        if ($constructorQuantity > 0) {
            $products[0]['base_price'] = $products[0]['base_total'] / $constructorQuantity;
            $products[0]['base_price_incl_tax'] = $products[0]['base_total_incl_tax'] / $constructorQuantity;
            $products[0]['price'] = $products[0]['total'] / $constructorQuantity;
            $products[0]['price_incl_tax'] = $products[0]['total_incl_tax'] / $constructorQuantity;
        }

        if (empty($cartProductsList)) {
            return trans('product::app.checkout.cart.integrity.qty-missing');
        }

        // Merge all child products (ingredients) into the products array
        $childProducts = array_merge(...$cartProductsList);
        $products = array_merge($products, $childProducts);

        // Recalculate total weight for parent
        $products[0]['total_weight'] = $products[0]['base_total_weight'] = $products[0]['weight'] * $products[0]['quantity'];

        return $products;
    }

    /**
     * Compare options to determine if two cart items are the same product configuration.
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

        $constructor1 = $options1['constructor_options'] ?? [];
        $constructor2 = $options2['constructor_options'] ?? [];

        $drinks1 = $options1['drinks'] ?? [];
        $drinks2 = $options2['drinks'] ?? [];

        return $constructor1 == $constructor2 && $drinks1 == $drinks2;
    }

    /**
     * Returns price indexer class for a specific product type
     *
     * @return string
     */
    public function getPriceIndexer()
    {
        return app(ConstructorIndexer::class);
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
     * Validate cart item product price and other things.
     *
     * For constructor items the price is fixed at the moment the user configured
     * and added the dish to the cart. We must NOT recalculate the parent total
     * from current product_flat prices because:
     * - ingredient prices can be changed by admin at any time
     * - the user already agreed to the price shown in the cart
     * - Simple::validateCartItem() on children would update their base_total
     *   to the current DB price, silently changing the order total
     *
     * We only check whether the constructor or any of its children became inactive.
     */
    public function validateCartItem(CartItem $item): CartItemValidationResult
    {
        $validation = new CartItemValidationResult;

        if (parent::isCartItemInactive($item)) {
            $validation->itemIsInactive();

            return $validation;
        }

        if ($item->children->isEmpty()) {
            return $validation;
        }

        // Only check for inactive/invalid children — do NOT reprice them.
        // Calling Simple::validateCartItem() would update each child's base_total
        // to the current product_flat price, corrupting the constructor total.
        foreach ($item->children as $childItem) {
            if (parent::isCartItemInactive($childItem)) {
                $validation->itemIsInactive();
            }
        }

        // Parent base_total is not touched — it stays exactly as set when the
        // user configured the constructor (correct price, correct quantities).
        return $validation;
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

//        dump($product);
//        dump($minimalPrice);
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
