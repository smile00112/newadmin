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
        $channelType = request()->query('channel_type');
        
        // Validate channel type if provided
        $validChannelTypes = ['whatsapp', 'email', 'telegram'];
        if ($channelType && !in_array($channelType, $validChannelTypes)) {
            $channelType = null;
        }
        
        if ($type === 'mailing-lists') {
            if ($channelType) {
                $stats = $this->dashboardHelper->getMailingListsStatsByChannelType($channelType);
            } else {
                $stats = $this->dashboardHelper->getMailingListsStats();
            }
        } else {
            if ($channelType) {
                $stats = $this->dashboardHelper->getMessagesStatsByChannelType($channelType);
            } else {
                $stats = $this->dashboardHelper->getMessagesStats();
            }
        }

        return response()->json([
            'statistics' => $stats,
            'date_range' => $this->dashboardHelper->getDateRange(),
        ]);
    }
}

