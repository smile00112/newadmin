<?php
/**
 * Plugin Name:       Remote Online Payments
 * Description:       Платежный шлюз для проведения онлайн оплаты на стороннем сервере.
 * Author:            Dolinger
 * Version:           1.0.0
 * Requires at least: 6.0
 * Tested up to:      6.9
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   10.1.2
 * Text Domain:       remote-online-payments
 * Requires Plugins:  woocommerce
 * Domain Path:       /languages
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

use RemoteOnlinePayments\Bootstrap;

if (!defined('ABSPATH')) {
    exit;
}

// Подключаем автозагрузчик Composer
require_once __DIR__ . '/vendor/autoload.php';

// Определяем основные константы плагина
const REMOTE_PAYMENTS_PLUGIN_PATH = __DIR__ . '/';
define('REMOTE_PAYMENTS_PLUGIN_URL', plugin_dir_url(__FILE__));
const REMOTE_PAYMENTS_VERSION = '1.0.0';

register_activation_hook(__FILE__, [Bootstrap::class, 'activate']);

/**
 * Главная функция-загрузчик плагина.
 * @return Bootstrap
 */
function remote_online_payments(): Bootstrap
{
    return Bootstrap::instance();
}

// Запускаем плагин
remote_online_payments();
