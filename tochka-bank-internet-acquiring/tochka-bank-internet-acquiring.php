<?php
/**
 * Plugin Name:       Tochka Bank: Internet-acquiring
 * Description:       Payment gateway for Tochka Bank in WooCommerce.
 * Author:            Tochka
 * Author URI:        https://tochka.com/
 * Version:           1.0.0
 * Requires at least: 6.0
 * Tested up to:      6.9
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   10.1.2
 * Text Domain:       tochka-bank-internet-acquiring
 * Requires Plugins:  woocommerce
 * Domain Path:       /languages
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

use Tochka\Woocommerce\Bootstrap;

if (!defined('ABSPATH')) {
    exit;
}

// 1. Подключаем автозагрузчик Composer
require_once __DIR__ . '/vendor/autoload.php';

// 2. Определяем основные константы плагина
const TOCHKA_PLUGIN_PATH = __DIR__ . '/';
define("TOCHKA_PLUGIN_URL", plugin_dir_url(__FILE__));
const TOCHKA_VERSION = '1.0.0';

register_activation_hook(__FILE__, [Bootstrap::class, 'activate']);

/**
 * Главная функция-загрузчик плагина.
 * @return Bootstrap
 */
function tochka_payments(): Bootstrap
{
    return Bootstrap::instance();
}

// 3. Запускаем плагин.
tochka_payments();