<?php

namespace App\Repositories;

use App\Models\BonusLevel;
use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;
use Webkul\Customer\Models\CustomerProxy;

class BonusLevelRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return BonusLevel::class;
    }

    /**
     * Get level for customer based on calculation type.
     *
     * @param  \Webkul\Customer\Contracts\Customer  $customer
     * @param  string  $calculationType
     * @return BonusLevel|null
     */
    public function getLevelForCustomer($customer, string $calculationType): ?BonusLevel
    {
        $levels = $this->model->orderBy('sort_order', 'asc')->get();
        
        $selectedLevel = null;

        foreach ($levels as $level) {
            $meetsRequirements = false;

            switch ($calculationType) {
                case 'orders':
                    $meetsRequirements = $level->meetsRequirements(
                        $customer->bonus_total_orders ?? 0
                    );
                    break;

                case 'amount':
                    $meetsRequirements = $level->meetsRequirements(
                        null,
                        $customer->bonus_total_spent ?? 0
                    );
                    break;

                case 'cart_value':
                    // For cart value, we need to get current cart
                    $cart = cart()->getCart();
                    $cartValue = $cart ? $cart->base_grand_total : 0;
                    $meetsRequirements = $level->meetsRequirements(
                        null,
                        null,
                        $cartValue
                    );
                    break;
            }

            if ($meetsRequirements) {
                $selectedLevel = $level;
            }
        }

        return $selectedLevel;
    }
}
