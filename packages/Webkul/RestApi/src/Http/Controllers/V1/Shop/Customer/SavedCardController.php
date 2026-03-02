<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\AlfabankPayment\Models\SavedCard;
use Webkul\RestApi\Http\Resources\V1\Shop\Customer\SavedCardResource;

class SavedCardController extends CustomerController
{
    /**
     * Get customer's saved cards.
     */
    public function index(Request $request): Response
    {
        $customer = $this->resolveShopUser($request);

        $cards = SavedCard::forCustomer($customer->id)
            ->orderBy('id', 'desc')
            ->get();

        return response([
            'data' => SavedCardResource::collection($cards),
        ]);
    }

    /**
     * Delete customer's saved card.
     */
    public function destroy(Request $request, int $id): Response
    {
        $customer = $this->resolveShopUser($request);
        $savedCardsService = app(\Webkul\AlfabankPayment\Services\SavedCardsService::class);

        if (! $savedCardsService->removeCard($customer->id, $id)) {
            return response([
                'message' => trans('rest-api::app.shop.customer.saved-cards.not-found'),
            ], 404);
        }

        return response([
            'message' => trans('rest-api::app.shop.customer.saved-cards.delete-success'),
        ]);
    }
}
