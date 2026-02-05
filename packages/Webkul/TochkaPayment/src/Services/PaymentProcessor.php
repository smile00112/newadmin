<?php

namespace Webkul\TochkaPayment\Services;

use Illuminate\Support\Facades\Log;
use Webkul\TochkaPayment\Exceptions\PaymentNotFoundException;
use Webkul\TochkaPayment\Models\TochkaPaymentHistoryProxy;

class PaymentProcessor
{
    /**
     * Process successful payment.
     *
     * @param  array  $paymentData
     * @return \Webkul\TochkaPayment\Models\TochkaPaymentHistory
     * @throws PaymentNotFoundException
     */
    public function processSuccessfulPayment(array $paymentData)
    {
        $payment = TochkaPaymentHistoryProxy::find($paymentData['payment_id']);

        if (!$payment) {
            Log::error('Tochka Payment: Payment not found', ['payment_id' => $paymentData['payment_id']]);
            throw new PaymentNotFoundException("Payment #{$paymentData['payment_id']} not found");
        }

        // Check if already paid
        if ($payment->isPaid()) {
            Log::info('Tochka Payment: Payment already processed', ['payment_id' => $payment->id]);
            return $payment;
        }

        // Validate amount
        if (abs($payment->amount - $paymentData['amount']) > 0.01) {
            Log::error('Tochka Payment: Amount mismatch', [
                'payment_id' => $payment->id,
                'expected' => $payment->amount,
                'received' => $paymentData['amount'],
            ]);
            throw new \Exception('Amount mismatch');
        }

        // Update payment status
        $payment->update([
            'status' => 'paid',
            'transaction_id' => $paymentData['transaction_id'],
            'callback_data' => $paymentData['callback_data'],
        ]);

        Log::info('Tochka Payment: Payment processed successfully', [
            'payment_id' => $payment->id,
            'transaction_id' => $paymentData['transaction_id'],
        ]);

        return $payment;
    }

    /**
     * Mark payment as failed.
     *
     * @param  int  $paymentId
     * @param  array  $callbackData
     * @return \Webkul\TochkaPayment\Models\TochkaPaymentHistory
     * @throws PaymentNotFoundException
     */
    public function markAsFailed(int $paymentId, array $callbackData = [])
    {
        $payment = TochkaPaymentHistoryProxy::find($paymentId);

        if (!$payment) {
            throw new PaymentNotFoundException("Payment #{$paymentId} not found");
        }

        $payment->update([
            'status' => 'failed',
            'callback_data' => $callbackData,
        ]);

        return $payment;
    }
}
