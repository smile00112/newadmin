<?php

declare(strict_types=1);

namespace Webkul\TochkaPayment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\TochkaPayment\Models\TochkaPaymentHistoryProxy;

/**
 * Handles redirects from Tochka Bank after payment (success/fail).
 */
final class RedirectController
{
    private const REDIRECT_DELAY_MS = 2000;

    /**
     * Show success page with message, then optional redirect to external site.
     */
    public function success(Request $request): View
    {
        return $this->showRedirectPage($request, 'success');
    }

    /**
     * Show fail page with message, then optional redirect to external site.
     */
    public function fail(Request $request): View
    {
        return $this->showRedirectPage($request, 'fail');
    }

    private function showRedirectPage(Request $request, string $type): View
    {
        $message = $type === 'success'
            ? trans('tochka-payment::app.redirect.success_message')
            : trans('tochka-payment::app.redirect.failure_message');

        $redirectUrl = $this->resolveRedirectUrl($request);

        return view('tochka-payment::redirect', [
            'type' => $type,
            'message' => $message,
            'redirect_url' => $redirectUrl,
            'redirect_delay_ms' => self::REDIRECT_DELAY_MS,
        ]);
    }

    /**
     * Resolve redirect URL to external site (WooCommerce) if payment has ExternalPayments binding.
     */
    private function resolveRedirectUrl(Request $request): ?string
    {
        $paymentId = $request->input('payment_id');
        $operationId = $request->input('operation_id') ?? $request->input('operationId');

        $payment = null;
        if ($operationId) {
            $payment = TochkaPaymentHistoryProxy::where('operation_id', $operationId)->first();
        }
        if (!$payment && $paymentId) {
            $payment = TochkaPaymentHistoryProxy::find($paymentId);
        }

        if (!$payment) {
            return null;
        }

        try {
            $repository = app(\Webkul\ExternalPayments\Repositories\ExternalPaymentRequestRepository::class);
        } catch (\Throwable) {
            return null;
        }

        $externalRequest = $repository->findByProviderPayment('tochka', (int) $payment->id);
        if (!$externalRequest || !$externalRequest->external_order_id) {
            return null;
        }

        $externalSystem = $externalRequest->externalSystem;
        if (!$externalSystem || empty($externalSystem->woocommerce_site_url)) {
            return null;
        }

        $baseUrl = rtrim($externalSystem->woocommerce_site_url, '/');

        return $baseUrl . '/checkout/order-received/' . $externalRequest->external_order_id . '/';
    }
}
