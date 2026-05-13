<?php

namespace Webkul\PushNotification\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Webkul\PushNotification\Models\OrderLiveActivityToken;
use Webkul\Sales\Models\Order;

class ApnsLiveActivityService
{
    /**
     * Cache key for the APNs JWT token.
     */
    private const JWT_CACHE_KEY = 'apns_live_activity_jwt';

    /**
     * JWT TTL in seconds (58 minutes — safely below the 60-minute limit).
     */
    private const JWT_TTL = 3480;

    /**
     * APNs HTTP status: success.
     */
    public const STATUS_OK = 200;

    /**
     * APNs HTTP status: device token no longer active (delete token).
     */
    public const STATUS_GONE = 410;

    /**
     * Terminal order statuses that trigger the "end" event.
     *
     * @var array<string>
     */
    public const TERMINAL_STATUSES = [
        Order::STATUS_COMPLETED,
        Order::STATUS_CLOSED,
        Order::STATUS_CANCELED,
    ];

    /**
     * Check if Apple Live Activity push is enabled and configured.
     */
    public function isEnabled(): bool
    {
        return (bool) core()->getConfigData('mobile_app.apple_live_activity.settings.enabled');
    }

    /**
     * Send a Live Activity update or end push to Apple APNs.
     * Returns the APNs HTTP status code (200 = success, 410 = token gone, etc.).
     */
    public function send(OrderLiveActivityToken $tokenRecord, Order $order): int
    {
        $isTerminal = in_array($order->status, self::TERMINAL_STATUSES, true);
        $timestamp  = $this->resolveTimestamp($tokenRecord);
        $payload    = $this->buildPayload($order, $timestamp, $isTerminal);

        $jwt  = $this->getJwt();
        $host = $this->getHost();
        $topic = $this->getTopic();

        $status = $this->sendHttp2(
            host:         $host,
            deviceToken:  $tokenRecord->push_token,
            topic:        $topic,
            jwt:          $jwt,
            payload:      json_encode($payload, JSON_UNESCAPED_UNICODE),
            context:      [
                'order_id'        => $order->id,
                'order_increment' => $order->increment_id,
                'event'           => $isTerminal ? 'end' : 'update',
                'timestamp'       => $timestamp,
            ],
        );

        if ($status === 401 || $status === 403) {
            Log::warning('ApnsLiveActivity: auth error, refreshing JWT and retrying', [
                'order_id'   => $order->id,
                'apns_status' => $status,
            ]);

            Cache::forget(self::JWT_CACHE_KEY);
            $jwt    = $this->getJwt();
            $status = $this->sendHttp2(
                host:        $host,
                deviceToken: $tokenRecord->push_token,
                topic:       $topic,
                jwt:         $jwt,
                payload:     json_encode($payload, JSON_UNESCAPED_UNICODE),
                context:     [
                    'order_id'        => $order->id,
                    'order_increment' => $order->increment_id,
                    'event'           => $isTerminal ? 'end' : 'update',
                    'timestamp'       => $timestamp,
                    'retry'           => true,
                ],
            );
        }

        if ($status === self::STATUS_OK) {
            $tokenRecord->update(['last_apns_timestamp' => $timestamp]);

            if ($isTerminal) {
                $tokenRecord->delete();

                Log::info('ApnsLiveActivity: end event sent, token deleted', [
                    'order_id'       => $order->id,
                    'order_increment' => $order->increment_id,
                ]);
            } else {
                Log::debug('ApnsLiveActivity: update event sent', [
                    'order_id'       => $order->id,
                    'order_increment' => $order->increment_id,
                    'status'         => $order->status,
                    'timestamp'      => $timestamp,
                ]);
            }
        } elseif ($status === self::STATUS_GONE) {
            $tokenRecord->delete();

            Log::info('ApnsLiveActivity: 410 Gone — token deleted', [
                'order_id'       => $order->id,
                'order_increment' => $order->increment_id,
            ]);
        } else {
            Log::error('ApnsLiveActivity: unexpected APNs status', [
                'order_id'       => $order->id,
                'order_increment' => $order->increment_id,
                'apns_status'    => $status,
            ]);
        }

        return $status;
    }

