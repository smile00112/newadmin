<?php

namespace Webkul\IikoIntegration\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Repositories\IikoSyncLogRepository;

class IikoApiService
{
    /**
     * Maximum number of retry attempts.
     */
    protected const MAX_RETRIES = 3;

    /**
     * Retry delay in seconds.
     */
    protected const RETRY_DELAY = 2;

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected IikoAuthService $authService,
        protected IikoSyncLogRepository $syncLogRepository
    ) {}

    /**
     * Make HTTP request to iiko API.
     */
    public function makeRequest(
        string $endpoint,
        string $method = 'GET',
        array $data = [],
        ?string $channelCode = null,
        bool $logRequest = true
    ): ?array {
        $baseUrl = $this->getBaseUrl($channelCode);
        $url = "{$baseUrl}{$endpoint}";
        $token = $this->authService->getAccessToken($channelCode);

        if (!$token) {
            Log::error('iiko: Cannot make request - access token not available');
            return null;
        }

        $attempt = 0;
        $lastException = null;

        while ($attempt < self::MAX_RETRIES) {
            try {
                if ($logRequest) {
                    $this->logRequest($endpoint, $method, $data, $channelCode);
                }

    Log::info([
        'type' => 'get request',
        'url' => $url,
        'method' => $method,
        'data' => $data,
        'token' => $token
    ]);

                $response = $this->sendRequest($url, $method, $data, $token);

                if ($response->successful()) {
                    $responseData = $response->json();
    Log::info([
        'type' => 'get response',
        'responseData' => $responseData
    ]);
                    if ($logRequest) {
                        $this->logResponse($endpoint, $method, $responseData, $channelCode, true);
                    }

                    return $responseData;
                }

                // Handle 401 Unauthorized - token might be expired
                if ($response->status() === 401) {
                    Log::warning('iiko: Token ('.$token.') expired, clearing cache and retrying');
                    $this->authService->clearTokenCache($channelCode);
                    $token = $this->authService->getAccessToken($channelCode);

                    if (!$token) {
                        Log::error('iiko: Failed to refresh token');
                        return null;
                    }

                    $response = $this->sendRequest($url, $method, $data, $token);

                    if ($response->successful()) {
                        $responseData = $response->json();

                        if ($logRequest) {
                            $this->logResponse($endpoint, $method, $responseData, $channelCode, true);
                        }

                        return $responseData;
                    }
                }

                // Retry on temporary errors (503, 502, timeout)
                if (in_array($response->status(), [502, 503, 504]) && $attempt < self::MAX_RETRIES - 1) {
                    $attempt++;
                    sleep(self::RETRY_DELAY * $attempt);
                    continue;
                }

                // Log error response
                $errorData = [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ];

                if ($logRequest) {
                    $this->logResponse($endpoint, $method, $errorData, $channelCode, false, $response->status());
                }

                Log::error("iiko: API request failed", [
                    'endpoint' => $endpoint,
                    'method'   => $method,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                ]);

                return null;
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $lastException = $e;

                if ($attempt < self::MAX_RETRIES - 1) {
                    $attempt++;
                    sleep(self::RETRY_DELAY * $attempt);
                    continue;
                }

                Log::error('iiko: Connection exception', [
                    'endpoint' => $endpoint,
                    'message'  => $e->getMessage(),
                ]);

                if ($logRequest) {
                    $this->logResponse($endpoint, $method, ['error' => $e->getMessage()], $channelCode, false);
                }
            } catch (\Exception $e) {
                $lastException = $e;

                Log::error('iiko: Exception during API request', [
                    'endpoint' => $endpoint,
                    'method'   => $method,
                    'message'  => $e->getMessage(),
                    'trace'    => $e->getTraceAsString(),
                ]);

                if ($logRequest) {
                    $this->logResponse($endpoint, $method, ['error' => $e->getMessage()], $channelCode, false);
                }

                return null;
            }
        }

        return null;
    }

    /**
     * Send HTTP request.
     */
    protected function sendRequest(string $url, string $method, array $data, string $token): \Illuminate\Http\Client\Response
    {
        $http = Http::timeout(30)
            ->withHeaders([
                'Authorization' => "Bearer {$token}",
            ]);

        return match (strtoupper($method)) {
            'GET'    => $http->get($url, $data),
            'POST'   => $http->post($url, $data),
            'PUT'    => $http->put($url, $data),
            'DELETE' => $http->delete($url, $data),
            default  => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };
    }

    /**
     * Get base URL from settings.
     */
    protected function getBaseUrl(?string $channelCode = null): string
    {
        $settingRepository = app(\Webkul\IikoIntegration\Repositories\IikoSettingRepository::class);
        $baseUrl = $settingRepository->getSettingWithFallback('base_url', $channelCode);

        return $baseUrl ?: 'https://api-ru.iiko.services';
    }

    /**
     * Log request to sync logs.
     */
    protected function logRequest(string $endpoint, string $method, array $data, ?string $channelCode = null): void
    {
        $this->syncLogRepository->create([
            'sync_type'    => 'api_request',
            'entity_id'    => null,
            'status'       => 'pending',
            'request_data' => json_encode([
                'endpoint' => $endpoint,
                'method'   => $method,
                'data'     => $data,
            ]),
            'response_data' => null,
            'error_message' => null,
        ]);
    }

    /**
     * Log response to sync logs.
     */
    protected function logResponse(
        string $endpoint,
        string $method,
        array $responseData,
        ?string $channelCode = null,
        bool $success = true,
        ?int $statusCode = null
    ): void {
        $this->syncLogRepository->create([
            'sync_type'    => 'api_request',
            'entity_id'    => null,
            'status'       => $success ? 'success' : 'error',
            'request_data' => json_encode([
                'endpoint' => $endpoint,
                'method'   => $method,
            ]),
            'response_data' => json_encode($responseData),
            'error_message' => $success ? null : ($responseData['error'] ?? "HTTP {$statusCode}"),
        ]);
    }

    /**
     * Get cancel causes by organization IDs.
     */
    public function getCancelCauses(array $organizationIds, ?string $channelCode = null): ?array
    {
        return $this->makeRequest('/api/1/cancel_causes', 'POST', [
            'organizationIds' => $organizationIds,
        ], $channelCode);
    }

    /**
     * Get payment types for organization.
     */
    public function getPaymentTypes(string $organizationId, ?string $channelCode = null): ?array
    {
        return $this->makeRequest('/api/1/payment_types', 'POST', [
            'organizationId' => $organizationId,
        ], $channelCode);
    }

    /**
     * Get order types for organization.
     */
    public function getOrderTypes(string $organizationId, ?string $channelCode = null): ?array
    {
        return $this->makeRequest('/api/1/order_types', 'POST', [
            'organizationId' => $organizationId,
        ], $channelCode);
    }

    /**
     * Get regions.
     */
    public function getRegions(?string $channelCode = null): ?array
    {
        return $this->makeRequest('/api/1/regions', 'GET', [], $channelCode);
    }

    /**
     * Get cities by region ID.
     */
    public function getCities(string $regionId, ?string $channelCode = null): ?array
    {
        return $this->makeRequest('/api/1/cities', 'POST', [
            'regionId' => $regionId,
        ], $channelCode);
    }

    /**
     * Get streets by city ID.
     */
    public function getStreets(string $cityId, ?string $channelCode = null): ?array
    {
        return $this->makeRequest('/api/1/streets', 'POST', [
            'cityId' => $cityId,
        ], $channelCode);
    }

    /**
     * Get terminal groups for organization.
     */
    public function getTerminalGroups(string $organizationId, ?string $channelCode = null): ?array
    {
        return $this->makeRequest('/api/1/terminal_groups', 'POST', [
            'organizationIds' => [$organizationId],
        ], $channelCode);
    }

    /**
     * Check terminal group availability.
     */
    public function checkTerminalGroupAvailability(string $terminalGroupId, ?string $channelCode = null): ?array
    {
        return $this->makeRequest('/api/1/terminal_groups/availability', 'POST', [
            'terminalGroupId' => $terminalGroupId,
        ], $channelCode);
    }

    /**
     * Get nomenclature for organization.
     */
    public function getNomenclature(string $organizationId, ?string $channelCode = null, ?string $externalMenuId = null): ?array
    {
        $data = [
            'organizationId' => $organizationId,
        ];

        if ($externalMenuId) {
            $data['externalMenuId'] = $externalMenuId;
        }

        return $this->makeRequest('/api/1/nomenclature', 'POST', $data, $channelCode);
    }
}
