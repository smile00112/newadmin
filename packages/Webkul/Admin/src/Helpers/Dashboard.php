<?php

namespace Webkul\Admin\Helpers;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Webkul\Admin\Helpers\Reporting\Customer;
use Webkul\Admin\Helpers\Reporting\Product;
use Webkul\Admin\Helpers\Reporting\Sale;
use Webkul\Admin\Helpers\Reporting\Visitor;

class Dashboard
{
    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct(
        protected Sale $saleReporting,
        protected Product $productReporting,
        protected Customer $customerReporting,
        protected Visitor $visitorReporting
    ) {}

    /**
     * Returns the overall statistics.
     */
    public function getOverAllStats(): array
    {
        return [
            'total_customers'       => $this->customerReporting->getTotalCustomersProgress(),
            'total_orders'          => $this->saleReporting->getTotalOrdersProgress(),
            'total_sales'           => $this->saleReporting->getTotalSalesProgress(),
            'avg_sales'             => $this->saleReporting->getAverageSalesProgress(),
            'total_unpaid_invoices' => [
                'total'           => $total = $this->saleReporting->getTotalPendingInvoicesAmount(),
                'formatted_total' => core()->formatBasePrice($total),
            ],
        ];
    }

    /**
     * Returns the today statistics.
     */
    public function getTodayStats(): array
    {
        $orders = $this->saleReporting->getTodayOrders();

        $orders = $orders->map(function ($order) {
            return [
                'id'                         => $order->id,
                'increment_id'               => $order->id,
                'status'                     => $order->status,
                'status_label'               => $order->status_label,
                'payment_method'             => core()->getConfigData('sales.payment_methods.'.$order->payment->method.'.title'),
                'base_grand_total'           => $order->base_grand_total,
                'formatted_base_grand_total' => core()->formatPrice($order->base_grand_total),
                'channel_name'               => $order->channel_name,
                'customer_email'             => $order->customer_email,
                'customer_name'              => $order->customer_full_name,
                'items'                      => view('admin::sales.orders.items', compact('order'))->render(),
                'billing_address'            => $order?->billing_address->city.($order?->billing_address->country ? ', '.core()->country_name($order?->billing_address->country) : ''),
                'created_at'                 => $order->created_at->format('d M Y, H:i:s'),
            ];
        });

        return [
            'total_sales'     => $this->saleReporting->getTodaySalesProgress(),
            'total_orders'    => $this->saleReporting->getTodayOrdersProgress(),
            'total_customers' => $this->customerReporting->getTodayCustomersProgress(),
            'orders'          => $orders,
        ];
    }

    /**
     * Returns the today statistics.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStockThresholdProducts()
    {
        $products = $this->productReporting->getStockThresholdProducts(5);

        $products = $products->map(function ($product) {
            return [
                'id'              => $product->product_id,
                'sku'             => $product->product->sku,
                'name'            => $product->product->name,
                'price'           => $product->product->price,
                'formatted_price' => core()->formatPrice($product->product->price),
                'total_qty'       => $product->total_qty,
                'image'           => $product->product->base_image_url,
            ];
        });

        return $products;
    }

    /**
     * Returns sales statistics.
     */
    public function getSalesStats(): array
    {
        return [
            'total_orders' => $this->saleReporting->getTotalOrdersProgress(),
            'total_sales'  => $this->saleReporting->getTotalSalesProgress(),
            'over_time'    => $this->saleReporting->getCurrentTotalSalesOverTime(),
        ];
    }

    /**
     * Returns visitors statistics.
     */
    public function getVisitorStats(): array
    {
        return [
            'total'     => $this->visitorReporting->getTotalVisitorsProgress(),
            'unique'    => $this->visitorReporting->getTotalUniqueVisitorsProgress(),
            'over_time' => $this->visitorReporting->getCurrentTotalVisitorsOverTime(),
        ];
    }