    /**
     * Send a Live Activity update with an explicit custom status and label.
     * Used for synthetic states like "rateOrder" or "close" that are not real order statuses.
     * Returns the APNs HTTP status code (200 = success, 410 = token gone, etc.).
     */
    public function sendCustomStatus(
        OrderLiveActivityToken $tokenRecord,
        Order $order,
        string $status,
        string $statusLabel,
    ): int {
        $timestamp = $this->resolveTimestamp($tokenRecord);
        $payload   = $this->buildCustomPayload($order, $timestamp, $status, $statusLabel);

        $jwt    = $this->getJwt();
        $host   = $this->getHost();
        $topic  = $this->getTopic();

        $context = [
            'order_id'        => $order->id,
            'order_increment' => $order->increment_id,
            'event'           => 'update',
            'custom_status'   => $status,
            'timestamp'       => $timestamp,
        ];

        $encodedPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $httpStatus = $this->sendHttp2(
            host:        $host,
            deviceToken: $tokenRecord->push_token,
            topic:       $topic,
            jwt:         $jwt,
            payload:     $encodedPayload,
            context:     $context,
        );

        if ($httpStatus === 401 || $httpStatus === 403) {
            Log::warning('ApnsLiveActivity: auth error on custom status, refreshing JWT and retrying', [
                'order_id'     => $order->id,
                'apns_status'  => $httpStatus,
                'custom_status' => $status,
            ]);

            Cache::forget(self::JWT_CACHE_KEY);
            $jwt = $this->getJwt();
            $httpStatus = $this->sendHttp2(
                host:        $host,
                deviceToken: $tokenRecord->push_token,
                topic:       $topic,
                jwt:         $jwt,
                payload:     $encodedPayload,
                context:     array_merge($context, ['retry' => true]),
            );
        }

        if ($httpStatus === self::STATUS_OK) {
            $tokenRecord->update(['last_apns_timestamp' => $timestamp]);

            Log::debug('ApnsLiveActivity: custom status update sent', [
                'order_id'       => $order->id,
                'order_increment' => $order->increment_id,
                'custom_status'  => $status,
                'timestamp'      => $timestamp,
            ]);
        } elseif ($httpStatus === self::STATUS_GONE) {
            $tokenRecord->delete();

            Log::info('ApnsLiveActivity: 410 Gone on custom status — token deleted', [
                'order_id'       => $order->id,
                'order_increment' => $order->increment_id,
                'custom_status'  => $status,
            ]);
        } else {
            Log::error('ApnsLiveActivity: unexpected APNs status on custom status', [
                'order_id'       => $order->id,
                'order_increment' => $order->increment_id,
                'custom_status'  => $status,
                'apns_status'    => $httpStatus,
            ]);
        }

        return $httpStatus;
    }

    /**
     * Build the APNs payload for a Live Activity push.
     *
     * @return array<string, mixed>
     */
    private function buildPayload(Order $order, int $timestamp, bool $isTerminal): array
    {
        $contentState = [
            'status'      => $order->status,
            'statusLabel' => $order->status_label,
            'tableNumber' => $order->table_number ?? null,
            'isRated'     => isset($order->rating) ? (bool) $order->rating : null,
        ];

        $aps = [
            'timestamp'     => $timestamp,
            'event'         => $isTerminal ? 'end' : 'update',
            'content-state' => $contentState,
        ];

        if ($isTerminal) {
            $aps['dismissal-date'] = $timestamp + 3600;
        }

        return ['aps' => $aps];
    }

    /**
     * Build the APNs payload for a custom Live Activity status (e.g. "rateOrder", "close").
     *
     * @return array<string, mixed>
     */
    private function buildCustomPayload(Order $order, int $timestamp, string $status, string $statusLabel): array
    {
        $contentState = [
            'status'      => $status,
            'statusLabel' => $statusLabel,
            'tableNumber' => $order->table_number ?? null,
            'isRated'     => isset($order->rating) ? (bool) $order->rating : null,
        ];

        return [
            'aps' => [
                'timestamp'     => $timestamp,
                'event'         => 'update',
                'content-state' => $contentState,
            ],
        ];
    }

    /**
     * Resolve a strictly-monotonic timestamp (> last sent).
     */
    private function resolveTimestamp(OrderLiveActivityToken $tokenRecord): int
    {
        $now  = time();
        $last = (int) $tokenRecord->last_apns_timestamp;

        return $now > $last ? $now : $last + 1;
    }

    /**
     * Get a cached JWT, generating a new one if necessary.
     */
    private function getJwt(): string
    {
        return Cache::remember(self::JWT_CACHE_KEY, self::JWT_TTL, function () {
            return $this->generateJwt();
        });
    }

