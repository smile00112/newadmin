<?php

namespace Webkul\Newsletters\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Newsletters\Repositories\CompanyAccountRepository;

class CheckAccountBalance
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(
        protected CompanyAccountRepository $accountRepository
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth()->guard('admin')->user();

        if (!$admin || !$admin->company_id) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.account.no-company'),
            ], 403);
        }

        $account = $this->accountRepository->getOrCreateForCompany($admin->company_id);

        if ($account->balance <= 0) {
            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.account.insufficient-balance'),
            ], 402);
        }

        return $next($request);
    }
}

