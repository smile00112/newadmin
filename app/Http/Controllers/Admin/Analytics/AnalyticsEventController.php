<?php

namespace App\Http\Controllers\Admin\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\EventTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsEventController extends Controller
{
    public function __construct(protected EventTracker $tracker) {}

    /**
     * POST /api/analytics/events
     *
     * Ingest a single event from app/kiosk/cashier.
     *
     * Body: {
     *   "event_name": "menu_viewed",
     *   "session_id": "abc123",
     *   "properties": { "category": "burgers" }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:100',
            'session_id' => 'nullable|string|max:64',
            'order_id'   => 'nullable|integer|exists:orders,id',
            'properties' => 'nullable|array',
        ]);

        $this->tracker->track(
            $validated['event_name'],
            $validated['properties'] ?? [],
            [
                'customer_id' => auth('customer')->id() ?? $request->header('X-Customer-Id'),
                'session_id'  => $validated['session_id'] ?? $request->header('X-Session-Id'),
                'channel'     => $request->header('X-Channel', 'app'),
                'location_id' => $request->header('X-Location-Id'),
                'device_type' => $request->header('X-Device-Type', 'unknown'),
            ]
        );

        return response()->json(['status' => 'ok'], 201);
    }

    /**
     * POST /api/analytics/events/batch
     *
     * Ingest multiple events in one request.
     *
     * Body: {
     *   "events": [
     *     { "event_name": "menu_viewed", "properties": {} },
     *     { "event_name": "cart_opened", "properties": {} }
     *   ]
     * }
     */
    public function storeBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'events'              => 'required|array|min:1|max:100',
            'events.*.event_name' => 'required|string|max:100',
            'events.*.session_id' => 'nullable|string|max:64',
            'events.*.order_id'   => 'nullable|integer',
            'events.*.properties' => 'nullable|array',
        ]);

        $this->tracker->trackBatch($validated['events']);

        return response()->json(['status' => 'ok', 'count' => count($validated['events'])], 201);
    }

    /**
     * POST /api/analytics/sessions
     *
     * Start or resume a session.
     *
     * Body: { "session_id": "abc123" }
     */
    public function startSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|string|max:64',
        ]);

        $session = $this->tracker->startSession(
            $validated['session_id'],
            auth('customer')->id() ?? $request->input('customer_id'),
            $request->header('X-Channel', 'app')
        );

        return response()->json([
            'session_id'    => $session->session_id,
            'visit_number'  => $session->visit_number,
            'is_first'      => $session->is_first_session,
        ], 201);
    }
}
