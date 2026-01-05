<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NewslettersContactFilter extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'newsletters_contact_filters';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contact_group_id',
        'name',
        'company_id',
    ];

    /**
     * Get the contact group that owns the filter.
     */
    public function contactGroup(): BelongsTo
    {
        return $this->belongsTo(NewslettersContactGroup::class, 'contact_group_id');
    }

    /**
     * Get the company that owns the filter.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the conditions for the filter.
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(NewslettersContactFilterCondition::class, 'filter_id')->orderBy('sort_order');
    }

    /**
     * Scope a query to only include filters for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}

