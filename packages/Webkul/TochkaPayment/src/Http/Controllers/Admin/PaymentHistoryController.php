<?php

namespace Webkul\TochkaPayment\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\TochkaPayment\Repositories\TochkaPaymentHistoryRepository;

class PaymentHistoryController extends Controller
{
    /**
     * Payment history repository instance.
     *
     * @var \Webkul\TochkaPayment\Repositories\TochkaPaymentHistoryRepository
     */
    protected $paymentHistoryRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\TochkaPayment\Repositories\TochkaPaymentHistoryRepository  $paymentHistoryRepository
     * @return void
     */
    public function __construct(TochkaPaymentHistoryRepository $paymentHistoryRepository)
    {
        $this->paymentHistoryRepository = $paymentHistoryRepository;
    }

    /**
     * Display a listing of the payment history.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $payments = $this->paymentHistoryRepository->paginate(request()->input('limit') ?? 10);

        return view('tochka-payment::admin.payment-history.index', compact('payments'));
    }

    /**
     * Show the specified payment history.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $payment = $this->paymentHistoryRepository->findOrFail($id);

        return view('tochka-payment::admin.payment-history.show', compact('payment'));
    }
}
