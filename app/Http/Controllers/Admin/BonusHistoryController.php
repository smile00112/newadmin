<?php

namespace App\Http\Controllers\Admin;

use App\DataGrids\BonusHistoryDataGrid;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;

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
