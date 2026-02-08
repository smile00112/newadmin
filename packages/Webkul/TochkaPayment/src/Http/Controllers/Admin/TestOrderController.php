<?php

declare(strict_types=1);

namespace Webkul\TochkaPayment\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\TochkaPayment\Services\PaymentRequestBuilder;

final class TestOrderController extends Controller
{
    /**
     * Display the test order form.
     */
    public function index(): View
    {
        $payment = session('payment');

        return view('tochka-payment::admin.test-order.index', compact('payment'));
    }

    /**
     * Create a test payment and redirect back with result.
     */
    public function store(Request $request): RedirectResponse
    {
        $minAmount = (float) config('tochka-payment.min_amount', 1.00);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:' . $minAmount,
            'product_name' => 'nullable|string|max:255',
            'external_order_id' => 'nullable|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'required|string|max:20',
        ]);

        $requestBuilder = new PaymentRequestBuilder();

        $payment = $requestBuilder->createPaymentHistory($validated, [], '');

        $requestParams = $requestBuilder->buildRequestParams($validated, $payment->id);
        $requestParams['product_name'] = $validated['product_name'] ?? null;
        $paymentUrl = $requestBuilder->buildPaymentUrl($requestParams);

        $payment->update([
            'order_id' => $requestParams['orderid'],
            'payment_url' => $paymentUrl,
            'request_data' => $requestParams,
        ]);

        return redirect()
            ->route('admin.tochka-payment.test-order.index')
            ->with('payment', $payment)
            ->with('success', trans('tochka-payment::app.admin.test-order.created'));
    }
}
