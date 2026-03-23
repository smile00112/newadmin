<?php

namespace Webkul\RestApi\Services\Auth;

use Illuminate\Http\Request;
use Webkul\Customer\Models\Customer;
use Webkul\RestApi\Models\CustomerTokenLog;

class CustomerTokenLogService
{
    /**
     * Log issued API token for the given customer.
     */
    public function logToken(
        Customer $customer,
        ?string $tokenName,
        array $abilities,
        ?\DateTimeInterface $expiresAt,
        ?string $plainTextToken = null,
        ?Request $request = null
    ): CustomerTokenLog {
        $request ??= request();

        return CustomerTokenLog::create([
            'customer_id' => $customer->id,
            'token_name'  => $tokenName,
            'token'       => $plainTextToken,
            'abilities'   => json_encode($abilities),
            'issued_at'   => now(),
            'expires_at'  => $expiresAt,
            'ip_address'  => $request?->ip(),
            'user_agent'  => $request?->userAgent(),
        ]);
    }
}

