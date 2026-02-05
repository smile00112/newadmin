<?php
// Файл: includes/BlocksSupport.php

declare(strict_types=1);

namespace Tochka\Woocommerce;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Интеграция с WooCommerce Blocks.
 */
final class BlocksSupport extends AbstractPaymentMethodType {

    protected $name = Bootstrap::GATEWAY_ID;
    /**
     * Экземпляр нашего платежного шлюза.
     *
     * @var Gateway|null
     */
    private ?Gateway $gateway = null;

    public function initialize(): void
    {
        $this->settings = get_option('woocommerce_' . $this->name . '_settings', []);
        $gateways = WC()->payment_gateways()->payment_gateways();
        if (isset($gateways[$this->name])) {
            $this->gateway = $gateways[$this->name];
        }
    }

    public function is_active(): bool
    {
        return $this->gateway && $this->gateway->is_available();
    }

    public function get_payment_method_script_handles(): array
    {
        $asset_path = TOCHKA_PLUGIN_PATH . 'assets/js/blocks.asset.php';
        $asset      = file_exists($asset_path) ? require $asset_path : ['dependencies' => [], 'version' => TOCHKA_VERSION];

        wp_register_script(
            'tochka-blocks',
            TOCHKA_PLUGIN_URL . 'assets/js/blocks.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_localize_script(
            'tochka-blocks',
            'tochka_payments_settings',
            $this->get_payment_method_data()
        );

        return ['tochka-blocks'];
    }

    public function get_payment_method_data(): array
    {
        if (!$this->gateway) {
            $this->initialize();
        }

        if (!$this->gateway) {
            return [
                'title'       => 'Error',
                'description' => 'Gateway not found.',
                'supports'    => [],
                'name'        => $this->name,
                'icon'        => ''
            ];
        }

        return [
            'title'       => $this->get_setting('title'),
            'description' => $this->get_setting('description'),
            'supports'    => $this->gateway->supports,
            'name'        => $this->name,
            'icon'        => $this->gateway->get_icon()
        ];
    }
}