    /**
     * Returns top selling products statistics.
     */
    public function getTopSellingProducts(): Collection
    {
        return $this->productReporting->getTopSellingProductsByRevenue(5);
    }

    /**
     * Returns top customers statistics.
     */
    public function getTopCustomers(): EloquentCollection
    {
        $customers = $this->customerReporting->getCustomersWithMostSales(5);

        $customers->map(function ($customer) {
            $customer->formatted_total = core()->formatBasePrice($customer->total);
        });

        return $customers;
    }

    /**
     * Get the start date.
     *
     * @return \Carbon\Carbon
     */
    public function getStartDate(): Carbon
    {
        return $this->saleReporting->getStartDate();
    }

    /**
     * Get the end date.
     *
     * @return \Carbon\Carbon
     */
    public function getEndDate(): Carbon
    {
        return $this->saleReporting->getEndDate();
    }

    /**
     * Returns date range
     */
    public function getDateRange(): string
    {
        return $this->getStartDate()->format('d M').' - '.$this->getEndDate()->format('d M');
    }

    /**
     * Returns messages statistics by day.
     */
    public function getMessagesStats(): array
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        
        // Get all days in the range
        $days = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $days[] = [
                'date' => $currentDate->format('Y-m-d'),
                'label' => $currentDate->format('d M'),
                'dayOfYear' => $currentDate->dayOfYear,
            ];
            $currentDate->addDay();
        }

        // Get sent messages (delivered = true OR sending = true OR send_error = true)
        $sentMessages = DB::table('newsletters_customer_numbers')
            ->select(
                DB::raw('DATE(COALESCE(sent_at, created_at)) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where(function ($query) {
                $query->where('delivered', true)
                    ->orWhere('sending', true)
                    ->orWhere('send_error', true);
            })
            ->whereBetween(DB::raw('DATE(COALESCE(sent_at, created_at))'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Get delivered messages (delivered = true)
        $deliveredMessages = DB::table('newsletters_customer_numbers')
            ->select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('delivered', true)
            ->whereBetween(DB::raw('DATE(updated_at)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Get incoming messages (incoming_message = true)
        $incomingMessages = DB::table('newsletters_customer_numbers')
            ->select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('incoming_message', true)
            ->whereBetween(DB::raw('DATE(updated_at)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Get read messages (viewed = true)
        $readMessages = DB::table('newsletters_customer_numbers')
            ->select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('viewed', true)
            ->whereBetween(DB::raw('DATE(updated_at)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Build the over_time array
        $overTime = [];
        foreach ($days as $day) {
            $sentItem = $sentMessages->get($day['date']);
            $deliveredItem = $deliveredMessages->get($day['date']);
            $incomingItem = $incomingMessages->get($day['date']);
            $readItem = $readMessages->get($day['date']);
            
            $overTime[] = [
                'label' => $day['label'],
                'sent' => $sentItem ? (int) $sentItem->count : 0,
                'received' => $deliveredItem ? (int) $deliveredItem->count : 0,
                'incoming' => $incomingItem ? (int) $incomingItem->count : 0,
                'read' => $readItem ? (int) $readItem->count : 0,
            ];
        }

        return [
            'over_time' => $overTime,
        ];
    }

    /**
     * Returns mailing lists statistics by day.
     */
    public function getMailingListsStats(): array
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        
        // Get all days in the range
        $days = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $days[] = [
                'date' => $currentDate->format('Y-m-d'),
                'label' => $currentDate->format('d M'),
                'dayOfYear' => $currentDate->dayOfYear,
            ];
            $currentDate->addDay();
        }

        // Get mailing lists count by day
        $mailingLists = DB::table('newsletters_mailing_lists')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Build the over_time array
        $overTime = [];
        foreach ($days as $day) {
            $mailingListItem = $mailingLists->get($day['date']);
            
            $overTime[] = [
                'label' => $day['label'],
                'count' => $mailingListItem ? (int) $mailingListItem->count : 0,
            ];
        }

        return [
            'over_time' => $overTime,
        ];
    }
}
