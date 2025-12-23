<?php

namespace Webkul\RestApi\Services\Auth;

class TestUserService
{
    /**
     * Test phone numbers with fixed verification code.
     */
    private const TEST_PHONE_NUMBERS = [
        '+71234567890',
        '+71231234567',
    ];

    /**
     * Fixed verification code for test users.
     */
    private const FIXED_CODE = '123456';

    /**
     * Check if identifier belongs to a test user.
     *
     * @param string $identifier Phone number or telegram_id
     * @return bool
     */
    public function isTestUser(string $identifier): bool
    {
        // Normalize identifier (remove + if present for comparison)
        $normalized = $this->normalizeIdentifier($identifier);
        
        // Check against test phone numbers (with and without +)
        foreach (self::TEST_PHONE_NUMBERS as $testNumber) {
            // Check exact match with +
            if ($identifier === $testNumber) {
                return true;
            }
            
            // Check normalized match (without +)
            $normalizedTest = $this->normalizeIdentifier($testNumber);
            if ($normalized === $normalizedTest) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get fixed verification code for test users.
     *
     * @return string
     */
    public function getFixedCode(): string
    {
        return self::FIXED_CODE;
    }

    /**
     * Get list of test user identifiers.
     *
     * @return array
     */
    public function getTestUsers(): array
    {
        $users = [];
        
        foreach (self::TEST_PHONE_NUMBERS as $phoneNumber) {
            // Add with +
            $users[] = $phoneNumber;
            // Add without +
            $users[] = $this->normalizeIdentifier($phoneNumber);
        }
        
        return array_unique($users);
    }

    /**
     * Normalize identifier by removing + prefix.
     *
     * @param string $identifier
     * @return string
     */
    private function normalizeIdentifier(string $identifier): string
    {
        return ltrim($identifier, '+');
    }
}

