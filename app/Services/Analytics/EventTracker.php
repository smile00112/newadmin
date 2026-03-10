<?php

namespace App\Services\Analytics;

use App\Models\Analytics\AnalyticsEvent;
use App\Models\Analytics\AnalyticsSession;
use Illuminate\Support\Facades\DB;

/**
 * Centralized event tracker.
 *
 * Usage:
 *   app(EventTracker::class)->track('menu_viewed', ['category' => 'burgers']);
 *   app(EventTracker::class)->track('cart_opened');
 *   app(EventTracker::class)->track('payment_started', ['method' => 'card', 'amount' => 1500]);
 *   app(EventTracker::class)->track('order_created', ['order_id' => 123]);
 *
 * Supported events:
 *   menu_viewed, product_viewed, cart_opened, cart_item_added, cart_item_removed,
 *   checkout_started, payment_started, payment_completed, payment_failed,
 *   order_created, order_accepted, order_preparing, order_ready, order_served,
 *   order_completed, order_cancelled, screen_view, app_crash, search_performed,
 *   customization_applied, ingredient_added, ingredient_removed
 */
class EventTracker
{
    public function track(string $eventName, array $properties = [], ?array $context = null): void
    {
        $ctx = $context ?? $this->resolveContext();

        AnalyticsEvent::create([
            'event_name'  => $eventName,
            'customer_id' => $ctx['customer_id'] ?? null,
            'session_id'  => $ctx['session_id'] ?? null,
            'order_id'    => $properties['order_id'] ?? null,
            'channel'     => $ctx['channel'] ?? null,
            'location_id' => $ctx['location_id'] ?? null,
            'device_type' => $ctx['device_type'] ?? null,
            'properties'  => ! empty($properties) ? $properties : null,
        ]);

        // Update session events count
        if (isset($ctx['session_id'])) {
            AnalyticsSession::where('session_id', $ctx['session_id'])
                ->increment('events_count');
        }
    }

    /**
     * Track multiple events in a batch.
     */
    public function trackBatch(array $events): void
    {
        $ctx = $this->resolveContext();
        $rows = [];
        $now = now();

        foreach ($events as $event) {
            $rows[] = [
                'event_name'  => $event['event_name'],
                'customer_id' => $event['customer_id'] ?? $ctx['customer_id'] ?? null,
                'session_id'  => $event['session_id'] ?? $ctx['session_id'] ?? null,
                'order_id'    => $event['order_id'] ?? null,
                'channel'     => $event['channel'] ?? $ctx['channel'] ?? null,
                'location_id' => $event['location_id'] ?? $ctx['location_id'] ?? null,
                'device_type' => $event['device_type'] ?? $ctx['device_type'] ?? null,
                'properties'  => isset($event['properties']) ? json_encode($event['properties']) : null,
                'created_at'  => $now,
            ];
        }

        DB::table('analytics_events')->insert($rows);
    }

    /**
     * Start or resume a session.
     */
    public function startSession(string $sessionId, ?int $customerId = null, ?string $channel = null): AnalyticsSession
    {
        $existing = AnalyticsSession::where('session_id', $sessionId)->first();

        if ($existing) {
            return $existing;
        }

        $visitNumber = 1;
        $isFirst = true;

        if ($customerId) {
            $visitNumber = AnalyticsSession::where('customer_id', $customerId)->count() + 1;
            $isFirst = $visitNumber === 1;
        }

        return AnalyticsSession::create([
            'session_id'       => $sessionId,
            'customer_id'      => $customerId,
            'channel'          => $channel ?? request()->header('X-Channel'),
            'location_id'      => request()->header('X-Location-Id'),
            'device_type'      => request()->header('X-Device-Type', 'unknown'),
            'is_first_session' => $isFirst,
            'visit_number'     => $visitNumber,
            'started_at'       => now(),
        ]);
    }

    /**
     * Mark session as having an order.
     */
    public function markSessionOrder(string $sessionId, int $orderId): void
    {
        AnalyticsSession::where('session_id', $sessionId)->update([
            'has_order' => true,
            'order_id'  => $orderId,
            'ended_at'  => now(),
        ]);
    }

    protected function resolveContext(): array
    {
        return [
            'customer_id' => auth('customer')->id() ?? request()->header('X-Customer-Id'),
            'session_id'  => request()->header('X-Session-Id', session()->getId()),
            'channel'     => request()->header('X-Channel', config('app.channel', 'web')),
            'location_id' => request()->header('X-Location-Id'),
            'device_type' => request()->header('X-Device-Type', 'unknown'),
        ];
    }
}
