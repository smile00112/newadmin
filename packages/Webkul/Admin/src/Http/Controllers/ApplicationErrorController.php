<?php

namespace Webkul\Admin\Http\Controllers;

use App\Models\ApplicationError;
use App\Repositories\ApplicationErrorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = $this->applicationErrorRepository
                ->getModel()
                ->newQuery()
                ->orderBy('created_at', 'desc');

            if ($search = $request->get('q')) {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('message', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%')
                        ->orWhere('source', 'like', '%' . $search . '%');
                });
            }

            if ($level = $request->get('level')) {
                $query->where('level', $level);
            }

            if ($platform = $request->get('platform')) {
                $query->where('platform', $platform);
            }

            if ($assignedTo = $request->get('assigned_to')) {
                $query->where('assigned_to', $assignedTo);
            }

            if ($request->has('is_read')) {
                $query->where('is_read', (bool) $request->get('is_read'));
            }

            $paginator = $query->paginate(
                perPage: (int) $request->get('per_page', 20),
                page: (int) $request->get('page', 1)
            );

            return response()->json([
                'items' => $paginator->items(),
                'meta'  => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                ],
            ]);
        }

        $now = now();

        $stats = [
            'total'    => $this->applicationErrorRepository->count(),
            'unread'   => $this->applicationErrorRepository->count(['is_read' => false]),
            'today'    => $this->applicationErrorRepository
                ->getModel()
                ->whereDate('created_at', $now->toDateString())
                ->count(),
            'critical' => $this->applicationErrorRepository
                ->getModel()
                ->where('level', 'critical')
                ->count(),
        ];

        return view('admin::application-errors.index', compact('stats'));
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

    /**
     * Mark the specified application error as read.
     */
    public function markAsRead(int $id): JsonResponse
    {
        $error = ApplicationError::findOrFail($id);

        $error->update(['is_read' => true]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Remove the specified application error.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->applicationErrorRepository->delete($id);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Remove all (optionally filtered) application errors.
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $query = $this->applicationErrorRepository
            ->getModel()
            ->newQuery();

        if ($level = $request->get('level')) {
            $query->where('level', $level);
        }

        if ($platform = $request->get('platform')) {
            $query->where('platform', $platform);
        }

        if ($assignedTo = $request->get('assigned_to')) {
            $query->where('assigned_to', $assignedTo);
        }

        $query->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
