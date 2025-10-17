<?php

namespace Webkul\Newsletters\Models;

use Illuminate\Database\Eloquent\Model;
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
    ];

    /**
     * Check if a phone number is blocked.
     */
    public static function isBlocked(string $phoneNumber): bool
    {
        return self::where('phone_number', $phoneNumber)->exists();
    }
}
