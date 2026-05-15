<?php

namespace Webkul\IikoIntegration\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Webkul\IikoIntegration\Services\IikoNomenclatureImportService;
use Webkul\IikoIntegration\Services\IikoNomenclatureService;

class ImportNomenclatureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(
        protected string $organizationId,
        protected ?array $selectedGroupIds,
        protected string $statusKey
    ) {
        $this->onQueue('iiko');
    }

    public function handle(IikoNomenclatureImportService $importService, IikoNomenclatureService $nomenclatureService): void
    {
        Cache::put($this->statusKey, [
            'status'     => 'running',
            'started_at' => now()->toISOString(),
        ], 7200);

        $nomenclature = $nomenclatureService->getCachedNomenclature($this->organizationId);

        if (!$nomenclature) {
            Cache::put($this->statusKey, [
                'status'      => 'failed',
                'message'     => 'Nomenclature data not found in cache. Please re-fetch nomenclature first.',
                'finished_at' => now()->toISOString(),
            ], 7200);

            return;
        }

        $result = $importService->importNomenclature($this->organizationId, $nomenclature, $this->selectedGroupIds);

        Cache::put($this->statusKey, [
            'status'      => $result['success'] ? 'completed' : 'failed',
            'message'     => $result['message'] ?? null,
            'data'        => $result['data'] ?? null,
            'finished_at' => now()->toISOString(),
        ], 7200);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('iiko: ImportNomenclatureJob permanently failed', [
            'organization_id' => $this->organizationId,
            'message'         => $e->getMessage(),
        ]);

        Cache::put($this->statusKey, [
            'status'      => 'failed',
            'message'     => $e->getMessage(),
            'finished_at' => now()->toISOString(),
        ], 7200);
    }
}
