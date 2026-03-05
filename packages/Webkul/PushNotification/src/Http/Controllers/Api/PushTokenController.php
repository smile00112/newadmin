<?php

namespace Webkul\PushNotification\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\PushNotification\Models\CustomerPushToken;

class PushTokenController
{
    /**
     * Store a new push token for the authenticated customer.
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'token'       => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $customer = auth('sanctum')->user();

        if (! $customer) {
            return response(['message' => 'Not authorized'], 401);
        }

        $pushToken = CustomerPushToken::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'token'       => $request->token,
            ],
            [
                'device_name' => $request->device_name ?? null,
                'is_active'   => true,
            ]
        );

        return response([
            'message' => 'Push token stored successfully',
            'data'    => [
                'id'          => $pushToken->id,
                'token'       => $pushToken->token,
                'device_name' => $pushToken->device_name,
                'is_active'   => $pushToken->is_active,
                'created_at'  => $pushToken->created_at,
                'updated_at'  => $pushToken->updated_at,
            ],
        ]);
    }

    /**
     * Delete a push token for the authenticated customer.
     */
    public function destroy(Request $request): Response
    {
        $customer = auth('sanctum')->user();

        if (! $customer) {
            return response(['message' => 'Not authorized'], 401);
        }

        $request->validate([
            'token' => 'nullable|string',
        ]);

        $token = $request->input('token');

        if ($token) {
            CustomerPushToken::where('customer_id', $customer->id)
                ->where('token', $token)
                ->delete();

            return response([
                'message' => 'Push token deleted successfully',
            ]);
        }

        CustomerPushToken::where('customer_id', $customer->id)->delete();

        return response([
            'message' => 'All push tokens deleted successfully',
        ]);
    }
}
