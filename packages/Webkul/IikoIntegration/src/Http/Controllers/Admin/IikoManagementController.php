<?php

namespace Webkul\IikoIntegration\Http\Controllers\Admin;

use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;

class IikoManagementController extends Controller
{
    /**
     * Display the management page.
     */
    public function index(): View
    {
        return view('iiko-integration::admin.iiko.management');
    }
}
