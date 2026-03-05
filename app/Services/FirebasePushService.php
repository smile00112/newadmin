<?php

namespace App\Services;

use App\Models\CustomerPushToken;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\Sales\Models\Order;

class FirebasePushService
{
    /**
     * Check if push notifications are enabled.
     */
    public function isEnabled(): bool
    {
        return (bool) core()->getConfigData('mobile_app.push_notifications.settings.enabled');
    }

    /**
     * Check if a specific order status is enabled for push notifications.
     */
    public function isStatusEnabled(string $status): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $enabledStatuses = core()->getConfigData('mobile_app.push_notifications.settings.statuses');

        if (! $enabledStatuses) {
            return false;
        }

        // Status can be stored as comma-separated string or JSON array
        $statuses = is_string($enabledStatuses)
            ? array_filter(array_map('trim', explode(',', $enabledStatuses)))
            : (is_array($enabledStatuses) ? $enabledStatuses : []);

        return in_array($status, $statuses, true);
    }

    /**
     * Get message for a specific order status.
     */
    public function getMessageForStatus(string $status, Order $order): ?array
    {
        $title = core()->getConfigData("mobile_app.push_notifications.messages.title_{$status}");
        $body = core()->getConfigData("mobile_app.push_notifications.messages.body_{$status}");

        if (! $title || ! $body) {
            return null;
        }

        // Replace placeholders
        $statusLabel = $order->status_label ?? ucfirst(str_replace('_', ' ', $status));

        $title = str_replace(['{order_id}', '{status_label}'], [$order->id, $statusLabel], $title);
        $body = str_replace(['{order_id}', '{status_label}'], [$order->id, $statusLabel], $body);

        return [
            'title' => $title,
            'body'  => $body,
        ];
    }

    /**
     * Send push notification to all customer's active tokens.
     */
    public function sendToCustomer(int $customerId, string $title, string $body, array $data = []): void
    {
        try {
            $tokens = CustomerPushToken::where('customer_id', $customerId)
                ->where('is_active', true)
                ->pluck('token')
                ->toArray();

            if (empty($tokens)) {
                return;
            }

            foreach ($tokens as $token) {
                try {
                    $result = $this->sendToToken($token, $title, $body, $data);

                    if (! $result) {
                        // Mark token as inactive if sending failed
                        CustomerPushToken::where('token', $token)->update(['is_active' => false]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error sending push to token', [
                        'token' => substr($token, 0, 10) . '***',
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in FirebasePushService::sendToCustomer', [
                'customer_id' => $customerId,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send push notification to a specific token via Firebase HTTP v1 API.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $projectId = core()->getConfigData('mobile_app.push_notifications.settings.firebase_project_id');
            $credentialsJson = core()->getConfigData('mobile_app.push_notifications.settings.firebase_credentials_json');

            if (! $projectId || ! $credentialsJson) {
                Log::warning('Firebase configuration is incomplete');
                return false;
            }

            // Parse credentials
            $credentials = json_decode($credentialsJson, true);
            if (! $credentials) {
                Log::warning('Invalid Firebase credentials JSON');
                return false;
            }

            // Get access token
            $accessToken = $this->getAccessToken($credentials);
            if (! $accessToken) {
                Log::warning('Failed to obtain Firebase access token');
                return false;
            }

            // Build FCM message payload
            $payload = [
                'message' => [
                    'token'        => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data'         => $data ?: [],
                ],
            ];

            // Send to Firebase
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type'  => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                Log::debug('Push notification sent successfully', [
                    'token' => substr($token, 0, 10) . '***',
                ]);
                return true;
            }

            // Check for specific error responses
            $responseData = $response->json();

            if (isset($responseData['error']['details'])) {
                $details = $responseData['error']['details'];
                foreach ($details as $detail) {
                    if ($detail['reason'] === 'NOT_FOUND' || $detail['reason'] === 'INVALID_ARGUMENT') {
                        // Token is invalid or expired
                        Log::info('Invalid or expired FCM token', [
                            'token'  => substr($token, 0, 10) . '***',
                            'reason' => $detail['reason'],
                        ]);
                        return false;
                    }
                }
            }

            Log::warning('FCM API error', [
                'token'    => substr($token, 0, 10) . '***',
                'error'    => $response->body(),
                'status'   => $response->status(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error sending push to FCM', [
                'token' => substr($token, 0, 10) . '***',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get FCM access token from Service Account credentials.
     */
    private function getAccessToken(array $credentials): ?string
    {
        try {
            // Create service account credentials
            $serviceAccount = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/cloud-platform',
                $credentials
            );

            // Get access token
            $authToken = $serviceAccount->fetchAuthToken(HttpHandlerFactory::build());

            return $authToken['access_token'] ?? null;
        } catch (\Exception $e) {
            Log::error('Error obtaining FCM access token', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
