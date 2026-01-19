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
     * @return string
     */
    public function getFixedCode(): string
    {
        return self::FIXED_CODE;
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
