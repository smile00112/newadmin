<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use App\Models\CustomerPushToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\RestApi\Http\Controllers\V1\Shop\Controller;

class PushTokenController extends Controller
{
    /**
     * Store a new push token for the authenticated customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'token'       => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $customer = auth('sanctum')->user();

        if (! $customer) {
            return response(['message' => trans('rest-api::app.shop.customer.auth.not-authorized')], 401);
        }

        // Upsert: update if token exists for this customer, otherwise create
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
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request): Response
    {
        $customer = auth('sanctum')->user();

        if (! $customer) {
            return response(['message' => trans('rest-api::app.shop.customer.auth.not-authorized')], 401);
        }

        $request->validate([
            'token' => 'nullable|string',
        ]);

        $token = $request->input('token');

        if ($token) {
            // Delete specific token
            CustomerPushToken::where('customer_id', $customer->id)
                ->where('token', $token)
                ->delete();

            return response([
                'message' => 'Push token deleted successfully',
            ]);
        }

        // Delete all tokens for this customer
        CustomerPushToken::where('customer_id', $customer->id)->delete();

        return response([
            'message' => 'All push tokens deleted successfully',
        ]);
    }
}
