<?php

namespace Webkul\Shop\Services;

use Illuminate\Support\Facades\Cache;

class InMemoryVerificationService
{
    /**
     * Generate a verification code and store it in cache.
     *
     * @param string $phone
     * @return array
     */
    public static function generateCode($phone)
    {
        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store in cache for 5 minutes
        $key = "verification_code_{$phone}";
        Cache::put($key, $code, 300); // 5 minutes
        
        return [
            'phone' => $phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(5)
        ];
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
        $key = "verification_code_{$phone}";
        $storedCode = Cache::get($key);
        
        if ($storedCode && $storedCode === $code) {
            // Remove the code after successful verification
            Cache::forget($key);
            return true;
        }
        
        return false;
    }
}




