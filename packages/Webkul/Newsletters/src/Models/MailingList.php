<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MailingList extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'newsletters_mailing_lists';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_text',
        'active',
        'mailing_hours_from',
        'mailing_hours_to',
        'message_delay',
        'status', //
        //'start_at'
    ];

    /**
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'start_at' => 'datetime',
        'message_delay' => 'integer',

    ];

    /**
     * Get the whatsapp instances for the mailing list.
     */
    public function whatsappInstances(): HasMany
    {
        return $this->hasMany(VacapInstance::class);
    }

    /**
     * Get the customer numbers for the mailing list.
     */
    public function customerNumbers(): HasMany
    {
        return $this->hasMany(CustomerNumber::class);
    }


}
