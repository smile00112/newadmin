<?php

namespace Webkul\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Customer\Models\Customer;

class PhoneVerificationCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone',
        'code',
        'expires_at',
        'used',
        'auth_type',
        'customer_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean'
    ];

    /**
     * Get the design options.
     *
     * @return array
     */
    public static function getAuthTypesOptions(): array
    {
        return [
            'sms' => 'CMC',
            'telegram' => 'Telegram',
            'whatsapp' => 'Whatsapp',
        ];
    }

    /**
     * Generate a new verification code for the given phone number.
     *
     * @param string $phone
     * @return PhoneVerificationCode
     */
    public static function generateCode($phone)
    {
        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Expires in 5 minutes
        $expiresAt = Carbon::now()->addMinutes(5);

        // Mark old codes as used
        self::where('phone', $phone)->update(['used' => true]);

        return self::create([
            'phone' => $phone,
            'code' => $code,
            'expires_at' => $expiresAt
        ]);
    }

    /**
     * Verify the code for the given phone number.
     *
     * @param string $phone
     * @param string $code
     * @return bool
     */
    public static function verifyCode($phone, $code)
    {
        $verificationCode = self::where('phone', $phone)
            ->where('code', $code)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($verificationCode) {
            $verificationCode->update(['used' => true]);
            return true;
        }

        return false;
    }

    /**
     * Check if the code is expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Clean up expired codes.
     *
     * @return int Number of deleted codes
     */
    public static function cleanupExpiredCodes()
    {
        return self::where('expires_at', '<', Carbon::now())->delete();
    }

    /**
     * Get the customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
