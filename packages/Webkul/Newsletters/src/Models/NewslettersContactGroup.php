<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NewslettersContactGroup extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'newsletters_contact_groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'has_external_integration',
        'request_url',
        'request_token',
        'auto_request_frequency',
    ];

    /**
     * Get the contacts for the group.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(NewslettersContact::class, 'contact_group_id');
    }
}

