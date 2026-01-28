<?php

namespace App\Payment;

use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;
use Webkul\Customer\Models\CustomerProxy;
use App\Services\BonusPaymentService;

class BonusPayment extends Payment
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'bonus';

    /**
     * Create a new payment method instance.
     *
     * @return void
     */
    public function __construct(
        protected BonusPaymentService $bonusPaymentService
    ) {
        parent::__construct();
    }

    /**
     * Get redirect url.
     *
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return '';
    }

    /**
     * Check if payment method is available.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if (! $this->getConfigData('active')) {
            return false;
        }

        if (! core()->getConfigData('bonus_system.general.enabled')) {
            return false;
        }

        if (! $this->cart) {
            $this->setCart();
        }

        if (! $this->cart || ! $this->cart->customer_id) {
            return false;
        }

        $customer = CustomerProxy::find($this->cart->customer_id);

        if (! $customer) {
            return false;
        }

        // Check if customer has available bonuses
        $availableBalance = app(BonusPaymentService::class)
            ->bonusService
            ->getAvailableBonusBalance($customer);

        if ($availableBalance <= 0) {
            return false;
        }

        // Check if cart has eligible products
        try {
            app(BonusPaymentService::class)->validateBonusProducts($this->cart);
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    /**
     * Get payment method image.
     *
     * @return string
     */
    public function getImage(): string
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/bonus-payment.png', 'shop');
    }
}
