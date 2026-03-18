<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webkul\AlfabankPayment\Models\SavedCard;
use Webkul\RestApi\Http\Resources\V1\Shop\Customer\SavedCardResource;
use Webkul\AlfabankPayment\Services\SavedCardsService;

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
     * Create or update customer's saved card from SDK data.
     */
    public function store(Request $request, SavedCardsService $savedCardsService): Response
    {
        $customer = $this->resolveShopUser($request);

        $validated = $this->validate($request, [
            'binding_id' => ['required', 'string', 'max:255'],
            'card_mask'  => ['required', 'string', 'max:255'],
            'card_type'  => ['nullable', 'string', 'max:255'],
            'client_id'  => ['nullable', 'string', 'max:255'],
        ]);

        // Prefer explicit client_id from SDK, otherwise keep existing/null.
        $clientId = $validated['client_id'] ?? null;

        // Try to find existing card by binding for this customer.
        $cardQuery = SavedCard::forCustomer($customer->id)
            ->where('binding_id', $validated['binding_id']);

        if ($clientId) {
            $cardQuery->where(function ($query) use ($clientId) {
                $query->whereNull('client_id')
                    ->orWhere('client_id', $clientId);
            });
        }

        $card = $cardQuery->first();

        if ($card) {
            $card->update([
                'client_id' => $clientId ?? $card->client_id,
                'card_mask' => $validated['card_mask'],
                'card_type' => $validated['card_type'] ?? $card->card_type,
                'is_active' => true,
            ]);
        } else {
            // If card does not exist yet, we can reuse part of the logic from SavedCardsService
            // to keep behavior consistent: one active card per binding+client.
            $existsForClient = null;

            if ($clientId) {
                $existsForClient = SavedCard::where('client_id', $clientId)
                    ->where('binding_id', $validated['binding_id'])
                    ->first();
            }

            if ($existsForClient) {
                $card = $existsForClient;

                $card->update([
                    'customer_id' => $customer->id,
                    'card_mask'   => $validated['card_mask'],
                    'card_type'   => $validated['card_type'] ?? $card->card_type,
                    'is_active'   => true,
                ]);
            } else {
                $card = SavedCard::create([
                    'customer_id' => $customer->id,
                    'client_id'   => $clientId,
                    'binding_id'  => $validated['binding_id'],
                    'card_mask'   => $validated['card_mask'],
                    'card_type'   => $validated['card_type'] ?? null,
                    'is_active'   => true,
                ]);
            }
        }

        return response([
            'data' => new SavedCardResource($card),
        ], 201);
    }

    /**
     * Delete customer's saved card.
     */
    public function destroy(Request $request, int $id): Response
    {
        $customer = $this->resolveShopUser($request);
        $savedCardsService = app(SavedCardsService::class);

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
