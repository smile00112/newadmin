<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MailInstance extends Model
{
    use HasFactory;

    protected $table = 'newsletters_mail_instances';

    protected $fillable = [
        'name',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_email',
        'from_name',
        'company_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'port' => 'integer',
    ];

    protected $hidden = [
        'password',
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
        return $this->belongsToMany(MailingList::class, 'newsletters_mailing_list_mail_instance', 'mail_instance_id', 'mailing_list_id');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}



