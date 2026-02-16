<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Contracts;

interface PaymentProviderAdapterInterface
{
    /**
     * Create a payment in the provider and return payment details.
     *
     * @param  array<string, mixed>  $data  Validated data: amount, client_name, client_email, client_phone, optional external_order_id, product_name
     * @return array{payment_id: int, payment_url: string, order_id: string}
     *
     * @throws \Exception
     */
    public function createPayment(array $data): array;

    /**
     * Get minimum amount for this provider (for validation).
     */
    public function getMinAmount(): float;
}
