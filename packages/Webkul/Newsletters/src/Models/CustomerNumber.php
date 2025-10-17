<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerNumber extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'newsletters_customer_numbers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone_number',
        'name',
        'greenapi_chat_id',
        'delivered',
        'viewed',
        'new_message',
        'mailing_list_id',
        'unsubscribed_at',
        'metadata',
    ];

    protected $casts = [
        'new_message' => 'boolean',
        'unsubscribed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the mailing list that owns the customer number.
     */
    public function mailingList(): BelongsTo
    {
        return $this->belongsTo(MailingList::class);
    }
}