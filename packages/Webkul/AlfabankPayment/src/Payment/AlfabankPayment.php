<?php

namespace Webkul\AlfabankPayment\Payment;

use Illuminate\Foundation\ViteException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Webkul\AlfabankPayment\Services\AlfabankApiService;
use Webkul\AlfabankPayment\Services\SavedCardsService;
use Webkul\Checkout\Facades\Cart;
use Webkul\Payment\Payment\Payment;
use Webkul\Sales\Contracts\Order as OrderContract;

class AlfabankPayment extends Payment
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'alfabank';

    /**
     * Alfabank API service instance.
     */
    protected AlfabankApiService $apiService;

    /**
     * Saved cards service instance.
     */
    protected SavedCardsService $savedCardsService;

    /**
     * Create a new payment method instance.
     */
    public function __construct(
        AlfabankApiService $apiService,
        SavedCardsService $savedCardsService
    ) {
        $this->apiService = $apiService;
        $this->savedCardsService = $savedCardsService;
    }

    /**
     * Get redirect url.
     *
     * @return string
     */
    public function getRedirectUrl(): string
    {
        $cart = $this->getCart();

        if (!$cart) {
            return '';
        }

        try {
            $orderData = $this->buildOrderData($cart);

            $response = $this->apiService->registerOrder($orderData);

            if (isset($response['errorCode']) && $response['errorCode'] != '0') {
                \Log::error('Alfabank payment registration error', [
                    'errorCode' => $response['errorCode'] ?? null,
                    'errorMessage' => $response['errorMessage'] ?? null,
                ]);

                return '';
            }

            Log::warning('Alfabank getRedirectUrl func', [
                '$orderData' => $orderData,
                '$response' => $response
            ]);

            if (empty($response['formUrl'])) {
                \Log::error('Alfabank payment registration: no formUrl in response', $response);
                return '';
            }

            // Store order number mapping in session for callback
            session()->put('alfabank_order_number', $orderData['orderNumber']);
            session()->put('alfabank_cart_id', $cart->id);

            return $response['formUrl'];
        } catch (\Exception $e) {
            \Log::error('Alfabank payment registration exception: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Build order data for registration.
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return array
     */
    protected function buildOrderData($cart): array
    {
        $amount = (int) round($cart->grand_total * 100); // Convert to kopecks
        $orderNumber = $cart->id . '_' . time();

        $returnUrl = route('alfabank.payment.return');
        $failUrl = route('alfabank.payment.return', ['status' => 'fail']);

        $successUrl = $this->getConfigData('success_url');
        $failUrlConfig = $this->getConfigData('fail_url');

        if ($successUrl) {
            $returnUrl = $successUrl . '?order_id=' . $cart->id;
        }

        if ($failUrlConfig) {
            $failUrl = $failUrlConfig . '?order_id=' . $cart->id;
        }

        $orderData = [
            'orderNumber' => $orderNumber,
            'amount'      => $amount,
            'returnUrl'   => $returnUrl,
            'failUrl'     => $failUrl,
        ];

        // Add currency if needed
        $currency = $cart->cart_currency_code ?? 'BYN';
        $currencyNumeric = $this->getNumericCurrencyCode($currency);
        if ($currencyNumeric) {
            $orderData['currency'] = $currencyNumeric;
        }

        // Add client ID if customer is logged in
        if ($cart->customer_id && $cart->customer) {
            $email = $cart->customer->email ?? '';
            $clientId = $this->savedCardsService->generateClientId($cart->customer_id, $email);
            $orderData['clientId'] = $clientId;

            // Add email if available
            if ($email) {
                $orderData['email'] = $email;
            }
        }

        // Add saved card binding ID if selected
        $selectedCard = $this->savedCardsService->getSelectedCard();
        if ($selectedCard) {
            $orderData['bindingId'] = $selectedCard;
        }

        // Add order bundle if send_order is enabled
        if ($this->getConfigData('send_order') == '1') {
            $orderBundle = $this->buildOrderBundle($cart);
            if ($orderBundle) {
                $orderData['orderBundle'] = $orderBundle;
            }

            $taxSystem = $this->getConfigData('tax_system');
            if ($taxSystem !== null) {
                $orderData['taxSystem'] = $taxSystem;
            }
        }

        // Add JSON params
        $jsonParams = [
            'CMS' => 'Laravel ' . app()->version() . ' + Surprise',
            'CMS_paymentType' => $selectedCard ? 'saved_card' : 'redirect',
        ];

        if ($selectedCard) {
            $jsonParams['CMS_bindingsEnabled'] = 'true';
        }

        $orderData['jsonParams'] = json_encode($jsonParams);

        // Add dynamic callback URL if enabled
        $callbackType = $this->getConfigData('callback_type') ?? 'STATIC';
        if ($callbackType === 'DYNAMIC') {
            $orderData['dynamicCallbackUrl'] = route('alfabank.payment.callback', [
                'cart_id' => $cart->id,
                'order_number' => $orderNumber,
            ]);
        }

        return $orderData;
    }

    /**
     * Build order bundle for cart items.
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return array|null
     */
    protected function buildOrderBundle($cart): ?array
    {
        $items = [];
        $positionId = 1;

        foreach ($cart->items as $item) {
            $itemPrice = (int) round($item->price * 100);
            $itemAmount = $itemPrice * $item->quantity;

            $items[] = [
                'positionId' => $positionId++,
                'name'      => $item->name,
                'quantity'  => [
                    'value'   => $item->quantity,
                    'measure' => '0',
                ],
                'itemAmount' => $itemAmount,
                'itemCode'   => $item->sku ?? ($positionId - 1 . '-' . $item->product_id),
                'itemPrice'  => $itemPrice,
                'tax'        => [
                    'taxType' => $this->getTaxType($item),
                ],
                'itemAttributes' => [
                    'attributes' => [
                        [
                            'name'  => 'paymentMethod',
                            'value' => $this->getConfigData('payment_method_type') ?? '4',
                        ],
                        [
                            'name'  => 'paymentObject',
                            'value' => $this->getConfigData('payment_object_type') ?? '1',
                        ],
                    ],
                ],
            ];
        }

        // Add shipping as item if exists
        if ($cart->selected_shipping_rate && $cart->selected_shipping_rate->price > 0) {
            $shippingPrice = (int) round($cart->selected_shipping_rate->price * 100);

            $items[] = [
                'positionId' => $positionId,
                'name'      => $cart->selected_shipping_rate->method_title ?? 'Доставка',
                'quantity'  => [
                    'value'   => 1,
                    'measure' => '0',
                ],
                'itemAmount' => $shippingPrice,
                'itemCode'   => 'delivery',
                'itemPrice'  => $shippingPrice,
                'tax'        => [
                    'taxType' => $this->getConfigData('payment_object_type_delivery') ?? '1',
                ],
                'itemAttributes' => [
                    'attributes' => [
                        [
                            'name'  => 'paymentMethod',
                            'value' => $this->getConfigData('payment_object_type_delivery') ?? '1',
                        ],
                        [
                            'name'  => 'paymentObject',
                            'value' => '4',
                        ],
                    ],
                ],
            ];
        }

        $orderBundle = [
            'orderCreationDate' => date('Y-m-d\TH:i:s'),
            'cartItems'         => ['items' => $items],
        ];

        if ($cart->customer && $cart->customer->email) {
            $orderBundle['customerDetails'] = [
                'email' => $cart->customer->email,
            ];
        }

        return $orderBundle;
    }

    /**
     * Build order data for registration from existing Order (API flow).
     *
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @return array
     */
    public function buildOrderDataFromOrder(OrderContract $order): array
    {
        $amount = (int) round($order->grand_total * 100);
        $orderNumber = (string) $order->id;

        $returnUrl = route('alfabank.payment.return');
        $failUrl = route('alfabank.payment.return', ['status' => 'fail']);

        $successUrl = $this->getConfigData('success_url');
        $failUrlConfig = $this->getConfigData('fail_url');

        if ($successUrl) {
            $returnUrl = $successUrl . '?order_id=' . $order->id;
        }

        if ($failUrlConfig) {
            $failUrl = $failUrlConfig . '?order_id=' . $order->id;
        }

        $orderData = [
            'orderNumber' => $orderNumber,
            'amount'      => $amount,
            'returnUrl'   => $returnUrl,
            'failUrl'     => $failUrl,
        ];

        $currency = $order->order_currency_code ?? 'BYN';
        $currencyNumeric = $this->getNumericCurrencyCode($currency);
        if ($currencyNumeric) {
            $orderData['currency'] = $currencyNumeric;
        }

        $payment = $order->payment;
        $paymentAdditional = $payment?->additional ?? [];
        $orderBindingId = $paymentAdditional['alfabank_binding_id'] ?? null;
        $orderClientId = $paymentAdditional['alfabank_client_id'] ?? null;

        if ($orderBindingId) {
            $orderData['bindingId'] = $orderBindingId;
            $orderData['clientId'] = $orderClientId ?: ($order->customer_id && $order->customer
                ? $this->savedCardsService->generateClientId(
                    $order->customer_id,
                    $order->customer_email ?? ($order->customer->email ?? '')
                )
                : null);
            if ($orderData['clientId']) {
                $email = $order->customer_email ?? ($order->customer->email ?? '');
                if ($email) {
                    $orderData['email'] = $email;
                }
            }
            $selectedCard = $orderBindingId;
        } else {
            if ($order->customer_id && $order->customer) {
                $email = $order->customer_email ?? ($order->customer->email ?? '');
                $clientId = $this->savedCardsService->generateClientId($order->customer_id, $email);
                $orderData['clientId'] = $clientId;
                if ($email) {
                    $orderData['email'] = $email;
                }
            }

            $selectedCard = $this->savedCardsService->getSelectedCard();
            if ($selectedCard) {
                $orderData['bindingId'] = $selectedCard;
            }
        }

        if ($this->getConfigData('send_order') == '1') {
            $orderBundle = $this->buildOrderBundleFromOrder($order);
            if ($orderBundle) {
                $orderData['orderBundle'] = $orderBundle;
            }
            $taxSystem = $this->getConfigData('tax_system');
            if ($taxSystem !== null) {
                $orderData['taxSystem'] = $taxSystem;
            }
        }

        $jsonParams = [
            'CMS' => 'Laravel ' . app()->version() . ' + Surprise',
            'CMS_paymentType' => $selectedCard ? 'saved_card' : 'redirect',
        ];
        if ($selectedCard) {
            $jsonParams['CMS_bindingsEnabled'] = 'true';
        }
        $orderData['jsonParams'] = json_encode($jsonParams);

        $callbackType = $this->getConfigData('callback_type') ?? 'STATIC';
        if ($callbackType === 'DYNAMIC') {
            $orderData['dynamicCallbackUrl'] = route('alfabank.payment.callback', [
                'order_id' => $order->id,
                'order_number' => $orderNumber,
            ]);
        }

        return $orderData;
    }

    /**
     * Build order bundle from order items (for existing Order).
     *
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @return array|null
     */
    protected function buildOrderBundleFromOrder(OrderContract $order): ?array
    {
        $items = [];
        $positionId = 1;

        foreach ($order->items as $item) {
            $itemPrice = (int) round($item->price * 100);
            $itemAmount = $itemPrice * $item->qty_ordered;

            $items[] = [
                'positionId' => $positionId++,
                'name'      => $item->name,
                'quantity'  => [
                    'value'   => (int) $item->qty_ordered,
                    'measure' => '0',
                ],
                'itemAmount' => $itemAmount,
                'itemCode'   => $item->sku ?? ($positionId - 1 . '-' . $item->product_id),
                'itemPrice'  => $itemPrice,
                'tax'        => [
                    'taxType' => $this->getTaxType($item),
                ],
                'itemAttributes' => [
                    'attributes' => [
                        [
                            'name'  => 'paymentMethod',
                            'value' => $this->getConfigData('payment_method_type') ?? '4',
                        ],
                        [
                            'name'  => 'paymentObject',
                            'value' => $this->getConfigData('payment_object_type') ?? '1',
                        ],
                    ],
                ],
            ];
        }

        if ($order->shipping_amount && (float) $order->shipping_amount > 0) {
            $shippingPrice = (int) round($order->shipping_amount * 100);
            $items[] = [
                'positionId' => $positionId,
                'name'      => $order->shipping_title ?? 'Доставка',
                'quantity'  => [
                    'value'   => 1,
                    'measure' => '0',
                ],
                'itemAmount' => $shippingPrice,
                'itemCode'   => 'delivery',
                'itemPrice'  => $shippingPrice,
                'tax'        => [
                    'taxType' => $this->getConfigData('payment_object_type_delivery') ?? '1',
                ],
                'itemAttributes' => [
                    'attributes' => [
                        [
                            'name'  => 'paymentMethod',
                            'value' => $this->getConfigData('payment_object_type_delivery') ?? '1',
                        ],
                        [
                            'name'  => 'paymentObject',
                            'value' => '4',
                        ],
                    ],
                ],
            ];
        }

        $orderBundle = [
            'orderCreationDate' => $order->created_at?->format('Y-m-d\TH:i:s') ?? date('Y-m-d\TH:i:s'),
            'cartItems'         => ['items' => $items],
        ];

        if ($order->customer_email) {
            $orderBundle['customerDetails'] = [
                'email' => $order->customer_email,
            ];
        }

        return $orderBundle;
    }

    /**
     * Get tax type for item.
     *
     * @param  mixed  $item
     * @return int
     */
    protected function getTaxType($item): int
    {
        $defaultTaxType = $this->getConfigData('tax_type') ?? 0;

        // Try to get tax rate from item if available
        // This is a simplified version - you may need to adjust based on your tax system
        return $defaultTaxType;
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

    /**
     * Check if payment method is available.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if (!parent::isAvailable()) {
            return false;
        }

        $cart = $this->getCart();

        if (!$cart) {
            return true;
        }

        // Check min/max order total when cart exists
        $minTotal = $this->getConfigData('min_order_total');
        $maxTotal = $this->getConfigData('max_order_total');

        if ($minTotal && $cart->grand_total < $minTotal) {
            return false;
        }

        if ($maxTotal && $cart->grand_total > $maxTotal) {
            return false;
        }

        return true;
    }

    /**
     * Get payment method image.
     *
     * @return string
     */
    public function getImage(): string
    {
        $url = $this->getConfigData('image');

        if ($url) {
            return Storage::url($url);
        }

        try {
            return bagisto_asset('images/alfabank-payment.png', 'shop');
        } catch (ViteException $e) {
            return asset('vendor/alfabank-payment/images/alfabank-payment.png');
        }
    }
}
