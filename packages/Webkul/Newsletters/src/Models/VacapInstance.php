<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VacapInstance extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'newsletters_whatsapp_instances';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'link_name',
        'login',
        'password',
        'mailing_list_id',
    ];

    /**
     * Get the mailing list that owns the whatsapp instance.
     */
    public function mailingList(): BelongsTo
    {
        return $this->belongsTo(MailingList::class);
    }

    public function customerNumbers(): HasMany
    {
        return $this->hasMany(CustomerNumber::class, 'whatsapp_instance_id', 'id');
    }
}
