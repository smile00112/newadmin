<?php

namespace Webkul\Admin\Http\Controllers;

use App\Models\ApplicationError;
use App\Repositories\ApplicationErrorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ApplicationErrorController extends Controller
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
     * Display a listing of application errors.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(\Webkul\Admin\DataGrids\ApplicationErrorDataGrid::class)->process();
        }

        return view('admin::application-errors.index');
    }

    /**
     * Display the specified application error.
     *
     * @param  int  $id
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(int $id): View|JsonResponse
    {
        $error = ApplicationError::findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'error' => $error,
            ]);
        }

        return view('admin::application-errors.show', compact('error'));
    }
}
