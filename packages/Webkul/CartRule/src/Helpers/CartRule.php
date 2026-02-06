<?php

namespace Webkul\CartRule\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Webkul\CartRule\Repositories\CartRuleCouponRepository;
use Webkul\CartRule\Repositories\CartRuleCouponUsageRepository;
use Webkul\CartRule\Repositories\CartRuleCustomerRepository;
use Webkul\CartRule\Repositories\CartRuleRepository;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Models\CartItem;
use Webkul\Checkout\Repositories\CartItemRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Rule\Helpers\Validator;

class CartRule
{
    /**
     * @var \Webkul\Checkout\Contracts\Cart
     */
    protected $cart = null;

    /**
     * @var array
     */
    protected $itemTotals = [];

    /**
     * @var array
     */
    protected $cartRules = null;

    /**
     * Create a new helper instance.
     *
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CartRuleRepository $cartRuleRepository,
        protected CartRuleCouponRepository $cartRuleCouponRepository,
        protected CartRuleCustomerRepository $cartRuleCustomerRepository,
        protected CartRuleCouponUsageRepository $cartRuleCouponUsageRepository,
        protected ProductRepository $productRepository,
        protected CartItemRepository $cartItemRepository,
        protected Validator $validator
    ) {}

    /**
     * Collect discount on cart
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return void
     */
    public function collect($cart)
    {
        $this->cart = $cart;

        /**
         * If cart rules are not available then don't process further.
         */
        if (
            ! $this->haveCartRules()
            && ! (float) $cart->base_discount_amount
        ) {
            return;
        }

        $appliedCartRuleIds = [];

        $this->calculateCartItemTotals();

        foreach ($cart->items as $item) {
            $itemCartRuleIds = $this->process($item);

            $appliedCartRuleIds = array_merge($appliedCartRuleIds, $itemCartRuleIds);

            if (
                $item->children()->count()
                && $item->getTypeInstance()->isChildrenCalculated()
            ) {
                $this->divideDiscount($item);
            }
        }

        $this->cart->update([
            'applied_cart_rule_ids' => implode(',', array_unique($appliedCartRuleIds, SORT_REGULAR)),
        ]);

        $this->processShippingDiscount();

        $this->processFreeShippingDiscount();

        // Process gift products for applied rules
        $this->processGiftProducts($appliedCartRuleIds);

        if (! $this->checkCouponCode()) {
            cart()->removeCouponCode();
        }
    }

