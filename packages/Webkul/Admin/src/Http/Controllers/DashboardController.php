<?php

namespace Webkul\Admin\Http\Controllers;

use App\Repositories\ApplicationErrorRepository;
use Webkul\Admin\Helpers\Dashboard;

class DashboardController extends Controller
{
    /**
     * Request param functions
     *
     * @var array
     */
    protected $typeFunctions = [
        'over-all'                 => 'getOverAllStats',
        'today'                    => 'getTodayStats',
        'stock-threshold-products' => 'getStockThresholdProducts',
        'total-sales'              => 'getSalesStats',
        'total-visitors'           => 'getVisitorStats',
        'top-selling-products'     => 'getTopSellingProducts',
        'top-customers'            => 'getTopCustomers',
    ];

    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct(
        protected Dashboard $dashboardHelper,
        protected ApplicationErrorRepository $applicationErrorRepository
    ) {}

    /**
     * Dashboard page.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $recentErrors = $this->applicationErrorRepository->getRecent(20);

        return view('admin::dashboard.index')->with([
            'startDate'   => $this->dashboardHelper->getStartDate(),
            'endDate'     => $this->dashboardHelper->getEndDate(),
            'recentErrors' => $recentErrors,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $stats = $this->dashboardHelper->{$this->typeFunctions[request()->query('type')]}();

        return response()->json([
            'statistics' => $stats,
            'date_range' => $this->dashboardHelper->getDateRange(),
        ]);
    }
}
