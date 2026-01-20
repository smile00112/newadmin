<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\Checkout\Facades\Cart;
use Webkul\Customer\Repositories\CustomerAddressRepository;
use Webkul\Payment\Facades\Payment;
use Webkul\RestApi\Http\Resources\V1\Shop\Checkout\CartResource;
use Webkul\RestApi\Http\Resources\V1\Shop\Checkout\CartShippingRateResource;
use Webkul\RestApi\Http\Resources\V1\Shop\Sales\OrderResource;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource as OrderTransformer;
use Webkul\Shipping\Facades\Shipping;
use Webkul\Shop\Http\Requests\CartAddressRequest;

class CheckoutController extends CustomerController
{
    public function __construct(
        protected CustomerAddressRepository $customerAddressRepository,
    ) {}

    /**
     * Save customer address.
     */
    public function saveAddress(CartAddressRequest $cartAddressRequest): JsonResponse
    {
        try {
            $data = $cartAddressRequest->all();

            if (
                Cart::hasError()
                || ! Shipping::collectRates()
            ) {
                return response()->json([
                    'message' => trans('rest-api::app.shop.checkout.error'),
                ], 400);
            }

            Cart::saveAddresses($data);

            $rates = [];

            foreach ($data as $key => $address) {
                if (isset($address['save_as_address']) && $address['save_as_address']) {
                    $this->storeAddress($cartAddressRequest, $address);
                }
            }

            $shippingMethods = Shipping::collectRates()['shippingMethods'] ?? [];

            foreach ($shippingMethods as $code => $shippingMethod) {
                $rates[] = [
                    'carrier_title' => $shippingMethod['carrier_title'],
                    'rates'         => CartShippingRateResource::collection(collect($shippingMethod['rates'])),
                ];
            }

            Cart::collectTotals();

            return response()->json([
                'data'    => [
                    'rates' => $rates,
                    'cart'  => new CartResource(Cart::getCart()),
                ],
                'message' => trans('rest-api::app.shop.checkout.billing-address-saved'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Save shipping method.
     */
    public function saveShipping(Request $request): JsonResponse
    {
        try {
            $validatedData = $this->validate($request, [
                'shipping_method' => 'required',
            ]);

            if (Cart::hasError()
                || ! $validatedData['shipping_method']
                || ! Cart::saveShippingMethod($validatedData['shipping_method'])
            ) {
                return response()->json([
                    'message' => trans('rest-api::app.shop.checkout.error'),
                ], 400);
            }

            // Collect shipping rates after saving shipping method
            // This is necessary for pickup/dinein methods that don't require shipping address
            Shipping::collectRates();

            Cart::collectTotals();

            return response()->json([
                'data'    => [
                    'methods' => Payment::getPaymentMethods(),
                    'cart'    => new CartResource(Cart::getCart()),
                ],
                'message' => trans('rest-api::app.shop.checkout.shipping-method-saved'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Save payment method.
     */
    public function savePayment(Request $request): JsonResponse
    {
        try {
            $validatedData = $this->validate($request, [
                'payment' => 'required',
            ]);

            if (
                Cart::hasError()
                || ! $validatedData['payment']
                || ! Cart::savePaymentMethod($validatedData['payment'])
            ) {
                return response()->json([
                    'message' => trans('rest-api::app.shop.checkout.error'),
                ], 400);
            }

            Cart::collectTotals();

            return response()->json([
                'data'    => [
                    'cart' => new CartResource(Cart::getCart()),
                ],
                'message' => trans('rest-api::app.shop.checkout.payment-method-saved'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Check for minimum order.
     */
    public function checkMinimumOrder(): JsonResponse
    {
        try {
            $minimumOrderAmount = (float) core()->getConfigData('sales.orderSettings.minimum-order.minimum_order_amount') ?? 0;

            $status = Cart::haveMinimumOrderAmount();

            return response()->json([
                'data'    => [
                    'cart'   => new CartResource(Cart::getCart()),
                    'status' => ! $status ? false : true,
                ],
                'message' => ! $status ? trans('rest-api::app.shop.checkout.minimum-order-message', ['amount' => core()->currency($minimumOrderAmount)]) : 'Success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Save order.
     */
    public function saveOrder(OrderRepository $orderRepository): JsonResponse
    {
        try {
            if (Cart::hasError()) {
                return response()->json([
                    'message' => trans('rest-api::app.shop.checkout.error'),
                ], 400);
            }

            Cart::collectTotals();

            $this->validateOrder();

            $cart = Cart::getCart();

            if ($redirectUrl = Payment::getRedirectUrl($cart)) {
                return response()->json([
                    'redirect_url' => $redirectUrl,
                ]);
            }

            $order = $orderRepository->create((new OrderTransformer($cart))->jsonSerialize());

            Cart::deActivateCart();

            return response()->json([
                'data'    => [
                    'order' => new OrderResource($order),
                ],
                'message' => trans('rest-api::app.shop.checkout.order-saved'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message'   => $e->getMessage(),
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => config('app.debug') ? $e->getTraceAsString() : null,
                'cart'      => Cart::getCart() ? [
                    'id'                     => Cart::getCart()->id,
                    'has_error'              => Cart::hasError(),
                    'items_count'            => Cart::getCart()->items_count,
                    'shipping_method'        => Cart::getCart()->shipping_method,
                    'shipping_address'       => Cart::getCart()->shipping_address ? true : false,
                    'billing_address'        => Cart::getCart()->billing_address ? true : false,
                    'selected_shipping_rate' => Cart::getCart()->selected_shipping_rate ? true : false,
                    'payment'                => Cart::getCart()->payment ? true : false,
                ] : null,
            ], 400);
        }
    }

    /**
     * Validate order before creation.
     *
     * @return void|\Exception
     */
    protected function validateOrder()
    {
        $cart = Cart::getCart();

        $minimumOrderAmount = core()->getConfigData('sales.orderSettings.minimum-order.minimum_order_amount') ?: 0;

        if (! Cart::haveMinimumOrderAmount()) {
            throw new \Exception(trans('rest-api::app.shop.checkout.minimum-order-message', ['amount' => core()->currency($minimumOrderAmount)]));
        }

        // Проверяем, выбран ли самовывоз или еда в зале
        $skipAddressValidation = in_array($cart->shipping_method, ['pickup_pickup', 'dinein_dinein']);

        if (
            $cart->haveStockableItems()
            && ! $skipAddressValidation
            && ! $cart->shipping_address
        ) {
            throw new \Exception(trans('rest-api::app.shop.checkout.check-shipping-address'));
        }

        if (! $skipAddressValidation && ! $cart->billing_address) {
            throw new \Exception(trans('rest-api::app.shop.checkout.check-billing-address'));
        }

        if ($cart->haveStockableItems() && ! $cart->selected_shipping_rate) {
            throw new \Exception(trans('rest-api::app.shop.checkout.specify-shipping-method'));
        }

        if (! $cart->payment) {
            throw new \Exception(trans('rest-api::app.shop.checkout.specify-payment-method'));
        }
    }

    /**
     * Store address.
     */
    protected function storeAddress($request, array $address)
    {
        $customer = $this->resolveShopUser($request);

        Event::dispatch('customer.addresses.create.before');

        $data = array_merge([
            'company_name'     => $address['company_name'] ?? null,
            'first_name'       => $address['first_name'] ?? null,
            'last_name'        => $address['last_name'] ?? null,
            'vat_id'           => $address['vat_id'] ?? null,
            'address'          => implode(PHP_EOL, array_filter($address['address'] ?? [])),
            'country'          => $address['country'] ?? null,
            'state'            => $address['state'] ?? null,
            'city'             => $address['city'] ?? null,
            'postcode'         => $address['postcode'] ?? null,
            'phone'            => $address['phone'] ?? null,
            'email'            => $address['email'] ?? null,
            'default_address'  => $address['default_address'] ?? 0,
        ], [
            'customer_id' => $customer->id,
        ]);

        // Ensure only one default address
        if (! empty($data['default_address'])) {
            $this->customerAddressRepository->where('customer_id', $data['customer_id'])
                ->where('default_address', 1)
                ->update(['default_address' => 0]);
        }

        $customerAddress = $this->customerAddressRepository->create($data);

        Event::dispatch('customer.addresses.create.after', $customerAddress);

        return $customerAddress;
    }
}
