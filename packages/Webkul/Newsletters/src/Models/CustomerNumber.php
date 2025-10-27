<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'whatsapp_instance_id',
        //'unsubscribed_at',
        'incoming_message'
        //'metadata',
    ];

    protected $casts = [
        'delivered' => 'boolean',
        'viewed' => 'boolean',
        'incoming_message' => 'boolean',
        //'unsubscribed_at' => 'datetime',
        //'metadata' => 'array',
    ];

    /**
     * Get the mailing list that owns the customer number.
     */
    public function mailingList(): BelongsTo
    {
        return $this->belongsTo(MailingList::class);
    }

    /**
     * Get the mailing instance.
     */
    public function whatsAppInstance(): belongsTo
    {
        return $this->belongsTo(VacapInstance::class, 'whatsapp_instance_id', 'id', 'whatsapp_instance_id');
    }
}
