<?php
// Файл: includes/Service/Settings.php

declare(strict_types=1);

namespace RemoteOnlinePayments\Service;

use RemoteOnlinePayments\Gateway;

/**
 * DTO для хранения и предоставления настроек шлюза.
 * Инкапсулирует получение опций из WC_Payment_Gateway.
 */
final class Settings
{
    private string $id;
    private bool $enabled;
    private string $title;
    private string $description;
    private string $apiUrl;
    private string $apiKey;
    private string $secretKey;
    private string $completedOrderStatus;
    private string $loggingLevel;
    private array $rawSettings;

    /**
     * Конструктор.
     *
     * @param Gateway $gateway Объект платежного шлюза, из которого извлекаются настройки
     */
    public function __construct(Gateway $gateway)
    {
        $this->rawSettings = $gateway->settings ?? [];

        $this->id = $gateway->id;
        $this->enabled = $this->getOption('enabled') === 'yes';
        $this->title = $this->getOption('title', __('Онлайн оплата', 'remote-online-payments'));
        $this->description = $this->getOption('description', __('Оплата через сторонний сервер', 'remote-online-payments'));
        $this->apiUrl = $this->getOption('api_url', '');
        $this->apiKey = $this->getOption('api_key', '');
        $this->secretKey = $this->getOption('secret_key', '');
        $this->completedOrderStatus = $this->getOption('completed_order_status', 'wc-processing');
        $this->loggingLevel = $this->getOption('logging_level', ApiConstants::LOG_LEVEL_INFO);
    }

    /**
     * Получает значение опции.
     *
     * @param string $key Ключ опции
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    private function getOption(string $key, $default = null)
    {
        return $this->rawSettings[$key] ?? $default;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getCompletedOrderStatus(): string
    {
        return $this->completedOrderStatus;
    }

    public function getLoggingLevel(): string
    {
        return $this->loggingLevel;
    }

    public function getRawSettingsArray(): array
    {
        return $this->rawSettings;
    }
}
