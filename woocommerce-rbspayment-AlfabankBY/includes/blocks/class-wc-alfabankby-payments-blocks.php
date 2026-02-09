<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
/**
* RBSPaymentAlfabankBY Payments Blocks integration
*
* @since 1.0.3
*/
final class WC_Gateway_RBSPaymentAlfabankBY_Blocks_Support extends AbstractPaymentMethodType {
/**
* The gateway instance.
*
* @var WC_Gateway_RBSPaymentAlfabankBY
*/
private $gateway;
/**
* Payment method name/id/slug.
*
* @var string
*/
protected $name = 'alfabankby';
/**
* Initializes the payment method type.
*/
public function initialize() {
$this->settings = get_option( 'woocommerce_alfabankby_settings', [] );
$gateways       = WC()->payment_gateways->payment_gateways();
$this->gateway  = $gateways[$this->name];
}
/**
* Returns if this payment method should be active. If false, the scripts will not be enqueued.
*
* @return boolean
*/
public function is_active() {
return $this->gateway->is_available();
}
/**
* Returns an array of scripts/handles to be registered for this payment method.
*
* @return array
*/
public function get_payment_method_script_handles() {
$script_path       = '/assets/js/frontend/blocks.js';
$script_asset_path = WC_RBSPaymentAlfabankBY_Payments::plugin_abspath() . 'assets/js/frontend/blocks.asset.php';
$script_asset      = file_exists( $script_asset_path )
? require( $script_asset_path )
: array(
'dependencies' => array(),
'version'      => '1.2.' . time()
);
$script_url        = WC_RBSPaymentAlfabankBY_Payments::plugin_url() . $script_path;
wp_register_script(
'wc-alfabankby-payments-blocks',
$script_url,
$script_asset[ 'dependencies' ], //false
$script_asset[ 'version' ], //null
true
);
wp_localize_script(
'wc-alfabankby-payments-blocks',
'alfabankby_data',
array(
'logo_url'    => WC_RBSPaymentAlfabankBY_Payments::plugin_url() . '/assets/images/logo.png',
'title'       => get_option( 'woocommerce_alfabankby_title', 'RBSPaymentAlfabankBY' ),
'description' => get_option( 'woocommerce_alfabankby_description', '' ),
)
);
if ( function_exists( 'wp_set_script_translations' ) ) {
wp_set_script_translations( 'wc-alfabankby-payments-blocks', 'woocommerce-gateway-alfabankby', WC_RBSPaymentAlfabankBY_Payments::plugin_abspath() . 'languages/' );
}
return [ 'wc-alfabankby-payments-blocks' ];
}
/**
* Returns an array of key=>value pairs of data made available to the payment methods script.
*
* @return array
*/
public function get_payment_method_data() {
return [
'title'       => $this->get_setting( 'title' ),
'description' => $this->get_setting( 'description' ),
'supports'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] )
];
}
}
