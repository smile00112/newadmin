<?php

namespace Webkul\Reporting\Services;

use Webkul\Reporting\Models\AnalyticsEvent;
use Webkul\Reporting\Models\AnalyticsSession;
use Illuminate\Support\Facades\DB;

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

        if (isset($ctx['session_id'])) {
            AnalyticsSession::where('session_id', $ctx['session_id'])
                ->increment('events_count');
        }
    }

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
