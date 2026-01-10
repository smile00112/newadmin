<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\AccountWarmingRepository;
use Webkul\Newsletters\Repositories\VacapInstanceRepository;
use Webkul\Newsletters\Services\AccountWarmingStarterService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AccountWarmingController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected AccountWarmingRepository $accountWarmingRepository,
        protected VacapInstanceRepository $vacapInstanceRepository,
        protected AccountWarmingStarterService $accountWarmingStarterService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warmings = $this->accountWarmingRepository->with('whatsappInstances')
            ->withCount('whatsappInstances')
            ->orderBy('created_at', 'desc')
            ->all();
        
        return view('newsletters::admin.account-warmings.index', compact('warmings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all WhatsApp instances
        $whatsappInstances = $this->vacapInstanceRepository->all();
        
        return view('newsletters::admin.account-warmings.create', compact('whatsappInstances'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Starting account warming creation process', [
            'user_id' => auth()->id(),
            'request_data' => $request->except(['phrases']),
        ]);

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'whatsapp_instance_ids' => 'required|array|min:2',
            'whatsapp_instance_ids.*' => 'exists:newsletters_whatsapp_instances,id',
            'phrases' => 'required|array|min:1',
            'phrases.*.question' => 'required|string|max:1000',
            'phrases.*.answer' => 'required|string|max:1000',
            'delay_from' => 'nullable|integer|min:1|max:3600',
            'delay_to' => 'nullable|integer|min:1|max:3600',
            'start_at' => 'nullable|date|after:now',
        ]);

        try {
            DB::beginTransaction();

            // Prepare phrases array
            $phrases = [];
            foreach ($request->input('phrases') as $phrase) {
                if (!empty(trim($phrase['question'])) && !empty(trim($phrase['answer']))) {
                    $phrases[] = [
                        'question' => trim($phrase['question']),
                        'answer' => trim($phrase['answer']),
                    ];
                }
            }

            if (empty($phrases)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['phrases' => trans('newsletters::app.admin.account-warmings.no-phrases')]);
            }

            // Create account warming
            $warmingData = [
                'name' => $request->input('name'),
                'selected_account_ids' => $request->input('whatsapp_instance_ids'),
                'phrases' => $phrases,
                'delay_from' => $request->input('delay_from', 5),
                'delay_to' => $request->input('delay_to', 5),
                'active' => false,
                'status' => 'created',
                'start_at' => $request->input('start_at'),
            ];

            $warming = $this->accountWarmingRepository->create($warmingData);

            // Sync WhatsApp instances
            $warming->whatsappInstances()->sync($request->input('whatsapp_instance_ids'));

            DB::commit();

            Log::info('Account warming created successfully', [
                'account_warming_id' => $warming->id,
            ]);

            session()->flash('success', trans('newsletters::app.admin.account-warmings.created'));

            return redirect()->route('admin.newsletters.account-warmings.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create account warming', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => trans('newsletters::app.admin.account-warmings.create-failed')]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $warming = $this->accountWarmingRepository->findOrFail($id);
        $whatsappInstances = $this->vacapInstanceRepository->all();
        
        return view('newsletters::admin.account-warmings.edit', compact('warming', 'whatsappInstances'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        Log::info('Starting account warming update process', [
            'account_warming_id' => $id,
            'user_id' => auth()->id(),
        ]);

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'whatsapp_instance_ids' => 'required|array|min:2',
            'whatsapp_instance_ids.*' => 'exists:newsletters_whatsapp_instances,id',
            'phrases' => 'required|array|min:1',
            'phrases.*.question' => 'required|string|max:1000',
            'phrases.*.answer' => 'required|string|max:1000',
            'delay_from' => 'nullable|integer|min:1|max:3600',
            'delay_to' => 'nullable|integer|min:1|max:3600',
            'start_at' => 'nullable|date|after:now',
        ]);

        try {
            DB::beginTransaction();

            $warming = $this->accountWarmingRepository->findOrFail($id);

            // Don't allow editing if warming is active
            if ($warming->active) {
                return redirect()->back()
                    ->withErrors(['error' => trans('newsletters::app.admin.account-warmings.cannot-edit-active')]);
            }

            // Prepare phrases array
            $phrases = [];
            foreach ($request->input('phrases') as $phrase) {
                if (!empty(trim($phrase['question'])) && !empty(trim($phrase['answer']))) {
                    $phrases[] = [
                        'question' => trim($phrase['question']),
                        'answer' => trim($phrase['answer']),
                    ];
                }
            }

            if (empty($phrases)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['phrases' => trans('newsletters::app.admin.account-warmings.no-phrases')]);
            }

            // Update account warming
            $warmingData = [
                'name' => $request->input('name'),
                'selected_account_ids' => $request->input('whatsapp_instance_ids'),
                'phrases' => $phrases,
                'delay_from' => $request->input('delay_from', 5),
                'delay_to' => $request->input('delay_to', 5),
                'start_at' => $request->input('start_at'),
            ];

            $this->accountWarmingRepository->update($warmingData, $id);

            // Sync WhatsApp instances
            $warming->whatsappInstances()->sync($request->input('whatsapp_instance_ids'));

            DB::commit();

            Log::info('Account warming updated successfully', [
                'account_warming_id' => $id,
            ]);

            session()->flash('success', trans('newsletters::app.admin.account-warmings.updated'));

            return redirect()->route('admin.newsletters.account-warmings.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update account warming', [
                'account_warming_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => trans('newsletters::app.admin.account-warmings.update-failed')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $warming = $this->accountWarmingRepository->findOrFail($id);

            // Don't allow deletion if warming is active
            if ($warming->active) {
                return response()->json([
                    'success' => false,
                    'message' => trans('newsletters::app.admin.account-warmings.cannot-delete-active')
                ], 400);
            }

            $this->accountWarmingRepository->delete($id);

            Log::info('Account warming deleted successfully', [
                'account_warming_id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => trans('newsletters::app.admin.account-warmings.deleted')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete account warming', [
                'account_warming_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.account-warmings.delete-failed')
            ], 500);
        }
    }

    /**
     * Start account warming.
     */
    public function start(int $id)
    {
        try {
            $warming = $this->accountWarmingRepository->findOrFail($id);
            $result = $this->accountWarmingStarterService->start($warming);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message']
            ], $result['status_code']);
        } catch (\Exception $e) {
            Log::error('Failed to start account warming', [
                'account_warming_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.account-warmings.warming-start-failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pause account warming.
     */
    public function pause(int $id)
    {
        try {
            $warming = $this->accountWarmingRepository->findOrFail($id);

            $this->accountWarmingRepository->update([
                'active' => false,
                'status' => 'paused'
            ], $id);

            Log::info('Account warming paused', [
                'account_warming_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => trans('newsletters::app.admin.account-warmings.warming-paused')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to pause account warming', [
                'account_warming_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('newsletters::app.admin.account-warmings.warming-pause-failed')
            ], 500);
        }
    }
}


