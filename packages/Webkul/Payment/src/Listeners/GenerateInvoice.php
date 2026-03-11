<?php

namespace Webkul\Payment\Listeners;

use Webkul\Payment\Jobs\GenerateInvoiceJob;

class GenerateInvoice
{
    /**
     * Dispatch invoice generation to the queue.
     *
     * @param  object  $order
     * @return void
     */
    public function handle($order)
    {
        if (
            $order->payment->method == 'cashondelivery'
            && core()->getConfigData('sales.payment_methods.cashondelivery.generate_invoice')
        ) {
            GenerateInvoiceJob::dispatch(
                $order->id,
                core()->getConfigData('sales.payment_methods.cashondelivery.invoice_status'),
                core()->getConfigData('sales.payment_methods.cashondelivery.order_status')
            );
        }

        if (
            $order->payment->method == 'moneytransfer'
            && core()->getConfigData('sales.payment_methods.moneytransfer.generate_invoice')
        ) {
            GenerateInvoiceJob::dispatch(
                $order->id,
                core()->getConfigData('sales.payment_methods.moneytransfer.invoice_status'),
                core()->getConfigData('sales.payment_methods.moneytransfer.order_status')
            );
        }
    }
}
