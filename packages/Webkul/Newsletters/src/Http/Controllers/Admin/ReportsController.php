<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Helpers\Dashboard;

class ReportsController extends Controller
{
    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct(protected Dashboard $dashboardHelper) {}

    /**
     * Reports page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('newsletters::admin.reports.index')->with([
            'startDate' => $this->dashboardHelper->getStartDate(),
            'endDate'   => $this->dashboardHelper->getEndDate(),
        ]);
    }

    /**
     * Get messages statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        $type = request()->query('type', 'messages');
        
        if ($type === 'mailing-lists') {
            $stats = $this->dashboardHelper->getMailingListsStats();
        } else {
            $stats = $this->dashboardHelper->getMessagesStats();
        }

        return response()->json([
            'statistics' => $stats,
            'date_range' => $this->dashboardHelper->getDateRange(),
        ]);
    }
}

