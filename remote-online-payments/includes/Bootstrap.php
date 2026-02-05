<?php
// Файл: includes/Bootstrap.php

declare(strict_types=1);

namespace RemoteOnlinePayments;

/**
 * Главный класс-загрузчик плагина.
 * Отвечает за инициализацию всех компонентов и регистрацию хуков.
 */
final class Bootstrap
{
    /**
     * @var string Уникальный идентификатор (ID) платежного шлюза
     */
    public const GATEWAY_ID = 'online_remote';

    /**
     * @var Bootstrap|null Статический экземпляр класса
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
            add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
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
        // WooCommerce автоматически регистрирует эндпоинты через woocommerce_api_{gateway_id}
        // Но мы можем зарегистрировать дополнительные эндпоинты здесь, если нужно
        flush_rewrite_rules();
    }

    /**
     * Показывает уведомление об отсутствии WooCommerce.
     */
    public function woocommerce_missing_notice(): void
    {
        ?>
        <div class="error">
            <p>
                <strong><?php esc_html_e('Remote Online Payments', 'remote-online-payments'); ?></strong>
                <?php esc_html_e('требует установленного и активированного плагина WooCommerce.', 'remote-online-payments'); ?>
            </p>
        </div>
        <?php
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
