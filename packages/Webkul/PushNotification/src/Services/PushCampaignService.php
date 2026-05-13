<?php

namespace Webkul\PushNotification\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\PushNotification\Models\CustomerPushToken;
use Webkul\PushNotification\Models\PushCampaign;
use Webkul\PushNotification\Models\PushCampaignLog;

class PushCampaignService
{
    public function __construct(
        protected FirebasePushService $firebaseService
    ) {}

    /**
     * Build audience query based on segment filters.
     * Returns customer IDs with at least one active push token.
     */
    public function buildAudienceQuery(array $filters = []): Builder
    {
        $query = CustomerPushToken::query()
            ->select('customer_push_tokens.customer_id', 'customer_push_tokens.token', 'customer_push_tokens.id as token_id')
            ->where('customer_push_tokens.is_active', true)
            ->join('customers', 'customers.id', '=', 'customer_push_tokens.customer_id')
            ->where('customers.status', true);

        // Filter by customer group
        if (! empty($filters['customer_group_ids'])) {
            $query->whereIn('customers.customer_group_id', $filters['customer_group_ids']);
        }

        // Filter by explicitly selected customers.
        if (! empty($filters['customer_ids']) && is_array($filters['customer_ids'])) {
            $customerIds = array_values(array_filter(array_map(static fn ($id) => (int) $id, $filters['customer_ids']), static fn ($id) => $id > 0));

            if (! empty($customerIds)) {
                $query->whereIn('customers.id', $customerIds);
            }
        }

        // Filter by explicit phone list (normalized digits).
        if (! empty($filters['phones']) && is_array($filters['phones'])) {
            $phones = array_values(array_filter(array_map(
                static fn ($phone) => preg_replace('/\D+/', '', (string) $phone),
                $filters['phones']
            )));

            if (! empty($phones)) {
                $query->whereIn(DB::raw("REGEXP_REPLACE(customers.phone, '[^0-9]', '')"), $phones);
            }
        }

        // Filter by gender
        if (! empty($filters['gender'])) {
            $query->where('customers.gender', $filters['gender']);
        }

        // Filter by registration date
        if (! empty($filters['registered_from'])) {
            $query->whereDate('customers.created_at', '>=', $filters['registered_from']);
        }
        if (! empty($filters['registered_to'])) {
            $query->whereDate('customers.created_at', '<=', $filters['registered_to']);
        }

        // Filter by orders existence
        if (isset($filters['has_orders']) && $filters['has_orders'] !== '') {
            if ($filters['has_orders']) {
                $query->whereExists(function ($sub) {
                    $sub->selectRaw('1')
                        ->from('orders')
                        ->whereColumn('orders.customer_id', 'customers.id');
                });
            } else {
                $query->whereNotExists(function ($sub) {
                    $sub->selectRaw('1')
                        ->from('orders')
                        ->whereColumn('orders.customer_id', 'customers.id');
                });
            }
        }

        // Filter by last order date
        if (! empty($filters['last_order_from']) || ! empty($filters['last_order_to'])) {
            $query->whereExists(function ($sub) use ($filters) {
                $sub->selectRaw('1')
                    ->from('orders')
                    ->whereColumn('orders.customer_id', 'customers.id');

                if (! empty($filters['last_order_from'])) {
                    $sub->whereDate('orders.created_at', '>=', $filters['last_order_from']);
                }
                if (! empty($filters['last_order_to'])) {
                    $sub->whereDate('orders.created_at', '<=', $filters['last_order_to']);
                }
            });
        }

        return $query;
    }

    /**
     * Count estimated audience size.
     */
    public function countAudience(array $filters = []): int
    {
        return $this->buildAudienceQuery($filters)->count();
    }

