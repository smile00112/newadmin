<?php

namespace Webkul\Payment\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderItemRepository;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 5;

    public function __construct(
        protected int $orderId,
        protected ?string $invoiceStatus = null,
        protected ?string $orderStatus = null
    ) {
        $this->onQueue('invoices');
    }

    public function handle(InvoiceRepository $invoiceRepository): void
    {
        $order = Order::with('items')->find($this->orderId);

        if (! $order) {
            Log::warning('GenerateInvoiceJob: Order not found', ['order_id' => $this->orderId]);

            return;
        }

        $invoiceData = ['order_id' => $order->id];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        $invoiceRepository->create($invoiceData, $this->invoiceStatus, $this->orderStatus);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerateInvoiceJob permanently failed', [
            'order_id' => $this->orderId,
            'message'  => $e->getMessage(),
        ]);
    }
}
