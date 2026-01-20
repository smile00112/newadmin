<?php

namespace Webkul\RestApi\Tests;

use Tests\TestCase;
use Webkul\Core\Tests\Concerns\CoreAssertions;
use Webkul\RestApi\Tests\Concerns\ApiAssertions;
use Webkul\Shop\Tests\Concerns\ShopTestBench;

class RestApiTestCase extends TestCase
{
    use ApiAssertions, CoreAssertions, ShopTestBench;

    /**
     * Base API URL prefix.
     */
    protected string $apiPrefix = '/api/v1';

    /**
     * Get API URL.
     */
    protected function getApiUrl(string $endpoint): string
    {
        return $this->apiPrefix . '/' . ltrim($endpoint, '/');
    }
}
