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
}
