<?php

namespace Webkul\TochkaPayment\Services;

use Webkul\TochkaPayment\Models\TochkaPaymentBuyer;

class TochkaPaymentBuyerService
{
    /**
     * Find or create a buyer by company and email.
     */
    public function findOrCreate(int $companyId, string $email, ?string $name = null, ?string $phone = null): TochkaPaymentBuyer
    {
        $email = trim(mb_strtolower($email));
        if (empty($email)) {
            throw new \InvalidArgumentException('Buyer email is required');
        }

        $buyer = TochkaPaymentBuyer::where('company_id', $companyId)
            ->where('client_email', $email)
            ->first();

        if ($buyer) {
            $update = [];
            if ($name !== null && $buyer->client_name !== $name) {
                $update['client_name'] = $name;
            }
            if ($phone !== null && $buyer->client_phone !== $phone) {
                $update['client_phone'] = $phone;
            }
            if (! empty($update)) {
                $buyer->update($update);
            }
            return $buyer;
        }

        return TochkaPaymentBuyer::create([
            'company_id' => $companyId,
            'client_email' => $email,
            'client_name' => $name,
            'client_phone' => $phone,
        ]);
    }

    /**
     * Update consumer ID for a buyer.
     */
    public function updateConsumerId(TochkaPaymentBuyer $buyer, string $consumerId): void
    {
        $buyer->update(['consumer_id' => trim($consumerId)]);
    }

    /**
     * Get consumer ID for payment request. Returns null if buyer has no consumerId (new payer).
     */
    public function getConsumerIdForPayment(?int $companyId, ?string $email): ?string
    {
        if (! $companyId || empty(trim((string) $email))) {
            return null;
        }

        $buyer = TochkaPaymentBuyer::where('company_id', $companyId)
            ->where('client_email', trim(mb_strtolower($email)))
            ->first();

        return $buyer?->consumer_id ?: null;
    }

    /**
     * Find buyer by payment data (company_id + client_email).
     */
    public function findByPayment(?int $companyId, ?string $email): ?TochkaPaymentBuyer
    {
        if (! $companyId || empty(trim((string) $email))) {
            return null;
        }

        return TochkaPaymentBuyer::where('company_id', $companyId)
            ->where('client_email', trim(mb_strtolower($email)))
            ->first();
    }
}
