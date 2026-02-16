<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\ExternalPayments\Models\ExternalSystem;
use Webkul\ExternalPayments\Models\ExternalSystemPaymentProvider;
use Webkul\ExternalPayments\Repositories\ExternalSystemRepository;
use Webkul\ExternalPayments\Services\PaymentProviderRegistry;

final class ExternalSystemController extends Controller
{
    public function __construct(
        protected ExternalSystemRepository $externalSystemRepository,
        protected PaymentProviderRegistry $providerRegistry
    ) {}

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

        return view('external-payments::admin.systems.create', [
            'providers'       => $providers,
            'providerConfig'  => $providerConfig,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $available = $this->providerRegistry->getAvailableProviders();
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'api_token'          => 'nullable|string|max:255',
            'webhook_url'        => 'nullable|url|max:500',
            'is_active'          => 'boolean',
            'payment_providers'  => 'required|array|min:1',
            'payment_providers.*'=> 'string|in:'.implode(',', $available),
            'default_provider'   => 'nullable|string|max:64|in:'.implode(',', $available),
        ]);

        if (! empty($validated['default_provider']) && ! in_array($validated['default_provider'], $validated['payment_providers'], true)) {
            return redirect()->back()->withInput()->withErrors([
                'default_provider' => __('external-payments::app.admin.systems.messages.validation.default_must_be_allowed'),
            ]);
        }

        $token = ! empty($validated['api_token'])
            ? $validated['api_token']
            : Str::random(64);

        $system = $this->externalSystemRepository->create([
            'name'        => $validated['name'],
            'api_token'   => $token,
            'webhook_url' => $validated['webhook_url'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        $this->syncPaymentProviders($system, $validated['payment_providers'], $validated['default_provider'] ?? null);

        session()->flash('success', __('external-payments::app.admin.systems.messages.created'));

        return redirect()->route('admin.external-payments.systems.index');
    }

    public function edit(int $id): View|RedirectResponse
    {
        $system = $this->externalSystemRepository->find($id);

        if (! $system) {
            return redirect()->route('admin.external-payments.systems.index')
                ->with('error', 'Not found');
        }

        $providers = $this->providerRegistry->getAvailableProviders();
        $providerConfig = config('external-payments.providers', []);

        return view('external-payments::admin.systems.edit', [
            'system'          => $system,
            'providers'       => $providers,
            'providerConfig'  => $providerConfig,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $system = $this->externalSystemRepository->findOrFail($id);

        $available = $this->providerRegistry->getAvailableProviders();
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'api_token'          => 'nullable|string|max:255',
            'webhook_url'        => 'nullable|url|max:500',
            'is_active'          => 'boolean',
            'payment_providers'  => 'required|array|min:1',
            'payment_providers.*'=> 'string|in:'.implode(',', $available),
            'default_provider'   => 'nullable|string|max:64|in:'.implode(',', $available),
        ]);

        if (! empty($validated['default_provider']) && ! in_array($validated['default_provider'], $validated['payment_providers'], true)) {
            return redirect()->back()->withInput()->withErrors([
                'default_provider' => __('external-payments::app.admin.systems.messages.validation.default_must_be_allowed'),
            ]);
        }

        $data = [
            'name'        => $validated['name'],
            'webhook_url' => $validated['webhook_url'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
        ];

        if (! empty($validated['api_token'])) {
            $data['api_token'] = $validated['api_token'];
        }

        $this->externalSystemRepository->update($system, $data);
        $this->syncPaymentProviders($system, $validated['payment_providers'], $validated['default_provider'] ?? null);

        session()->flash('success', __('external-payments::app.admin.systems.messages.updated'));

        return redirect()->route('admin.external-payments.systems.index');
    }

    protected function syncPaymentProviders(ExternalSystem $system, array $allowedKeys, ?string $defaultKey): void
    {
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
    }
}
