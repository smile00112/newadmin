<?php

namespace Webkul\Newsletters\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Webkul\Newsletters\Traits\HasNewsletterRole;

class CheckNewsletterPermission
{
    use HasNewsletterRole;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        $this->requireNewsletterPermission($permission);

        return $next($request);
    }
}

