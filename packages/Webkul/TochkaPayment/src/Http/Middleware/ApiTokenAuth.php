<?php

namespace Webkul\TochkaPayment\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication token required',
            ], 401);
        }

        $expectedToken = config('tochka-payment.api_token');

        if (empty($expectedToken)) {
            return response()->json([
                'success' => false,
                'message' => 'API token not configured',
            ], 500);
        }

        if (!hash_equals($expectedToken, $token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid authentication token',
            ], 401);
        }

        return $next($request);
    }
}
