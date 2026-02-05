<?php
// Файл: includes/Service/Settings.php

declare(strict_types=1);

namespace Tochka\Woocommerce\Service;

use Tochka\Woocommerce\Gateway;

/**
 * DTO для хранения и предоставления настроек шлюза.
 * Инкапсулирует получение опций из `WC_Payment_Gateway`.
 */
final class Settings
{
    private string $id;
    private bool $enabled;
    private string $title;
    private string $description;
    private string $serverUrl;
    private string $login;
    private string $secretKey;
    private bool $cartEnable;
    private string $defaultVat;
    private string $defaultItemType;
    private bool $autoRedirect;
    private bool $ajaxCheckoutSupport;
    private string $completedOrderStatus;
    private string $loggingLevel;
    private array $rawSettings;

    /**
     * Конструктор.
     *
     * @param Gateway $gateway Объект платежного шлюза, из которого извлекаются настройки.
     */
    public function __construct(Gateway $gateway)
    {
        $this->rawSettings = $gateway->settings;

        $this->id = $gateway->id;
        $this->enabled = $this->getOption('enabled') === 'yes';
        $this->title = $this->getOption('title', __('Банковские карты и СБП', 'tochka-bank-internet-acquiring'));
        $this->description = $this->getOption('description');
        $this->serverUrl = $this->getOption('tochka_server_url', 'https://merch.tochka.com/woocommerce');
        $this->login = $this->getOption('login');
        $this->secretKey = $this->getOption('secret_key');
        $this->cartEnable = $this->getOption('cart_enable') === 'yes';
        $this->defaultVat = $this->getOption('default_vat', 'none');
        $this->defaultItemType = $this->getOption('default_fiscal_item_type', 'goods');
        $this->autoRedirect = $this->getOption('auto_redirect') === 'yes';
        $this->ajaxCheckoutSupport = $this->getOption('ajax_checkout_support') === 'yes';
        $this->completedOrderStatus = $this->getOption('completed_order_status', 'wc-processing');
        $this->loggingLevel = $this->getOption('logging_level', 'INFO');
    }

    private function getOption(string $key, $default = null)
    {
        return $this->rawSettings[$key] ?? $default;
    }

    public function getId(): string { return $this->id; }
    public function isEnabled(): bool { return $this->enabled; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getServerUrl(): string { return $this->serverUrl; }
    public function getLogin(): string { return $this->login; }
    public function getSecretKey(): string { return $this->secretKey; }
    public function isCartEnable(): bool { return $this->cartEnable; }
    public function getDefaultVat(): string { return $this->defaultVat; }
    public function getDefaultItemType(): string { return $this->defaultItemType; }
    public function isAutoRedirect(): bool { return $this->autoRedirect; }
    public function isAjaxCheckoutSupport(): bool { return $this->ajaxCheckoutSupport; }
    public function getCompletedOrderStatus(): string { return $this->completedOrderStatus; }
    public function getLoggingLevel(): string { return $this->loggingLevel; }
    public function getRawSettingsArray(): array { return $this->rawSettings; }

}