<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'phone',
        'active',
        'sending_message_count',
        'blocked',
        'company_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'sending_message_count' => 'integer',
        'blocked' => 'boolean',
    ];

    /**
     * Scope a query to only include active instances.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include non-blocked instances.
     */
    public function scopeNotBlocked($query)
    {
        return $query->where('blocked', false);
    }

    /**
     * Get the mailing lists that use this whatsapp instance.
     */
    public function mailingLists(): BelongsToMany
    {
        return $this->belongsToMany(MailingList::class, 'newsletters_mailing_list_whatsapp_instance', 'whatsapp_instance_id', 'mailing_list_id');
    }

    public function customerNumbers(): HasMany
    {
        return $this->hasMany(CustomerNumber::class, 'whatsapp_instance_id', 'id');
    }

    /**
     * Get the company that owns the whatsapp instance.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to only include instances for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
