<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailingStatistic extends Model
{
    protected $table = 'newsletters_mailing_statistics';
    protected $fillable = [
        'mailing_list_id',
        'customer_number_id',
        'event_type',
        'event_time',
        'meta',
    ];

    protected $casts = [
        'event_time' => 'datetime',
        'meta' => 'array',
    ];

    public function mailingList(): BelongsTo
    {
        return $this->belongsTo(MailingList::class, 'mailing_list_id');
    }

    public function customerNumber(): BelongsTo
    {
        return $this->belongsTo(CustomerNumber::class, 'customer_number_id');
    }
}
