<?php

declare(strict_types=1);

namespace Webkul\ExternalPayments\Services;

use Webkul\ExternalPayments\Contracts\PaymentProviderAdapterInterface;
use Illuminate\Contracts\Container\Container;

class PaymentProviderRegistry
{
    /** @var array<string, class-string<PaymentProviderAdapterInterface>> */
    protected array $adapters = [];

    public function __construct(
        protected Container $container
    ) {
        $adapters = config('external-payments.adapters', []);
        foreach ($adapters as $key => $class) {
            if (is_string($class) && class_exists($class)) {
                $this->adapters[$key] = $class;
            }
        }
    }

    public function get(string $providerKey): PaymentProviderAdapterInterface
    {
        $class = $this->adapters[$providerKey] ?? null;

        if (! $class) {
            throw new \InvalidArgumentException("Unknown payment provider: {$providerKey}");
        }

        $adapter = $this->container->make($class);

        if (! $adapter instanceof PaymentProviderAdapterInterface) {
            throw new \InvalidArgumentException("Adapter for {$providerKey} does not implement PaymentProviderAdapterInterface");
        }

        return $adapter;
    }

    public function has(string $providerKey): bool
    {
        return isset($this->adapters[$providerKey]);
    }

    /**
     * @return array<string>
     */
    public function getAvailableProviders(): array
    {
        $config = config('external-payments.providers', []);
        $available = [];

        foreach ($config as $key => $item) {
            if (is_array($item) && ($item['enabled'] ?? true) && $this->has($key)) {
                $available[] = $key;
            }
        }

        return $available;
    }
}
