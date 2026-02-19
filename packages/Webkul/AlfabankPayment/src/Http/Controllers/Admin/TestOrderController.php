<?php

namespace Webkul\AlfabankPayment\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\AlfabankPayment\Services\AlfabankApiService;

class TestOrderController extends Controller
{
    /**
     * Alfabank API service instance.
     */
    protected AlfabankApiService $apiService;

    /**
     * Create a new controller instance.
     */
    public function __construct(AlfabankApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Display the test order page.
     */
    public function index(): View
    {
        return view('alfabank-payment::admin.test-order.index');
    }

    /**
     * Send test order to Alfabank.
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orderNumber' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0.01',
            'returnUrl' => 'required|url|max:512',
            'failUrl' => 'required|url|max:512',
            'currency' => 'nullable|string|max:3',
            'clientId' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'bindingId' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'dynamicCallbackUrl' => 'nullable|url|max:512',
        ]);

        try {
            // Convert amount to kopecks
            $orderData = [
                'orderNumber' => $validated['orderNumber'],
                'amount' => (int) round($validated['amount'] * 100),
                'returnUrl' => $validated['returnUrl'],
                'failUrl' => $validated['failUrl'],
            ];

            // Add optional fields
            if (!empty($validated['currency'])) {
                $currencyCode = $this->getNumericCurrencyCode($validated['currency']);
                if ($currencyCode) {
                    $orderData['currency'] = $currencyCode;
                }
            }

            if (!empty($validated['clientId'])) {
                $orderData['clientId'] = $validated['clientId'];
            }

            if (!empty($validated['email'])) {
                $orderData['email'] = $validated['email'];
            }

            if (!empty($validated['bindingId'])) {
                $orderData['bindingId'] = $validated['bindingId'];
            }

            if (!empty($validated['description'])) {
                $orderData['description'] = $validated['description'];
            }

            if (!empty($validated['dynamicCallbackUrl'])) {
                $orderData['dynamicCallbackUrl'] = $validated['dynamicCallbackUrl'];
            }

            // Add JSON params
            $orderData['jsonParams'] = json_encode([
                'CMS' => 'Laravel ' . app()->version() . ' + Surprise',
                'CMS_paymentType' => !empty($validated['bindingId']) ? 'saved_card' : 'redirect',
                'CMS_testOrder' => 'true',
            ]);

            $response = $this->apiService->registerOrder($orderData);

            if (isset($response['errorCode']) && $response['errorCode'] != '0') {
                return new JsonResponse([
                    'success' => false,
                    'message' => $response['errorMessage'] ?? 'Ошибка регистрации заказа',
                    'errorCode' => $response['errorCode'] ?? null,
                    'response' => $response,
                ], 400);
            }

            return new JsonResponse([
                'success' => true,
                'message' => 'Заказ успешно зарегистрирован',
                'formUrl' => $response['formUrl'] ?? null,
                'orderId' => $response['orderId'] ?? null,
                'response' => $response,
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get numeric currency code.
     *
     * @param  string  $currencyCode
     * @return string|null
     */
    protected function getNumericCurrencyCode(string $currencyCode): ?string
    {
        $codes = [
            'BYN' => '933',
            'BYR' => '974',
            'RUB' => '643',
            'USD' => '840',
            'EUR' => '978',
        ];

        return $codes[strtoupper($currencyCode)] ?? null;
    }
}
