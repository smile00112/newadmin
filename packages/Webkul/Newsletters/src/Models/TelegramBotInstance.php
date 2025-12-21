<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TelegramBotInstance extends Model
{
    use HasFactory;

    protected $table = 'newsletters_telegram_bot_instances';

    protected $fillable = [
        'bot_token',
        'bot_username',
        'bot_name',
        'company_id',
        'mailing_list_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $hidden = [
        'bot_token',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function mailingList(): BelongsTo
    {
        return $this->belongsTo(MailingList::class);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}



