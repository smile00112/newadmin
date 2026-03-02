<?php

namespace Webkul\RestApi\Http\Controllers\V1\Shop;

use App\Repositories\ApplicationErrorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\RestApi\Http\Controllers\RestApiController;

class ErrorReportController extends RestApiController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ApplicationErrorRepository $applicationErrorRepository
    ) {}

    /**
     * Store a new application error.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:65535',
            'code'    => 'nullable|string|max:255',
            'file'    => 'nullable|string|max:2048',
            'line'    => 'nullable|integer|min:0',
            'trace'   => 'nullable',
            'context' => 'nullable|array',
            'source'  => 'nullable|string|max:255',
        ]);

        $trace = $validated['trace'] ?? null;
        if (is_array($trace)) {
            $validated['trace'] = json_encode($trace);
        }

        $error = $this->applicationErrorRepository->create($validated);

        return response()->json([
            'message' => trans('rest-api::app.shop.errors.report-success'),
            'data'    => [
                'id' => $error->id,
            ],
        ], 201);
    }
}
