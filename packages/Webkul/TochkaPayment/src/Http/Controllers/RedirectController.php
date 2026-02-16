<?php

declare(strict_types=1);

namespace Webkul\TochkaPayment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles redirects from Tochka Bank after payment (success/fail).
 */
final class RedirectController
{
    /**
     * Show success page with request data.
     */
    public function success(Request $request): View
    {
        return view('tochka-payment::redirect', [
            'type' => 'success',
            'data' => $request->all(),
        ]);
    }

    /**
     * Show fail page with request data.
     */
    public function fail(Request $request): View
    {
        return view('tochka-payment::redirect', [
            'type' => 'fail',
            'data' => $request->all(),
        ]);
    }
}
