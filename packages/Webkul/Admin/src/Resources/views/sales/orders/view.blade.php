<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.sales.orders.view.title', ['order_id' => $order->increment_id])
    </x-slot>

    <!-- Header -->
    <div class="grid">
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            {!! view_render_event('bagisto.admin.sales.order.title.before', ['order' => $order]) !!}

            <div class="flex items-center gap-2.5">
                <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                    @lang('admin::app.sales.orders.view.title', ['order_id' => $order->increment_id])
                </p>

                <!-- Order Status -->
                <span class="label-{{ $order->status }} text-sm mx-1.5">
                    @lang("admin::app.sales.orders.view.$order->status")
                </span>
            </div>

            {!! view_render_event('bagisto.admin.sales.order.title.after', ['order' => $order]) !!}

            <!-- Back Button -->
            <a
                href="{{ route('admin.sales.orders.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                @lang('admin::app.account.edit.back-btn')
            </a>
        </div>
    </div>

    <div class="mt-5 flex-wrap items-center justify-between gap-x-1 gap-y-2">
        <div class="flex gap-1.5" style="display: none;">
            {!! view_render_event('bagisto.admin.sales.order.page_action.before', ['order' => $order]) !!}

            @if (
                $order->canReorder()
                && bouncer()->hasPermission('sales.orders.create')
                && core()->getConfigData('sales.order_settings.reorder.admin')
            )
                <a
                    href="{{ route('admin.sales.orders.reorder', $order->id) }}"
                    class="transparent-button px-1 py-1.5 hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    <span class="icon-cart text-2xl"></span>

                    @lang('admin::app.sales.orders.view.reorder')
                </a>
            @endif

            @if (
                $order->canInvoice()
                && bouncer()->hasPermission('sales.invoices.create')
                && $order->payment->method !== 'paypal_standard'
            )
                @include('admin::sales.invoices.create')
            @endif

            @if (
                $order->canShip()
                && bouncer()->hasPermission('sales.shipments.create')
            )
                @include('admin::sales.shipments.create')
            @endif

            @if (
                $order->canRefund()
                && bouncer()->hasPermission('sales.refunds.create')
            )
                @include('admin::sales.refunds.create')
            @endif

            @if (
                $order->canCancel()
                && bouncer()->hasPermission('sales.orders.cancel')
            )
                <form
                    method="POST"
                    ref="cancelOrderForm"
                    action="{{ route('admin.sales.orders.cancel', $order->id) }}"
                >
                    @csrf
                </form>

                <div
                    class="transparent-button px-1 py-1.5 hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                    @click="$emitter.emit('open-confirm-modal', {
                        message: '@lang('admin::app.sales.orders.view.cancel-msg')',
                        agree: () => {
                            this.$refs['cancelOrderForm'].submit()
                        }
                    })"
                >
                    <span
                        class="icon-cancel text-2xl"
                        role="presentation"
                        tabindex="0"
                    >
                    </span>

                    <a href="javascript:void(0);">
                        @lang('admin::app.sales.orders.view.cancel')
                    </a>
                </div>
            @endif

            {!! view_render_event('bagisto.admin.sales.order.page_action.after', ['order' => $order]) !!}
        </div>

        <!-- Order details -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Component -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                {!! view_render_event('bagisto.admin.sales.order.left_component.before', ['order' => $order]) !!}

                <div class="box-shadow rounded bg-white dark:bg-gray-900">
                    <div class="flex justify-between p-4">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('Order Items') ({{ count($order->items) }})
                        </p>

                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.sales.orders.view.grand-total', ['grand_total' => core()->formatBasePrice($order->base_grand_total)])
                        </p>
                    </div>

                    <!-- Order items -->
                    <div class="grid">
                        @foreach ($order->items as $item)
                            {!! view_render_event('bagisto.admin.sales.order.list.before', ['order' => $order]) !!}

                            <div class="flex justify-between gap-2.5 border-b border-slate-300 px-4 py-6 dark:border-gray-800">
                                <div class="flex gap-2.5">
                                    @if($item?->product?->base_image_url)
                                        <img
                                            class="relative h-[60px] max-h-[60px] w-full max-w-[60px] rounded"
                                            src="{{ $item?->product->base_image_url }}"
                                        >
                                    @else
                                        <div class="relative h-[60px] max-h-[60px] w-full max-w-[60px] rounded border border-dashed border-gray-300 dark:border-gray-800 dark:mix-blend-exclusion dark:invert">
                                            <img src="{{ bagisto_asset('images/product-placeholders/front.svg') }}">

                                            <p class="absolute bottom-1.5 w-full text-center text-[6px] font-semibold text-gray-400">
                                                @lang('admin::app.sales.invoices.view.product-image')
                                            </p>
                                        </div>
                                    @endif

                                    <div class="grid place-content-start gap-1.5">
                                        <p class="break-all text-base font-semibold text-gray-800 dark:text-white">
                                            {{ $item->name }}
                                        </p>

                                        <div class="flex flex-col place-items-start gap-1.5">
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.amount-per-unit', [
                                                    'amount' => core()->formatBasePrice($item->base_price),
                                                    'qty'    => $item->qty_ordered,
                                                ])
                                            </p>
                                            <!--
                                            @if (isset($item->additional['attributes']))
                                                @foreach ($item->additional['attributes'] as $attribute)
                                                    <p class="text-gray-600 dark:text-gray-300">
                                                        @if (
                                                            ! isset($attribute['attribute_type'])
                                                            || $attribute['attribute_type'] !== 'file'
                                                        )
                                                        {{ $attribute['attribute_name'] }} : {{ $attribute['option_label'] }}
                                                    @else
                                                        {{ $attribute['attribute_name'] }} :

                                                            <a
                                                                href="{{ Storage::url($attribute['option_label']) }}"
                                                                class="text-blue-600 hover:underline"
                                                                download="{{ File::basename($attribute['option_label']) }}"
                                                            >
                                                                {{ File::basename($attribute['option_label']) }}
                                                        </a>
