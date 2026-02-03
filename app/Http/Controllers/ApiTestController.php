<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ApiTestController extends Controller
{
    /**
     * Display the API testing page.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        return view('api-test');
    }
}
