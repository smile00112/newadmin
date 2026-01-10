<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountWarming extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'newsletters_account_warmings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'selected_account_ids',
        'phrases',
        'delay_from',
        'delay_to',
        'active',
        'status',
        'company_id',
        'start_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'selected_account_ids' => 'array',
        'phrases' => 'array',
        'active' => 'boolean',
        'delay_from' => 'integer',
        'delay_to' => 'integer',
        'start_at' => 'datetime',
    ];

    /**
     * Get the whatsapp instances for the account warming.
     */
    public function whatsappInstances(): BelongsToMany
    {
        return $this->belongsToMany(
            VacapInstance::class,
            'newsletters_account_warming_whatsapp_instance',
            'account_warming_id',
            'whatsapp_instance_id'
        );
    }

    /**
     * Get the participants for the account warming.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(AccountWarmingParticipant::class);
    }

    /**
     * Get the company that owns the account warming.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to only include account warmings for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include active account warmings.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}


