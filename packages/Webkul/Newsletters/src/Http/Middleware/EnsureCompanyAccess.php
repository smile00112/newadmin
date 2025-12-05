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

        // Super admins with permission_type 'all' can access without company_id
        if ($admin->role && $admin->role->permission_type === 'all') {
            return $next($request);
        }

        // If admin has no company_id, deny access to newsletters data
        if (!$admin->company_id) {
            abort(403, 'Access denied. Admin must be assigned to a company.');
        }

        return $next($request);
    }
}

