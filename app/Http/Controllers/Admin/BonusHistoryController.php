<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Bonus\DataGrids\BonusHistoryDataGrid;

class BonusHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        if (request()->ajax()) {
            return datagrid(BonusHistoryDataGrid::class)->process();
        }

        return view('admin::bonus-history.index');
    }
}
