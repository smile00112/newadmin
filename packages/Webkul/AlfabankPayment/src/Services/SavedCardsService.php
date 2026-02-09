<?php

namespace Webkul\AlfabankPayment\Services;

use Illuminate\Support\Facades\Session;
use Webkul\AlfabankPayment\Models\SavedCard;

class SavedCardsService
{
    /**
     * Alfabank API service instance.
     */
    protected AlfabankApiService $apiService;

    /**
     * Create a new service instance.
     */
    public function __construct(AlfabankApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Get customer's saved cards from bank and sync to local database.
     *
     * @param  int  $customerId
     * @param  string  $clientId
     * @return array
     */
    public function getCustomerCards(int $customerId, string $clientId): array
    {
        try {
            $response = $this->apiService->getBindings($clientId);

            if (isset($response['errorCode']) && $response['errorCode'] != '0') {
                return [];
            }

            $bindings = $response['bindings'] ?? [];

            if (empty($bindings)) {
                // Deactivate all cards for this customer if no bindings returned
                SavedCard::forCustomer($customerId)->update(['is_active' => false]);
                return [];
            }

            return $this->syncCardsFromBank($customerId, $bindings);
        } catch (\Exception $e) {
            \Log::error('Error fetching saved cards: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sync cards from bank API response to local database.
     *
     * @param  int  $customerId
     * @param  array  $bindings
     * @return array
     */
    public function syncCardsFromBank(int $customerId, array $bindings): array
    {
        $syncedCards = [];
        $bindingIds = [];

        foreach ($bindings as $binding) {
            $bindingId = $binding['bindingId'] ?? null;
            $cardMask = $binding['displayLabel'] ?? '';
            $cardType = $binding['paymentSystem'] ?? null;

            if (!$bindingId) {
                continue;
            }

            $bindingIds[] = $bindingId;

            $card = SavedCard::where('binding_id', $bindingId)->first();

            if ($card) {
                // Update existing card
                $card->update([
                    'customer_id' => $customerId,
                    'card_mask'   => $cardMask,
                    'card_type'   => $cardType,
                    'is_active'   => true,
                ]);
            } else {
                // Create new card
                $card = SavedCard::create([
                    'customer_id' => $customerId,
                    'binding_id'  => $bindingId,
                    'card_mask'   => $cardMask,
                    'card_type'   => $cardType,
                    'is_active'   => true,
                ]);
            }

            $syncedCards[] = $card;
        }

        // Deactivate cards that are no longer in bank response
        SavedCard::forCustomer($customerId)
            ->whereNotIn('binding_id', $bindingIds)
            ->update(['is_active' => false]);

        return $syncedCards;
    }

    /**
     * Get selected card binding ID from session.
     *
     * @return string|null
     */
    public function getSelectedCard(): ?string
    {
        return Session::get('alfabank_selected_card');
    }

    /**
     * Set selected card binding ID in session.
     *
     * @param  string|null  $bindingId
     * @return void
     */
    public function setSelectedCard(?string $bindingId): void
    {
        if ($bindingId) {
            Session::put('alfabank_selected_card', $bindingId);
        } else {
            Session::forget('alfabank_selected_card');
        }
    }

    /**
     * Generate client ID for customer (MD5 hash).
     *
     * @param  int  $customerId
     * @param  string  $email
     * @return string
     */
    public function generateClientId(int $customerId, string $email): string
    {
        $siteUrl = config('app.url');

        return md5($customerId . $email . $siteUrl);
    }

    /**
     * Get active saved cards for customer from local database.
     *
     * @param  int  $customerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLocalCards(int $customerId)
    {
        return SavedCard::forCustomer($customerId)
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
