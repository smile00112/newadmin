<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NewslettersContactFilterCondition extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'newsletters_contact_filter_conditions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'filter_id',
        'field',
        'operator',
        'value_from',
        'value_to',
        'value',
        'values',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'value_from' => 'decimal:2',
        'value_to' => 'decimal:2',
        'values' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Get the filter that owns the condition.
     */
    public function filter(): BelongsTo
    {
        return $this->belongsTo(NewslettersContactFilter::class, 'filter_id');
    }
}

