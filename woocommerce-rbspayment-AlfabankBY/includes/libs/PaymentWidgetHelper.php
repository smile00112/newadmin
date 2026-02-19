<?php
namespace WoocommerceRBSPaymentAlfabankBY\Includes\Libs;
class PaymentWidgetHelper {
private $gateway;
private $id;
private $method_title;
private $merchant;
private $module_version;
private $test_mode;
private $pay_buttons_url = "";
public function __construct($gateway = null) {
$this->gateway = $gateway;
$this->id = $gateway->id ?? 'google_pay';
$this->method_title = $gateway->method_title ?? 'Payment Widget';
$this->merchant = $gateway->merchant ?? '';
$this->module_version = $gateway->module_version ?? '1.0';
$this->test_mode = $gateway->test_mode ?? 'TEST';
if ($this->gateway->get_option('payment_widget_environment') == "TEST") {
$this->pay_buttons_url = $this->get_pay_buttons_url(RBSPAYMENT_ALFABANKBY_TEST_URL);
} else {
$this->pay_buttons_url = $this->get_pay_buttons_url(RBSPAYMENT_ALFABANKBY_PROD_URL);
}
}
function add_actions($gateway) {
if (!empty($gateway)) {
add_action('wp_enqueue_scripts', array($this, 'add_payment_widget_script'), 11);
add_action('wp_footer', array($this, 'add_payment_widget_button_on_checkout_page'), 15);
add_action('woocommerce_after_add_to_cart_button', array($this, 'add_payment_widget_button_on_product_page'), 14);
add_action('wp_ajax_process_payment_widget', array($this, 'handle_payment_widget_ajax'));
add_action('wp_ajax_nopriv_process_payment_widget', array($this, 'handle_payment_widget_ajax'));
add_filter('script_loader_tag', function($tag, $handle) {
if ($handle === 'alfabankby-payment-widget') {
return '<script id="pay-buttons" src="' . esc_url($this->pay_buttons_url) . '" defer></script>';
}
return $tag;
}, 10, 2);
}
}
function handle_payment_widget_ajax() {
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
if (!$product_id) {
wp_send_json_error(['message' => 'Incorrect request data']);
}
$user_id = get_current_user_id();
$existing_order = null;
if ($user_id) {
$orders = wc_get_orders([
'customer_id'  => $user_id,
'status'       => ['pending', 'on-hold'],
'limit'        => 10,
'orderby'      => 'date',
'order'        => 'DESC',
'date_created' => '>' . (time() - HOUR_IN_SECONDS),
]);
foreach ($orders as $order) {
$items = $order->get_items();
if (count($items) !== 1) {
continue;
}
/** @var WC_Order_Item_Product $item */
$item = reset($items);
if ((int) $item->get_product_id() !== (int) $product_id) {
continue;
}
if ((int) $item->get_quantity() !== 1) {
continue;
}
$order_total   = (float) $order->get_total();
$product_price = (float) wc_get_product($product_id)->get_price();
if (abs($order_total - $product_price) > 0.01) {
continue;
}
$existing_order = $order;
break;
}
}
if ($existing_order) {
$order_id = $existing_order->get_id();
$order    = $existing_order;
} else {
$payment_method = $this->id;
$products = [
1 => [$product_id, 1],
];
$product = wc_get_product($product_id);
$product_price = $product->get_price();
$mark_paid = false;
$order_id = $this->gateway->create_woocommerce_order(
$user_id,
$payment_method,
$products,
$mark_paid,
[]
);
if (is_wp_error($order_id)) {
wp_send_json_error(['message' => $order_id->get_error_message()]);
} else {
}
$order = wc_get_order($order_id);
}
$orderNumber = $this->gateway->get_order_number_for_gateway($order);
$currency_code = $order->get_currency();
wp_send_json_success([
'merchantLogin' => substr($this->merchant, 0, -4),
'merchantToken' => $this->gateway->get_option('payment_widget_merchant_token'),
'orderId'     => $order_id,
'orderNumber' => $orderNumber,
'amount'      => $order->get_total() * 100,
'currency'    => $this->gateway->get_numeric_currency_code($currency_code),
'returnUrl'   => get_option('siteurl') . '?wc-api=alfabankby&action=result&order_id=' . $order_id,
'dynamicCallbackUrl'   => get_option('siteurl') . '?wc-api=alfabankby' . '&action=callback&dynamic=1&order_id=' . $order_id,
'siteDomain' => preg_replace('#^https?://#', '', get_option('siteurl'))
]);
die;
}
function add_payment_widget_button_on_product_page() {
$payment_sections = (array) $this->gateway->get_option('payment_widget_sections');
$payment_buttons = (array) $this->gateway->get_option('payment_widget_buttons');
if (is_product()
&& in_array('PRODUCT_PAGE', $payment_sections)
&& is_user_logged_in()
) {
global $post;
$product = wc_get_product( $post->ID );
if ( $product instanceof WC_Product_Subscription ) {
return;
} elseif ( $product instanceof WC_Product_Variation ) {
return;
} elseif ( $product instanceof WC_Product_Variable ) {
return;
} elseif ( $product instanceof WC_Product_Simple ) {
}
?>
<div id="payment_widget-container" style="margin-top: 20px;"></div>
<script>
document.addEventListener("DOMContentLoaded", function () {
var widget = payButtonsWidget("payment_widget-container");
let preparedGatewayInfo = null;
let prepareErrorMessage = null;
function preparePaymentData() {
jQuery.ajax({
url: "<?php echo admin_url('admin-ajax.php');?>",
type: 'POST',
data: {
action: 'process_payment_widget',
product_id: <?php echo $product->get_id(); ?>
},
success: function(response) {
if (!response.success || !response.data || !response.data.orderNumber) {
prepareErrorMessage =
response.data?.message ||
'Failed to create the order. Please try again.';
preparedGatewayInfo = null;
} else {
preparedGatewayInfo = response.data;
prepareErrorMessage = null;
}
},
error: function() {
prepareErrorMessage =
'AJAX error while creating the order. Please try again.';
preparedGatewayInfo = null;
}
});
}
preparePaymentData();
widget.init({
gatewayInfo: {
token: "<?php echo $this->gateway->get_option('payment_widget_merchant_token'); ?>",
amount: 100,
merchantLogin: "<?php echo substr($this->merchant, 0, -4);?>",
},
onClick: function (gatewayInfo) {
if (prepareErrorMessage) {
alert(prepareErrorMessage);
return gatewayInfo; // fallback
}
if (!preparedGatewayInfo) {
alert('Payment is not ready. Please try again.');
return gatewayInfo; // fallback
}
gatewayInfo.merchantLogin = preparedGatewayInfo.merchantLogin;
gatewayInfo.token = preparedGatewayInfo.merchantToken;
gatewayInfo.orderNumber = preparedGatewayInfo.orderNumber;
gatewayInfo.currency = preparedGatewayInfo.currency;
gatewayInfo.amount = preparedGatewayInfo.amount;
gatewayInfo.returnUrl = preparedGatewayInfo.returnUrl;
gatewayInfo.dynamicCallbackUrl = preparedGatewayInfo.dynamicCallbackUrl;
return gatewayInfo;
},
<?php if(in_array('applePay', $payment_buttons)): ?>
applePay: { // Information for ApplePay session
merchantId: "<?php echo preg_replace('#^https?://#', '', get_option('siteurl')); ?>", // id of the merchant in Apple
},
<?php endif; ?>
<?php if(in_array('googlePay', $payment_buttons)): ?>
googlePay: {
environment: "<?php echo $this->gateway->get_option('payment_widget_environment'); ?>",
},
<?php endif; ?>
});
});
</script>
<?php
}
}
function add_payment_widget_button_on_checkout_page() {
$payment_sections = (array) $this->gateway->get_option('payment_widget_sections');
$payment_buttons = (array) $this->gateway->get_option('payment_widget_buttons');
if (
!empty($this->gateway->get_option('payment_widget_merchant_token'))
&& is_checkout()
&& !is_order_received_page()
&& in_array('CHECKOUT_PAGE', $payment_sections)
) {
$order_id = WC()->session->get('store_api_draft_order');
$order_number = '';
$order_amount = 0;
if ($order_id) {
$order = wc_get_order($order_id);
if ($order) {
$order_number = $order->get_order_number() . "_" . time();
$order_amount = $order->get_total() * 100;
}
$returnUrl = get_option('siteurl') . '?wc-api=alfabankby' . '&action=result&order_id=' . $order_id;
$dynamicCallbackUrl  = get_option('siteurl') . '?wc-api=alfabankby' . '&action=callback&dynamic=1&order_id=' . $order_id;
$currency_code = $order->get_currency();
}
?>
<div id="payment_widget-container" style="margin-top: 20px;"></div>
<script>
document.addEventListener("DOMContentLoaded", function () {
const container = document.getElementById('payment_widget-container');
const totalsWrappers = document.querySelectorAll('.wp-block-woocommerce-checkout-order-summary-block');
const lastTotalsWrapper = totalsWrappers[totalsWrappers.length - 1];
if (container && lastTotalsWrapper) {
lastTotalsWrapper.parentNode.insertBefore(
container,
lastTotalsWrapper.nextSibling
);
}
var widget = payButtonsWidget("payment_widget-container");
widget.init({
gatewayInfo: {
token: "<?php echo $this->gateway->get_option('payment_widget_merchant_token'); ?>",
orderNumber: "<?php echo esc_js($order_number); ?>",
amount: "<?php echo intval($order_amount); ?>",
currency: "<?php echo $this->gateway->get_numeric_currency_code($currency_code);?>",
returnUrl: "<?php echo esc_js($returnUrl);?>",
dynamicCallbackUrl: "<?php echo esc_js($dynamicCallbackUrl);?>",
merchantLogin: "<?php echo substr($this->merchant, 0, -4);?>",
},
<?php if(in_array('applePay', $payment_buttons)): ?>
applePay: { // Information for ApplePay session
merchantId: "<?php echo preg_replace('#^https?://#', '', get_option('siteurl')); ?>", // id of the merchant in Apple
},
<?php endif; ?>
<?php if(in_array('googlePay', $payment_buttons)): ?>
googlePay: {
environment: "<?php echo $this->gateway->get_option('payment_widget_environment'); ?>",
},
<?php endif; ?>
});
});
</script>
<?php
}
}
function add_payment_widget_script() {
if ( !empty($this->gateway->get_option('payment_widget_merchant_token')) && (is_product() || is_checkout()) ) {
wp_enqueue_script(
'alfabankby-payment-widget',
$this->pay_buttons_url,
array(),
null,
true
);
}
}
static function getPaymentWidgetFields($id) {
return array(
'payment_widget_options' => array(
'type' => 'title',
'css' => 'font-size: 1.4em;',
'title' => __('Patment Widget settings', 'wc-' . $id . '-text-domain'),
'description' => __('Configure the integration options for Google Pay, including mode and credentials.', 'wc-' . $id . '-text-domain'),
),
'payment_widget_merchant_token' => array(
'title' => __('Payment Widget merchant token', 'wc-' . $id . '-text-domain'),
'type' => 'text',
'default' => '',
'description' => __('If not specified, the button will not be displayed.', 'wc-' . $id . '-text-domain'),
),
'payment_widget_sections' => array(
'title'       => __('Payment Sections', 'wc-' . $id . '-text-domain'),
'type'        => 'multiselect',
'class'       => 'wc-enhanced-select',
'options'     => array(
'PRODUCT_PAGE'  => __('Product Page', 'wc-' . $id . '-text-domain'),
'CHECKOUT_PAGE' => __('Checkout Page', 'wc-' . $id . '-text-domain'),
),
'description' => __(
'Increase your conversion rate by offering Google Pay on your Product and Cart pages, or at the top of the checkout page. <br/>
Note: you can control which products display Google Pay by going to the product edit page.',
'wc-' . $id . '-text-domain'
),
'default'     => array('PRODUCT_PAGE', 'CHECKOUT_PAGE'),
),
'payment_widget_buttons' => array(
'title'       => __('Payment Buttons', 'wc-' . $id . '-text-domain'),
'type'        => 'multiselect',
'class'       => 'wc-enhanced-select',
'options'     => array(
'applePay'  => __('applePay', 'wc-' . $id . '-text-domain'),
'googlePay' => __('googlePay', 'wc-' . $id . '-text-domain'),
),
'default'     => array('applePay', 'googlePay'),
),
'payment_widget_environment' => array(
'title' => __('Environment', 'wc-' . $id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => 'TEST',
'options' => array(
'TEST' => __('TEST', 'wc-' . $id . '-text-domain'),
'PRODUCTION' => __('PRODUCTION', 'wc-' . $id . '-text-domain'),
),
'description' => __('Select the operation mode for Google Pay: TEST or PRODUCTION.', 'wc-' . $id . '-text-domain'),
'default' => 'no'
),
);
}
function get_pay_buttons_url(string $base_url): string {
$base_url = rtrim($base_url, '/');
if (str_ends_with($base_url, '/payment/rest')) {
return str_replace(
'/payment/rest',
'/payment/pay-buttons/pay-buttons.js',
$base_url
);
}
}
}
