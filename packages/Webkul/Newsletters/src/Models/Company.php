<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'companies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the mailing lists for the company.
     */
    public function mailingLists(): HasMany
    {
        return $this->hasMany(MailingList::class);
    }

    /**
     * Get the whatsapp instances for the company.
     */
    public function whatsappInstances(): HasMany
    {
        return $this->hasMany(VacapInstance::class);
    }

    /**
     * Get the customer numbers for the company.
     */
    public function customerNumbers(): HasMany
    {
        return $this->hasMany(CustomerNumber::class);
    }

    /**
     * Get the contact groups for the company.
     */
    public function contactGroups(): HasMany
    {
        return $this->hasMany(NewslettersContactGroup::class);
    }

    /**
     * Get the contacts for the company.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(NewslettersContact::class);
    }

    /**
     * Get the stop list entries for the company.
     */
    public function stopListEntries(): HasMany
    {
        return $this->hasMany(StopList::class);
    }

    /**
     * Get the account for the company.
     */
    public function account(): HasOne
    {
        return $this->hasOne(CompanyAccount::class);
    }

    /**
     * Get the admins (owners and managers) for the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function admins(): HasMany
    {
        return $this->hasMany(\Webkul\User\Models\Admin::class, 'company_id');
    }
}