@endif
                                                    </p>
                                                @endforeach
                                            @endif
                                            -->
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.sku', ['sku' => $item->sku])
                                            </p>
                                            <!--
                                            <p class="text-gray-600 dark:text-gray-300">
                                                {{ $item->qty_ordered ? trans('admin::app.sales.orders.view.item-ordered', ['qty_ordered' => $item->qty_ordered]) : '' }}

                                            {{ $item->qty_invoiced ? trans('admin::app.sales.orders.view.item-invoice', ['qty_invoiced' => $item->qty_invoiced]) : '' }}

                                            {{ $item->qty_shipped ? trans('admin::app.sales.orders.view.item-shipped', ['qty_shipped' => $item->qty_shipped]) : '' }}

                                            {{ $item->qty_refunded ? trans('admin::app.sales.orders.view.item-refunded', ['qty_refunded' => $item->qty_refunded]) : '' }}

                                            {{ $item->qty_canceled ? trans('admin::app.sales.orders.view.item-canceled', ['qty_canceled' => $item->qty_canceled]) : '' }}
                                            </p>
-->
                                        </div>
                                    </div>
                                </div>

                                <div class="grid place-content-start gap-1">
                                    <div class="">
                                        <p class="flex items-center justify-end gap-x-1 text-base font-semibold text-gray-800 dark:text-white">
                                            {{ core()->formatBasePrice($item->base_total + $item->base_tax_amount - $item->base_discount_amount) }}
                                        </p>
                                    </div>

                                    <div class="flex flex-col place-items-start items-end gap-1.5">
                                        @if (core()->getConfigData('sales.taxes.sales.display_prices') == 'including_tax')
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.price', ['price' => core()->formatBasePrice($item->base_price_incl_tax)])
                                            </p>
                                        @elseif (core()->getConfigData('sales.taxes.sales.display_prices') == 'both')
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.price-excl-tax', ['price' => core()->formatBasePrice($item->base_price)])
                                            </p>

                                            <p class="text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.price-incl-tax', ['price' => core()->formatBasePrice($item->base_price_incl_tax)])
                                            </p>
                                        @else
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.price', ['price' => core()->formatBasePrice($item->base_price)])
                                            </p>
                                        @endif

                                        <p class="text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.sales.orders.view.tax', [
                                                'percent' => number_format($item->tax_percent, 2) . '%',
                                                'tax'     => core()->formatBasePrice($item->base_tax_amount)
                                            ])
                                        </p>

                                        @if ($order->base_discount_amount > 0)
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.discount', ['discount' => core()->formatBasePrice($item->base_discount_amount)])
                                            </p>
                                        @endif

                                        @if (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'including_tax')
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.sub-total', ['sub_total' => core()->formatBasePrice($item->base_total_incl_tax)])
                                            </p>
                                        @elseif (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'both')
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.sub-total-excl-tax', ['sub_total' => core()->formatBasePrice($item->base_total)])
                                            </p>

                                            <p class="text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.sub-total-incl-tax', ['sub_total' => core()->formatBasePrice($item->base_total_incl_tax)])
                                            </p>
                                        @else
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.sub-total', ['sub_total' => core()->formatBasePrice($item->base_total)])
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {!! view_render_event('bagisto.admin.sales.order.list.after', ['order' => $order]) !!}

                        @endforeach
                    </div>

                    <div class="mt-4 flex flex-auto justify-end p-4">
                        <div class="grid max-w-max gap-2 text-sm">

                            {!! view_render_event('bagisto.admin.sales.order.view.subtotal.before') !!}

                            <!-- Sub Total -->
                            @if (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'including_tax')
                                <div class="flex w-full justify-between gap-x-5">
                                    <p class="font-semibold !leading-5 text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.sales.orders.view.summary-sub-total-incl-tax')
                                    </p>

                                    <p class="font-semibold !leading-5 text-gray-600 dark:text-gray-300">
                                        {{ core()->formatBasePrice($order->base_sub_total_incl_tax) }}
                                    </p>
                                </div>
                            @elseif (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'both')
                                <div class="flex w-full justify-between gap-x-5">
                                    <p class="font-semibold !leading-5 text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.sales.orders.view.summary-sub-total-excl-tax')
                                    </p>

                                    <p class="font-semibold !leading-5 text-gray-600 dark:text-gray-300">
                                        {{ core()->formatBasePrice($order->base_sub_total) }}
                                    </p>
                                </div>

                                <div class="flex w-full justify-between gap-x-5">
                                    <p class="font-semibold !leading-5 text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.sales.orders.view.summary-sub-total-incl-tax')
                                    </p>

                                    <p class="font-semibold !leading-5 text-gray-600 dark:text-gray-300">
                                        {{ core()->formatBasePrice($order->base_sub_total_incl_tax) }}
                                    </p>
                                </div>
                            @else
                                <div class="flex w-full justify-between gap-x-5">
                                    <p class="font-semibold !leading-5 text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.sales.orders.view.summary-sub-total')
                                    </p>

                                    <p class="font-semibold !leading-5 text-gray-600 dark:text-gray-300">
                                        {{ core()->formatBasePrice($order->base_sub_total) }}
                                    </p>
                                </div>
                            @endif

                            {!! view_render_event('bagisto.admin.sales.order.view.subtotal.after') !!}

                            {!! view_render_event('bagisto.admin.sales.order.view.shipping.before') !!}

                            <!-- Shipping And Handling -->
                            @if ($haveStockableItems = $order->haveStockableItems())
                                @if (core()->getConfigData('sales.taxes.sales.display_subtotal') == 'including_tax')
                                    <div class="flex w-full justify-between gap-x-5">
                                        <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.sales.orders.view.shipping-and-handling-incl-tax')
                                        </p>

                                        <p class="font-semibold !leading-5 text-gray-600 dark:text-gray-300">
                                            {{ core()->formatBasePrice($order->base_shipping_amount_incl_tax) }}
                                        </p>
                                    </div>
                                @elseif (core()->getConfigData('sales.taxes.sales.display_shipping_amount') == 'both')
                                    <div class="flex w-full justify-between gap-x-5">
                                        <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.sales.orders.view.shipping-and-handling-excl-tax')
                                        </p>

                                        <p class="font-semibold !leading-5 text-gray-600 dark:text-gray-300">
                                            {{ core()->formatBasePrice($order->base_shipping_amount) }}
                                        </p>
                                    </div>

                                    <div class="flex w-full justify-between gap-x-5">
                                        <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.sales.orders.view.shipping-and-handling-incl-tax')
                                        </p>

                                        <p class="font-semibold !leading-5 text-gray-600 dark:text-gray-300">
                                            {{ core()->formatBasePrice($order->base_shipping_amount_incl_tax) }}
                                        </p>
                                    </div>
                                @else
                                    <div class="flex w-full justify-between gap-x-5">
                                        <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.sales.orders.view.shipping-and-handling')
                                        </p>

                                        <p class="font-semibold !leading-5 text-gray-600 dark:text-gray-300">
                                            {{ core()->formatBasePrice($order->base_shipping_amount) }}
                                        </p>
                                    </div>
                                @endif
                            @endif

                            {!! view_render_event('bagisto.admin.sales.order.view.shipping.after') !!}

                            {!! view_render_event('bagisto.admin.sales.order.view.tax-amount.before') !!}

                            <!-- Tax Amount -->
                            <div class="flex w-full justify-between gap-x-5">
                                <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.sales.orders.view.summary-tax')
                                </p>

                                <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                    {{ core()->formatBasePrice($order->base_tax_amount) }}
                                </p>
                            </div>

                            {!! view_render_event('bagisto.admin.sales.order.view.tax-amount.after') !!}

                            {!! view_render_event('bagisto.admin.sales.order.view.discount.before') !!}

                            <!-- Discount -->
                            <div class="flex w-full justify-between gap-x-5">
                                <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.sales.orders.view.summary-discount')
                                </p>

                                <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                    {{ core()->formatBasePrice($order->base_discount_amount) }}
                                </p>
                            </div>

                            {!! view_render_event('bagisto.admin.sales.order.view.discount.after') !!}

                            {!! view_render_event('bagisto.admin.sales.order.view.grand-total.before') !!}

                            <!-- Grand Total -->
                            <div class="flex w-full justify-between gap-x-5">
                                <p class="text-base font-semibold !leading-5 text-gray-800 dark:text-white">
                                    @lang('admin::app.sales.orders.view.summary-grand-total')
                                </p>

                                <p class="text-base font-semibold !leading-5 text-gray-800 dark:text-white">
                                    {{ core()->formatBasePrice($order->base_grand_total) }}
                                </p>
                            </div>

                            {!! view_render_event('bagisto.admin.sales.order.view.grand-total.after') !!}

                            {!! view_render_event('bagisto.admin.sales.order.view.total-paid.before') !!}

                            <!-- Total Paid -->
                            <div class="flex w-full justify-between gap-x-5">
                                <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.sales.orders.view.total-paid')
                                </p>

                                <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                    {{ core()->formatBasePrice($order->base_grand_total_invoiced) }}
                                </p>
                            </div>

                            {!! view_render_event('bagisto.admin.sales.order.view.total-paid.after') !!}

                            {!! view_render_event('bagisto.admin.sales.order.view.total-refunded.before') !!}

                            <!-- Total Refund -->
                            <div class="flex w-full justify-between gap-x-5">
                                <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.sales.orders.view.total-refund')
                                </p>

                                <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                    {{ core()->formatBasePrice($order->base_grand_total_refunded) }}
                                </p>
                            </div>

                            {!! view_render_event('bagisto.admin.sales.order.view.total-refunded.after') !!}

                            {!! view_render_event('bagisto.admin.sales.order.view.total-due.before') !!}

                            <!-- Total Due -->
                            <div class="flex w-full justify-between gap-x-5 font-semibold">
                                <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.sales.orders.view.total-due')
                                </p>

                                @if($order->status !== 'canceled')
                                    <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                        {{ core()->formatBasePrice($order->base_total_due) }}
                                    </p>
                                @else
                                    <p class="!leading-5 text-gray-600 dark:text-gray-300">
                                        {{ core()->formatBasePrice(0.00) }}
                                    </p>
                                @endif
                            </div>

                            {!! view_render_event('bagisto.admin.sales.order.view.total-due.after') !!}

                        </div>
                    </div>
                </div>

                <!-- Customer's comment form -->
                <div class="box-shadow rounded bg-white dark:bg-gray-900">
                    <p class="p-4 pb-0 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.sales.orders.view.comments')
                    </p>

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
                                    class="secondary-button"
                                    aria-label="{{ trans('admin::app.sales.orders.view.submit-comment') }}"
                                >
                                    @lang('admin::app.sales.orders.view.submit-comment')
                                </button>
                            </div>
                        </div>
                    </x-admin::form>

                    <span class="block w-full border-b dark:border-gray-800"></span>

                    <!-- Comment List -->
                    @foreach ($order->comments()->orderBy('id', 'desc')->get() as $comment)
                        <div class="grid gap-1.5 p-4">
                            <p class="break-all text-base leading-6 text-gray-800 dark:text-white">
                                {{ $comment->comment }}
                            </p>

                            <!-- Notes List Title and Time -->
                            <p class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                @if ($comment->customer_notified)
                                    <span class="icon-done h-fit rounded-full bg-blue-100 text-2xl text-blue-600"></span>

                                    @lang('admin::app.sales.orders.view.customer-notified', ['date' => core()->formatDate($comment->created_at, 'Y-m-d H:i:s a')])
                                @else
                                    <span class="icon-cancel-1 h-fit rounded-full bg-red-100 text-2xl text-red-600"></span>

                                    @lang('admin::app.sales.orders.view.customer-not-notified', ['date' => core()->formatDate($comment->created_at, 'Y-m-d H:i:s a')])
                                @endif
                            </p>
                        </div>

                        <span class="block w-full border-b dark:border-gray-800"></span>
                    @endforeach
                </div>

                {!! view_render_event('bagisto.admin.sales.order.left_component.after', ['order' => $order]) !!}
            </div>

            <!-- Right Component -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                {!! view_render_event('bagisto.admin.sales.order.right_component.before', ['order' => $order]) !!}

                <!-- Customer and address information -->
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                            @lang('admin::app.sales.orders.view.customer')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <div class="{{ $order->billing_address ? 'pb-4' : '' }}">
                            <div class="flex flex-col gap-1.5">
                                <p class="font-semibold text-gray-800 dark:text-white">
                                    {{ $order->customer_full_name }}
                                </p>

                                {!! view_render_event('bagisto.admin.sales.order.customer_full_name.after', ['order' => $order]) !!}

                                <p class="text-gray-600 dark:text-gray-300">
                                    {{ $order->customer_email }}
                                </p>

                                {!! view_render_event('bagisto.admin.sales.order.customer_email.after', ['order' => $order]) !!}

                                <p class="text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.sales.orders.view.customer-group') : {{ $order->is_guest ? core()->getGuestCustomerGroup()?->name : ($order->customer->group->name ?? '') }}
                                </p>

                                {!! view_render_event('bagisto.admin.sales.order.customer_group.after', ['order' => $order]) !!}
                            </div>
                        </div>

                        <!-- Billing Address -->
                        {{--
                        @if ($order->billing_address)
                            <span class="block w-full border-b dark:border-gray-800"></span>

                            <div class="{{ $order->shipping_address ? 'pb-4' : '' }}">

                                <div class="flex items-center justify-between">
                                    <p class="py-4 text-base font-semibold text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.sales.orders.view.billing-address')
                                    </p>
                                </div>

                                @include ('admin::sales.address', ['address' => $order->billing_address])

                                {!! view_render_event('bagisto.admin.sales.order.billing_address.after', ['order' => $order]) !!}
                            </div>
                        @endif
                        --}}

                        <!-- Shipping Address -->
                        @if ($order->shipping_address)
                            <span class="block w-full border-b dark:border-gray-800"></span>

                            <div class="flex items-center justify-between">
                                <p class="py-4 text-base font-semibold text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.sales.orders.view.shipping-address')
                                </p>
                            </div>

                            @include ('admin::sales.address', ['address' => $order->shipping_address])

                            {!! view_render_event('bagisto.admin.sales.order.shipping_address.after', ['order' => $order]) !!}
                        @endif
                    </x-slot>
                </x-admin::accordion>

                <!-- Order Labels -->
                @if ($order->order_labels && count($order->order_labels) > 0)
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                                @lang('admin::app.sales.orders.view.order-labels')
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <div class="flex flex-col gap-2">
                                @foreach ($order->order_labels as $label)
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/20">
                                            {{ $label }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </x-slot>
                    </x-admin::accordion>
                @endif

                <!-- Order Information -->
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                            @lang('admin::app.sales.orders.view.order-information')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <div class="flex w-full flex-col gap-y-4">
                            <!-- Order Date Row -->
                            <div class="flex flex-wrap justify-between items-center min-h-[40px]">
                                <p class="text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.sales.orders.view.order-date')
                                </p>
                                <div class="flex items-center">
                                    {!! view_render_event('bagisto.admin.sales.order.created_at.before', ['order' => $order]) !!}
                                    <p class="text-gray-600 dark:text-gray-300">
                                        {{core()->formatDate($order->created_at) }}
                                    </p>
                                    {!! view_render_event('bagisto.admin.sales.order.created_at.after', ['order' => $order]) !!}
                                </div>
                            </div>

                            <!-- Order Status Row -->
                            <div class="flex flex-wrap justify-between items-center min-h-[40px]" >
                                <p class="text-gray-600 dark:text-gray-300" style="margin-bottom: 6px;width: 100%;">
                                    @lang('admin::app.sales.orders.view.order-status')
                                </p>
                                <div class="flex items-center" style="width: 100%; justify-content: space-between;">
                                    {!! view_render_event('bagisto.admin.sales.order.status_label.before', ['order' => $order]) !!}
                                    <x-admin::form action="{{ route('admin.sales.orders.update_status', $order->id) }}" method="POST" style="width: 100%;">
                                        @csrf
                                        <div class="flex items-center gap-2" style="justify-content: space-between;    width: 100%;">
                                            <x-admin::form.control-group.control
                                                type="select"
                                                name="status"
                                                :value="$order->status"
                                                rules="required"
                                                class="min-w-[200px]"
                                            >
                                                <option value="{{ \Webkul\Sales\Models\Order::STATUS_PENDING }}" {{ $order->status === \Webkul\Sales\Models\Order::STATUS_PENDING ? 'selected' : '' }}>
                                                    Оплата
                                                </option>
                                                <option value="{{ \Webkul\Sales\Models\Order::STATUS_PENDING_PAYMENT }}" {{ $order->status === \Webkul\Sales\Models\Order::STATUS_PENDING_PAYMENT ? 'selected' : '' }}>
                                                    Ожидание оплаты
                                                </option>
                                                <option value="{{ \Webkul\Sales\Models\Order::STATUS_PROCESSING }}" {{ $order->status === \Webkul\Sales\Models\Order::STATUS_PROCESSING ? 'selected' : '' }}>
                                                    Принят
                                                </option>
                                                <option value="{{ \Webkul\Sales\Models\Order::STATUS_COMPLETED }}" {{ $order->status === \Webkul\Sales\Models\Order::STATUS_COMPLETED ? 'selected' : '' }}>
                                                    Завершен
                                                </option>
                                                <option value="{{ \Webkul\Sales\Models\Order::STATUS_CANCELED }}" {{ $order->status === \Webkul\Sales\Models\Order::STATUS_CANCELED ? 'selected' : '' }}>
                                                    Отмена
                                                </option>
                                                <option value="{{ \Webkul\Sales\Models\Order::STATUS_CLOSED }}" {{ $order->status === \Webkul\Sales\Models\Order::STATUS_CLOSED ? 'selected' : '' }}>
                                                    Закрыт
                                                </option>
                                                <option value="{{ \Webkul\Sales\Models\Order::STATUS_FRAUD }}" {{ $order->status === \Webkul\Sales\Models\Order::STATUS_FRAUD ? 'selected' : '' }}>
                                                    Мошенничество
                                                </option>
                                            </x-admin::form.control-group.control>

                                            <button
                                                type="submit"
                                                class="secondary-button whitespace-nowrap"
                                            >
                                                <svg version="1.1" id="Uploaded to svgrepo.com" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                                     width="24px" height="24px" viewBox="0 0 32 32" xml:space="preserve">
                                                    <style type="text/css">
                                                        .blueprint_een{fill:#111918;}
                                                        .st0{fill:#0B1719;}
                                                    </style>
                                                    <path class="blueprint_een" d="M27,1H2C1.448,1,1,1.448,1,2v28c0,0.552,0.448,1,1,1h28c0.552,0,1-0.448,1-1V5L27,1z M8,3h16
                                                        v10H8V3z M29,29H3V3h4v10c0,0.552,0.448,1,1,1h16c0.552,0,1-0.448,1-1V3h1.172L29,5.829V29z M9,26h14c0.552,0,1-0.448,1-1v-7
                                                        c0-0.552-0.448-1-1-1H9c-0.552,0-1,0.448-1,1v7C8,25.552,8.448,26,9,26z M9,18h14v7H9V18z M18,12h5V4h-5V12z M19,5h3v6h-3V5z M10,19
                                                        h12v1H10V19z M10,21h12v1H10V21z M10,23h12v1H10V23z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </x-admin::form>
                                    {!! view_render_event('bagisto.admin.sales.order.status_label.after', ['order' => $order]) !!}
                                </div>
                            </div>

                            <!-- Order Channel Row -->
                            <div class="flex flex-wrap justify-between items-center min-h-[40px]">
                                <p class="text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.sales.orders.view.channel')
                                </p>
                                <div class="flex items-center">
                                    {!! view_render_event('bagisto.admin.sales.order.channel_name.before', ['order' => $order]) !!}
                                    <p class="text-gray-600 dark:text-gray-300">
                                        {{$order->channel_name}}
                                    </p>
                                    {!! view_render_event('bagisto.admin.sales.order.channel_name.after', ['order' => $order]) !!}
                                </div>
                            </div>

                            <!-- Order Rating Row -->
                            <div class="flex flex-wrap justify-between items-center min-h-[40px]">
                                <p class="text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.sales.orders.view.rating')
                                </p>
                                <div class="flex items-center">
                                    {!! view_render_event('bagisto.admin.sales.order.rating.before', ['order' => $order]) !!}
                                    @if($order->rating === true)
                                        <p class="text-green-600 dark:text-green-400 font-semibold">
                                            @lang('admin::app.sales.orders.view.rating-like')
                                        </p>
                                    @elseif($order->rating === false)
                                        <p class="text-red-600 dark:text-red-400 font-semibold">
                                            @lang('admin::app.sales.orders.view.rating-dislike')
                                        </p>
                                    @else
                                        <p class="text-gray-500 dark:text-gray-400">
                                            @lang('admin::app.sales.orders.view.rating-not-rated')
                                        </p>
                                    @endif
                                    {!! view_render_event('bagisto.admin.sales.order.rating.after', ['order' => $order]) !!}
                                </div>
                            </div>
                        </div>
                    </x-slot>
                </x-admin::accordion>

                <!-- Payment and Shipping Information-->
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                            @lang('admin::app.sales.orders.view.payment-and-shipping')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <div>
                            <!-- Payment method -->
                            <p class="font-semibold text-gray-800 dark:text-white">
                                {{ core()->getConfigData('sales.payment_methods.' . $order->payment->method . '.title') }}
                            </p>

                            <p class="text-gray-600 dark:text-gray-300">
                                @lang('admin::app.sales.orders.view.payment-method')
                            </p>
                            {{--
                                                        <!-- Currency -->
                                                        <p class="pt-4 font-semibold text-gray-800 dark:text-white">
                                                            {{ $order->order_currency_code }}
                                                        </p>

                                                        <p class="text-gray-600 dark:text-gray-300">
                                                            @lang('admin::app.sales.orders.view.currency')
                                                        </p>
                            --}}
                            @php $additionalDetails = \Webkul\Payment\Payment::getAdditionalDetails($order->payment->method); @endphp

                                <!-- Additional details -->
                            @if (! empty($additionalDetails))
                                <p class="pt-4 font-semibold text-gray-800 dark:text-white">
                                    {{ $additionalDetails['title'] }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    {{ $additionalDetails['value'] }}
                                </p>
                            @endif

                            {!! view_render_event('bagisto.admin.sales.order.payment-method.after', ['order' => $order]) !!}
                        </div>

                        <!-- Shipping Method and Price Details -->
                        @if ($order->shipping_address)
                            <span class="mt-4 block w-full border-b dark:border-gray-800"></span>

                            <div class="pt-4">
                                <p class="font-semibold text-gray-800 dark:text-white">
                                    <!--  {{ $order->shipping_title }} -->
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

                            {!! view_render_event('bagisto.admin.sales.order.shipping-method.after', ['order' => $order]) !!}
                        @endif
                    </x-slot>
                </x-admin::accordion>
                {{--
                                <!-- Invoice Information-->
                                <x-admin::accordion>
                                    <x-slot:header>
                                        <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.sales.orders.view.invoices') ({{ count($order->invoices) }})
                                        </p>
                                    </x-slot>

                                    <x-slot:content>
                                        @forelse ($order->invoices as $index => $invoice)
                                            <div class="grid gap-y-2.5">
                                                <div>
                                                    <p class="font-semibold text-gray-800 dark:text-white">
                                                        @lang('admin::app.sales.orders.view.invoice-id', ['invoice' => $invoice->increment_id ?? $invoice->id])
                                                    </p>

                                                    <p class="text-gray-600 dark:text-gray-300">
                                                        {{ core()->formatDate($invoice->created_at, 'd M, Y H:i:s a') }}
                                                    </p>
                                                </div>

                                                <div class="flex gap-2.5">
                                                    <a
                                                        href="{{ route('admin.sales.invoices.view', $invoice->id) }}"
                                                        class="text-sm text-blue-600 transition-all hover:underline"
                                                    >
                                                        @lang('admin::app.sales.orders.view.view')
                                                    </a>

                                                    <a
                                                        href="{{ route('admin.sales.invoices.print', $invoice->id) }}"
                                                        class="text-sm text-blue-600 transition-all hover:underline"
                                                    >
                                                        @lang('admin::app.sales.orders.view.download-pdf')
                                                    </a>
                                                </div>
                                            </div>

                                            @if ($index < count($order->invoices) - 1)
                                                <span class="mb-4 mt-4 block w-full border-b dark:border-gray-800"></span>
                                            @endif
                                        @empty
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.no-invoice-found')
                                            </p>
                                        @endforelse
                                    </x-slot>

                                </x-admin::accordion>
                --}}

                {{--
                                <!-- Shipment Information-->
                                <x-admin::accordion>
                                    <x-slot:header>
                                        <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                                            @lang('admin::app.sales.orders.view.shipments') ({{ count($order->shipments) }})
                                        </p>
                                    </x-slot>

                                    <x-slot:content>
                                        @forelse ($order->shipments as $shipment)
                                            <div class="grid gap-y-2.5">
                                                <div>
                                                    <!-- Shipment Id -->
                                                    <p class="font-semibold text-gray-800 dark:text-white">
                                                        @lang('admin::app.sales.orders.view.shipment', ['shipment' => $shipment->id])
                                                    </p>

                                                    <!-- Shipment Created -->
                                                    <p class="text-gray-600 dark:text-gray-300">
                                                        {{ core()->formatDate($shipment->created_at, 'd M, Y H:i:s a') }}
                                                    </p>
                                                </div>

                                                <div class="flex gap-2.5">
                                                    <a
                                                        href="{{ route('admin.sales.shipments.view', $shipment->id) }}"
                                                        class="text-sm text-blue-600 transition-all hover:underline"
                                                    >
                                                        @lang('admin::app.sales.orders.view.view')
                                                    </a>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-gray-600 dark:text-gray-300">
                                                @lang('admin::app.sales.orders.view.no-shipment-found')
                                            </p>
                                        @endforelse
                                    </x-slot>
                                </x-admin::accordion>
                --}}
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

                {!! view_render_event('bagisto.admin.sales.order.right_component.after', ['order' => $order]) !!}
            </div>
        </div>
    </div>
</x-admin::layouts>
