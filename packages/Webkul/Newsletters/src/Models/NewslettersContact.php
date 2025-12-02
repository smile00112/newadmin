<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NewslettersContact extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'newsletters_contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'gender',
        'last_order_date',
        'registration_date',
        'birth_date',
        'orders_count',
        'average_check',
        'total_check',
        'average_order_rating',
        'favorite_category',
        'favorite_dish',
        'store',
        'contact_group_id',
        'company_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_order_date' => 'date',
        'registration_date' => 'date',
        'birth_date' => 'date',
        'orders_count' => 'integer',
        'average_check' => 'decimal:2',
        'total_check' => 'decimal:2',
        'average_order_rating' => 'decimal:2',
    ];

    /**
     * Get the contact group that owns the contact.
     */
    public function contactGroup(): BelongsTo
    {
        return $this->belongsTo(NewslettersContactGroup::class, 'contact_group_id');
    }

    /**
     * Get the customer numbers for the contact.
     */
    public function customerNumbers(): HasMany
    {
        return $this->hasMany(CustomerNumber::class, 'contact_id');
    }

    /**
     * Get the company that owns the contact.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to only include contacts for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}

