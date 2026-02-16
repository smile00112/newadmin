<?php

namespace Webkul\TochkaPayment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webkul\TochkaPayment\Models\TochkaPaymentHistory;

class PaymentSuccess
{
    use Dispatchable, SerializesModels;

    /**
     * The payment history instance.
     *
     * @var \Webkul\TochkaPayment\Models\TochkaPaymentHistory
     */
    public $payment;

    /**
     * Create a new event instance.
     *
     * @param  \Webkul\TochkaPayment\Models\TochkaPaymentHistory  $payment
     * @return void
     */
    public function __construct(TochkaPaymentHistory $payment)
    {
        $this->payment = $payment;
    }
}
