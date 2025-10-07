<?php

namespace Webkul\Product\Type;

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
}
