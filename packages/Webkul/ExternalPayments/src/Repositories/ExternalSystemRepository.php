<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\ExternalPayments\Models\ExternalSystem;

class ExternalSystemRepository
{
    /**
     * @return \Illuminate\Pagination\LengthAwarePaginator<ExternalSystem>
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return ExternalSystem::with('paymentProviders')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function find(int $id): ?ExternalSystem
    {
        return ExternalSystem::with('paymentProviders')->find($id);
    }

    public function findOrFail(int $id): ExternalSystem
    {
        return ExternalSystem::with('paymentProviders')->findOrFail($id);
    }

    public function findByToken(string $token): ?ExternalSystem
    {
        return ExternalSystem::where('api_token', $token)
            ->where('is_active', true)
            ->with('paymentProviders')
            ->first();
    }

    public function create(array $data): ExternalSystem
    {
        return ExternalSystem::create($data);
    }

    public function update(ExternalSystem $model, array $data): ExternalSystem
    {
        $model->update($data);

        return $model->fresh();
    }

    public function delete(ExternalSystem $model): bool
    {
        return $model->delete();
    }
}
