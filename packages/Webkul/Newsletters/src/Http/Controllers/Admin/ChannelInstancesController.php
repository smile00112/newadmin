<?php

namespace Webkul\Newsletters\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Newsletters\Repositories\VacapInstanceRepository;
use Webkul\Newsletters\Repositories\MailInstanceRepository;
use Webkul\Newsletters\Repositories\TelegramBotInstanceRepository;
use Webkul\Newsletters\Repositories\MailingListRepository;
use Webkul\Newsletters\Repositories\CompanyRepository;
use Webkul\Newsletters\Traits\HasNewsletterRole;

class ChannelInstancesController extends Controller
{
    use HasNewsletterRole;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected VacapInstanceRepository $vacapInstanceRepository,
        protected MailInstanceRepository $mailInstanceRepository,
        protected TelegramBotInstanceRepository $telegramBotInstanceRepository,
        protected MailingListRepository $mailingListRepository,
        protected CompanyRepository $companyRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'whatsapp');

        // Validate type
        if (!in_array($type, ['whatsapp', 'email', 'telegram'])) {
            $type = 'whatsapp';
        }

        // Get instances based on type
        $instances = $this->getInstancesByType($type);
        $mailingLists = $this->mailingListRepository->all();

        return view('newsletters::admin.channel-instances.index', compact('instances', 'type', 'mailingLists'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $type)
    {
        // Validate type
        if (!in_array($type, ['whatsapp', 'email', 'telegram'])) {
            return redirect()->route('admin.newsletters.channel-instances.index')
                ->with('error', trans('newsletters::app.admin.channel-instances.invalid-type'));
        }

        $mailingLists = $this->mailingListRepository->all();

        // Get companies based on user role (only for email type)
        $companies = collect();
        $isAdmin = false;
        $isOwner = false;
        
        if ($type === 'email') {
            $admin = auth()->guard('admin')->user();
            
            // Check if user is admin (permission_type = 'all' and no company_id)
            if ($admin && $admin->role && $admin->role->permission_type === 'all' && !$admin->company_id) {
                $isAdmin = true;
                $companies = $this->companyRepository->all();
            } 
            // Check if user is owner
            elseif ($admin && $admin->company_id && $this->isCompanyOwner()) {
                $isOwner = true;
                $company = $this->companyRepository->find($admin->company_id);
                if ($company) {
                    $companies = collect([$company]);
                }
            }
        }

        return view('newsletters::admin.channel-instances.create', compact('type', 'mailingLists', 'companies', 'isAdmin', 'isOwner'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $type)
    {
        // Validate type
        if (!in_array($type, ['whatsapp', 'email', 'telegram'])) {
            return redirect()->route('admin.newsletters.channel-instances.index')
                ->with('error', trans('newsletters::app.admin.channel-instances.invalid-type'));
        }

        $validationRules = $this->getValidationRules($type);
        $validated = $request->validate($validationRules);

        // Handle active checkbox
        if ($type === 'email' || $type === 'telegram') {
            $validated['active'] = $request->has('active') ? (bool) $request->input('active') : true;
        }

        // For email type, ensure company_id is set
        if ($type === 'email') {
            $admin = auth()->guard('admin')->user();
            
            // If owner, use their company_id (even if not in request)
            if ($admin && $admin->company_id && $this->isCompanyOwner()) {
                $validated['company_id'] = $admin->company_id;
            }
            // If admin, use company_id from request (already validated)
            // If company_id is not set, it will be handled by repository
        }

        try {
            $instance = $this->createInstanceByType($type, $validated);

            session()->flash('success', trans('newsletters::app.admin.channel-instances.create-success', ['type' => $type]));

            return redirect()->route('admin.newsletters.channel-instances.index', ['type' => $type]);
        } catch (\Exception $e) {
            dd($e->getMessage());
            session()->flash('error', trans('newsletters::app.admin.channel-instances.create-failed', ['type' => $type]));

            return redirect()->back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $type, int $id)
    {
        // Validate type
        if (!in_array($type, ['whatsapp', 'email', 'telegram'])) {
            return redirect()->route('admin.newsletters.channel-instances.index')
                ->with('error', trans('newsletters::app.admin.channel-instances.invalid-type'));
        }

        try {
            $instance = $this->getInstanceByType($type, $id);
            $mailingLists = $this->mailingListRepository->all();

            return view('newsletters::admin.channel-instances.edit', compact('instance', 'type', 'mailingLists'));
        } catch (\Exception $e) {
            session()->flash('error', trans('newsletters::app.admin.channel-instances.not-found'));

            return redirect()->route('admin.newsletters.channel-instances.index', ['type' => $type]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $type, int $id)
    {
        // Validate type
        if (!in_array($type, ['whatsapp', 'email', 'telegram'])) {
            return redirect()->route('admin.newsletters.channel-instances.index')
                ->with('error', trans('newsletters::app.admin.channel-instances.invalid-type'));
        }

        $validationRules = $this->getValidationRules($type, true);
        $validated = $request->validate($validationRules);

        // Handle active checkbox
        if ($type === 'email' || $type === 'telegram') {
            $validated['active'] = $request->has('active') ? (bool) $request->input('active') : false;
        }

        // Remove password from update if not provided
        if (($type === 'whatsapp' || $type === 'email') && empty($validated['password'])) {
            unset($validated['password']);
        }

        try {
            $this->updateInstanceByType($type, $id, $validated);

            session()->flash('success', trans('newsletters::app.admin.channel-instances.update-success', ['type' => $type]));

            return redirect()->route('admin.newsletters.channel-instances.index', ['type' => $type]);
        } catch (\Exception $e) {
            session()->flash('error', trans('newsletters::app.admin.channel-instances.update-failed', ['type' => $type]));

            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $type, int $id)
    {
        // Validate type
        if (!in_array($type, ['whatsapp', 'email', 'telegram'])) {
            return response()->json([
                'message' => trans('newsletters::app.admin.channel-instances.invalid-type'),
            ], 400);
        }

        try {
            $this->deleteInstanceByType($type, $id);

            return response()->json([
                'message' => trans('newsletters::app.admin.channel-instances.delete-success', ['type' => $type]),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('newsletters::app.admin.channel-instances.delete-failed', ['type' => $type]),
            ], 500);
        }
    }

    /**
     * Get instances by type.
     */
    protected function getInstancesByType(string $type)
    {
        return match ($type) {
            'whatsapp' => $this->vacapInstanceRepository->all(),
            'email' => $this->mailInstanceRepository->all(),
            'telegram' => $this->telegramBotInstanceRepository->all(),
            default => collect(),
        };
    }

    /**
     * Get instance by type and id.
     */
    protected function getInstanceByType(string $type, int $id)
    {
        return match ($type) {
            'whatsapp' => $this->vacapInstanceRepository->findOrFail($id),
            'email' => $this->mailInstanceRepository->findOrFail($id),
            'telegram' => $this->telegramBotInstanceRepository->findOrFail($id),
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };
    }

    /**
     * Create instance by type.
     */
    protected function createInstanceByType(string $type, array $data)
    {
        return match ($type) {
            'whatsapp' => $this->vacapInstanceRepository->create($data),
            'email' => $this->mailInstanceRepository->create($data),
            'telegram' => $this->telegramBotInstanceRepository->create($data),
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };
    }

    /**
     * Update instance by type.
     */
    protected function updateInstanceByType(string $type, int $id, array $data)
    {
        return match ($type) {
            'whatsapp' => $this->vacapInstanceRepository->update($data, $id),
            'email' => $this->mailInstanceRepository->update($data, $id),
            'telegram' => $this->telegramBotInstanceRepository->update($data, $id),
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };
    }

    /**
     * Delete instance by type.
     */
    protected function deleteInstanceByType(string $type, int $id)
    {
        return match ($type) {
            'whatsapp' => $this->vacapInstanceRepository->delete($id),
            'email' => $this->mailInstanceRepository->delete($id),
            'telegram' => $this->telegramBotInstanceRepository->delete($id),
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };
    }

    /**
     * Get validation rules by type.
     */
    protected function getValidationRules(string $type, bool $isUpdate = false)
    {
        $passwordRule = $isUpdate ? 'nullable|string|max:255' : 'required|string|max:255';

        $rules = match ($type) {
            'whatsapp' => [
                'link_name' => 'required|string|max:255',
                'login' => 'required|string|max:255',
                'password' => $passwordRule,
            ],
            'email' => [
                'name' => 'nullable|string|max:255',
                'host' => 'required|string|max:255',
                'port' => 'required|integer|min:1|max:65535',
                'username' => 'required|string|max:255',
                'password' => $passwordRule,
                'encryption' => 'nullable|string|in:tls,ssl',
                'from_email' => 'required|email|max:255',
                'from_name' => 'nullable|string|max:255',
                'active' => 'nullable|boolean',
            ],
            'telegram' => [
                'bot_token' => 'required|string|max:255',
                'bot_username' => 'nullable|string|max:255',
                'bot_name' => 'nullable|string|max:255',
                'active' => 'nullable|boolean',
            ],
            default => [],
        };

        // Add company_id validation for email type (only for admin, owner's company_id is set automatically)
        if ($type === 'email' && !$isUpdate) {
            $admin = auth()->guard('admin')->user();
            
            // Only require company_id validation for admin users
            if ($admin && $admin->role && $admin->role->permission_type === 'all' && !$admin->company_id) {
                $rules['company_id'] = 'required|exists:companies,id';
            }
        }

        return $rules;
    }
}