    /**
     * Dispatch campaign: create log entries and queue sending job.
     */
    public function dispatchCampaign(PushCampaign $campaign): void
    {
        $filters = $campaign->segment_filters ?? [];

        // Keep retrieval shape identical to countAudience() query to avoid
        // mismatches between preview count and actual dispatch selection.
        $tokens = $this->buildAudienceQuery($filters)->get();

        Log::info('PushCampaignService: dispatch audience resolved', [
            'campaign_id'    => $campaign->id,
            'status_before'  => $campaign->status,
            'filters'        => $filters,
            'resolved_count' => $tokens->count(),
        ]);

        if ($tokens->isEmpty()) {
            $campaign->update(['status' => 'sent', 'total_recipients' => 0]);

            Log::warning('PushCampaignService: dispatch resolved empty audience', [
                'campaign_id' => $campaign->id,
                'filters'     => $filters,
            ]);

            return;
        }

        // Create log entries in bulk
        $now = now();
        $logData = $tokens->map(fn ($row) => [
            'campaign_id' => $campaign->id,
            'customer_id' => $row->customer_id,
            'token'       => $row->token,
            'status'      => 'pending',
            'created_at'  => $now,
            'updated_at'  => $now,
        ])->toArray();

        // Insert in chunks to avoid memory issues
        foreach (array_chunk($logData, 500) as $chunk) {
            PushCampaignLog::insert($chunk);
        }

        $campaign->update([
            'status'           => 'sending',
            'total_recipients' => count($logData),
        ]);

        // Dispatch batch job
        \Webkul\PushNotification\Jobs\SendCampaignJob::dispatch($campaign->id);
    }

    /**
     * Send a single log entry and update stats.
     */
    public function sendLogEntry(PushCampaignLog $log): void
    {
        $campaign = $log->campaign;

        try {
            $data = array_merge(
                $campaign->data ?? [],
                ['campaign_id' => (string) $campaign->id]
            );

            $success = $this->firebaseService->sendToToken(
                $log->token,
                $campaign->title,
                $campaign->body,
                $data
            );

            if ($success) {
                $log->update([
                    'status'  => 'sent',
                    'sent_at' => now(),
                ]);

                PushCampaign::where('id', $campaign->id)->increment('delivered_count');
                PushCampaign::where('id', $campaign->id)->increment('sent_count');
            } else {
                $log->update([
                    'status'        => 'failed',
                    'error_message' => 'FCM rejected the token',
                ]);

                PushCampaign::where('id', $campaign->id)->increment('sent_count');

                // Deactivate invalid token
                CustomerPushToken::where('token', $log->token)->update(['is_active' => false]);
            }
        } catch (\Exception $e) {
            Log::error('PushCampaignService: error sending log', [
                'log_id' => $log->id,
                'error'  => $e->getMessage(),
            ]);

            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            PushCampaign::where('id', $campaign->id)->increment('sent_count');
        }
    }

    /**
     * Mark campaign as sent when all logs are processed.
     */
    public function finalizeCampaign(int $campaignId): void
    {
        $campaign = PushCampaign::find($campaignId);

        if (! $campaign) {
            return;
        }

        if ($campaign->sent_count >= $campaign->total_recipients) {
            $campaign->update(['status' => 'sent']);
        }
    }

    /**
     * Record a push notification open event.
     */
    public function recordOpen(int $campaignId, int $customerId): void
    {
        $this->recordReadStatus($campaignId, $customerId, true);
    }

    /**
     * Record push notification read/unread state.
     */
    public function recordReadStatus(int $campaignId, int $customerId, bool $isRead): void
    {
        if ($isRead) {
            $updated = PushCampaignLog::where('campaign_id', $campaignId)
                ->where('customer_id', $customerId)
                ->whereIn('status', ['sent', 'pending'])
                ->whereNull('opened_at')
                ->update([
                    'status'    => 'opened',
                    'opened_at' => now(),
                ]);

            if ($updated > 0) {
                PushCampaign::where('id', $campaignId)->increment('opened_count');
            }

            return;
        }

        $updated = PushCampaignLog::where('campaign_id', $campaignId)
            ->where('customer_id', $customerId)
            ->where('status', 'opened')
            ->whereNotNull('opened_at')
            ->update([
                'status'    => 'sent',
                'opened_at' => null,
            ]);

        if ($updated > 0) {
            PushCampaign::where('id', $campaignId)
                ->where('opened_count', '>', 0)
                ->decrement('opened_count');
        }
    }
}
