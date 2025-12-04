<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyAccount extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'company_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'balance',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'balance' => 'decimal:2',
    ];

    /**
     * Get the company that owns the account.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the topups for the account.
     */
    public function topups(): HasMany
    {
        return $this->hasMany(AccountTopup::class, 'account_id');
    }

    /**
     * Check if account has sufficient balance.
     *
     * @return bool
     */
    public function hasBalance(): bool
    {
        return $this->balance > 0;
    }
}

