<?php

namespace Webkul\IikoIntegration\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;
use Webkul\IikoIntegration\Models\IikoSyncLog;

class IikoSyncLogRepository extends Repository
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return IikoSyncLog::class;
    }

    /**
     * Get logs by sync type.
     */
    public function getBySyncType(string $syncType, int $limit = 50)
    {
        return $this->model
            ->where('sync_type', $syncType)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent error logs.
     */
    public function getRecentErrors(int $limit = 50)
    {
        return $this->model
            ->where('status', IikoSyncLog::STATUS_ERROR)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
