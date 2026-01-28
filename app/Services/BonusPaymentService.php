<?php

namespace App\Services;

use App\Services\BonusService;
use Illuminate\Support\Facades\DB;
use Webkul\Checkout\Contracts\Cart;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\Sales\Models\OrderProxy;

class BonusPaymentService
{
    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(
        protected BonusService $bonusService
    ) {}

    /**
     * Check if customer can use bonus.
     *
     * @param  \Webkul\Customer\Contracts\Customer  $customer
     * @param  float  $amount
     * @return bool
     */
    public function canUseBonus($customer, float $amount): bool
    {
        if (! core()->getConfigData('bonus_system.general.enabled')) {
            return false;
        }

        $availableBalance = $this->bonusService->getAvailableBonusBalance($customer);

        return $availableBalance >= $amount;
    }

    /**
     * Apply bonus to cart.
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @param  float  $bonusAmount
     * @return void
     */
    public function applyBonusToCart(Cart $cart, float $bonusAmount): void
    {
        if (! $cart->customer_id) {
            throw new \RuntimeException('Cart must have a customer');
        }

        $customer = CustomerProxy::find($cart->customer_id);

        if (! $customer) {
            throw new \RuntimeException('Customer not found');
        }

        // Validate products
        $this->validateBonusProducts($cart);

        // Get max bonus percent
        $maxPercent = (float) (core()->getConfigData('bonus_system.general.max_bonus_percent') ?? 100);
        $maxBonusAmount = ($cart->base_grand_total * $maxPercent) / 100;

        // Limit bonus amount
        $bonusAmount = min($bonusAmount, $maxBonusAmount, $cart->base_grand_total);

        // Check available balance
        $availableBalance = $this->bonusService->getAvailableBonusBalance($customer);
        $bonusAmount = min($bonusAmount, $availableBalance);

        // Update cart
        $cart->update([
            'bonus_amount' => core()->convertPrice($bonusAmount),
            'base_bonus_amount' => $bonusAmount,
        ]);
    }

    /**
     * Remove bonus from cart.
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return void
     */
    public function removeBonusFromCart(Cart $cart): void
    {
        $cart->update([
            'bonus_amount' => 0,
            'base_bonus_amount' => 0,
        ]);
    }

    /**
     * Process bonus payment for order.
     *
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @param  float  $bonusAmount
     * @return void
     */
    public function processBonusPayment($order, float $bonusAmount): void
    {
        if ($bonusAmount <= 0) {
            return;
        }

        if (! $order->customer_id) {
            throw new \RuntimeException('Order must have a customer');
        }

        $customer = CustomerProxy::find($order->customer_id);

        if (! $customer) {
            throw new \RuntimeException('Customer not found');
        }

        DB::transaction(function () use ($order, $bonusAmount, $customer) {
            // Deduct bonuses
            $this->bonusService->deductBonus($customer, $bonusAmount, $order);

            // Update order
            $order->update([
                'bonus_amount' => core()->convertPrice($bonusAmount),
                'base_bonus_amount' => $bonusAmount,
            ]);
        });
    }

    /**
     * Validate products for bonus system.
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return void
     * @throws \RuntimeException
     */
    public function validateBonusProducts(Cart $cart): void
    {
        $includedProducts = core()->getConfigData('bonus_system.general.included_products');
        $excludedProducts = core()->getConfigData('bonus_system.general.excluded_products');

        $cartProductIds = $cart->items->pluck('product_id')->toArray();

        // If included products list is not empty, check if cart products are in the list
        if (! empty($includedProducts)) {
            $includedIds = is_array($includedProducts) ? $includedProducts : explode(',', $includedProducts);
            $includedIds = array_map('intval', $includedIds);
            
            $hasValidProduct = false;
            foreach ($cartProductIds as $productId) {
                if (in_array((int) $productId, $includedIds)) {
                    $hasValidProduct = true;
                    break;
                }
            }

            if (! $hasValidProduct) {
                throw new \RuntimeException('Cart does not contain products eligible for bonus system');
            }
        }

        // Check if cart contains excluded products
        if (! empty($excludedProducts)) {
            $excludedIds = is_array($excludedProducts) ? $excludedProducts : explode(',', $excludedProducts);
            $excludedIds = array_map('intval', $excludedIds);
            
            foreach ($cartProductIds as $productId) {
                if (in_array((int) $productId, $excludedIds)) {
                    throw new \RuntimeException('Cart contains products excluded from bonus system');
                }
            }
        }
    }
}
