<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Webkul\AlfabankPayment\Services\AlfabankApiService;
use Webkul\RestApi\Http\Controllers\V1\Shop\ShopController;

class PaymentGatewayController extends ShopController
{
    public function __construct(
        protected AlfabankApiService $apiService,
    ) {}

    /**
     * Register a new order on the payment gateway.
     *
     * POST /api/v1/customer/payment/register
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'amount'      => 'required|integer|min:1',
            'orderNumber' => 'nullable|string|max:32',
            'returnUrl'   => 'nullable|string|max:512',
            'failUrl'     => 'nullable|string|max:512',
            'clientId'    => 'nullable|string|max:255',
            'features'    => 'nullable|string|max:64',
        ]);

        $customer = $request->user();

        $orderData = [
            'amount'      => $request->input('amount'),
            'orderNumber' => $request->input('orderNumber', 'APP-' . time()),
            'returnUrl'   => $request->input('returnUrl', 'sdk://done'),
            'failUrl'     => $request->input('failUrl', 'sdk://done'),
            'clientId'    => $request->input('clientId', 'customer_' . $customer->id),
        ];

        if ($request->filled('features')) {
            $orderData['jsonParams'] = json_encode(['FORCE_SSL' => true]);
            $orderData['clientId'] = $orderData['clientId'];
        }

        // FORCE_SSL for card binding orders
        if ($request->input('features') === 'FORCE_SSL') {
            $orderData['jsonParams'] = json_encode(['features' => 'FORCE_SSL']);
        }

        try {
            $result = $this->apiService->registerOrder($orderData);

            if (isset($result['errorCode']) && $result['errorCode'] !== '0' && $result['errorCode'] !== 0) {
                return response()->json([
                    'error'   => true,
                    'message' => $result['errorMessage'] ?? 'Registration failed',
                ], 422);
            }

            return response()->json([
                'orderId'  => $result['orderId'] ?? null,
                'formUrl'  => $result['formUrl'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('PaymentGateway register error: ' . $e->getMessage());

            return response()->json([
                'error'   => true,
                'message' => 'Payment gateway error',
            ], 500);
        }
    }

    /**
     * Pay with new card using seToken.
     *
     * POST /api/v1/customer/payment/pay
     */
    public function pay(Request $request): JsonResponse
    {
        $request->validate([
            'mdOrder' => 'required|string',
            'seToken' => 'required|string',
        ]);

        try {
            $result = $this->apiService->payWithNewCard(
                $request->input('mdOrder'),
                $request->input('seToken'),
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('PaymentGateway pay error: ' . $e->getMessage());

            return response()->json([
                'error'   => true,
                'message' => 'Payment failed',
            ], 500);
        }
    }

    /**
     * Pay with saved card (binding).
     *
     * POST /api/v1/customer/payment/pay-binding
     */
    public function payBinding(Request $request): JsonResponse
    {
        $request->validate([
            'mdOrder'   => 'required|string',
            'bindingId' => 'required|string',
            'cvc'       => 'nullable|string|max:4',
        ]);

        try {
            $result = $this->apiService->payWithBinding(
                $request->input('mdOrder'),
                $request->input('bindingId'),
                $request->input('cvc'),
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('PaymentGateway payBinding error: ' . $e->getMessage());

            return response()->json([
                'error'   => true,
                'message' => 'Payment failed',
            ], 500);
        }
    }

    /**
     * Reverse (cancel) a payment.
     *
     * POST /api/v1/customer/payment/reverse
     */
    public function reverse(Request $request): JsonResponse
    {
        $request->validate([
            'mdOrder' => 'required|string',
        ]);

        try {
            $result = $this->apiService->reverseOrder(
                $request->input('mdOrder'),
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('PaymentGateway reverse error: ' . $e->getMessage());

            return response()->json([
                'error'   => true,
                'message' => 'Reverse failed',
            ], 500);
        }
    }

    /**
     * Get gateway public key for seToken encryption.
     *
     * GET /api/v1/customer/payment/public-key
     */
    public function publicKey(): JsonResponse
    {
        try {
            $result = $this->apiService->getPublicKey();

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('PaymentGateway publicKey error: ' . $e->getMessage());

            return response()->json([
                'error'   => true,
                'message' => 'Failed to get public key',
            ], 500);
        }
    }

    /**
     * Get order status from gateway.
     *
     * POST /api/v1/customer/payment/order-status
     */
    public function orderStatus(Request $request): JsonResponse
    {
        $request->validate([
            'mdOrder' => 'required|string',
        ]);

        try {
            $result = $this->apiService->getOrderStatus(
                $request->input('mdOrder'),
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('PaymentGateway orderStatus error: ' . $e->getMessage());

            return response()->json([
                'error'   => true,
                'message' => 'Failed to get order status',
            ], 500);
        }
    }
}
