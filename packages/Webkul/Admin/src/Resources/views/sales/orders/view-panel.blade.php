<x-admin::layouts :hideNavigation="true">
    <!-- Page Title -->
    <x-slot:title>
        Заказ #{{ $order->increment_id }}
    </x-slot>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .7; } }
        body { background: #f8f9fb !important; }
        /* Hide panel header when loaded inside iframe (drawer provides its own header) */
        body.in-iframe .panel-header-block { display: none !important; }
        body.in-iframe { padding: 0 !important; margin: 0 !important; }
        body.in-iframe > div { padding: 8px 16px !important; min-height: auto !important; }
    </style>

    <script>
        // Detect if we're inside an iframe and add class to body
        if (window !== window.parent) {
            document.documentElement.classList.add('in-iframe');
            document.addEventListener('DOMContentLoaded', function() {
                document.body.classList.add('in-iframe');
            });
        }
    </script>

    <!-- Panel Header (hidden when inside iframe) -->
    <div class="panel-header-block" style="display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:20px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <!-- Close Panel Button -->
            <button
                onclick="window.parent.postMessage({type:'close-order-panel'}, '*');"
                style="display:flex; align-items:center; justify-content:center; width:40px; height:40px; min-width:40px; border-radius:12px; background:#f3f4f6; cursor:pointer; border:none; transition:all 0.2s;"
                onmouseenter="this.style.background='#e5e7eb'; this.style.transform='scale(1.05)';"
                onmouseleave="this.style.background='#f3f4f6'; this.style.transform='scale(1)';"
                title="Закрыть панель"
            >
                <svg style="width:20px; height:20px; color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Order Icon + Title -->
            <div style="display:flex; align-items:center; justify-content:center; width:44px; height:44px; min-width:44px; border-radius:12px; background:linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); box-shadow:0 4px 15px rgba(124,58,237,0.3);">
                <svg style="width:20px; height:20px; color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>

            <div>
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <p style="font-size:20px; font-weight:700; color:#1f2937; margin:0;">
                        Заказ #{{ $order->increment_id }}
                    </p>
                    @php
                        $statusColor = $statusColorMap[$order->status] ?? '#6b7280';
                        $statusName = \Webkul\Sales\Models\OrderStatus::nameByCode($order->status);
                    @endphp
                    <span
                        data-order-status-badge
                        style="display:inline-block; padding:4px 12px; border-radius:9999px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; background:{{ $statusColor }}1a; color:{{ $statusColor }};"
                    >
                        {{ $statusName }}
                    </span>
                </div>
                <p style="font-size:12px; color:#9ca3af; margin:3px 0 0 0;">
                    {{ $order->created_at->format('d.m.Y H:i') }}
                </p>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:8px;">
            <!-- Open Full Page Button -->
            <a
                href="{{ route('admin.sales.orders.view', $order->id) }}"
                target="_top"
                style="display:flex; align-items:center; gap:8px; padding:8px 16px; font-size:14px; font-weight:700; border-radius:12px; transition:all 0.2s; background:linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); color:white; box-shadow:0 4px 15px rgba(124,58,237,0.3); text-decoration:none;"
                onmouseenter="this.style.boxShadow='0 6px 20px rgba(124,58,237,0.4)'"
                onmouseleave="this.style.boxShadow='0 4px 15px rgba(124,58,237,0.3)'"
            >
                <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Открыть
            </a>
        </div>
    </div>

    <!-- Order details (synced with view.blade.php) -->
    <div class="mt-3.5 flex max-xl:flex-wrap" style="gap: 20px;">
        <!-- Left Component -->
        <div class="flex flex-1 flex-col max-xl:flex-auto" style="gap: 20px;">

            <!-- Step Progress Bar -->
            <div class="box-shadow rounded-2xl bg-white dark:bg-gray-900 overflow-hidden">
                <div class="flex items-center gap-2.5 px-5 py-3.5" style="border-bottom: 1px solid #f3f4f6;">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg" style="background: #f1f0ff;">
                        <svg class="w-4 h-4" style="color: #7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-800 dark:text-white">
                        Шаговый прогресс
                    </p>
                    @php
                        $paymentLabel = core()->getConfigData('sales.payment_methods.' . $order->payment->method . '.title') ?? $order->payment->method;
                    @endphp
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full" style="background: #f3f4f6; color: #6b7280;">
                        {{ $paymentLabel }}
                    </span>
                </div>

                <v-order-step-progress
                    order-id="{{ $order->id }}"
                    initial-status="{{ $order->status }}"
                    update-url="{{ route('admin.sales.orders.update_status', $order->id) }}"
                ></v-order-step-progress>
            </div>

            <!-- Items Editor -->
            <div class="box-shadow rounded-2xl bg-white dark:bg-gray-900 overflow-hidden">
                @php
                    $itemsJson = $order->items->map(function ($item) {
                        $imageUrl = null;
                        if ($item->product?->category_image) {
                            $imageUrl = \Illuminate\Support\Facades\Storage::url($item->product->category_image);
                        } elseif ($item->product?->base_image_url) {
                            $imageUrl = $item->product->base_image_url;
                        }
                        return [
                            'id'                  => $item->id,
                            'name'                => $item->name,
                            'sku'                 => $item->sku,
                            'qty_ordered'         => $item->qty_ordered,
                            'base_price'          => $item->base_price,
                            'base_total'          => $item->base_total,
                            'base_tax_amount'     => $item->base_tax_amount,
                            'base_discount_amount'=> $item->base_discount_amount,
                            'formatted_price'     => core()->formatBasePrice($item->base_price),
                            'image_url'           => $imageUrl,
                        ];
                    })->toArray();
                @endphp

                <v-order-items-editor
                    order-id="{{ $order->id }}"
                    initial-items='@json($itemsJson)'
                    grand-total="{{ core()->formatBasePrice($order->base_grand_total) }}"
                ></v-order-items-editor>

                <div class="p-4" style="border-top: 2px solid #f3f0ff;">
                    <div class="flex flex-col gap-2.5" style="max-width: 380px; margin-left: auto;">

                        <!-- Sub Total -->
                        @if (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'including_tax')
                            <div class="flex w-full justify-between gap-x-5">
                                <p class="text-sm text-gray-500">
                                    @lang('admin::app.sales.orders.view.summary-sub-total-incl-tax')
                                </p>
                                <p class="text-sm font-bold text-gray-700 dark:text-gray-200" data-total="sub_total">
                                    {{ core()->formatBasePrice($order->base_sub_total_incl_tax) }}
                                </p>
                            </div>
                        @elseif (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'both')
                            <div class="flex w-full justify-between gap-x-5">
                                <p class="text-sm text-gray-500">
                                    @lang('admin::app.sales.orders.view.summary-sub-total-excl-tax')
                                </p>
                                <p class="text-sm font-bold text-gray-700 dark:text-gray-200" data-total="sub_total">
                                    {{ core()->formatBasePrice($order->base_sub_total) }}
                                </p>
                            </div>
                            <div class="flex w-full justify-between gap-x-5">
                                <p class="text-sm text-gray-500">
                                    @lang('admin::app.sales.orders.view.summary-sub-total-incl-tax')
                                </p>
                                <p class="text-sm font-bold text-gray-700 dark:text-gray-200" data-total="sub_total_incl_tax">
                                    {{ core()->formatBasePrice($order->base_sub_total_incl_tax) }}
                                </p>
                            </div>
                        @else
                            <div class="flex w-full justify-between gap-x-5">
                                <p class="text-sm text-gray-500">
                                    @lang('admin::app.sales.orders.view.summary-sub-total')
                                </p>
                                <p class="text-sm font-bold text-gray-700 dark:text-gray-200" data-total="sub_total">
                                    {{ core()->formatBasePrice($order->base_sub_total) }}
                                </p>
                            </div>
                        @endif

                        <!-- Shipping And Handling -->
                        @if ($haveStockableItems = $order->haveStockableItems())
                            @if (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'including_tax')
                                <div class="flex w-full justify-between gap-x-5">
                                    <p class="text-sm text-gray-500">
                                        @lang('admin::app.sales.orders.view.shipping-and-handling-incl-tax')
                                    </p>
                                    <p class="text-sm font-bold text-gray-700 dark:text-gray-200">
                                        {{ core()->formatBasePrice($order->base_shipping_amount_incl_tax) }}
                                    </p>
                                </div>
                            @elseif (core()->getConfigData('sales.taxes.sales.display_shipping_amount') == 'both')
                                <div class="flex w-full justify-between gap-x-5">
                                    <p class="text-sm text-gray-500">
                                        @lang('admin::app.sales.orders.view.shipping-and-handling-excl-tax')
                                    </p>
                                    <p class="text-sm font-bold text-gray-700 dark:text-gray-200">
                                        {{ core()->formatBasePrice($order->base_shipping_amount) }}
                                    </p>
                                </div>
                                <div class="flex w-full justify-between gap-x-5">
                                    <p class="text-sm text-gray-500">
                                        @lang('admin::app.sales.orders.view.shipping-and-handling-incl-tax')
                                    </p>
                                    <p class="text-sm font-bold text-gray-700 dark:text-gray-200">
                                        {{ core()->formatBasePrice($order->base_shipping_amount_incl_tax) }}
                                    </p>
                                </div>
                            @else
                                <div class="flex w-full justify-between gap-x-5">
                                    <p class="text-sm text-gray-500">
                                        @lang('admin::app.sales.orders.view.shipping-and-handling')
                                    </p>
                                    <p class="text-sm font-bold text-gray-700 dark:text-gray-200">
                                        {{ core()->formatBasePrice($order->base_shipping_amount) }}
                                    </p>
                                </div>
                            @endif
                        @endif

                        <!-- Tax Amount -->
                        <div class="flex w-full justify-between gap-x-5">
                            <p class="text-sm text-gray-500">
                                @lang('admin::app.sales.orders.view.summary-tax')
                            </p>
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-200" data-total="tax">
                                {{ core()->formatBasePrice($order->base_tax_amount) }}
                            </p>
                        </div>

                        <!-- Discount -->
                        <div class="flex w-full justify-between gap-x-5">
                            <p class="text-sm text-gray-500">
                                @lang('admin::app.sales.orders.view.summary-discount')
                            </p>
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-200" data-total="discount">
                                {{ core()->formatBasePrice($order->base_discount_amount) }}
                            </p>
                        </div>

                        <!-- Bonus Payment -->
                        @if ($order->base_bonus_amount > 0)
                            <div class="flex w-full justify-between gap-x-5">
                                <p class="text-sm text-gray-500">
                                    @lang('admin::app.sales.orders.view.bonus-payment')
                                </p>
                                <p class="text-sm font-bold text-gray-700 dark:text-gray-200">
                                    {{ core()->formatBasePrice($order->base_bonus_amount) }}
                                </p>
                            </div>
                        @endif

                        <!-- Purple separator before grand total -->
                        <div style="height: 2px; background: linear-gradient(90deg, transparent, #7c3aed, transparent); margin: 4px 0;"></div>

                        <!-- Grand Total -->
                        <div class="flex w-full justify-between items-center gap-x-5 py-1">
                            <p class="text-base font-bold text-gray-800 dark:text-white">
                                @lang('admin::app.sales.orders.view.summary-grand-total')
                            </p>
                            <p class="text-lg font-black dark:text-white" style="color:#7c3aed; letter-spacing:-0.02em;" data-total="grand_total">
                                {{ core()->formatBasePrice($order->base_grand_total) }}
                            </p>
                        </div>

                        <div style="height: 1px; background: #f0f0f0; margin: 2px 0;"></div>

                        <!-- Total Paid -->
                        <div class="flex w-full justify-between gap-x-5">
                            <p class="text-sm text-gray-500">
                                @lang('admin::app.sales.orders.view.total-paid')
                            </p>
                            <p class="text-sm font-bold" style="color:#059669;" data-total="total_paid">
                                {{ core()->formatBasePrice($order->base_grand_total_invoiced) }}
                            </p>
                        </div>

                        <!-- Total Refund -->
                        <div class="flex w-full justify-between gap-x-5">
                            <p class="text-sm text-gray-500">
                                @lang('admin::app.sales.orders.view.total-refund')
                            </p>
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-200" data-total="total_refunded">
                                {{ core()->formatBasePrice($order->base_grand_total_refunded) }}
                            </p>
                        </div>

                        <!-- Total Due -->
                        <div class="flex w-full justify-between gap-x-5">
                            <p class="text-sm font-bold text-gray-600 dark:text-gray-300">
                                @lang('admin::app.sales.orders.view.total-due')
                            </p>
                            @if($order->status !== 'canceled')
                                <p class="text-sm font-bold" style="color:#ea580c;" data-total="total_due">
                                    {{ core()->formatBasePrice($order->base_total_due) }}
                                </p>
                            @else
                                <p class="text-sm font-bold text-gray-700 dark:text-gray-200" data-total="total_due">
                                    {{ core()->formatBasePrice(0.00) }}
                                </p>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            <!-- Customer's comment form -->
            <div class="box-shadow rounded-2xl bg-white dark:bg-gray-900 overflow-hidden">
                <div class="flex items-center gap-2.5 p-4 pb-0">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg" style="background:#fef3c7;">
                        <svg class="w-4 h-4" style="color:#b45309;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                    </div>
                    <p class="text-base font-bold text-gray-800 dark:text-white">
                        @lang('admin::app.sales.orders.view.comments')
                    </p>
                </div>

                <x-admin::form action="{{ route('admin.sales.orders.comment', $order->id) }}">
                    <div class="p-4">
                        <div class="mb-2.5">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.control
                                    type="textarea"
                                    id="comment"
                                    name="comment"
                                    rules="required"
                                    :label="trans('admin::app.sales.orders.view.comments')"
                                    :placeholder="trans('admin::app.sales.orders.view.write-your-comment')"
                                    rows="3"
                                />

                                <x-admin::form.control-group.error control-name="comment" />
                            </x-admin::form.control-group>
                        </div>

                        <div class="flex items-center justify-between">
                            <label
                                class="flex w-max cursor-pointer select-none items-center gap-1 p-1.5"
                                for="customer_notified"
                            >
                                <input
                                    type="checkbox"
                                    name="customer_notified"
                                    id="customer_notified"
                                    value="1"
                                    class="peer hidden"
                                >

                                <span
                                    class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"
                                    role="button"
                                    tabindex="0"
                                >
                                </span>

                                <p class="flex cursor-pointer items-center gap-x-1 font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100">
                                    @lang('admin::app.sales.orders.view.notify-customer')
                                </p>
                            </label>

                            <button
                                type="submit"
                                class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white rounded-xl transition-all duration-200"
                                style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); box-shadow: 0 2px 8px rgba(124,58,237,0.25);"
                                aria-label="{{ trans('admin::app.sales.orders.view.submit-comment') }}"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                @lang('admin::app.sales.orders.view.submit-comment')
                            </button>
                        </div>
                    </div>
                </x-admin::form>

                <span class="block w-full border-b dark:border-gray-800"></span>

                <!-- Comment List -->
                @foreach ($order->comments()->orderBy('id', 'desc')->get() as $comment)
                    <div class="p-4 mx-4 mb-3 rounded-xl" style="background:#fafafa;">
                        <p class="break-all text-sm leading-6 text-gray-800 dark:text-white">
                            {{ $comment->comment }}
                        </p>

                        <p class="flex items-center gap-2 mt-2 text-xs text-gray-400">
                            @if ($comment->customer_notified)
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full" style="background:#dbeafe;">
                                    <svg class="w-3 h-3" style="color:#2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                @lang('admin::app.sales.orders.view.customer-notified', ['date' => core()->formatDate($comment->created_at, 'd.m.Y H:i')])
                            @else
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full" style="background:#ffe4e6;">
                                    <svg class="w-3 h-3" style="color:#be123c;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </span>
                                @lang('admin::app.sales.orders.view.customer-not-notified', ['date' => core()->formatDate($comment->created_at, 'd.m.Y H:i')])
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>

        </div>

        <!-- Right Component -->
        <div class="flex w-[360px] max-w-full flex-col max-sm:w-full" style="gap: 20px;">

            <!-- Customer and address information -->
            <x-admin::accordion>
                <x-slot:header>
                    <div class="flex items-center gap-2.5 p-2.5">
                        <div class="flex items-center justify-center w-7 h-7 rounded-lg" style="background:#f1f0ff;">
                            <svg class="w-3.5 h-3.5" style="color:#7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <p class="text-base font-bold text-gray-700 dark:text-gray-200">
                            @lang('admin::app.sales.orders.view.customer')
                        </p>
                    </div>
                </x-slot>

                <x-slot:content>
                    <div class="{{ $order->billing_address ? 'pb-4' : '' }}">
                        <div class="flex flex-col gap-3">
                            <!-- Customer Avatar + Name + Edit Link -->
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 flex items-center justify-center text-white font-bold text-lg" style="min-width:48px;">
                                    {{ strtoupper(substr($order->customer_first_name ?? '?', 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-gray-800 dark:text-white text-base truncate">
                                            {{ $order->customer_full_name }}
                                        </p>
                                        @if($order->customer_id)
                                            <a href="{{ route('admin.customers.customers.view', $order->customer_id) }}"
                                               target="_top"
                                               class="flex-shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-md bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 transition-colors"
                                               title="Редактировать клиента"
                                            >
                                                <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                    @if($order->customer_id)
                                        <p class="text-xs text-gray-400">ID: {{ $order->customer_id }}@if(isset($customerMetrics) && $customerMetrics['registered_at'] && $customerMetrics['registered_at'] !== '-') • с {{ $customerMetrics['registered_at'] }}@endif</p>
                                    @else
                                        <p class="text-xs text-gray-400">Гость</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Phone -->
                            @php
                                $customerPhone = $order->shipping_address?->phone
                                    ?? $order->billing_address?->phone
                                    ?? $order->customer?->phone
                                    ?? null;
                                $phoneDigits = $customerPhone ? preg_replace('/[^0-9]/', '', $customerPhone) : null;
                            @endphp
                            @if($customerPhone)
                                <div class="flex items-center justify-between p-3 rounded-xl" style="background: #f0fdf4;">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg" style="background: #dcfce7;">
                                            <svg class="w-4 h-4" style="color: #16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                        </div>
                                        <a href="tel:{{ $phoneDigits }}" class="text-sm font-semibold text-gray-800 dark:text-white hover:underline">
                                            {{ $customerPhone }}
                                        </a>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <a href="tel:{{ $phoneDigits }}"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                                           style="background: #dbeafe;"
                                           title="Позвонить"
                                           onmouseenter="this.style.background='#bfdbfe'" onmouseleave="this.style.background='#dbeafe'"
                                        >
                                            <svg class="w-4 h-4" style="color: #2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                        </a>
                                        <a href="https://wa.me/{{ $phoneDigits }}"
                                           target="_blank"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                                           style="background: #dcfce7;"
                                           title="WhatsApp"
                                           onmouseenter="this.style.background='#bbf7d0'" onmouseleave="this.style.background='#dcfce7'"
                                        >
                                            <svg class="w-4 h-4" style="color: #16a34a;" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <!-- Email -->
                            <div class="flex items-center gap-3 p-3 rounded-xl" style="background: #eff6ff;">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg" style="background: #dbeafe;">
                                    <svg class="w-4 h-4" style="color: #2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <a href="mailto:{{ $order->customer_email }}" class="text-sm font-medium text-gray-700 dark:text-gray-200 hover:underline">
                                    {{ $order->customer_email }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Metrics -->
                    @if(isset($customerMetrics) && $customerMetrics)
                        <span class="block w-full border-b dark:border-gray-800"></span>
                        <div class="pt-3 pb-2">
                            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;">
                                <div class="p-2.5 bg-gray-50 dark:bg-gray-800 rounded-xl text-center">
                                    <p class="text-lg font-bold text-gray-800 dark:text-white">{{ $customerMetrics['order_count'] }}</p>
                                    <p class="text-[10px] uppercase tracking-wider text-gray-400">Заказов</p>
                                </div>
                                <div class="p-2.5 bg-gray-50 dark:bg-gray-800 rounded-xl text-center">
                                    <p class="text-lg font-bold text-gray-800 dark:text-white">{{ $customerMetrics['total_spent'] }}</p>
                                    <p class="text-[10px] uppercase tracking-wider text-gray-400">Всего ₽</p>
                                </div>
                                <div class="p-2.5 bg-gray-50 dark:bg-gray-800 rounded-xl text-center">
                                    <p class="text-lg font-bold text-gray-800 dark:text-white">{{ $customerMetrics['average_check'] }}</p>
                                    <p class="text-[10px] uppercase tracking-wider text-gray-400">Средний ₽</p>
                                </div>
                            </div>

                            <!-- Last Order -->
                            @if($customerMetrics['last_order'])
                                @php
                                    $lastOrderBadge = '';
                                    $lastOrderBadgeColor = '';
                                    try {
                                        $lastDate = \Carbon\Carbon::parse($customerMetrics['last_order']);
                                        $diffDays = (int) $lastDate->startOfDay()->diffInDays(now()->startOfDay());
                                        if ($diffDays === 0) {
                                            $lastOrderBadge = 'сегодня';
                                            $lastOrderBadgeColor = 'background:#d4edda;color:#155724;';
                                        } elseif ($diffDays === 1) {
                                            $lastOrderBadge = 'вчера';
                                            $lastOrderBadgeColor = 'background:#cce5ff;color:#004085;';
                                        } else {
                                            $lastOrderBadge = $diffDays . ' дн. назад';
                                            $lastOrderBadgeColor = 'background:#fff3cd;color:#856404;';
                                        }
                                    } catch(\Exception $e) {}
                                @endphp
                                <div class="flex items-center justify-between mt-3 p-3 rounded-xl" style="background:#fef9e7;">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" style="color:#c4a635;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-sm text-gray-700 dark:text-gray-200">Последний заказ: {{ $customerMetrics['last_order'] }}</span>
                                    </div>
                                    @if($lastOrderBadge)
                                        <span class="px-3 py-1 text-xs font-bold uppercase rounded-full" style="{{ $lastOrderBadgeColor }}">{{ $lastOrderBadge }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Shipping Address -->
                    @if ($order->shipping_address)
                        <span class="block w-full border-b dark:border-gray-800"></span>

                        <div class="flex items-center justify-between">
                            <p class="py-4 text-base font-semibold text-gray-600 dark:text-gray-300">
                                @lang('admin::app.sales.orders.view.shipping-address')
                            </p>
                        </div>

                        @include ('admin::sales.address', ['address' => $order->shipping_address])
                    @endif
                </x-slot>
            </x-admin::accordion>

            <!-- Order Labels -->
            @if ($order->order_labels && count($order->order_labels) > 0)
                <x-admin::accordion>
                    <x-slot:header>
                        <div class="flex items-center gap-2.5 p-2.5">
                            <div class="flex items-center justify-center w-7 h-7 rounded-lg" style="background:#fef3c7;">
                                <svg class="w-3.5 h-3.5" style="color:#b45309;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <p class="text-base font-bold text-gray-700 dark:text-gray-200">
                                @lang('admin::app.sales.orders.view.order-labels')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($order->order_labels as $label)
                                <span class="inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-full" style="background:#e0e7ff; color:#4338ca;">
                                    {{ $label }}
                                </span>
                            @endforeach
                        </div>
                    </x-slot>
                </x-admin::accordion>
            @endif

            <!-- Order Information -->
            <x-admin::accordion>
                <x-slot:header>
                    <div class="flex items-center gap-2.5 p-2.5">
                        <div class="flex items-center justify-center w-7 h-7 rounded-lg" style="background:#e0e7ff;">
                            <svg class="w-3.5 h-3.5" style="color:#4338ca;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-base font-bold text-gray-700 dark:text-gray-200">
                            @lang('admin::app.sales.orders.view.order-information')
                        </p>
                    </div>
                </x-slot>

                <x-slot:content>
                    <div class="flex w-full flex-col" style="gap: 5px;">
                        <!-- Order Date Row -->
                        <div class="flex justify-between items-center p-3 rounded-xl" style="background:#fafafa;">
                            <p class="text-sm text-gray-500">
                                @lang('admin::app.sales.orders.view.order-date')
                            </p>
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-200">
                                {{ core()->formatDate($order->created_at) }}
                            </p>
                        </div>

                        <!-- Order Status Row (SPA) -->
                        <div class="p-3 rounded-xl" style="background:#fafafa;">
                            <p class="text-sm text-gray-500 mb-2">
                                @lang('admin::app.sales.orders.view.order-status')
                            </p>
                            <v-order-status-changer
                                order-id="{{ $order->id }}"
                                initial-status="{{ $order->status }}"
                                update-url="{{ route('admin.sales.orders.update_status', $order->id) }}"
                            ></v-order-status-changer>
                        </div>

                        <!-- Order Channel Row -->
                        <div class="flex justify-between items-center p-3 rounded-xl" style="background:#fafafa;">
                            <p class="text-sm text-gray-500">
                                @lang('admin::app.sales.orders.view.channel')
                            </p>
                            <p class="text-sm font-bold text-gray-700 dark:text-gray-200">
                                {{ $order->channel_name }}
                            </p>
                        </div>

                        <!-- Order Rating Row -->
                        <div class="flex justify-between items-center p-3 rounded-xl" style="background:#fafafa;">
                            <p class="text-sm text-gray-500">
                                @lang('admin::app.sales.orders.view.rating')
                            </p>
                            <div class="flex items-center">
                                @if($order->rating === true)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full" style="background:#d1fae5; color:#047857;">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                                        @lang('admin::app.sales.orders.view.rating-like')
                                    </span>
                                @elseif($order->rating === false)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full" style="background:#ffe4e6; color:#be123c;">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/></svg>
                                        @lang('admin::app.sales.orders.view.rating-dislike')
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">
                                        @lang('admin::app.sales.orders.view.rating-not-rated')
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Order Table Number Row -->
                        <div class="p-3 rounded-xl" style="background:#fafafa;">
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-gray-500">
                                    @lang('admin::app.sales.orders.view.table-number')
                                </p>
                                <div class="flex items-center gap-2">
                                    @if($order->table_number)
                                        <span class="px-3 py-1 text-sm font-bold rounded-lg" style="background:#e0e7ff; color:#4338ca;">
                                            {{ $order->table_number }}
                                        </span>
                                        <x-admin::form action="{{ route('admin.sales.orders.unbind_table', $order->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                                style="background:#ffe4e6;"
                                                title="Отвязать стол"
                                            >
                                                <svg class="w-3.5 h-3.5" style="color:#be123c;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </x-admin::form>
                                    @else
                                        <span class="text-xs text-gray-400">
                                            @lang('admin::app.sales.orders.view.table-not-set')
                                        </span>
                                        <x-admin::form action="{{ route('admin.sales.orders.bind_table', $order->id) }}" method="POST" class="flex items-center gap-2">
                                            @csrf
                                            <x-admin::form.control-group.control
                                                type="number"
                                                name="table_number"
                                                :value="null"
                                                rules="required|integer|min:1"
                                                min="1"
                                                class="w-16 text-center"
                                                placeholder="№"
                                            />
                                            <button
                                                type="submit"
                                                class="flex items-center justify-center w-7 h-7 rounded-lg transition-colors"
                                                style="background:#d1fae5;"
                                                title="Привязать стол"
                                            >
                                                <svg class="w-3.5 h-3.5" style="color:#047857;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </x-admin::form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </x-slot>
            </x-admin::accordion>

            <!-- Payment and Shipping Information -->
            <x-admin::accordion>
                <x-slot:header>
                    <div class="flex items-center gap-2.5 p-2.5">
                        <div class="flex items-center justify-center w-7 h-7 rounded-lg" style="background:#d1fae5;">
                            <svg class="w-3.5 h-3.5" style="color:#047857;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <p class="text-base font-bold text-gray-700 dark:text-gray-200">
                            @lang('admin::app.sales.orders.view.payment-and-shipping')
                        </p>
                    </div>
                </x-slot>

                <x-slot:content>
                    <div>
                        @php
                            $isPaid = $order->base_grand_total_invoiced >= $order->base_grand_total;
                            $paymentLabel = core()->getConfigData('sales.payment_methods.' . $order->payment->method . '.title') ?? $order->payment->method;
                        @endphp
                        <div class="flex items-center gap-2 mb-3">
                            @if($isPaid)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full" style="background: #d1fae5; color: #047857;">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Оплачено
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full" style="background: #fef3c7; color: #b45309;">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Не оплачено
                                </span>
                            @endif
                        </div>

                        <v-order-payment-changer
                            order-id="{{ $order->id }}"
                            current-method="{{ $order->payment->method }}"
                            current-label="{{ $paymentLabel }}"
                            methods='@json($paymentMethods ?? [])'
                        ></v-order-payment-changer>

                        @php $additionalDetails = \Webkul\Payment\Payment::getAdditionalDetails($order->payment->method); @endphp

                        @if (! empty($additionalDetails))
                            <p class="pt-4 font-semibold text-gray-800 dark:text-white">
                                {{ $additionalDetails['title'] }}
                            </p>
                            <p class="text-gray-600 dark:text-gray-300">
                                {{ $additionalDetails['value'] }}
                            </p>
                        @endif
                    </div>

                    @if ($order->shipping_address)
                        <span class="mt-4 block w-full border-b dark:border-gray-800"></span>

                        <div class="pt-4">
                            <p class="font-semibold text-gray-800 dark:text-white">
                                Доставка курьером
                            </p>
                            <p class="text-gray-600 dark:text-gray-300">
                                @lang('admin::app.sales.orders.view.shipping-method')
                            </p>
                            <p class="pt-4 font-semibold text-gray-800 dark:text-white">
                                {{ core()->formatBasePrice($order->base_shipping_amount) }}
                            </p>
                            <p class="text-gray-600 dark:text-gray-300">
                                @lang('admin::app.sales.orders.view.shipping-price')
                            </p>
                        </div>
                    @endif
                </x-slot>
            </x-admin::accordion>

            <!-- Refund Information -->
            <x-admin::accordion>
                <x-slot:header>
                    <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                        @lang('admin::app.sales.orders.view.refund')
                    </p>
                </x-slot>

                <x-slot:content>
                    @forelse ($order->refunds as $refund)
                        <div class="grid gap-y-2.5">
                            <div>
                                <p class="font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.sales.orders.view.refund-id', ['refund' => $refund->id])
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    {{ core()->formatDate($refund->created_at, 'd M, Y H:i:s a') }}
                                </p>
                                <p class="mt-4 font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.sales.orders.view.name')
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    {{ $refund->order->customer_full_name }}
                                </p>
                                <p class="mt-4 font-semibold text-gray-800 dark:text-white">
                                    @lang('admin::app.sales.orders.view.status')
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.sales.orders.view.refunded')
                                    <span class="font-semibold text-gray-800 dark:text-white">
                                        {{ core()->formatBasePrice($refund->base_grand_total) }}
                                    </span>
                                </p>
                            </div>
                            <div class="flex gap-2.5">
                                <a
                                    href="{{ route('admin.sales.refunds.view', $refund->id) }}"
                                    target="_top"
                                    class="text-sm text-blue-600 transition-all hover:underline"
                                >
                                    @lang('admin::app.sales.orders.view.view')
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-600 dark:text-gray-300">
                            @lang('admin::app.sales.orders.view.no-refund-found')
                        </p>
                    @endforelse
                </x-slot>
            </x-admin::accordion>

        </div>
    </div>

    @pushOnce('scripts')
        <!-- Step Progress Component -->
        <script type="text/x-template" id="v-order-step-progress-template">
            <div style="padding: 28px 24px 24px;">
                <div style="position: relative; display: flex; align-items: flex-start; justify-content: space-between;">
                    <!-- Connecting line (background) -->
                    <div style="position: absolute; top: 22px; left: 44px; right: 44px; height: 3px; background: #e5e7eb; border-radius: 2px; z-index: 0;"></div>
                    <!-- Connecting line (filled) -->
                    <div :style="{
                        position: 'absolute',
                        top: '22px',
                        left: '44px',
                        height: '3px',
                        background: 'linear-gradient(90deg, #7c3aed, #6d28d9)',
                        borderRadius: '2px',
                        zIndex: 1,
                        transition: 'width 0.5s ease',
                        width: progressWidth,
                    }"></div>

                    <!-- Steps -->
                    <div
                        v-for="(step, idx) in steps"
                        :key="step.key"
                        style="display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2; cursor: pointer; flex: 1;"
                        @click="goToStep(step, idx)"
                    >
                        <div :style="{
                            width: '44px',
                            height: '44px',
                            borderRadius: '50%',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            transition: 'all 0.3s ease',
                            boxShadow: stepIndex(step) <= activeIdx ? '0 4px 15px rgba(124,58,237,0.3)' : '0 2px 8px rgba(0,0,0,0.06)',
                            background: stepIndex(step) <= activeIdx
                                ? 'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)'
                                : (stepIndex(step) === activeIdx + 1 ? '#f5f3ff' : '#f3f4f6'),
                            border: stepIndex(step) === activeIdx + 1 ? '2px solid #c4b5fd' : 'none',
                        }">
                            <svg v-if="stepIndex(step) < activeIdx" style="width: 20px; height: 20px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <svg v-else :style="{
                                width: '20px',
                                height: '20px',
                                color: stepIndex(step) <= activeIdx ? 'white' : (stepIndex(step) === activeIdx + 1 ? '#7c3aed' : '#9ca3af'),
                            }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="step.icon"/>
                            </svg>

                            <svg v-if="isSaving && savingIdx === idx" style="position: absolute; width: 44px; height: 44px; animation: spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                                <circle style="opacity: 0.2;" cx="12" cy="12" r="10" stroke="#7c3aed" stroke-width="2"></circle>
                                <path style="opacity: 0.8;" fill="#7c3aed" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </div>

                        <p :style="{
                            marginTop: '10px',
                            fontSize: '12px',
                            fontWeight: stepIndex(step) === activeIdx ? '700' : '600',
                            color: stepIndex(step) <= activeIdx ? '#4c1d95' : (stepIndex(step) === activeIdx + 1 ? '#6d28d9' : '#9ca3af'),
                            transition: 'color 0.3s ease',
                            textAlign: 'center',
                            letterSpacing: '0.01em',
                        }">
                            @{{ step.label }}
                        </p>
                    </div>
                </div>
            </div>
        </script>

        <!-- SPA Status Changer Component -->
        <script type="text/x-template" id="v-order-status-changer-template">
            <div class="flex items-center gap-2 w-full">
                <select
                    v-model="currentStatus"
                    @change="updateStatus"
                    :disabled="isSaving"
                    class="flex-1 px-3 py-1.5 text-sm font-medium border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 disabled:opacity-50 min-w-[200px]"
                >
                    <option v-for="s in allStatuses" :key="s.code" :value="s.code">@{{ s.name }}</option>
                </select>
                <svg v-if="isSaving" class="w-5 h-5 animate-spin text-violet-500 flex-shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-if="showSuccess" class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </script>

        <!-- SPA Payment Changer Component -->
        <script type="text/x-template" id="v-order-payment-changer-template">
            <div class="relative">
                <div class="flex items-center gap-2">
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-white">@{{ currentMethodLabel }}</p>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">Способ оплаты</p>
                    </div>
                    <button
                        @click="isEditing = !isEditing"
                        class="flex items-center justify-center w-7 h-7 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        title="Изменить способ оплаты"
                    >
                        <svg class="w-4 h-4 text-gray-400 hover:text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                </div>
                <div v-if="isEditing" class="mt-2">
                    <select
                        v-model="selectedMethod"
                        @change="updatePayment"
                        :disabled="isSaving"
                        class="w-full px-3 py-1.5 text-sm border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-violet-500 disabled:opacity-50"
                    >
                        <option v-for="method in availableMethods" :key="method.code" :value="method.code">@{{ method.title }}</option>
                    </select>
                    <svg v-if="isSaving" class="w-4 h-4 animate-spin text-violet-500 mt-1" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </script>

        <!-- SPA Item Editor Component -->
        <script type="text/x-template" id="v-order-items-editor-template">
            <div>
                <div class="flex justify-between items-center p-4" style="border-bottom: 1px solid #f0f0f0;">
                    <div class="flex items-center gap-2.5">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg" style="background:#f1f0ff;">
                            <svg class="w-4 h-4" style="color:#7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <p class="text-base font-bold text-gray-800 dark:text-white">Товары</p>
                        <span class="px-2 py-0.5 text-xs font-bold rounded-full" style="background:#f1f0ff; color:#7c3aed;">@{{ items.length }}</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <p class="text-lg font-black" style="color:#7c3aed; letter-spacing: -0.02em;">@{{ formattedGrandTotal }}</p>
                        <button v-if="!isEditing" @click="startEditing" class="flex items-center justify-center w-8 h-8 rounded-lg transition-all duration-200" style="background:#f5f3ff;" title="Редактировать кол-во">
                            <svg class="w-4 h-4" style="color:#7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                        <template v-if="isEditing">
                            <button @click="saveChanges" :disabled="isSaving" class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white rounded-xl transition-all duration-200 disabled:opacity-50" style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); box-shadow: 0 2px 8px rgba(124,58,237,0.3);">
                                <svg v-if="isSaving" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Сохранить
                            </button>
                            <button @click="cancelEditing" class="px-4 py-2 text-xs font-bold text-gray-500 rounded-xl transition-colors" style="background: #f3f4f6;">Отмена</button>
                        </template>
                    </div>
                </div>

                <div>
                    <div v-for="item in items" :key="item.id" class="flex justify-between gap-3 px-4 py-4 transition-all duration-200" style="border-bottom: 1px solid #f5f5f5;" @mouseenter="$event.currentTarget.style.background='#fafafa'" @mouseleave="$event.currentTarget.style.background='transparent'">
                        <div class="flex gap-3">
                            <div v-if="item.image_url" class="flex-shrink-0 w-14 h-14 rounded-xl overflow-hidden" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                <img :src="item.image_url" class="w-full h-full object-cover" />
                            </div>
                            <div v-else class="flex-shrink-0 w-14 h-14 rounded-xl flex items-center justify-center" style="background:#f8f7ff; border: 1px dashed #e0dff5;">
                                <svg class="w-6 h-6" style="color:#c4b5fd;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex flex-col gap-1">
                                <p class="text-sm font-bold text-gray-800 dark:text-white">@{{ item.name }}</p>
                                <div class="flex flex-col gap-1">
                                    <p v-if="!isEditing" class="text-xs text-gray-500">@{{ item.formatted_price }} × @{{ item.qty_ordered }} шт.</p>
                                    <div v-else class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500">@{{ item.formatted_price }} ×</span>
                                        <div class="flex items-center rounded-xl overflow-hidden" style="border: 2px solid #ede9fe;">
                                            <button @click="decrementQty(item)" class="px-2.5 py-1 text-sm font-bold transition-colors" style="color:#7c3aed; background:#f5f3ff;" @mouseenter="$event.currentTarget.style.background='#ede9fe'" @mouseleave="$event.currentTarget.style.background='#f5f3ff'">−</button>
                                            <input type="number" v-model.number="item.qty_ordered" min="0" class="w-10 text-center text-sm font-bold border-none bg-transparent text-gray-800 dark:text-white focus:ring-0 p-1" />
                                            <button @click="incrementQty(item)" class="px-2.5 py-1 text-sm font-bold transition-colors" style="color:#7c3aed; background:#f5f3ff;" @mouseenter="$event.currentTarget.style.background='#ede9fe'" @mouseleave="$event.currentTarget.style.background='#f5f3ff'">+</button>
                                        </div>
                                        <span class="text-xs text-gray-500">шт.</span>
                                    </div>
                                    <p class="text-[11px] text-gray-400">SKU: @{{ item.sku }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <p class="text-sm font-bold text-gray-800 dark:text-white whitespace-nowrap">
                                @{{ formatPrice(Number(item.base_total) + Number(item.base_tax_amount) - Number(item.base_discount_amount)) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            // Step Progress Component
            app.component('v-order-step-progress', {
                template: '#v-order-step-progress-template',
                props: ['orderId', 'initialStatus', 'updateUrl'],
                data() {
                    return {
                        currentStatus: this.initialStatus,
                        isSaving: false,
                        savingIdx: -1,
                        steps: (() => {
                            const allSt = @json($allStatuses ?? []);
                            const negative = ['canceled', 'on_hold', 'refunded', 'closed', 'fraud', 'failed'];
                            const defaultIcon = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
                            return allSt
                                .filter(s => !negative.includes(s.code))
                                .map(s => ({
                                    key: s.code,
                                    statuses: [s.code],
                                    label: s.name,
                                    color: s.color || '#6b7280',
                                    icon: defaultIcon,
                                }));
                        })(),
                    };
                },
                computed: {
                    activeIdx() {
                        for (let i = this.steps.length - 1; i >= 0; i--) {
                            if (this.steps[i].statuses.includes(this.currentStatus)) return i;
                        }
                        if (['canceled', 'closed'].includes(this.currentStatus)) return -1;
                        return 0;
                    },
                    progressWidth() {
                        if (this.activeIdx <= 0) return '0%';
                        const pct = (this.activeIdx / (this.steps.length - 1)) * 100;
                        return `calc(${pct}% - 0px)`;
                    },
                },
                methods: {
                    stepIndex(step) {
                        return this.steps.indexOf(step);
                    },
                    goToStep(step, idx) {
                        if (this.isSaving) return;
                        if (step.statuses.includes(this.currentStatus)) return;

                        const newStatus = step.key;
                        this.isSaving = true;
                        this.savingIdx = idx;

                        this.$axios.post(this.updateUrl, {
                            status: newStatus,
                        }, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then((response) => {
                            this.isSaving = false;
                            this.savingIdx = -1;
                            if (response.data.success) {
                                this.currentStatus = newStatus;
                                this.$emitter.emit('order-status-changed', newStatus);
                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.message
                                });
                                const badge = document.querySelector('[data-order-status-badge]');
                                if (badge) {
                                    const colorMap = @json($statusColorMap ?? []);
                                    const c = colorMap[newStatus] || '#6b7280';
                                    badge.style.cssText = 'background:' + c + '1a;color:' + c + ';';
                                    const stepData = this.steps.find(s => s.key === newStatus);
                                    if (stepData) badge.textContent = stepData.label;
                                }
                                // Notify parent window about status change
                                window.parent.postMessage({type: 'order-status-updated', orderId: this.orderId}, '*');
                            } else {
                                this.$emitter.emit('add-flash', { type: 'warning', message: response.data.message });
                            }
                        })
                        .catch((error) => {
                            this.isSaving = false;
                            this.savingIdx = -1;
                            this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Ошибка при обновлении статуса' });
                        });
                    },
                },
                mounted() {
                    this.$emitter.on('order-status-synced', (status) => {
                        this.currentStatus = status;
                    });
                },
            });

            // SPA Status Changer
            app.component('v-order-status-changer', {
                template: '#v-order-status-changer-template',
                props: ['orderId', 'initialStatus', 'updateUrl'],
                data() {
                    return {
                        currentStatus: this.initialStatus,
                        isSaving: false,
                        showSuccess: false,
                        allStatuses: @json($allStatuses ?? []),
                    };
                },
                methods: {
                    updateStatus() {
                        if (this.currentStatus === this.initialStatus) return;
                        this.isSaving = true;
                        this.showSuccess = false;

                        this.$axios.post(this.updateUrl, {
                            status: this.currentStatus,
                        }, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then((response) => {
                            this.isSaving = false;
                            if (response.data.success) {
                                this.showSuccess = true;
                                setTimeout(() => { this.showSuccess = false; }, 2000);
                                this.$emitter.emit('order-status-synced', this.currentStatus);
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                window.parent.postMessage({type: 'order-status-updated', orderId: this.orderId}, '*');
                            } else {
                                this.$emitter.emit('add-flash', { type: 'warning', message: response.data.message });
                            }
                        })
                        .catch((error) => {
                            this.isSaving = false;
                            this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Ошибка при обновлении статуса' });
                        });
                    },
                },
                mounted() {
                    this.$emitter.on('order-status-changed', (status) => {
                        this.currentStatus = status;
                    });
                },
            });

            // SPA Payment Changer
            app.component('v-order-payment-changer', {
                template: '#v-order-payment-changer-template',
                props: ['orderId', 'currentMethod', 'currentLabel', 'methods'],
                data() {
                    return {
                        selectedMethod: this.currentMethod,
                        currentMethodLabel: this.currentLabel,
                        availableMethods: JSON.parse(this.methods || '[]'),
                        isEditing: false,
                        isSaving: false,
                    };
                },
                methods: {
                    updatePayment() {
                        if (this.selectedMethod === this.currentMethod) return;
                        this.isSaving = true;

                        this.$axios.post(`{{ url('admin/sales/orders') }}/${this.orderId}/update-payment`, {
                            method: this.selectedMethod,
                        }, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then((response) => {
                            this.isSaving = false;
                            if (response.data.success) {
                                this.currentMethodLabel = response.data.method_label;
                                this.isEditing = false;
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                            }
                        })
                        .catch((error) => {
                            this.isSaving = false;
                            this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Ошибка при обновлении способа оплаты' });
                        });
                    },
                },
            });

            // SPA Items Editor
            app.component('v-order-items-editor', {
                template: '#v-order-items-editor-template',
                props: ['orderId', 'initialItems', 'grandTotal'],
                data() {
                    return {
                        items: JSON.parse(this.initialItems || '[]'),
                        originalItems: [],
                        formattedGrandTotal: this.grandTotal,
                        isEditing: false,
                        isSaving: false,
                    };
                },
                methods: {
                    formatPrice(amount) {
                        const num = parseFloat(amount);
                        if (isNaN(num)) return '0,00 ₽';
                        return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB' }).format(num);
                    },
                    startEditing() {
                        this.originalItems = JSON.parse(JSON.stringify(this.items));
                        this.isEditing = true;
                    },
                    cancelEditing() {
                        this.items = JSON.parse(JSON.stringify(this.originalItems));
                        this.isEditing = false;
                    },
                    incrementQty(item) {
                        item.qty_ordered++;
                    },
                    decrementQty(item) {
                        if (item.qty_ordered > 0) item.qty_ordered--;
                    },
                    saveChanges() {
                        this.isSaving = true;

                        const updates = this.items.map(item => ({
                            id: item.id,
                            qty: item.qty_ordered,
                        }));

                        this.$axios.post(`{{ url('admin/sales/orders') }}/${this.orderId}/update-items`, {
                            items: updates,
                        }, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then((response) => {
                            this.isSaving = false;
                            if (response.data.success) {
                                this.isEditing = false;
                                if (response.data.items) {
                                    this.items = response.data.items;
                                }
                                if (response.data.grand_total) {
                                    this.formattedGrandTotal = response.data.grand_total;
                                }
                                if (response.data.totals) {
                                    Object.keys(response.data.totals).forEach(key => {
                                        const el = document.querySelector(`[data-total="${key}"]`);
                                        if (el) el.textContent = response.data.totals[key];
                                    });
                                }
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                window.parent.postMessage({type: 'order-items-updated', orderId: this.orderId}, '*');
                            }
                        })
                        .catch((error) => {
                            this.isSaving = false;
                            this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Ошибка при обновлении товаров' });
                        });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
