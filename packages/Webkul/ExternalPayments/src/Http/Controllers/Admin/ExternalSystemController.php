<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Http\Controllers\Admin;

use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\ExternalPayments\Models\ExternalSystem;
use Webkul\ExternalPayments\Models\ExternalSystemPaymentProvider;
use Webkul\ExternalPayments\Repositories\ExternalSystemRepository;
use Webkul\ExternalPayments\Services\PaymentProviderRegistry;
use Webkul\Newsletters\Repositories\CompanyRepository;

final class ExternalSystemController extends Controller
{
    public function __construct(
        protected ExternalSystemRepository $externalSystemRepository,
        protected PaymentProviderRegistry $providerRegistry,
        protected CompanyRepository $companyRepository
    ) {}

    protected function getCompanies(): \Illuminate\Support\Collection
    {
        $admin = auth()->guard('admin')->user();
        $companies = collect();

        if ($admin) {
            if ($admin->role && $admin->role->permission_type === 'all' && !$admin->company_id) {
                // Суперадмин - показываем все компании
                $companies = $this->companyRepository->all();
            } elseif ($admin->company_id) {
                // Владелец - показываем только свою компанию
                $company = $this->companyRepository->find($admin->company_id);
                if ($company) {
                    $companies = collect([$company]);
                }
            }
        }

        return $companies;
    }

    protected function isSuperAdmin(): bool
    {
        $admin = auth()->guard('admin')->user();
        return $admin && $admin->role && $admin->role->permission_type === 'all' && !$admin->company_id;
    }

    protected function getCompanyId(Request $request): ?int
    {
        $admin = auth()->guard('admin')->user();
        
        if ($this->isSuperAdmin()) {
            // Суперадмин может выбрать компанию
            return $request->integer('company_id') ?: null;
        } elseif ($admin && $admin->company_id) {
            // Владелец использует свою компанию
            return (int) $admin->company_id;
        }

        return null;
    }

    public function index(Request $request): View
    {
        $perPage = (int) $request->input('limit', 10);
        $systems = $this->externalSystemRepository->paginate($perPage);
        $providerNames = config('external-payments.providers', []);

        return view('external-payments::admin.systems.index', [
            'systems'        => $systems,
            'providerNames'  => $providerNames,
        ]);
    }

