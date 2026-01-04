<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function mailingLists(): BelongsToMany
    {
        return $this->belongsToMany(MailingList::class, 'newsletters_mailing_list_telegram_instance', 'telegram_instance_id', 'mailing_list_id');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}



