<?php

namespace Webkul\AlfabankPayment\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Webkul\AlfabankPayment\Payment\AlfabankPayment;
use Webkul\AlfabankPayment\Services\AlfabankApiService;
use Webkul\AlfabankPayment\Services\SavedCardsService;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Shop\Http\Controllers\Controller;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Order repository instance.
     */
    protected OrderRepository $orderRepository;

    /**
     * Alfabank API service instance.
     */
    protected AlfabankApiService $apiService;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        OrderRepository $orderRepository,
        AlfabankApiService $apiService
    ) {
        $this->orderRepository = $orderRepository;
        $this->apiService = $apiService;
    }

    /**
     * Display payment error page.
     *
     * @return \Illuminate\View\View
     */
    public function paymentError(): View
    {
        return view('alfabank-payment::shop.payment.error');
    }

    /**
     * Handle payment callback from bank.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request)
    {
        if (config('alfabank-payment.log_enabled', true)) {
            $channel = config('alfabank-payment.log_channel', 'daily');
            Log::channel($channel)->info('Alfabank incoming callback', [
                'query' => $request->query(),
                'input' => $request->all(),
            ]);
        }

        $orderId = $request->get('mdOrder') ?? $request->get('orderId');

        if (!$orderId) {
            Log::error('Alfabank callback: missing orderId');
            return response('Bad Request', 400);
        }

        try {
            $response = $this->apiService->getOrderStatus($orderId);

            if (isset($response['errorCode']) && $response['errorCode'] != '0') {
                Log::error('Alfabank callback: API error', [
                    'errorCode' => $response['errorCode'],
                    'errorMessage' => $response['errorMessage'] ?? null,
                ]);
                return response('Error', 500);
            }

            $orderStatus = $response['orderStatus'] ?? null;
            $orderNumber = $response['orderNumber'] ?? '';

            $order = $this->resolveOrderFromOrderNumber($orderNumber);

            if (!$order) {
                Log::error('Alfabank callback: order not found or could not be created', ['orderNumber' => $orderNumber]);
                return response('Order not found', 404);
            }

            // Store bank order ID
            $order->update(['additional' => array_merge($order->additional ?? [], ['alfabank_order_id' => $orderId])]);

            // Process order status
            $this->processOrderStatus($order, $orderStatus, $response);

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Alfabank callback exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response('Internal Server Error', 500);
        }
    }

    /**
     * Handle user return from payment page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function return(Request $request): RedirectResponse
    {
        if (config('alfabank-payment.log_enabled', true)) {
            $channel = config('alfabank-payment.log_channel', 'daily');
            Log::channel($channel)->info('Alfabank incoming return', [
                'query' => $request->query(),
                'input' => $request->all(),
            ]);
        }

        $orderId = $request->get('orderId') ?? $request->get('mdOrder');
        $status = $request->get('status');

        if (!$orderId) {
            return redirect()->route('alfabank.payment.error')
                ->with('error', 'Ошибка обработки платежа');
        }

        try {
            $response = $this->apiService->getOrderStatus($orderId);

            if (isset($response['errorCode']) && $response['errorCode'] != '0') {
                Log::error('Alfabank return: API error', [
                    'errorCode' => $response['errorCode'],
                    'errorMessage' => $response['errorMessage'] ?? null,
                ]);

                return redirect()->route('alfabank.payment.error')
                    ->with('error', 'Ошибка обработки платежа: ' . ($response['errorMessage'] ?? 'Неизвестная ошибка'));
            }

            $orderStatus = $response['orderStatus'] ?? null;
            $orderNumber = $response['orderNumber'] ?? '';

            $order = $this->resolveOrderFromOrderNumber($orderNumber);

            if (!$order) {
                return redirect()->route('alfabank.payment.error')
                    ->with('error', 'Заказ не найден');
            }

            // Store bank order ID
            $order->update(['additional' => array_merge($order->additional ?? [], ['alfabank_order_id' => $orderId])]);

            // Process order status
            $this->processOrderStatus($order, $orderStatus, $response);

            // Redirect based on status
            if ($orderStatus == '1' || $orderStatus == '2') {
                // Payment successful
                $successUrl = core()->getConfigData('sales.payment_methods.alfabank.success_url');
                if ($successUrl) {
                    return redirect($successUrl . '?order_id=' . $order->id);
                }

                return redirect()->route('shop.checkout.onepage.success')
                    ->with('order_id', $order->id);
            } else {
                // Payment failed
                $failUrl = core()->getConfigData('sales.payment_methods.alfabank.fail_url');
                if ($failUrl) {
                    return redirect($failUrl . '?order_id=' . $order->id);
                }

                return redirect()->route('alfabank.payment.error')
                    ->with('error', 'Платеж не был выполнен: ' . ($response['actionCodeDescription'] ?? 'Неизвестная ошибка'));
            }
        } catch (\Exception $e) {
            Log::error('Alfabank return exception: ' . $e->getMessage());
            return redirect()->route('alfabank.payment.error')
                ->with('error', 'Ошибка обработки платежа');
        }
    }

    /**
     * Start payment: register order in bank and redirect to formUrl (API flow).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function startPayment(Request $request)
    {
        $orderId = $request->get('order_id');
        if (!$orderId) {
            Log::warning('Alfabank startPayment: missing order_id');
            return redirect()->route('alfabank.payment.error')
                ->with('error', 'Ошибка: не указан заказ');
        }

        $order = Order::find($orderId);
        if (!$order) {
            Log::warning('Alfabank startPayment: order not found', ['order_id' => $orderId]);
            return redirect()->route('alfabank.payment.error')
                ->with('error', 'Заказ не найден');
        }

        $paymentMethod = $order->payment ? $order->payment->method : null;
        if ($paymentMethod !== 'alfabank') {
            Log::warning('Alfabank startPayment: order payment is not alfabank', ['order_id' => $orderId]);
            return redirect()->route('alfabank.payment.error')
                ->with('error', 'Неверный способ оплаты');
        }

        $allowedStatuses = ['pending', 'pending_payment', 'failed'];
        if (!in_array($order->status, $allowedStatuses)) {
            Log::warning('Alfabank startPayment: order status does not allow payment', [
                'order_id' => $orderId,
                'status' => $order->status,
            ]);
            return redirect()->route('alfabank.payment.error')
                ->with('error', 'Оплата для этого заказа недоступна');
        }

        try {
            $existingFormUrl = $order->payment?->additional['alfabank_form_url'] ?? null;
            if (!empty($existingFormUrl)) {
                return redirect()->away($existingFormUrl);
            }

            $alfabankPayment = app(AlfabankPayment::class);
            $orderData = $alfabankPayment->buildOrderDataFromOrder($order);
            $response = $this->apiService->registerOrder($orderData);

            if (isset($response['errorCode']) && $response['errorCode'] != '0') {
                Log::error('Alfabank startPayment: registration error', [
                    'order_id' => $orderId,
                    'errorCode' => $response['errorCode'] ?? null,
                    'errorMessage' => $response['errorMessage'] ?? null,
                ]);
                $failUrl = core()->getConfigData('sales.payment_methods.alfabank.fail_url');
                if ($failUrl) {
                    return redirect($failUrl . '?order_id=' . $order->id);
                }
                return redirect()->route('alfabank.payment.error')
                    ->with('error', 'Ошибка регистрации платежа: ' . ($response['errorMessage'] ?? 'Неизвестная ошибка'));
            }

            if (empty($response['formUrl'])) {
                Log::error('Alfabank startPayment: no formUrl in response', ['order_id' => $orderId, 'response' => $response]);
                return redirect()->route('alfabank.payment.error')
                    ->with('error', 'Ошибка регистрации платежа');
            }

            $order->update([
                'additional' => array_merge($order->additional ?? [], [
                    'alfabank_order_id' => $response['orderId'] ?? null,
                ]),
            ]);

            $paymentAdditional = $order->payment->additional ?? [];
            $formUrl = $response['formUrl'] ?? null;
            $order->payment->update([
                'additional' => array_merge($paymentAdditional, [
                    'alfabank_order_id' => $response['orderId'] ?? null,
                    'alfabank_form_url' => $formUrl,
                ]),
            ]);

            if (config('alfabank-payment.log_enabled', true)) {
                $channel = config('alfabank-payment.log_channel', 'daily');
                Log::channel($channel)->info('Alfabank: payment link saved to order', [
                    'order_id'          => $order->id,
                    'alfabank_order_id' => $response['orderId'] ?? null,
                    'form_url'          => $formUrl,
                ]);
            }

            return redirect()->away($response['formUrl']);
        } catch (\Exception $e) {
            Log::error('Alfabank startPayment exception: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('alfabank.payment.error')
                ->with('error', 'Ошибка обработки платежа');
        }
    }

    /**
     * Get saved cards for customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSavedCards(Request $request)
    {
        $customer = auth()->guard('customer')->user();

        if (!$customer) {
            return response()->json(['cards' => []], 200);
        }

        try {
            $savedCardsService = app(\Webkul\AlfabankPayment\Services\SavedCardsService::class);
            $clientId = $savedCardsService->generateClientId($customer->id, $customer->email);
            $cards = $savedCardsService->getCustomerCards($customer->id, $clientId);

            return response()->json([
                'cards' => $cards->map(function ($card) {
                    return [
                        'binding_id' => $card->binding_id,
                        'card_mask'  => $card->card_mask,
                        'card_type'  => $card->card_type,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching saved cards: ' . $e->getMessage());
            return response()->json(['cards' => []], 200);
        }
    }

    /**
     * Set selected card in session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setSelectedCard(Request $request)
    {
        $bindingId = $request->get('binding_id');

        try {
            $savedCardsService = app(\Webkul\AlfabankPayment\Services\SavedCardsService::class);
            $savedCardsService->setSelectedCard($bindingId);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error setting selected card: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Resolve order from bank orderNumber (API flow: numeric id, or cart flow: cart_id_timestamp).
     *
     * @param  string  $orderNumber
     * @return \Webkul\Sales\Contracts\Order|null
     */
    protected function resolveOrderFromOrderNumber(string $orderNumber)
    {
        if ($orderNumber === '') {
            return null;
        }

        // API flow: orderNumber is order id (numeric only)
        if (ctype_digit($orderNumber)) {
            return Order::find((int) $orderNumber);
        }

        // Cart flow: orderNumber is cart_id_timestamp
        $parts = explode('_', $orderNumber);
        $cartId = isset($parts[0]) ? (int) $parts[0] : 0;
        if ($cartId <= 0) {
            return null;
        }

        return $this->findOrCreateOrder($cartId);
    }

    /**
     * Find or create order from cart.
     *
     * @param  int  $cartId
     * @return \Webkul\Sales\Contracts\Order|null
     */
    protected function findOrCreateOrder(int $cartId)
    {
        // Try to find existing order by cart ID
        $order = \Webkul\Sales\Models\Order::where('cart_id', $cartId)->first();

        if ($order) {
            return $order;
        }

        // Try to restore cart from session
        $cart = \Webkul\Checkout\Models\Cart::find($cartId);

        if (!$cart) {
            // Try to get cart ID from session
            $sessionCartId = Session::get('alfabank_cart_id');
            if ($sessionCartId == $cartId) {
                Cart::setCart($cartId);
                $cart = Cart::getCart();
            }
        }

        if (!$cart) {
            return null;
        }

        // Create order from cart
        try {
            Cart::setCart($cart);
            Cart::collectTotals();

            // Validate cart before creating order
            if (Cart::hasError()) {
                Log::error('Alfabank: Cart has errors, cannot create order', ['cartId' => $cartId]);
                return null;
            }

            $orderData = (new \Webkul\Sales\Transformers\OrderResource($cart))->jsonSerialize();

            $order = $this->orderRepository->create($orderData);

            Cart::deActivateCart();

            return $order;
        } catch (\Exception $e) {
            Log::error('Error creating order from cart: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Process order status from bank response.
     *
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @param  string|null  $orderStatus
     * @param  array  $response
     * @return void
     */
    protected function processOrderStatus($order, ?string $orderStatus, array $response): void
    {
        DB::beginTransaction();

        try {
            $orderStatusPaid = core()->getConfigData('sales.payment_methods.alfabank.order_status_paid') ?? 'processing';

            if ($orderStatus == '1' || $orderStatus == '2') {
                // Payment successful
                if (in_array($order->status, ['pending', 'failed'])) {
                    $order->update([
                        'status' => $orderStatusPaid,
                    ]);

                    // Store transaction ID
                    if (isset($response['authRefNum'])) {
                        $payment = $order->payment;
                        if ($payment) {
                            $additional = $payment->additional ?? [];
                            $additional['transaction_id'] = $response['authRefNum'];
                            $payment->update(['additional' => $additional]);
                        }
                    }

                    // Dispatch order status updated event
                    if (class_exists(\Webkul\Sales\Events\OrderStatusUpdated::class)) {
                        event(new \Webkul\Sales\Events\OrderStatusUpdated($order));
                    }

                    Log::info('Alfabank payment successful', [
                        'orderId' => $order->id,
                        'orderStatus' => $orderStatus,
                        'transactionId' => $response['authRefNum'] ?? null,
                    ]);

                    // Save card from payment response if bindingInfo and cardAuthInfo present
                    if ($order->customer_id && isset($response['bindingInfo']) && isset($response['cardAuthInfo'])) {
                        app(SavedCardsService::class)->saveCardFromPaymentResponse($order->customer_id, $response);
                    }
                }
            } elseif ($orderStatus == '3') {
                // Reversed
                Log::info('Alfabank order reversed', [
                    'orderId' => $order->id,
                    'response' => $response,
                ]);
            } elseif ($orderStatus == '4') {
                // Refunded
                Log::info('Alfabank order refunded', [
                    'orderId' => $order->id,
                    'response' => $response,
                ]);
            } else {
                // Payment failed
                if ($order->status == 'pending') {
                    $order->update([
                        'status' => 'failed',
                    ]);

                    Log::warning('Alfabank payment failed', [
                        'orderId' => $order->id,
                        'orderStatus' => $orderStatus,
                        'errorMessage' => $response['actionCodeDescription'] ?? null,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing order status: ' . $e->getMessage(), [
                'orderId' => $order->id,
                'orderStatus' => $orderStatus,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
