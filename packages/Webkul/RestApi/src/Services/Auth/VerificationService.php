<?php

namespace Webkul\RestApi\Services\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VerificationService
{
    /**
     * Controller instance.
     *
     * @return void
     */
    public function __construct(
        protected TestUserService $testUserService
    ) {}

    /**
     * Generate verification code and token.
     *
     * @param string $identifier
     * @param string $channel
     * @return array
     */
    public function generateVerificationCode(string $identifier, string $channel): array
    {
        // Check if test user - use fixed code, otherwise generate random
        $isTestUser = $this->testUserService->isTestUser($identifier, $channel);
        $verificationCode = $isTestUser
            ? $this->testUserService->getFixedCode()
            : str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        if ($isTestUser) {
            Log::info("Test user verification code generated", [
                'identifier' => $identifier,
                'channel' => $channel,
                'code' => $verificationCode,
            ]);
        }
        
        $verificationToken = Str::random(64);
        
        // Store verification data in cache for 10 minutes
        Cache::put("verification_{$verificationToken}", [
            'code' => $verificationCode,
            'identifier' => $identifier,
            'channel' => $channel,
            'attempts' => 0,
            'created_at' => now(),
        ], 600);

        return [
            'verification_code' => $verificationCode,
            'verification_token' => $verificationToken,
            'expires_in' => 600, // 10 minutes
        ];
    }

    /**
     * Verify the provided code.
     *
     * @param string $verificationToken
     * @param string $code
     * @return bool
     */
    public function verifyCode(string $verificationToken, string $code): bool
    {
        $verificationData = Cache::get("verification_{$verificationToken}");
        
        if (!$verificationData) {
            return false;
        }

        // Check attempt limit (max 3 attempts)
        if ($verificationData['attempts'] >= 3) {
            Cache::forget("verification_{$verificationToken}");
            return false;
        }

        // Increment attempts
        $verificationData['attempts']++;
        Cache::put("verification_{$verificationToken}", $verificationData, 600);

        if ($verificationData['code'] === $code) {
            // Mark as verified
            Cache::put("verified_{$verificationToken}", $verificationData, 300); // 5 minutes
            Cache::forget("verification_{$verificationToken}");
            return true;
        }

        return false;
    }

    /**
     * Get verified data.
     *
     * @param string $verificationToken
     * @return array|null
     */
    public function getVerifiedData(string $verificationToken): ?array
    {
        return Cache::get("verified_{$verificationToken}");
    }

    /**
     * Clean up verification data.
     *
     * @param string $verificationToken
     * @return void
     */
    public function cleanupVerification(string $verificationToken): void
    {
        Cache::forget("verification_{$verificationToken}");
        Cache::forget("verified_{$verificationToken}");
    }
}
