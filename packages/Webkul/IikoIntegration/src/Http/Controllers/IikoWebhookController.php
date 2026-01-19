<?php

namespace Webkul\IikoIntegration\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Repositories\IikoSettingRepository;
use Webkul\IikoIntegration\Services\IikoWebhookService;

class IikoWebhookController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected IikoWebhookService $webhookService,
        protected IikoSettingRepository $settingRepository
    ) {}

    /**
     * Handle incoming webhook from iiko.
     */
    public function handleWebhook(Request $request): Response
    {
        try {
            $data = $request->all();
            $signature = $request->header('X-Iiko-Signature');

            // Validate webhook signature if configured
            if ($this->shouldValidateSignature()) {
                if (!$this->validateSignature($request, $signature)) {
                    Log::warning('iiko: Invalid webhook signature', [
                        'signature' => $signature,
                    ]);

                    return response(['error' => 'Invalid signature'], 401);
                }
            }

            // Determine webhook event type
            $eventType = $data['eventType'] ?? $data['type'] ?? null;

            if (!$eventType) {
                Log::warning('iiko: Webhook missing event type', ['data' => $data]);
                return response(['error' => 'Missing event type'], 400);
            }

            // Process webhook based on event type
            $processed = match ($eventType) {
                'order.status.update', 'orderStatusUpdate' => $this->webhookService->processOrderStatusUpdate($data),
                'order.courier.assigned', 'courierAssigned' => $this->webhookService->processCourierAssignment($data),
                'order.cancelled', 'orderCancelled' => $this->webhookService->processOrderCancellation($data),
                default => $this->handleUnknownEventType($eventType, $data),
            };

            if ($processed) {
                return response(['status' => 'ok'], 200);
            }

            return response(['error' => 'Failed to process webhook'], 500);
        } catch (\Exception $e) {
            Log::error('iiko: Exception handling webhook', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Check if signature validation should be performed.
     */
    protected function shouldValidateSignature(): bool
    {
        $secret = $this->settingRepository->getSettingWithFallback('webhook_secret');
        return !empty($secret);
    }

    /**
     * Validate webhook signature.
     */
    protected function validateSignature(Request $request, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }

        $secret = $this->settingRepository->getSettingWithFallback('webhook_secret');
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Handle unknown event type.
     */
    protected function handleUnknownEventType(string $eventType, array $data): bool
    {
        Log::info('iiko: Unknown webhook event type', [
            'event_type' => $eventType,
            'data'       => $data,
        ]);

        // Log the webhook for later analysis
        app(\Webkul\IikoIntegration\Repositories\IikoSyncLogRepository::class)->create([
            'sync_type'    => \Webkul\IikoIntegration\Models\IikoSyncLog::TYPE_WEBHOOK,
            'entity_id'    => null,
            'status'       => \Webkul\IikoIntegration\Models\IikoSyncLog::STATUS_SUCCESS,
            'request_data' => $data,
            'response_data' => ['event_type' => $eventType, 'note' => 'Unknown event type'],
        ]);

        return true;
    }
}
