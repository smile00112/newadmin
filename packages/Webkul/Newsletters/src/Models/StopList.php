<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StopList extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'newsletters_stop_list';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone_number',
        'company_id',
    ];

    /**
     * Get the company that owns the stop list entry.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to only include stop list entries for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Check if a phone number is blocked for a specific company.
     */
    public static function isBlocked(string $phoneNumber, ?int $companyId = null): bool
    {
        $query = self::where('phone_number', $phoneNumber);
        
        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        }
        
        return $query->exists();
    }
}
