<?php

namespace Webkul\Bonus\Http\Controllers\Shop;

use App\Services\BonusPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Checkout\Facades\Cart;
use Webkul\Shop\Http\Controllers\Controller;

class BonusController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected BonusPaymentService $bonusPaymentService
    ) {}

    /**
     * Apply bonus to cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyBonus(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        // Получаем аутентифицированного пользователя из запроса (работает с Sanctum)
        $customer = $request->user();
        
        // Если пользователь аутентифицирован через Sanctum, явно инициализируем корзину
        if ($customer) {
            Cart::initCart($customer);
        }

        $cart = Cart::getCart();

        if (! $cart || ! $cart->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Корзина не найдена или пользователь не авторизован',
            ], 400);
        }

        try {
            $amount = (float) $request->input('amount');
            $this->bonusPaymentService->applyBonusToCart($cart, $amount);

            Cart::collectTotals();

            return response()->json([
                'success' => true,
                'message' => 'Бонусы успешно применены',
                'cart' => Cart::getCart(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove bonus from cart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeBonus(): JsonResponse
    {
        $cart = Cart::getCart();

        if (! $cart) {
            return response()->json([
                'success' => false,
                'message' => 'Корзина не найдена',
            ], 400);
        }

        try {
            $this->bonusPaymentService->removeBonusFromCart($cart);

            Cart::collectTotals();

            return response()->json([
                'success' => true,
                'message' => 'Бонусы удалены',
                'cart' => Cart::getCart(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
