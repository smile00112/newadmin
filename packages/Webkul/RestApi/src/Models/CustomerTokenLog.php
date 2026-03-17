<?php

namespace Webkul\RestApi\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\Customer;

class CustomerTokenLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_token_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'token_name',
        'abilities',
        'issued_at',
        'expires_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the token log.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}

