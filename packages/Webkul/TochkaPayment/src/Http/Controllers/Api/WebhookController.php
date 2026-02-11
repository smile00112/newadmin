<?php

namespace Webkul\TochkaPayment\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Webkul\TochkaPayment\Exceptions\InvalidSignatureException;
use Webkul\TochkaPayment\Exceptions\PaymentNotFoundException;
use Webkul\TochkaPayment\Services\WebhookHandler;

class WebhookController
{
    /**
     * Webhook handler instance.
     *
     * @var \Webkul\TochkaPayment\Services\WebhookHandler
     */
    protected $webhookHandler;

    /**
     * Create a new controller instance.
     */
    public function __construct(WebhookHandler $webhookHandler)
    {
        $this->webhookHandler = $webhookHandler;
    }

    /**
     * Handle webhook from Tochka Bank.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request): Response
    {
        try {
            // Get JWT token from request body
            // According to documentation, webhook comes as POST with JWT token in body as plain text
            $jwtToken = $request->getContent();

            if (empty($jwtToken)) {
                Log::error('Tochka Payment: Empty webhook payload');
                return response('Empty payload', 400)
                    ->header('Content-Type', 'text/plain');
            }

            // Try to extract company ID from request if available (e.g., from route parameter)
            $companyId = $request->route('companyId') ?? null;

            // Process webhook
            $result = $this->webhookHandler->process($jwtToken, $companyId);

            Log::info('Tochka Payment: Webhook processed successfully', [
                'result' => $result,
            ]);

            // Return HTTP 200 as required by Tochka Bank
            return response('OK', 200)
                ->header('Content-Type', 'text/plain');

        } catch (InvalidSignatureException $e) {
            Log::error('Tochka Payment: Invalid webhook signature', [
                'error' => $e->getMessage(),
            ]);

            return response($e->getMessage(), $e->getCode())
                ->header('Content-Type', 'text/plain');

        } catch (PaymentNotFoundException $e) {
            Log::warning('Tochka Payment: Payment not found for webhook', [
                'error' => $e->getMessage(),
            ]);

            // Still return 200 to prevent webhook retries for non-existent payments
            return response('OK', 200)
                ->header('Content-Type', 'text/plain');

        } catch (\Exception $e) {
            Log::error('Tochka Payment: Webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return 500 to trigger webhook retry
            return response('Internal server error', 500)
                ->header('Content-Type', 'text/plain');
        }
    }
}
