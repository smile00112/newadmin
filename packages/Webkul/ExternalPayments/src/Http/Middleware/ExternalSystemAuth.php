<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Webkul\ExternalPayments\Repositories\ExternalSystemRepository;

class ExternalSystemAuth
{
    public function __construct(
        protected ExternalSystemRepository $externalSystemRepository
    ) {}

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('External Payments: Incoming request auth', [
            'path'             => $request->path(),
            'method'           => $request->method(),
            'has_bearer_token' => (bool) $request->bearerToken(),
        ]);

        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => __('external-payments::app.api.token_required'),
            ], 401);
        }

        $externalSystem = $this->externalSystemRepository->findByToken($token);

        if (! $externalSystem) {
            return response()->json([
                'success' => false,
                'message' => __('external-payments::app.api.token_invalid'),
            ], 401);
        }

        $request->attributes->set('external_system', $externalSystem);

        Log::info('External Payments: Request authenticated', [
            'path'               => $request->path(),
            'external_system_id' => $externalSystem->id,
            'external_system_name' => $externalSystem->name ?? null,
        ]);

        return $next($request);
    }
}