    /**
     * Returns cart rules
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCartRules()
    {
        if ($this->cartRules) {
            return $this->cartRules;
        }

        $this->cartRules = $this->getCartRuleQuery()
            ->with([
                'cart_rule_customer_groups',
                'cart_rule_channels',
                'cart_rule_coupon',
            ])
            ->get();

        return $this->cartRules;
    }

    /**
     * Check if cart rule can be applied
     *
     * @param  \Webkul\CartRule\Contracts\CartRule  $rule
     */
    public function canProcessRule($rule): bool
    {
        if ($rule->coupon_type) {
            if (! strlen($this->cart->coupon_code)) {
                return false;
            }

            /** @var \Webkul\CartRule\Models\CartRule $rule */
            // Laravel relation is used instead of repository for performance
            // reasons (cart_rule_coupon-relation is pre-loaded by self::getCartRuleQuery())
            $coupon = $rule->cart_rule_coupon()->where('code', $this->cart->coupon_code)->first();

            if (
                $coupon
                && $coupon->code === $this->cart->coupon_code
            ) {
                if (
                    $coupon->usage_limit
                    && $coupon->times_used >= $coupon->usage_limit
                ) {
                    return false;
                }

                if (
                    $this->cart->customer_id
                    && $coupon->usage_per_customer
                ) {
                    $couponUsage = $this->cartRuleCouponUsageRepository->findOneWhere([
                        'cart_rule_coupon_id' => $coupon->id,
                        'customer_id'         => $this->cart->customer_id,
                    ]);

                    if (
                        $couponUsage
                        && $couponUsage->times_used >= $coupon->usage_per_customer
                    ) {
                        return false;
                    }
                }
            } else {
                return false;
            }
        }

        if ($rule->usage_per_customer) {
            $ruleCustomer = $this->cartRuleCustomerRepository->findOneWhere([
                'cart_rule_id' => $rule->id,
                'customer_id'  => $this->cart->customer_id,
            ]);

            if (
                $ruleCustomer
                && $ruleCustomer->times_used >= $rule->usage_per_customer
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Cart item discount calculation process
     */
    public function process(CartItem $item): array
    {
        $item->discount_percent = 0;
        $item->discount_amount = 0;
        $item->base_discount_amount = 0;

        $appliedRuleIds = [];

        foreach ($rules = $this->getCartRules() as $rule) {
            if (! $this->canProcessRule($rule)) {
                continue;
            }

            if (! $this->validator->validate($rule, $item)) {
                continue;
            }

            if ($rule->coupon_code) {
                $item->coupon_code = $rule->coupon_code;
            }

            $quantity = $rule->discount_quantity ? min($item->quantity, $rule->discount_quantity) : $item->quantity;

            $discountAmount = $baseDiscountAmount = 0;

            switch ($rule->action_type) {
                case 'by_percent':
                    $rulePercent = min(100, $rule->discount_amount);

                    $discountAmount = ($quantity * $item->price - $item->discount_amount) * ($rulePercent / 100);

                    $baseDiscountAmount = ($quantity * $item->base_price - $item->base_discount_amount) * ($rulePercent / 100);

                    if (
                        ! $rule->discount_quantity
                        || $rule->discount_quantity > $quantity
                    ) {
                        $discountPercent = min(100, $item->discount_percent + $rulePercent);

                        $item->discount_percent = $discountPercent;
                    }

                    break;

                case 'by_fixed':
                    $discountAmount = $quantity * core()->convertPrice($rule->discount_amount);

                    $baseDiscountAmount = $quantity * $rule->discount_amount;

                    break;

                case 'cart_fixed':
                    if ($this->itemTotals[$rule->id]['total_items'] <= 1) {
                        $discountAmount = core()->convertPrice($rule->discount_amount);

                        $baseDiscountAmount = min($item->base_price * $quantity, $rule->discount_amount);
                    } else {
                        $discountRate = $item->base_price * $quantity / $this->itemTotals[$rule->id]['base_total_price'];

                        $maxDiscount = $rule->discount_amount * $discountRate;

                        $discountAmount = core()->convertPrice($maxDiscount);

                        $baseDiscountAmount = min($item->base_price * $quantity, $maxDiscount);
                    }

                    break;

                case 'buy_x_get_y':
                    if (
                        ! $rule->discount_step
                        || $rule->discount_amount > $rule->discount_step
                    ) {
                        break;
                    }

                    $buyAndDiscountQty = $rule->discount_step + $rule->discount_amount;

                    $qtyPeriod = floor($quantity / $buyAndDiscountQty);

                    $freeQty = $quantity - $qtyPeriod * $buyAndDiscountQty;

                    $discountQty = $qtyPeriod * $rule->discount_amount;

                    if ($freeQty > $rule->discount_step) {
                        $discountQty += $freeQty - $rule->discount_step;
                    }

                    $discountAmount = $discountQty * $item->price;

                    $baseDiscountAmount = $discountQty * $item->base_price;

                    break;

                case 'gift':
                    // For gift type, we don't apply discount to existing items
                    // The gift product will be added in collect() method
                    break;
            }

            $item->discount_amount = min(
                $item->discount_amount + $discountAmount,
                $item->price * $quantity
            );
            $item->base_discount_amount = min(
                $item->base_discount_amount + $baseDiscountAmount,
                $item->base_price * $quantity
            );

            $appliedRuleIds[$rule->id] = $rule->id;

            if ($rule->end_other_rules) {
                break;
            }
        }

        $item->applied_cart_rule_ids = implode(',', $appliedRuleIds);

        $item->save();

        return $appliedRuleIds;
    }

    /**
     * Cart shipping discount calculation process
     *
     * @return self|void
     */
    public function processShippingDiscount()
    {
        if (! $selectedShipping = $this->cart->selected_shipping_rate) {
            return;
        }

        $selectedShipping->discount_amount = 0;
        $selectedShipping->base_discount_amount = 0;

        $appliedRuleIds = [];

        foreach ($this->getCartRules() as $rule) {
            if (! $this->canProcessRule($rule)) {
                continue;
            }

            if (! $this->validator->validate($rule, $this->cart)) {
                continue;
            }

            if (
                ! $rule
                || ! $rule->apply_to_shipping
            ) {
                continue;
            }

            $discountAmount = $baseDiscountAmount = 0;

            switch ($rule->action_type) {
                case 'by_percent':
                    $rulePercent = min(100, $rule->discount_amount);

                    $discountAmount = ($selectedShipping->price - $selectedShipping->discount_amount) * $rulePercent / 100;

                    $baseDiscountAmount = ($selectedShipping->base_price - $selectedShipping->base_discount_amount) * $rulePercent / 100;

                    break;

                case 'by_fixed':
                    $discountAmount = core()->convertPrice($rule->discount_amount);

                    $baseDiscountAmount = $rule->discount_amount;

                    break;
            }

            $selectedShipping->discount_amount = min($selectedShipping->discount_amount + $discountAmount, $selectedShipping->price);

            $selectedShipping->base_discount_amount = min(
                $selectedShipping->base_discount_amount + $baseDiscountAmount,
                $selectedShipping->base_price
            );

            $selectedShipping->save();

            $appliedRuleIds[$rule->id] = $rule->id;

            if ($rule->end_other_rules) {
                break;
            }
        }

        $selectedShipping->save();

        $cartAppliedCartRuleIds = array_merge(explode(',', $this->cart->applied_cart_rule_ids), $appliedRuleIds);

        $cartAppliedCartRuleIds = array_filter($cartAppliedCartRuleIds);

        $cartAppliedCartRuleIds = array_unique($cartAppliedCartRuleIds);

        $this->cart->update([
            'applied_cart_rule_ids' => implode(',', $cartAppliedCartRuleIds),
        ]);

        return $this;
    }

    /**
     * Cart free shipping discount calculation process
     *
     * @return void
     */
    public function processFreeShippingDiscount()
    {
        if (! $selectedShipping = $this->cart->selected_shipping_rate) {
            return;
        }

        $selectedShipping->discount_amount = 0;

        $selectedShipping->base_discount_amount = 0;

        $appliedRuleIds = [];

        foreach ($this->cart->items->all() as $item) {
            foreach ($this->getCartRules() as $rule) {
                if (! $this->canProcessRule($rule)) {
                    continue;
                }

                /* given CartItem instance to the validator */
                if (! $this->validator->validate($rule, $item)) {
                    continue;
                }

                if (
                    ! $rule
                    || ! $rule->free_shipping
                ) {
                    continue;
                }

                $selectedShipping->price = 0;

                $selectedShipping->price_incl_tax = 0;

                $selectedShipping->base_price = 0;

                $selectedShipping->base_price_incl_tax = 0;

                $selectedShipping->save();

                $appliedRuleIds[$rule->id] = $rule->id;

                if ($rule->end_other_rules) {
                    break;
                }
            }
        }

        $cartAppliedCartRuleIds = array_merge(explode(',', $this->cart->applied_cart_rule_ids), $appliedRuleIds);

        $cartAppliedCartRuleIds = array_filter($cartAppliedCartRuleIds);

        $cartAppliedCartRuleIds = array_unique($cartAppliedCartRuleIds);

        $this->cart->update([
            'applied_cart_rule_ids' => implode(',', $cartAppliedCartRuleIds),
        ]);
    }

    /**
     * Calculate cart item totals for each rule
     *
     * @return array|void
     */
    public function calculateCartItemTotals()
    {
        foreach ($this->getCartRules() as $rule) {
            if ($rule->action_type != 'cart_fixed') {
                continue;
            }

            $totalPrice = $totalBasePrice = $validCount = 0;

            foreach ($this->cart->items as $item) {
                if (! $this->canProcessRule($rule)) {
                    continue;
                }

                if (! $this->validator->validate($rule, $item)) {
                    continue;
                }

                $quantity = $rule->discount_quantity ? min($item->quantity, $rule->discount_quantity) : $item->quantity;

                $totalBasePrice += $item->base_price * $quantity;

                $validCount++;
            }

            $this->itemTotals[$rule->id] = [
                'base_total_price' => $totalBasePrice,
                'total_items'      => $validCount,
            ];
        }
    }

    /**
     * Check if coupon code is applied or not
     */
    public function checkCouponCode(): bool
    {
        if (! $this->cart->coupon_code) {
            return true;
        }

        $coupons = $this->cartRuleCouponRepository->where(['code' => $this->cart->coupon_code])->get();

        foreach ($coupons as $coupon) {
            if (in_array($coupon->cart_rule_id, explode(',', $this->cart->applied_cart_rule_ids))) {
                return true;
            }
        }

        // If coupon is invalid, remove gift products associated with it
        $this->removeGiftProducts($this->cart->coupon_code);

        return false;
    }

    /**
     * Process gift products for applied cart rules
     *
     * @param  array  $appliedCartRuleIds
     * @return void
     */
    protected function processGiftProducts(array $appliedCartRuleIds): void
    {
        if (empty($appliedCartRuleIds)) {
            return;
        }

        $appliedCartRuleIds = array_unique($appliedCartRuleIds);

        foreach ($appliedCartRuleIds as $ruleId) {
            $rule = $this->cartRuleRepository->find($ruleId);

            if (
                $rule
                && $rule->action_type === 'gift'
                && $rule->gift_product_id
                && $this->canProcessRule($rule)
            ) {
                $this->addGiftProduct($rule);
            }
        }
    }

    /**
     * Add gift product to cart
     *
     * @param  \Webkul\CartRule\Contracts\CartRule  $rule
     * @return void
     */
    protected function addGiftProduct($rule): void
    {
        if (! $this->cart || ! $rule->gift_product_id) {
            return;
        }

        $product = $this->productRepository->find($rule->gift_product_id);

        if (! $product || ! $product->status) {
            return;
        }

        // Check if gift product already exists for this coupon
        $couponCode = $this->cart->coupon_code;
        $existingGiftItem = $this->cart->all_items->first(function ($item) use ($product, $couponCode, $rule) {
            $additional = $item->additional ?? [];
            return $item->product_id == $product->id
                && ($additional['is_gift'] ?? false) === true
                && ($additional['gift_coupon_code'] ?? null) === $couponCode
                && ($additional['gift_cart_rule_id'] ?? null) == $rule->id;
        });

        if ($existingGiftItem) {
            return;
        }

        try {
            // Prepare data for adding product with zero price
            $cartData = [
                'quantity' => 1,
                'additional' => [
                    'is_gift' => true,
                    'gift_coupon_code' => $couponCode,
                    'gift_cart_rule_id' => $rule->id,
                ],
            ];

            // Add product to cart using Cart facade
            $cartProducts = $product->getTypeInstance()->prepareForCart(array_merge([
                'cart_id' => $this->cart->id,
            ], $cartData));

            if (is_string($cartProducts)) {
                return;
            }

            $parentCartItem = null;

            foreach ($cartProducts as $cartProduct) {
                // Set zero price for gift product
                $cartProduct['price'] = 0;
                $cartProduct['base_price'] = 0;
                $cartProduct['additional'] = array_merge(
                    $cartProduct['additional'] ?? [],
                    $cartData['additional']
                );

                // Check if item already exists
                $cartItem = $this->getItemByProductForGift($cartProduct, $cartData);

                if (isset($cartProduct['parent_id'])) {
                    $cartProduct['parent_id'] = $parentCartItem->id;
                }

                if (! $cartItem) {
                    $cartItem = $this->cartItemRepository->create(array_merge($cartProduct, ['cart_id' => $this->cart->id]));
                } else {
                    if (
                        isset($cartProduct['parent_id'])
                        && $cartItem->parent_id !== $parentCartItem->id
                    ) {
                        $cartItem = $this->cartItemRepository->create(array_merge($cartProduct, ['cart_id' => $this->cart->id]));
                    } else {
                        $this->cartItemRepository->update($cartProduct, $cartItem->id);
                    }
                }

                // Ensure price is set to 0 for gift product and update totals
                if ($cartItem) {
                    $this->cartItemRepository->update([
                        'price' => 0,
                        'base_price' => 0,
                        'total' => 0,
                        'base_total' => 0,
                        'total_incl_tax' => 0,
                        'base_total_incl_tax' => 0,
                    ], $cartItem->id);
                }

                if (! $parentCartItem) {
                    $parentCartItem = $cartItem;
                }
            }
        } catch (\Exception $e) {
            // Silently fail if product cannot be added
            return;
        }
    }

    /**
     * Get cart item by product for gift
     *
     * @param  array  $cartProduct
     * @param  array  $data
     * @return \Webkul\Checkout\Models\CartItem|null
     */
    protected function getItemByProductForGift(array $cartProduct, array $data)
    {
        $items = $this->cart->all_items;

        foreach ($items as $item) {
            $additional = $item->additional ?? [];
            
            if (
                $item->product_id == $cartProduct['product_id']
                && ($additional['is_gift'] ?? false) === true
                && ($additional['gift_coupon_code'] ?? null) === ($data['additional']['gift_coupon_code'] ?? null)
                && ($additional['gift_cart_rule_id'] ?? null) == ($data['additional']['gift_cart_rule_id'] ?? null)
            ) {
                if (! isset($cartProduct['parent_id'])) {
                    return $item;
                }

                if ($item->parent_id == $cartProduct['parent_id']) {
                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * Remove gift products from cart
     *
     * @param  string|null  $couponCode
     * @return void
     */
    public function removeGiftProducts(?string $couponCode = null): void
    {
        if (! $this->cart) {
            return;
        }

        $items = $this->cart->all_items;

        foreach ($items as $item) {
            $additional = $item->additional ?? [];

            if (! ($additional['is_gift'] ?? false)) {
                continue;
            }

            // If coupon code is specified, remove only gifts for that coupon
            if ($couponCode !== null) {
                if (($additional['gift_coupon_code'] ?? null) !== $couponCode) {
                    continue;
                }
            }

            // Remove gift item
            cart()->removeItem($item->id);
        }
    }

    /**
     * Divide discount amount to children
     *
     * @param  \Webkul\Checkout\Contracts\CartItem  $item
     * @return void
     */
    protected function divideDiscount($item)
    {
        foreach ($item->children as $child) {
            $ratio = $item->base_total != 0 ? $child->base_total / $item->base_total : 0;

            foreach (['discount_amount', 'base_discount_amount'] as $column) {
                if (! $item->{$column}) {
                    continue;
                }

                $child->{$column} = round(($item->{$column} * $ratio), 4);

                $child->save();
            }
        }
    }

    /**
     * @return \Builder
     */
    public function getCartRuleQuery()
    {
        $customerGroup = $this->customerRepository->getCurrentGroup();

        return $this->cartRuleRepository
            ->leftJoin('cart_rule_customer_groups', 'cart_rules.id', '=',
                'cart_rule_customer_groups.cart_rule_id')
            ->leftJoin('cart_rule_channels', 'cart_rules.id', '=', 'cart_rule_channels.cart_rule_id')
            ->where('cart_rule_customer_groups.customer_group_id', $customerGroup->id)
            ->where('cart_rule_channels.channel_id', core()->getCurrentChannel()->id)
            ->where(function ($query) {
                /** @var Builder $query1 */
                $query->where('cart_rules.starts_from', '<=', Carbon::now()->format('Y-m-d H:m:s'))
                    ->orWhereNull('cart_rules.starts_from');
            })
            ->where(function ($query) {
                /** @var Builder $query2 */
                $query->where('cart_rules.ends_till', '>=', Carbon::now()->format('Y-m-d H:m:s'))
                    ->orWhereNull('cart_rules.ends_till');
            })
            ->where('status', 1)
            ->orderBy('sort_order', 'asc');
    }

    /**
     * Check if cart rules are available or not for current customer group and channel
     */
    public function haveCartRules(): bool
    {
        return (bool) $this->getCartRuleQuery()->count();
    }
}
