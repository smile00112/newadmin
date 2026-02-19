<?php

namespace Webkul\AlfabankPayment\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\Customer;

class SavedCard extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'alfabank_saved_cards';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'client_id',
        'binding_id',
        'card_mask',
        'card_type',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the customer that owns the saved card.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope a query to only include active cards.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include cards for a specific customer.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $customerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
