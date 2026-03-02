<?php

namespace Webkul\Product\Helpers\Indexers\Price;

class Constructor extends AbstractType
{
    /**
     * Returns product specific pricing for customer group
     *
     * @return array
     */
    public function getIndices()
    {
        return [
            'min_price'         => $this->getMinimalPrice() ?? 0,
            'regular_min_price' => $this->getRegularMinimalPrice() ?? 0,
            'max_price'         => $this->getMaximumPrice() ?? 0,
            'regular_max_price' => $this->getRegularMaximumPrice() ?? 0,
            'product_id'        => $this->product->id,
            'channel_id'        => $this->channel->id,
            'customer_group_id' => $this->customerGroup->id,
        ];
    }

    /**
     * Get product minimal price.
     *
     * For constructor products, return the base price from the price field.
     *
     * @param  int  $qty
     * @return float
     */
    public function getMinimalPrice($qty = null)
    {
        // For constructor products, base price = price field
        return (float) ($this->product->price ?? 0);
    }

    /**
     * Get product regular minimal price.
     *
     * @return float
     */
    public function getRegularMinimalPrice()
    {
        // For constructor products, regular price = price field
        return (float) ($this->product->price ?? 0);
    }

    /**
     * Get product maximum price.
     *
     * @return float
     */
    public function getMaximumPrice()
    {
        // For constructor products, max price = price field
        // (ingredients are added in cart, not in index)
        return (float) ($this->product->price ?? 0);
    }

    /**
     * Get product regular maximum price.
     *
     * @return float
     */
    public function getRegularMaximumPrice()
    {
        // For constructor products, regular max price = price field
        return (float) ($this->product->price ?? 0);
    }
}