    public function create(): View
    {
        $providers = $this->providerRegistry->getAvailableProviders();
        $providerConfig = config('external-payments.providers', []);
        $companies = $this->getCompanies();
        $isSuperAdmin = $this->isSuperAdmin();

        return view('external-payments::admin.systems.create', [
            'providers'       => $providers,
            'providerConfig'  => $providerConfig,
            'companies'       => $companies,
            'isSuperAdmin'    => $isSuperAdmin,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $available = $this->providerRegistry->getAvailableProviders();
            $validated = $request->validate([
                'name'                   => 'required|string|max:255',
                'api_token'              => 'nullable|string|max:255',
                'webhook_url'            => 'nullable|url|max:500',
                'woocommerce_site_url'   => 'nullable|url|max:500',
                'is_active'              => 'boolean',
                'company_id'             => 'nullable|integer|exists:companies,id',
                'payment_providers'      => 'required|array|min:1',
                'payment_providers.*'    => 'string|in:'.implode(',', $available),
                'default_provider'       => 'nullable|string|max:64|in:'.implode(',', $available),
            ]);

            if (! empty($validated['default_provider']) && ! in_array($validated['default_provider'], $validated['payment_providers'], true)) {
                return redirect()->back()->withInput()->withErrors([
                    'default_provider' => __('external-payments::app.admin.systems.messages.validation.default_must_be_allowed'),
                ]);
            }

            $token = ! empty($validated['api_token'])
                ? $validated['api_token']
                : $this->generateUniqueToken();

            $companyId = $this->getCompanyId($request);
            if ($companyId && isset($validated['company_id']) && $this->isSuperAdmin()) {
                $companyId = $validated['company_id'] ?: $companyId;
            }

            $dataToCreate = [
                'name'                  => $validated['name'],
                'api_token'             => $token,
                'webhook_url'           => $validated['webhook_url'] ?? null,
                'woocommerce_site_url'  => $validated['woocommerce_site_url'] ?? null,
                'is_active'             => $request->boolean('is_active', true),
                'company_id'            => $companyId,
            ];

            // Удаляем null значения для company_id, если оно не требуется
            if ($dataToCreate['company_id'] === null) {
                unset($dataToCreate['company_id']);
            }

            Log::info('Creating external system', ['data' => $dataToCreate]);

            try {
                $system = $this->externalSystemRepository->create($dataToCreate);
            } catch (\Exception $createException) {
                Log::error('Exception during repository create', [
                    'exception' => $createException->getMessage(),
                    'data'     => $dataToCreate,
                    'trace'    => $createException->getTraceAsString(),
                ]);
                throw $createException;
            }

            if (! $system || ! $system->id) {
                Log::error('System creation returned null or no ID', [
                    'system' => $system,
                    'data'   => $dataToCreate,
                ]);
                throw new \RuntimeException('Failed to create external system: model was not created or has no ID');
            }

            $this->syncPaymentProviders($system, $validated['payment_providers'], $validated['default_provider'] ?? null);

            session()->flash('success', __('external-payments::app.admin.systems.messages.created'));

            return redirect()->route('admin.external-payments.systems.index');
        } catch (QueryException $e) {
            Log::error('Failed to create external system (QueryException)', [
                'error'     => $e->getMessage(),
                'code'      => $e->getCode(),
                'sql_state' => $e->errorInfo[0] ?? null,
                'sql_code'  => $e->errorInfo[1] ?? null,
                'sql_msg'   => $e->errorInfo[2] ?? null,
                'trace'     => $e->getTraceAsString(),
                'request'   => $request->all(),
            ]);

            $errorMessage = __('external-payments::app.admin.systems.messages.create_failed');
            
            // Проверяем на уникальность api_token
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry') || ($e->errorInfo[0] ?? null) === '23000') {
                $errorMessage = __('external-payments::app.admin.systems.messages.api_token_exists');
            } else {
                // Показываем более детальную информацию об ошибке БД
                $sqlMessage = $e->errorInfo[2] ?? $e->getMessage();
                $errorMessage .= ' ' . __('external-payments::app.admin.systems.messages.database_error') . ': ' . $sqlMessage;
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        } catch (\Exception $e) {
            Log::error('Failed to create external system (Exception)', [
                'error'   => $e->getMessage(),
                'code'    => $e->getCode(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            $errorMessage = __('external-payments::app.admin.systems.messages.create_failed');
            
            // В режиме разработки показываем детали ошибки
            if (config('app.debug')) {
                $errorMessage .= ': ' . $e->getMessage() . ' (File: ' . basename($e->getFile()) . ', Line: ' . $e->getLine() . ')';
            } else {
                $errorMessage .= ': ' . $e->getMessage();
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    public function edit(int $id): View|RedirectResponse
    {
        $system = $this->externalSystemRepository->find($id);

        if (! $system) {
            return redirect()->route('admin.external-payments.systems.index')
                ->with('error', 'Not found');
        }

        // Делаем api_token видимым для формы редактирования
        $system->makeVisible('api_token');

        $providers = $this->providerRegistry->getAvailableProviders();
        $providerConfig = config('external-payments.providers', []);
        $companies = $this->getCompanies();
        $isSuperAdmin = $this->isSuperAdmin();

        return view('external-payments::admin.systems.edit', [
            'system'          => $system,
            'providers'       => $providers,
            'providerConfig'  => $providerConfig,
            'companies'       => $companies,
            'isSuperAdmin'    => $isSuperAdmin,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $system = $this->externalSystemRepository->findOrFail($id);

        $available = $this->providerRegistry->getAvailableProviders();
        $validated = $request->validate([
            'name'                   => 'required|string|max:255',
            'api_token'              => 'nullable|string|max:255',
            'webhook_url'            => 'nullable|url|max:500',
            'woocommerce_site_url'   => 'nullable|url|max:500',
            'is_active'              => 'boolean',
            'company_id'             => 'nullable|integer|exists:companies,id',
            'payment_providers'      => 'required|array|min:1',
            'payment_providers.*'    => 'string|in:'.implode(',', $available),
            'default_provider'       => 'nullable|string|max:64|in:'.implode(',', $available),
        ]);

        if (! empty($validated['default_provider']) && ! in_array($validated['default_provider'], $validated['payment_providers'], true)) {
            return redirect()->back()->withInput()->withErrors([
                'default_provider' => __('external-payments::app.admin.systems.messages.validation.default_must_be_allowed'),
            ]);
        }

        $companyId = $this->getCompanyId($request);
        if ($companyId && isset($validated['company_id']) && $this->isSuperAdmin()) {
            $companyId = $validated['company_id'] ?: $companyId;
        }

        $data = [
            'name'                  => $validated['name'],
            'webhook_url'           => $validated['webhook_url'] ?? null,
            'woocommerce_site_url'  => $validated['woocommerce_site_url'] ?? null,
            'is_active'             => $request->boolean('is_active', true),
            'company_id'            => $companyId,
        ];

        if (! empty($validated['api_token'])) {
            $data['api_token'] = $validated['api_token'];
        }

        $this->externalSystemRepository->update($system, $data);
        $this->syncPaymentProviders($system, $validated['payment_providers'], $validated['default_provider'] ?? null);

        session()->flash('success', __('external-payments::app.admin.systems.messages.updated'));

        return redirect()->route('admin.external-payments.systems.index');
    }

    public function generateToken(int $id): RedirectResponse
    {
        try {
            $system = $this->externalSystemRepository->findOrFail($id);
            
            $newToken = $this->generateUniqueToken();
            $system->update(['api_token' => $newToken]);

            session()->flash('success', __('external-payments::app.admin.systems.messages.token_generated'));

            return redirect()->route('admin.external-payments.systems.edit', $id);
        } catch (\Exception $e) {
            Log::error('Failed to generate token', [
                'system_id' => $id,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', __('external-payments::app.admin.systems.messages.create_failed') . ': ' . $e->getMessage());
        }
    }

    protected function generateUniqueToken(int $maxAttempts = 10): string
    {
        $attempts = 0;
        do {
            $token = Str::random(64);
            $exists = ExternalSystem::where('api_token', $token)->exists();
            $attempts++;
        } while ($exists && $attempts < $maxAttempts);

        if ($exists) {
            throw new \RuntimeException('Failed to generate unique API token after ' . $maxAttempts . ' attempts');
        }

        return $token;
    }

    protected function syncPaymentProviders(ExternalSystem $system, array $allowedKeys, ?string $defaultKey): void
    {
        try {
            $system->paymentProviders()->delete();

            $firstKey = null;
            foreach ($allowedKeys as $key) {
                if (! is_string($key) || $key === '') {
                    continue;
                }
                if ($firstKey === null) {
                    $firstKey = $key;
                }
                $isDefault = ($defaultKey === $key) || ($defaultKey === null && $firstKey === $key);
                ExternalSystemPaymentProvider::create([
                    'external_system_id' => $system->id,
                    'payment_provider'   => $key,
                    'is_default'         => $isDefault,
                ]);
            }

            if ($defaultKey && ! in_array($defaultKey, $allowedKeys, true)) {
                $first = $system->paymentProviders()->first();
                if ($first) {
                    $first->update(['is_default' => true]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync payment providers', [
                'system_id' => $system->id,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
