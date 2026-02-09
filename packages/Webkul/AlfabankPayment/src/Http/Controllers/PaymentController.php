<?php

namespace Webkul\AlfabankPayment\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Webkul\AlfabankPayment\Services\AlfabankApiService;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Shop\Http\Controllers\Controller;

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
     * Handle payment callback from bank.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request)
    {
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

            // Extract cart ID from order number (format: cart_id_timestamp)
            $parts = explode('_', $orderNumber);
            $cartId = $parts[0] ?? null;

            if (!$cartId) {
                Log::error('Alfabank callback: invalid order number', ['orderNumber' => $orderNumber]);
                return response('Bad Request', 400);
            }

            // Find or create order
            $order = $this->findOrCreateOrder($cartId);

            if (!$order) {
                Log::error('Alfabank callback: order not found or could not be created', ['cartId' => $cartId]);
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
        $orderId = $request->get('orderId') ?? $request->get('mdOrder');
        $status = $request->get('status');

        if (!$orderId) {
            return redirect()->route('shop.checkout.cart.index')
                ->with('error', 'Ошибка обработки платежа');
        }

        try {
            $response = $this->apiService->getOrderStatus($orderId);

            if (isset($response['errorCode']) && $response['errorCode'] != '0') {
                Log::error('Alfabank return: API error', [
                    'errorCode' => $response['errorCode'],
                    'errorMessage' => $response['errorMessage'] ?? null,
                ]);

                return redirect()->route('shop.checkout.cart.index')
                    ->with('error', 'Ошибка обработки платежа: ' . ($response['errorMessage'] ?? 'Неизвестная ошибка'));
            }

            $orderStatus = $response['orderStatus'] ?? null;
            $orderNumber = $response['orderNumber'] ?? '';

            // Extract cart ID from order number
            $parts = explode('_', $orderNumber);
            $cartId = $parts[0] ?? null;

            if (!$cartId) {
                return redirect()->route('shop.checkout.cart.index')
                    ->with('error', 'Ошибка обработки платежа');
            }

            // Find or create order
            $order = $this->findOrCreateOrder($cartId);

            if (!$order) {
                return redirect()->route('shop.checkout.cart.index')
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

                return redirect()->route('shop.checkout.cart.index')
                    ->with('error', 'Платеж не был выполнен: ' . ($response['actionCodeDescription'] ?? 'Неизвестная ошибка'));
            }
        } catch (\Exception $e) {
            Log::error('Alfabank return exception: ' . $e->getMessage());
            return redirect()->route('shop.checkout.cart.index')
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
