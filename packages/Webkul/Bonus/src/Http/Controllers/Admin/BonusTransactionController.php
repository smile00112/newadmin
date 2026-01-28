<?php

namespace Webkul\Bonus\Http\Controllers\Admin;

use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Bonus\Repositories\BonusTransactionRepository;

class BonusTransactionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected BonusTransactionRepository $bonusTransactionRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $query = $this->bonusTransactionRepository->model->query();

        if (request()->has('customer_id')) {
            $query->where('customer_id', request('customer_id'));
        }

        if (request()->has('order_id')) {
            $query->where('order_id', request('order_id'));
        }

        if (request()->has('type')) {
            $query->where('type', request('type'));
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('bonus::admin.transactions.index', compact('transactions'));
    }
}
