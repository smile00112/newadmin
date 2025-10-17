<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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


}
