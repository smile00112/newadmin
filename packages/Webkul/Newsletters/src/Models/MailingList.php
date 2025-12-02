<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'message_links',
        'active',
        'mailing_hours_from',
        'mailing_hours_to',
        'message_delay_from',
        'message_delay_to',
        'start_at',
        'status', // created, pending, completed
        'max_messages_per_instance',
        'company_id',
    ];

    /**
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'start_at' => 'datetime',
        'message_delay_from' => 'integer',
        'message_delay_to' => 'integer',
        'message_links' => 'array',
        'max_messages_per_instance' => 'integer',
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

    /**
     * Get the company that owns the mailing list.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to only include mailing lists for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
