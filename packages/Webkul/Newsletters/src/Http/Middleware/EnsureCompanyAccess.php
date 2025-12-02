<?php

namespace Webkul\Newsletters\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanyAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            abort(401, 'Unauthorized');
        }

        // If admin has no company_id, deny access to newsletters data
        if (!$admin->company_id) {
            abort(403, 'Access denied. Admin must be assigned to a company.');
        }

        return $next($request);
    }
}

