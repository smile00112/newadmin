<?php

namespace Webkul\IikoIntegration\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Repositories\IikoSettingRepository;

class IikoAuthService
{
    /**
     * Cache key prefix for access tokens.
     */
    protected const CACHE_PREFIX = 'iiko_access_token';

    /**
     * Token cache TTL in seconds (60 minutes).
     */
    protected const TOKEN_TTL = 3600;

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected IikoSettingRepository $settingRepository
    ) {}

    /**
     * Get access token from cache or API.
     */
    public function getAccessToken(?string $channelCode = null): ?string
    {
        $cacheKey = $this->getCacheKey($channelCode);

        // Try to get from cache first
        $token = Cache::get($cacheKey);

        if ($token) {
            return $token;
        }

        // Get new token from API
        return $this->requestAccessToken($channelCode);
    }

    /**
     * Request new access token from iiko API.
     */
    protected function requestAccessToken(?string $channelCode = null): ?string
    {
        try {
            $apiLogin = $this->settingRepository->getSettingWithFallback('api_login', $channelCode);
            $baseUrl = $this->settingRepository->getSettingWithFallback('base_url', $channelCode);

            if (!$apiLogin) {
                Log::error('iiko: API login is not configured');
                return null;
            }

            $response = Http::timeout(30)
                ->post("{$baseUrl}/api/1/access_token", [
                    'apiLogin' => $apiLogin,
                ]);

            if (!$response->successful()) {
                Log::error('iiko: Failed to get access token', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            if (!isset($data['token'])) {
                Log::error('iiko: Access token not found in response', ['response' => $data]);
                return null;
            }

            $token = $data['token'];
            $cacheKey = $this->getCacheKey($channelCode);

            // Cache token for 60 minutes (or until expiration if provided)
            $ttl = $data['expirationDate'] ?? self::TOKEN_TTL;
            if (isset($data['expirationDate'])) {
                // Calculate TTL from expiration date
                $expiration = \Carbon\Carbon::parse($data['expirationDate']);
                $ttl = max(60, $expiration->diffInSeconds(now()) - 60); // Cache until 1 minute before expiration
            }

            Cache::put($cacheKey, $token, $ttl);

            Log::info('iiko: Access token obtained and cached', ['ttl' => $ttl]);

            return $token;
        } catch (\Exception $e) {
            Log::error('iiko: Exception while getting access token', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Clear cached access token.
     */
    public function clearTokenCache(?string $channelCode = null): void
    {
        $cacheKey = $this->getCacheKey($channelCode);
        Cache::forget($cacheKey);
    }

    /**
     * Get cache key for access token.
     */
    protected function getCacheKey(?string $channelCode = null): string
    {
        $key = self::CACHE_PREFIX;

        if ($channelCode) {
            $key .= ":{$channelCode}";
        }

        return $key;
    }

    /**
     * Check if integration is enabled.
     */
    public function isEnabled(?string $channelCode = null): bool
    {
        return $this->settingRepository->isIntegrationEnabled($channelCode);
    }
}