    /**
     * Generate an ES256 JWT for APNs authentication.
     */
    private function generateJwt(): string
    {
        $teamId = (string) core()->getConfigData('mobile_app.apple_live_activity.settings.team_id');
        $keyId  = (string) core()->getConfigData('mobile_app.apple_live_activity.settings.key_id');
        $keyContent = $this->getPrivateKeyContent();

        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'ES256',
            'kid' => $keyId,
        ]));

        $claims = $this->base64UrlEncode(json_encode([
            'iss' => $teamId,
            'iat' => time(),
        ]));

        $signingInput = $header . '.' . $claims;

        $privateKey = openssl_pkey_get_private($keyContent);

        if ($privateKey === false) {
            throw new \RuntimeException('ApnsLiveActivity: failed to load .p8 private key');
        }

        $signature = '';
        $result = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (! $result) {
            throw new \RuntimeException('ApnsLiveActivity: failed to sign JWT');
        }

        $jwtSignature = $this->base64UrlEncode($this->derToP1363($signature));

        return $signingInput . '.' . $jwtSignature;
    }

    /**
     * Convert a DER-encoded ECDSA signature to IEEE P1363 format (r||s, 32+32 bytes)
     * required by JWT ES256.
     */
    private function derToP1363(string $der): string
    {
        $offset = 0;

        if (ord($der[$offset]) !== 0x30) {
            throw new \RuntimeException('ApnsLiveActivity: invalid DER ECDSA signature (missing SEQUENCE marker)');
        }

        $offset += 2;

        if (ord($der[$offset]) !== 0x02) {
            throw new \RuntimeException('ApnsLiveActivity: invalid DER ECDSA signature (missing r INTEGER marker)');
        }

        $rLen = ord($der[$offset + 1]);
        $r    = substr($der, $offset + 2, $rLen);
        $offset += 2 + $rLen;

        if (ord($der[$offset]) !== 0x02) {
            throw new \RuntimeException('ApnsLiveActivity: invalid DER ECDSA signature (missing s INTEGER marker)');
        }

        $sLen = ord($der[$offset + 1]);
        $s    = substr($der, $offset + 2, $sLen);

        // Strip leading 0x00 padding byte (DER positive-integer marker) and pad to 32 bytes
        $r = str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT);
        $s = str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);

        return $r . $s;
    }

    /**
     * Base64Url encode without padding (required for JWT).
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Get the .p8 private key content from config (path or raw PEM).
     */
    private function getPrivateKeyContent(): string
    {
        $key = (string) core()->getConfigData('mobile_app.apple_live_activity.settings.p8_key');

        if (str_starts_with($key, '/') || str_starts_with($key, 'storage/')) {
            $path = str_starts_with($key, '/') ? $key : base_path($key);

            if (! file_exists($path)) {
                throw new \RuntimeException("ApnsLiveActivity: .p8 key file not found at [{$path}]");
            }

            $key = file_get_contents($path);
        }

        return $key;
    }

    /**
     * Resolve the APNs host based on environment config.
     */
    private function getHost(): string
    {
        $sandbox = (bool) core()->getConfigData('mobile_app.apple_live_activity.settings.sandbox');

        return $sandbox
            ? 'api.sandbox.push.apple.com'
            : 'api.push.apple.com';
    }

    /**
     * Get the full APNs topic for Live Activity.
     * Format: <bundle_id>.push-type.liveactivity
     */
    private function getTopic(): string
    {
        $bundleId = (string) core()->getConfigData('mobile_app.apple_live_activity.settings.bundle_id');

        return $bundleId . '.push-type.liveactivity';
    }

    /**
     * Send the payload to APNs over HTTP/2 using cURL.
     * Returns the HTTP response status code.
     */
    private function sendHttp2(
        string $host,
        string $deviceToken,
        string $topic,
        string $jwt,
        string $payload,
        array $context = [],
    ): int {
        $url = "https://{$host}/3/device/{$deviceToken}";

        $headers = [
            'content-type: application/json',
            "apns-topic: {$topic}",
            'apns-push-type: liveactivity',
            'apns-priority: 10',
            "authorization: bearer {$jwt}",
        ];

        $ch = curl_init($url);

        Log::info('ApnsLiveActivity: request', array_merge($context, [
            'url'          => $url,
            'method'       => 'POST',
            'headers'      => [
                'content-type'   => 'application/json',
                'apns-topic'     => $topic,
                'apns-push-type' => 'liveactivity',
                'apns-priority'  => 10,
                'authorization'  => 'bearer ' . substr($jwt, 0, 12) . '***',
            ],
            'request_body' => json_decode($payload, true),
            'device_token' => substr($deviceToken, 0, 12) . '***',
        ]));

        $startedAt = microtime(true);

        curl_setopt_array($ch, [
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_2_0,
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $payload,
            CURLOPT_HTTPHEADER      => $headers,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_TIMEOUT         => 10,
            CURLOPT_SSL_VERIFYPEER  => true,
            CURLOPT_SSL_VERIFYHOST  => 2,
        ]);

        $responseBody = curl_exec($ch);
        $httpStatus   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        $elapsedMs    = (int) round((microtime(true) - $startedAt) * 1000);

        curl_close($ch);

        Log::info('ApnsLiveActivity: response', array_merge($context, [
            'status'       => $httpStatus,
            'response_body'=> $responseBody,
            'curl_error'   => $curlError !== '' ? $curlError : null,
            'elapsed_ms'   => $elapsedMs,
            'device_token' => substr($deviceToken, 0, 12) . '***',
        ]));

        if ($curlError) {
            Log::error('ApnsLiveActivity: cURL error', [
                'error'        => $curlError,
                'device_token' => substr($deviceToken, 0, 12) . '***',
            ]);

            return 0;
        }

        if ($httpStatus !== self::STATUS_OK) {
            Log::warning('ApnsLiveActivity: APNs non-200 response', [
                'status'       => $httpStatus,
                'body'         => $responseBody,
                'device_token' => substr($deviceToken, 0, 12) . '***',
            ]);
        }

        return $httpStatus;
    }
}
