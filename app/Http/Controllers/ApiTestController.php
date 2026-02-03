<?php

namespace App\Http\Controllers;

class ApiTestController extends Controller
{
    /**
     * Display the API testing page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('api-test');
    }
}
