<?php
// Файл: includes/Bootstrap.php

declare(strict_types=1);

namespace Tochka\Woocommerce;

/**
 * Главный класс-загрузчик плагина.
 * Отвечает за инициализацию всех компонентов и регистрацию хуков.
 */
final class Bootstrap
{
    /**
     * @var string Уникальный идентификатор (ID) платежного шлюза.
     */
    public const GATEWAY_ID = 'tochka_payments';

    /**
     * @var Bootstrap|null Статический экземпляр класса.
     */
    private static ?Bootstrap $instance = null;

    /**
     * Приватный конструктор.
     */
    private function __construct()
    {
        add_action('plugins_loaded', [$this, 'init']);
    }

    /**
     * Инициализирует плагин после загрузки всех зависимостей.
     */
    public function init(): void
    {
        if (!class_exists('WooCommerce')) {
            return;
        }

        $hooks_manager = new HooksManager();
        $hooks_manager->init_hooks();
    }

    /**
     * Выполняется при активации плагина.
     * Регистрирует эндпоинт и сбрасывает правила перезаписи URL.
     */
    public static function activate(): void
    {
        add_rewrite_endpoint(Service\ApiConstants::ENDPOINT_AJAX, EP_ROOT);
        flush_rewrite_rules();
    }

    /**
     * Возвращает единственный экземпляр класса.
     *
     * @return Bootstrap
     */
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}