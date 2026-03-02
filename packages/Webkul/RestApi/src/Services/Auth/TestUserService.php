<?php

namespace Webkul\RestApi\Services\Auth;

use Webkul\RestApi\Repositories\AuthChannelSettingRepository;

class TestUserService
{
    /**
     * Fixed verification code for test users.
     */
    private const FIXED_CODE = '123456';

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected AuthChannelSettingRepository $settingRepository
    ) {}

    /**
     * Check if identifier belongs to a test user for a specific channel.
     *
     * @param string $identifier Phone number or telegram_id
     * @param string $authChannel Auth channel type (sms, whatsapp, telegram)
     * @return bool
     */
    public function isTestUser(string $identifier, string $authChannel = 'sms'): bool
    {
        $testIdentifiers = $this->getTestIdentifiers($authChannel);

        if (empty($testIdentifiers)) {
            return false;
        }

        // Clean identifier - keep only digits
        $cleanedIdentifier = preg_replace('/[^0-9]/', '', $identifier);

        // Check against test identifiers (already cleaned in storage)
        foreach ($testIdentifiers as $testIdentifier) {
            if ($cleanedIdentifier === $testIdentifier) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get fixed verification code for test users.
     *
     * @param int $codeLength Desired code length
     * @return string
     */
    public function getFixedCode(int $codeLength = 6): string
    {
        // Ensure code length is within valid range (4-10)
        $codeLength = max(4, min(10, $codeLength));
        
        // If requested length is less than or equal to FIXED_CODE length, return substring
        if ($codeLength <= strlen(self::FIXED_CODE)) {
            return substr(self::FIXED_CODE, 0, $codeLength);
        }
        
        // If requested length is longer than FIXED_CODE, repeat the pattern
        $result = self::FIXED_CODE;
        while (strlen($result) < $codeLength) {
            $result .= self::FIXED_CODE;
        }
        
        return substr($result, 0, $codeLength);
    }

    /**
     * Get list of test identifiers for a specific channel.
     *
     * @param string $authChannel Auth channel type (sms, whatsapp, telegram)
     * @return array
     */
    public function getTestIdentifiers(string $authChannel): array
    {
        $channelCode = core()->getCurrentChannelCode();

        $testIdentifiersRaw = $this->settingRepository->getSetting($authChannel, 'test_phone_numbers', $channelCode);

        if (empty($testIdentifiersRaw)) {
            return [];
        }

        // Parse multiline string into array, trimming each line and removing empty lines
        $identifiers = array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $testIdentifiersRaw)),
            fn($value) => !empty($value)
        );

        return $identifiers;
    }

}
