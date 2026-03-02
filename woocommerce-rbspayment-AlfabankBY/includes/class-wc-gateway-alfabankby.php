<?php
/**
* WC_Gateway_RBSPaymentAlfabankBY class
*/
use WoocommerceRBSPaymentAlfabankBY\Includes\FormFieldsGenerator;
if (!defined('ABSPATH')) {
exit;
}
/**
* RBSPaymentAlfabankBY Gateway.
* @class    WC_Gateway_RBSPaymentAlfabankBY
*/
class WC_Gateway_RBSPaymentAlfabankBY extends WC_Payment_Gateway
{
/**
* Payment gateway instructions.
* @var string
*
*/
protected $instructions;
/**
* Whether the gateway is visible for non-admin users.
* @var boolean
*
*/
public $id = 'alfabankby';
public $module_version = "5.5.1";
public $has_fields;
public $supports;
public $method_title;
public $method_description;
public $title;
public $description;
public $merchant;
public $password;
public $test_mode;
public $stage_mode;
public $order_status_paid;
public $send_order;
public $tax_system;
public $tax_type;
public $success_url;
public $fail_url;
public $backToShopUrl;
public $backToShopUrlName;
public $versionFfd;
public $paymentMethodType;
public $paymentObjectType;
public $paymentObjectType_delivery;
public $pData;
public $logging;
public $allowCallbacks;
public $callbackType = "DYNAMIC";
public $enable_for_methods;
public $test_url;
public $prod_url;
public $home_url;
public $fesHelper = null;
public $googlePayHelper = null;
public $fes_cashboxId;
protected function setup_properties()
{
$this->method_title = RBSPAYMENT_ALFABANKBY_PAYMENT_NAME;
$this->method_description = __('Allows customers to pay with bank cards through `AlfabankBY` in your WooCommerce store.', 'woocommerce-gateway-alfabankby');
$this->has_fields = false;
}
public $enable_GooglePay;
public $enable_PaymentWidget;
public $enable_saved_cards_payment;
public function __construct()
{
$this->setup_properties();
$this->supports = array(
'products',
);
if (defined('RBSPAYMENT_ALFABANKBY_ENABLE_REFUNDS') && RBSPAYMENT_ALFABANKBY_ENABLE_REFUNDS == true) {
$this->supports[] = 'refunds';
}
$this->init_form_fields();
$this->init_settings();
$this->title = $this->get_option('title');
$this->description = $this->get_option('description');
$this->instructions = $this->get_option('instructions', $this->description);
$this->merchant = $this->get_option('merchant');
$this->password = $this->get_option('password');
if (!empty($this->get_option('token'))) {
$decoded_credentials = base64_decode($this->get_option('token'));
list($l, $p) = explode(':', $decoded_credentials);
$this->merchant = $l;
$this->password = $p;
}
$this->test_mode = $this->get_option('test_mode');
$this->stage_mode = $this->get_option('stage_mode');
$this->description = $this->get_option('description');
$this->order_status_paid = $this->get_option('order_status_paid');
$this->send_order = $this->get_option('send_order');
$this->tax_system = $this->get_option('tax_system');
$this->tax_type = $this->get_option('tax_type');
$this->success_url = $this->get_option('success_url');
$this->fail_url = $this->get_option('fail_url');
$this->backToShopUrl = $this->get_option('backToShopUrl');
$this->backToShopUrlName = $this->get_option('backToShopUrlName');
$this->versionFfd = $this->get_option('versionFfd');
$this->paymentMethodType = $this->get_option('paymentMethodType');
$this->paymentObjectType = $this->get_option('paymentObjectType');
$this->paymentObjectType_delivery = $this->get_option('paymentMethodType_delivery');
$this->fes_cashboxId = $this->get_option('fes_cashboxId');
$this->pData = get_plugin_data(__FILE__);
$this->logging = RBSPAYMENT_ALFABANKBY_ENABLE_LOGGING;
$this->allowCallbacks = defined('RBSPAYMENT_ALFABANKBY_ENABLE_CALLBACK') ? RBSPAYMENT_ALFABANKBY_ENABLE_CALLBACK : true;
$this->callbackType = defined('RBSPAYMENT_ALFABANKBY_CALLBACK_TYPE') ? RBSPAYMENT_ALFABANKBY_CALLBACK_TYPE : $this->callbackType;
$this->enable_for_methods = $this->get_option('enable_for_methods', array());
$this->enable_GooglePay = defined('RBSPAYMENT_ALFABANKBY_ENABLE_FAST_CHECKOUT') ? RBSPAYMENT_ALFABANKBY_ENABLE_FAST_CHECKOUT : false;;
$this->enable_PaymentWidget = defined('RBSPAYMENT_ALFABANKBY_ENABLE_PAYMENT_WIDGET') ? RBSPAYMENT_ALFABANKBY_ENABLE_PAYMENT_WIDGET : false;;
$this->enable_saved_cards_payment = defined('RBSPAYMENT_ALFABANKBY_ENABLE_SAVED_CARDS_PAYMENT') ? RBSPAYMENT_ALFABANKBY_ENABLE_SAVED_CARDS_PAYMENT : false;;
$this->test_url = RBSPAYMENT_ALFABANKBY_TEST_URL;
$this->prod_url = RBSPAYMENT_ALFABANKBY_PROD_URL;
$this->home_url = defined('RBSPAYMENT_ALFABANKBY_HOME_URL') ? RBSPAYMENT_ALFABANKBY_HOME_URL : null;
add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
add_action('woocommerce_scheduled_subscription_payment_alfabankby', array($this, 'process_subscription_payment'), 10, 2);
add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
add_action('woocommerce_api_alfabankby', array($this, 'webhook_result'));
add_action('woocommerce_before_checkout_form', array($this, 'display_custom_error_message'), 12);
if (class_exists('WoocommerceRBSPaymentAlfabankBY\\Includes\\Libs\\PaymentWidgetHelper')) {
if (isset($this->enable_PaymentWidget)) {
$this->paymentWidgetHelper = new WoocommerceRBSPaymentAlfabankBY\Includes\Libs\PaymentWidgetHelper($this);
$this->paymentWidgetHelper->add_actions($this);
}
}
if (class_exists('WoocommerceRBSPaymentAlfabankBY\\Includes\\Libs\\GooglePayHelper')) {
if (isset($this->enable_GooglePay)) {
$this->googlePayHelper = new WoocommerceRBSPaymentAlfabankBY\Includes\Libs\GooglePayHelper($this);
$this->googlePayHelper->add_actions($this);
}
}
if ($this->enable_saved_cards_payment && $this->get_option('saved_cards_payment_enable') == 'yes') {
add_action('wp_enqueue_scripts', [$this, 'add_saved_cards_script']);
add_action('wp_ajax_alfabankby_show_saved_cards', [$this, 'alfabankby_show_saved_cards_callback']);
add_action('wp_ajax_nopriv_alfabankby_show_saved_cards', [$this, 'alfabankby_show_saved_cards_callback']);
add_filter('woocommerce_checkout_fields', function ($fields) {
$fields['billing']['alfabankby_saved_card_field'] = [
'type'     => 'hidden',
'required' => false,
];
return $fields;
});
add_action('wp_ajax_alfabankby_set_card', function() {
if (isset($_POST['card_id'])) {
WC()->session->set('alfabankby_session_selected_card', sanitize_text_field($_POST['card_id']));
}
wp_die();
});
add_action('wp_ajax_nopriv_alfabankby_set_card', function() {
if (isset($_POST['card_id'])) {
WC()->session->set('alfabankby_session_selected_card', sanitize_text_field($_POST['card_id']));
}
wp_die();
});
}
}
public function add_saved_cards_script() {
if (is_checkout()) {
wp_enqueue_script(
'alfabankby-saved-cards',
plugins_url('../assets/js/saved-cards.js', __FILE__),
['jquery', 'wp-data'], // wp-data для Blocks
time(),
true
);
$client_id = '';
if (is_user_logged_in()) {
$current_user = wp_get_current_user();
$client_email = $current_user->user_email;
$customer_id = $current_user->ID;
$client_id = md5($customer_id . $client_email . get_option('siteurl'));
}
wp_localize_script('alfabankby-saved-cards', 'alfabankby_plugin_ajax', [
'ajax_url' => admin_url('admin-ajax.php'),
'client_id' => $client_id
]);
}
}
public function alfabankby_show_saved_cards_callback() {
$clientId = isset($_POST['client_id']) ? sanitize_text_field($_POST['client_id']) : 0;
$data = http_build_query([
'userName'    => $this->merchant,
'password'    => $this->password,
'clientId'    => $clientId,
'bindingType' => 'C',
]);
if ($this->test_mode == 'yes') {
$action_adr = $this->test_url;
} else {
$action_adr = $this->prod_url;
}
$action_address_bindings = $action_adr . 'getBindings.do';
$headers = ['Content-Type: application/x-www-form-urlencoded'];
$jsonResponse = $this->_sendGatewayData($data, $action_address_bindings, $headers);
$response = json_decode($jsonResponse, true);
$bindings = isset($response['bindings']) ? $response['bindings'] : [];
$html = '';
if (!empty($bindings)) {
WC()->session->set('alfabankby_session_CMS_bindingsShown', count($bindings));
$html .= '<div class="alfabankby-saved-cards-wrapper">';
foreach ($bindings as $index => $b) {
$cardMask = esc_html($b['displayLabel']);
$cardType = esc_html($b['paymentSystem']);
$cardId = esc_attr($b['bindingId']);
$html .= '<label class="saved-card">';
$html .= '<input type="radio" name="alfabankby_saved_card_radio" value="'. $cardId .'"> ';
$html .= $cardMask . ' — ' . $cardType;
$html .= '</label><br>';
}
$html .= '</div>';
echo $html;
}
wp_die();
}
public function display_custom_error_message()
{
if (WC()->session->get('custom_error_message')) {
wc_print_notices();
WC()->session->__unset('custom_error_message');
}
}
public function init_form_fields()
{
$shipping_methods = array();
if (is_admin())
foreach (WC()->shipping()->load_shipping_methods() as $method) {
$shipping_methods[$method->id] = $method->get_method_title();
}
require_once 'form-fields.php';
$this->form_fields = WoocommerceRBSPaymentAlfabankBY\Includes\FormFieldsGenerator::generate($this->id);
}
public function admin_options() {
if (!empty($this->home_url)) {
$currentVersion = $this->module_version;
$remoteVersion = @file_get_contents($this->home_url  . 'VERSION');
if ($remoteVersion
&& $this->compare_major_minor($currentVersion, $remoteVersion) < 0
) {
echo '<div style="padding:10px; background:#fff3cd; border-left:4px solid #ff9900; margin:10px 0;">';
printf(
__('<strong>A new version is available:</strong> %1$s', 'wc-' . $this->id . '-text-domain') . '<br>' .
__('You are currently using version <strong>%2$s</strong>, but version <strong>%1$s</strong> is available.', 'wc-' . $this->id . '-text-domain') . '<br>' .
wp_kses(__('Please <a href="%3$s">download the latest version here</a>.', 'wc-' . $this->id . '-text-domain'), ['a' => ['href' => [], 'target' => []]]),
esc_html($remoteVersion),
esc_html($currentVersion),
esc_url($this->home_url . 'wp_woocommerce.zip')
);
echo '</div>';
}
}
parent::admin_options();
}
public function is_available() {
if (!parent::is_available()) {
return false;
}
$order_total = 0;
if (WC()->cart) {
$order_total = WC()->cart->total;
}
$min_total = $this->get_option('min_order_total');
$max_total = $this->get_option('max_order_total');
if ($min_total !== '' && is_numeric($min_total) && $order_total < (float)$min_total) {
return false;
}
if ($max_total !== '' && is_numeric($max_total) && $order_total > (float)$max_total) {
return false;
}
$allowed_categories    = (array) $this->get_option('allowed_category', []);
$disallowed_categories = (array) $this->get_option('disallowed_category', []);
if (!empty($allowed_categories) || !empty($disallowed_categories)) {
$in_cart_cats = [];
if (function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) {
foreach (WC()->cart->get_cart() as $cart_item) {
$product_id   = $cart_item['product_id'];
$product_cats = wc_get_product_terms($product_id, 'product_cat', ['fields' => 'ids']);
foreach ($product_cats as $cat_id) {
$in_cart_cats[] = $cat_id;
$parents = get_ancestors($cat_id, 'product_cat');
if (!empty($parents)) {
$in_cart_cats = array_merge($in_cart_cats, $parents);
}
}
}
$in_cart_cats = array_unique($in_cart_cats);
if (!empty($allowed_categories) && empty(array_intersect($in_cart_cats, $allowed_categories))) {
return false;
}
if (!empty($disallowed_categories) && !empty(array_intersect($in_cart_cats, $disallowed_categories))) {
return false;
}
}
}
return true;
}
public function process_admin_options()
{
if ($this->allowCallbacks == false) {
return parent::process_admin_options();
}
if (isset($_POST['woocommerce_alfabankby_test_mode'])) {
$action_adr = $this->test_url;
$gate_url = str_replace("payment/rest", "mportal/mvc/public/merchant/update", $action_adr);
} else {
$action_adr = $this->prod_url;
$gate_url = str_replace("payment/rest", "mportal/mvc/public/merchant/update", $action_adr);
}
$gate_url .= substr($this->merchant, 0, -4);
$callback_addresses_string = "";
if ($this->callbackType != "DYNAMIC") {
$callback_addresses_string = get_option('siteurl') . '?wc-api=alfabankby' . '&action=callback';
}
if ($this->allowCallbacks == true) {
$response = $this->_updateGatewayCallback($this->merchant, $this->password, $gate_url, $callback_addresses_string);
if (RBSPAYMENT_ALFABANKBY_ENABLE_LOGGING === true) {
$this->writeLog("REQUEST:\n" . $gate_url . "\n[callback_addresses_string]: " . $callback_addresses_string . "\nRESPONSE:\n" . $response);
}
}
parent::process_admin_options();
}
public function _updateGatewayCallback($login, $password, $action_address, $callback_addresses_string = "")
{
$headers = array(
'Content-Type:application/json',
'Authorization: Basic ' . base64_encode($login . ":" . $password)
);
$data['callbacks_enabled'] = true;
$data['callback_type'] = $this->callbackType;
if (!empty($callback_addresses_string)) {
$data['callback_addresses'] = $callback_addresses_string;
}
$data['callback_http_method'] = "GET";
$data['callback_operations'] = "deposited,approved,declinedByTimeout,reversed,refunded";
$response = $this->_sendGatewayData(json_encode($data), $action_address, $headers);
return $response;
}
public function _sendGatewayData($data, $action_address, $headers = array())
{
$curl_opt = array(
CURLOPT_VERBOSE => true,
CURLOPT_SSL_VERIFYHOST => false,
CURLOPT_URL => $action_address,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_POST => true,
CURLOPT_POSTFIELDS => $data,
CURLOPT_HEADER => true
);
if (!empty($headers) && is_array($headers)) {
$curl_opt[CURLOPT_HTTPHEADER] = $headers;
}
$ssl_verify_peer = false;
$curl_opt[CURLOPT_SSL_VERIFYPEER] = $ssl_verify_peer;
$ch = curl_init();
curl_setopt_array($ch, $curl_opt);
$response = curl_exec($ch);
if ($response === false) {
$this->writeLog("The payment gateway is returning an empty response.");
}
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);
return substr($response, $header_size);
}
function writeLog($var, $info = true)
{
if ($this->test_mode != "yes") {
}
$information = "";
if ($var) {
if ($info) {
$information = "\n\n";
$information .= str_repeat("-=", 64);
$information .= "\nDate: " . date('Y-m-d H:i:s');
$information .= "\nWordpress version " . get_bloginfo('version') . "; Woocommerce version: " . wpbo_get_woo_version_number() . "\n";
}
$result = $var;
if (is_array($var) || is_object($var)) {
$result = "\n" . print_r($var, true);
}
$result .= "\n\n";
$path = dirname(__FILE__) . '/../logs/wc_alfabankby_' . date('Y-m') . '.log';
error_log($information . $result, 3, $path);
return true;
}
return false;
}
public function process_payment($order_id)
{
$order = wc_get_order($order_id);
if (!empty($_GET['pay_for_order']) && $_GET['pay_for_order'] == 'true') {
$this->generate_form($order_id);
exit();
}
$pay_now_url = $order->get_checkout_payment_url(true);
return array(
'result' => 'success',
'redirect' => $pay_now_url
);
}
public function generate_form($order_id)
{
$order = wc_get_order($order_id);
$amount = $order->get_total() * 100;
$coupons = array();
global $woocommerce;
if (!empty($woocommerce->cart->applied_coupons)) {
foreach ($woocommerce->cart->applied_coupons as $code) {
$coupons[] = new WC_Coupon($code);
}
}
if ($this->test_mode == 'yes') {
$action_adr = $this->test_url;
} else {
$action_adr = $this->prod_url;
}
if ($this->stage_mode == 'two-stage') {
$action_adr_register = $action_adr . 'registerPreAuth.do';
} else if ($this->stage_mode == 'one-stage') {
$action_adr_register = $action_adr . 'register.do';
}
$order_data = $order->get_data();
$language = substr(get_bloginfo("language"), 0, 2);
switch ($language) {
case  ('uk'):
$language = 'ua';
break;
case ('be'):
$language = 'by';
break;
}
$jsonParams = array(
'CMS' => 'Wordpress ' . get_bloginfo('version') . " + woocommerce version: " . wpbo_get_woo_version_number(),
'Module-Version' => $this->module_version,
);
if ($this->enable_GooglePay) {
$jsonParams['CMS_googlePayEnabled'] = (
!empty($this->get_option('google_pay_merchantId')) &&
$this->get_option('google_pay_mode') === "PRODUCTION"
);
}
#BLOCK_PHONE_TRANSFER_START[builder]
if (!empty($order_data['billing']['phone'])) {
$jsonParams['phone'] = $this->cleanPhoneNumber($order_data['billing']['phone']);
}
#BLOCK_PHONE_TRANSFER_END
if (class_exists('CRB')) {
$crb = new CRB();
$crbParams = $crb->processTaxItems($order);
$jsonParams = array_merge($jsonParams, $crbParams);
}
if (defined('RBSPAYMENT_ALFABANKBY_ENABLE_BACK_URL_SETTINGS')
&& RBSPAYMENT_ALFABANKBY_ENABLE_BACK_URL_SETTINGS === true
&& !empty($this->backToShopUrl)
) {
$jsonParams['backToShopUrl'] = $this->backToShopUrl;
}
if (class_exists('WoocommerceRBSPaymentAlfabankBY\\Includes\\Libs\\DiscountHelper')) {
if (!isset($this->fesHelper)) {
$this->fesHelper = new WoocommerceRBSPaymentAlfabankBY\Includes\Libs\FesHelper();
}
if (!empty($this->fesHelper) && !empty($this->fes_cashboxId)) {
$jsonParams['fes_cashboxId'] = $this->fes_cashboxId;
}
}
$args = array(
'userName' => $this->merchant,
'password' => $this->password,
'amount' => $amount,
);
#BLOCK_PHONE_TRANSFER_START[builder]
if (!empty($order_data['billing']['phone'])) {
$args['orderPayerData'] = json_encode(array(
"mobilePhone" => $this->cleanPhoneNumber($order_data['billing']['phone'])
));
}
#BLOCK_PHONE_TRANSFER_END
if (defined('RBSPAYMENT_ALFABANKBY_MANDATORY_CURRENCY') && RBSPAYMENT_ALFABANKBY_MANDATORY_CURRENCY === true) {
$currency_code = $order->get_currency();
$numeric_code = $this->get_numeric_currency_code($currency_code);
if (!empty($numeric_code)) {
$args['currency'] = $numeric_code;
}
}
if (defined('RBSPAYMENT_ALFABANKBY_SEND_CLIENT_FULL_INFO') && RBSPAYMENT_ALFABANKBY_SEND_CLIENT_FULL_INFO === true) {
$billingPayerData = $this->_getBillingPayerData($order_data);
if (!empty($billingPayerData)) {
$args['billingPayerData'] = json_encode($billingPayerData);
}
}
if (!empty($order_data['customer_id'] && $order_data['customer_id'] > 0)) {
$client_email = !empty($order_data['billing']['email']) ? $order_data['billing']['email'] : "";
$args['clientId'] = md5($order_data['customer_id'] . $client_email . get_option('siteurl'));
}
if ($this->allowCallbacks && $this->callbackType == "DYNAMIC") {
$args['dynamicCallbackUrl'] = get_option('siteurl') . '?wc-api=alfabankby' . '&action=callback&dynamic=1&order_id=' . $order_id;
}
if (defined('RBSPAYMENT_ALFABANKBY_ENABLE_CART_OPTIONS') && RBSPAYMENT_ALFABANKBY_ENABLE_CART_OPTIONS == true && $this->send_order == 'yes') {
$args['taxSystem'] = $this->tax_system;
$order_bundle = $this->_createOrderBundle($order);
if (class_exists('WoocommerceRBSPaymentAlfabankBY\\Includes\\Libs\\DiscountHelper')) {
$discountHelper = new \WoocommerceRBSPaymentAlfabankBY\Includes\Libs\DiscountHelper();
$discount = $discountHelper->discoverDiscount($args['amount'], $order_bundle['cartItems']['items']);
if ($discount != 0) {
$discountHelper->setOrderDiscount($discount);
$recalculatedPositions = $discountHelper->normalizeItems($order_bundle['cartItems']['items']);
$recalculatedAmount = $discountHelper->getResultAmount();
$order_bundle['cartItems']['items'] = $recalculatedPositions;
}
}
if (!empty($order_bundle)) {
$args['orderBundle'] = json_encode($order_bundle);
}
}
$order_number = method_exists($order, 'get_order_number')
? $order->get_order_number()
: $order->get_id();
$args['orderNumber'] = $this->get_order_number_for_gateway($order);
$args['returnUrl'] = get_option('siteurl') . '?wc-api=alfabankby' . '&action=result&order_id=' . $order_number;
$jsonParams['CMS_paymentType'] = "redirect";
if ($this->enable_saved_cards_payment && $this->get_option('saved_cards_payment_enable') == 'yes') {
$selected_card = WC()->session->get('alfabankby_session_selected_card');
if (!empty($selected_card)) {
$args['bindingId'] = $selected_card;
$jsonParams['CMS_paymentType'] = "saved_card";
$jsonParams['CMS_bindingsEnabled'] = "true";
}
}
$jsonParams['CMS_bindingsShown'] = (bool) WC()->session->get('alfabankby_session_CMS_bindingsShown');
$args['jsonParams'] = json_encode($jsonParams);
$response = $this->_sendGatewayData(http_build_query($args, '', '&'), $action_adr_register);
if (RBSPAYMENT_ALFABANKBY_ENABLE_LOGGING === true) {
$logData = $args;
$logData['password'] = '**removed from log**';
$this->writeLog("[REQUEST]: " . $action_adr_register . ": \nDATA: " . print_r($logData, true) . "\n[RESPONSE]: " . $response);
}
$response = json_decode($response, true);
if (empty($response['errorCode'])) {
if (RBSPAYMENT_ALFABANKBY_SKIP_CONFIRMATION_STEP == true) {
wp_redirect($response['formUrl']); //PLUG-4104 Comment this line for redirect via pressing button (step)
exit();
}
} else {
wc_add_notice(__('There was an error while processing payment', 'wc-' . $this->id . '-text-domain') . "<br/>ERRORCODE# " . $response['errorCode'] . " " . $response['errorMessage'], 'error');
wp_safe_redirect($order->get_checkout_payment_url());
exit();
return;
}
}
protected function _createOrderBundle($order)
{
$order_bundle = array();
$order_data = $order->get_data();
$order_items = $order->get_items();
$order_timestamp_created = $order_data['date_created']->getTimestamp();
$items = array();
$itemsCnt = 1;
foreach ($order_items as $value) {
$item = array();
$product_variation_id = $value['variation_id'];
if ($product_variation_id) {
$product = new WC_Product_Variation($value['variation_id']);
$item_code = $itemsCnt . "-" . $value['variation_id'];
} else {
$product = new WC_Product($value['product_id']);
$item_code = $itemsCnt . "-" . $value['product_id'];
}
$product_sku = get_post_meta($value['product_id'], '_sku', true);
$item_code = !empty($product_sku) ? $product_sku : $item_code;
$tax_type = $this->getTaxType($product);
$product_price = round((($value['total'] + $value['total_tax']) / $value['quantity']) * 100);
if ($product->get_type() == 'variation') {
}
$item['positionId'] = $itemsCnt++;
$item['name'] = $value['name'];
if ($this->versionFfd == 'v1_05') {
$item['quantity'] = array(
'value' => $value['quantity'],
'measure' => defined('RBSPAYMENT_ALFABANKBY_MEASUREMENT_NAME') ? RBSPAYMENT_ALFABANKBY_MEASUREMENT_NAME : 'pcs'
);
} else {
$item['quantity'] = array(
'value' => $value['quantity'],
'measure' => defined('RBSPAYMENT_ALFABANKBY_MEASUREMENT_CODE') ? RBSPAYMENT_ALFABANKBY_MEASUREMENT_CODE : '0'
);
}
$item['itemAmount'] = $product_price * $value['quantity'];
$item['itemCode'] = $item_code;
$item['tax'] = array('taxType' => $tax_type);
$item['itemPrice'] = $product_price;
if (!empty($this->fesHelper) && !empty($this->fes_cashboxId)) {
$tru_code = get_post_meta($value['product_id'], '_fes_truCode', true);
if (!empty($tru_code)) {
$item['itemDetails']['itemDetailsParams'][] = array("name" => "fes_truCode", "value" => $tru_code);
}
}
$attributes = array();
$attributes[] = array("name" => "paymentMethod", "value" => $this->paymentMethodType);
$attributes[] = array("name" => "paymentObject", "value" => $this->paymentObjectType);
$item['itemAttributes']['attributes'] = $attributes;
$items[] = $item;
}
$shipping_total = $order->get_shipping_total();
$shipping_tax = $order->get_shipping_tax();
if ($shipping_total > 0) {
$WC_Order_Item_Shipping = new WC_Order_Item_Shipping();
$itemShipment['positionId'] = $itemsCnt;
$itemShipment['name'] = __('Delivery', 'wc-' . $this->id . '-text-domain');
if ($this->versionFfd == 'v1_05') {
$itemShipment['quantity'] = array(
'value' => 1,
'measure' => defined('RBSPAYMENT_ALFABANKBY_MEASUREMENT_NAME') ? RBSPAYMENT_ALFABANKBY_MEASUREMENT_NAME : 'pcs'
);
} else {
$itemShipment['quantity'] = array(
'value' => 1,
'measure' => defined('RBSPAYMENT_ALFABANKBY_MEASUREMENT_CODE') ? RBSPAYMENT_ALFABANKBY_MEASUREMENT_CODE : '0'
);
}
$itemShipment['itemAmount'] = $itemShipment['itemPrice'] = $shipping_total * 100;
$itemShipment['itemCode'] = 'delivery';
$itemShipment['tax'] = array('taxType' => $this->getTaxType($WC_Order_Item_Shipping));
$attributes = array();
$attributes[] = array("name" => "paymentMethod", "value" => $this->paymentObjectType_delivery);
$attributes[] = array("name" => "paymentObject", "value" => 4);
$itemShipment['itemAttributes']['attributes'] = $attributes;
$items[] = $itemShipment;
}
$order_bundle['orderCreationDate'] = $order_timestamp_created;
$order_bundle['cartItems'] = array('items' => $items);
if (!empty($order_data['billing']['email'])) {
$order_bundle['customerDetails']['email'] = $order_data['billing']['email'];
}
#BLOCK_PHONE_TRANSFER_START[builder]
if (!empty($order_data['billing']['phone'])) {
$order_bundle['customerDetails']['phone'] = $this->cleanPhoneNumber($order_data['billing']['phone']);
}
#BLOCK_PHONE_TRANSFER_END
return $order_bundle;
}
function getTaxType($product)
{
$tax = new WC_Tax();
if (get_option("woocommerce_calc_taxes") == "no") { // PLUG-4056
$item_rate = -1;
} else {
$base_tax_rates = $tax->get_base_tax_rates($product->get_tax_class(true));
if (!empty($base_tax_rates)) {
$temp = $tax->get_rates($product->get_tax_class());
$rates = array_shift($temp);
$item_rate = round(array_shift($rates));
} else {
$item_rate = -1;
}
}
$rate_to_type = [
20 => 6,
18 => 3,
10 => 2,
0  => 1,
5  => 10,
7  => 12,
22 => 13
];
return $rate_to_type[$item_rate] ?? $this->tax_type;
}
function correctBundleItem(&$item, $discount)
{
$item['itemAmount'] -= $discount;
$diff_price = fmod($item['itemAmount'], $item['quantity']['value']); //0.5 quantity
if ($diff_price != 0) {
$item['itemAmount'] += $item['quantity']['value'] - $diff_price;
}
$item['itemPrice'] = $item['itemAmount'] / $item['quantity']['value'];
}
function _getBillingPayerData($order_data)
{
$billingPayerData = array();
$pattern = '/^[A-Za-z0-9\s\'"!#$%&@^~*+=\-_.,:;<>|，΄´–\/?\\\\{}()\[\]\n]+$/';
if (!empty($order_data['billing']['city']) && preg_match($pattern, $order_data['billing']['city'])) {
$billingPayerData['billingCity'] = $order_data['billing']['city'];
}
if (!empty($order_data['billing']['country']) && preg_match($pattern, $order_data['billing']['country'])) {
$billingPayerData['billingCountry'] = $order_data['billing']['country'];
}
if (!empty($order_data['billing']['address_1']) && preg_match($pattern, $order_data['billing']['address_1'])) {
$billingPayerData['billingAddressLine1'] = $order_data['billing']['address_1'];
}
if (!empty($order_data['billing']['address_2']) && preg_match($pattern, $order_data['billing']['address_2'])) {
$billingPayerData['billingAddressLine2'] = $order_data['billing']['address_2'];
}
if (!empty($order_data['billing']['address_3']) && preg_match($pattern, $order_data['billing']['address_3'])) {
$billingPayerData['billingAddressLine3'] = $order_data['billing']['address_3'];
}
if (!empty($order_data['billing']['postcode']) && preg_match($pattern, $order_data['billing']['postcode'])) {
$billingPayerData['billingPostalCode'] = $order_data['billing']['postcode'];
}
if (!empty($order_data['billing']['state']) && preg_match($pattern, $order_data['billing']['state'])) {
$billingPayerData['billingState'] = $order_data['billing']['state'];
}
return $billingPayerData;
}
function receipt_page($order)
{
$this->generate_form($order);
exit();
}
public function webhook_result()
{
if (isset($_GET['action'])) {
$action = $_GET['action'];
if ($this->test_mode == 'yes') {
$action_adr = $this->test_url;
} else {
$action_adr = $this->prod_url;
}
$action_adr .= 'getOrderStatusExtended.do';
$args = array(
'userName' => $this->merchant,
'password' => $this->password,
);
switch ($action) {
case "result":
$args['orderId'] = isset($_GET['orderId']) ? $_GET['orderId'] : null;
$order_number = $_GET['order_id'];
$order = $this->find_order_by_number($order_number);
$response = $this->_sendGatewayData(http_build_query($args, '', '&'), $action_adr);
if (RBSPAYMENT_ALFABANKBY_ENABLE_LOGGING === true) {
$logData = $args;
$logData['password'] = '**removed from log**';
$this->writeLog("[REQUEST RU]: " . $action_adr . ": " . print_r($logData, true) . "\n[RESPONSE]: " . print_r($response, true));
}
$response = json_decode($response, true);
$orderStatus = $response['orderStatus'];
if ($orderStatus == '1' || $orderStatus == '2') {
if ($this->allowCallbacks === false) {
$order->update_status($this->order_status_paid, "AlfabankBY: " . __('Payment successful', 'wc-' . $this->id . '-text-domain'));
try {
wc_reduce_stock_levels($order_id);
} catch (Exception $e) {
}
update_post_meta($order_id, 'orderId', $args['orderId']);
$transaction_id = sanitize_text_field($response['authRefNum']);
$order->set_transaction_id($transaction_id);
$order->payment_complete();
}
if (!empty($this->success_url)) {
WC()->cart->empty_cart();
wp_redirect($this->success_url . "?order_id=" . $order_id);
exit;
}
wp_redirect($this->get_return_url($order));
exit;
} else {
$order->update_status('failed', "AlfabankBY: " . __('Payment failed', 'wc-' . $this->id . '-text-domain'));
if (!empty($this->fail_url)) {
wp_redirect($this->fail_url . "?order_id=" . $order_id);
exit;
}
wc_add_notice(__('There was an error while processing payment', 'wc-' . $this->id . '-text-domain') . "<br/>" . $response['actionCodeDescription'], 'error');
wp_safe_redirect($order->get_checkout_payment_url());
exit;
}
$order->save();
break;
case "callback":
$args['orderId'] = isset($_GET['mdOrder']) ? $_GET['mdOrder'] : null;
$response = $this->_sendGatewayData(http_build_query($args, '', '&'), $action_adr);
$response = json_decode($response, true);
$parts = explode("_", $response['orderNumber']);
$order_number = $parts[0];
$order = $this->find_order_by_number($order_number);
$orderStatus = $response['orderStatus'];
$this->writeLog("[Incoming cb (" . $args['orderId'] . "|" . $order_number . ")]: OrderStatus= " . $orderStatus);
if ($orderStatus == '1' || $orderStatus == '2') {
$order->update_meta_data('orderId', $args['orderId']);
$transaction_id = sanitize_text_field($response['authRefNum']);
$order->set_transaction_id($transaction_id);
if (strpos($order->get_status(), "pending") !== false
|| strpos($order->get_status(), "failed") !== false
|| strpos($order->get_status(), "draft") !== false
) { //PLUG-4415, 4495
$order->update_status($this->order_status_paid, "AlfabankBY: " . __('Payment successful', 'wc-' . $this->id . '-text-domain'));
$this->writeLog("[VALUE TO SET ORDER_STATUS](".$args['orderId']."): " . $this->order_status_paid); //PLUG-7155
try {
$order->reduce_order_stock();
} catch (Exception $e) {
}
$order->payment_complete();
}
if (isset($_GET['showMercy'])) {
if (!empty($this->success_url)) {
WC()->cart->empty_cart();
wp_redirect($this->success_url . "?order_id=" . $order_number);
exit;
}
}
wp_redirect($this->get_return_url($order));
exit;
}
else if ($orderStatus == '4') {
if ( $order && $order->get_meta('orderId', true) != $args['orderId'] ) {
$this->writeLog($order->get_meta('orderId', true) . "!=" . $args['orderId']);
exit();
}
$is_part_refunted = $response['paymentAmountInfo']['approvedAmount'] === $response['amount'] && $response['paymentAmountInfo']['refundedAmount'] != 0;
$is_full_refunded = $response['paymentAmountInfo']['approvedAmount'] === $response['paymentAmountInfo']['refundedAmount'];
if($is_full_refunded) {
$refund_amount = $response['amount'] / 100;
$refund_massage = 'REFUNDED_FULL_SUCCESS_MESSAGE ' . $refund_amount;
} else if($is_part_refunted) {
$refund_amount = $response['paymentAmountInfo']['refundedAmount'] / 100;
$refund_massage = 'REFUNDED_SUCCESS_MESSAGE ' . $refund_amount;
}
$refund_id = wc_create_refund(array(
'amount'   => $refund_amount,
'reason'   => $refund_massage,
'order_id' => $order->get_id(),
));
if (is_wp_error($refund_id)) {
$this->writeLog("REFUND ERROR: " . $refund_id->get_error_message());
} else {
$order->add_order_note($refund_massage, false);
$order->save();
}
exit();
}
else if ($orderStatus == '3') {
if ( $order && $order->get_meta('orderId', true) != $args['orderId'] ) {
$this->writeLog($order->get_meta('orderId', true) . "!=" . $args['orderId']);
exit();
}
$is_part_reverse = $response['paymentAmountInfo']['approvedAmount'] > 0 && $response['paymentAmountInfo']['approvedAmount'] < $response['amount'];
$is_full_reverse = $response['paymentAmountInfo']['approvedAmount'] === 0;
if($is_full_reverse) {
$reverse_amount = '';
$reverse_massage = 'REVERSE_FULL_SUCCESS_MESSAGE ' . $reverse_amount;
} else if($is_part_reverse) {
$reverse_amount = $response['amount'] - $response['paymentAmountInfo']['approvedAmount'];
$reverse_massage = 'REVERSE_SUCCESS_MESSAGE ' . ($reverse_amount / 100);
}
$refund_id = wc_create_refund(array(
'amount'   => $reverse_amount,
'reason'   => $reverse_massage,
'order_id' => $order->get_id(),
));
if (is_wp_error($refund_id)) {
$this->writeLog("REVERSE ERROR: " . $refund_id->get_error_message());
} else {
$order->add_order_note($reverse_massage, false);
$order->save();
}
exit();
}
elseif ($order
&& empty($order->get_meta('orderId', true))
&& $this->id == $order->get_payment_method()
) {
$this->writeLog(">>" . $order->get_meta('orderId') . "<<");
$order->update_status('failed', "AlfabankBY: " . __('Payment failed', 'wc-' . $this->id . '-text-domain'));
if (isset($_GET['showMercy'])) {
if (!empty($this->fail_url)) {
wp_redirect($this->fail_url . "?order_id=" . $order_number);
exit;
}
wc_add_notice(__('There was an error while processing payment', 'wc-' . $this->id . '-text-domain') . "<br/>" . $response['actionCodeDescription'], 'error');
wp_safe_redirect($order->get_checkout_payment_url());
exit;
}
} else {
/* noop */
}
$order->save();
break;
}
exit;
}
}
public function process_refund($order_id, $amount = null, $reason = '')
{
$order = wc_get_order($order_id);
if ($amount == "0.00") {
$amount = 0;
} else {
$amount = $amount * 100;
}
$order_key = $order->get_order_key();
$args = array(
'userName' => $this->merchant,
'password' => $this->password,
'orderId' => get_post_meta($order_id, 'orderId', true),
'amount' => $amount
);
if ($this->test_mode == 'yes') {
$action_adr = $this->test_url;
} else {
$action_adr = $this->prod_url;
}
$gose = $this->_sendGatewayData(http_build_query($args, '', '&'), $action_adr . 'getOrderStatusExtended.do');
$res = json_decode($gose, true);
if ($res["orderStatus"] == "2" || $res["orderStatus"] == "4") { //DEPOSITED||REFUNDED
$result = $this->_sendGatewayData(http_build_query($args, '', '&'), $action_adr . 'refund.do');
if (RBSPAYMENT_ALFABANKBY_ENABLE_LOGGING === true) {
$logData = $args;
$logData['password'] = '**removed from log**';
$this->writeLog("[DEPOSITED REFUND RESPONSE]: " . print_r($logData, true) . " \n" . $result);
}
} elseif ($res["orderStatus"] == "1") { //APPROVED 2x
if ($amount == 0) {
unset($args['amount']);
}
$result = $this->_sendGatewayData(http_build_query($args, '', '&'), $action_adr . 'reverse.do');
if (RBSPAYMENT_ALFABANKBY_ENABLE_LOGGING === true) {
$logData = $args;
$logData['password'] = '**removed from log**';
$this->writeLog("[APPROVED REVERSE RESPONSE]: " . print_r($logData, true) . " \n" . $result);
}
} else {
return new WP_Error('wc_' . $this->id . '_refund_failed', sprintf(__('Order ID (%s) failed to be refunded. Please contact administrator for more help.', 'wc-' . $this->id . '-text-domain'), $order_id));
}
$response = json_decode($result, true);
if ($response["errorCode"] != "0") {
if ($response["errorCode"] == "7") {
return new WP_Error('wc_' . $this->id . '_refund_failed', "For partial refunds Order state should be in DEPOSITED in Gateway");
}
return new WP_Error('wc_' . $this->id . '_refund_failed', $response["errorMessage"]);
} else {
$result = $this->_sendGatewayData(http_build_query($args, '', '&'), $action_adr . 'getOrderStatusExtended.do');
if (RBSPAYMENT_ALFABANKBY_ENABLE_LOGGING === true) {
$this->writeLog("[FINALE STATE]: " . $result);
}
$response = json_decode($result, true);
$orderStatus = $response['orderStatus'];
if ($orderStatus == '4' || $orderStatus == '3') {
return true;
} elseif ($orderStatus == '1') {
return true;
}
}
return false;
}
/**
* Process subscription payment.
*
* @param float $amount
* @param WC_Order $order
* @return void
*/
public function process_subscription_payment($amount, $order)
{
$payment_result = $this->get_option('result');
if ('success' === $payment_result) {
$order->payment_complete();
} else {
$message = __('Order payment failed. To make a successful payment using RBSPaymentAlfabankBY Payments, please review the gateway settings.', 'woocommerce-gateway-alfabankby');
throw new Exception($message);
}
}
function get_numeric_currency_code($currency_code)
{
$currency_codes = array(
'BYN' => '933',
'BHD' => '048',
'BYR' => '974',
'CAD' => '124',
'CNY' => '156',
'EUR' => '978',
'GBP' => '826',
'HKD' => '344',
'HUF' => '348',
'ILS' => '376',
'JPY' => '392',
'KGS' => '417',
'KRW' => '410',
'KZT' => '398',
'MDL' => '498',
'MYR' => '458',
'OMR' => '512',
'PHP' => '608',
'RON' => '946',
'RUB' => '643',
'RUR' => '810',
'SGD' => '702',
'UAH' => '980',
'USD' => '840',
'NGN' => '566',
'MZN' => '943',
'BGN' => '975',
'BZD' => '084',
'GHS' => '936',
'GNF' => '324',
'XOF' => '952',
'PLN' => '985',
'LSL' => '426',
'TZS' => '834',
'NZD' => '554',
'KHR' => '116',
'TRY' => '949',
'AMD' => '051',
'SAR' => '682',
'AED' => '784',
'COP' => '170',
'AUD' => '036',
'IDR' => '360',
'KWD' => '414',
'JOD' => '400',
'INR' => '356'
);
return isset($currency_codes[$currency_code]) ? $currency_codes[$currency_code] : null;
}
private function cleanPhoneNumber($telephone): string
{
return substr(preg_replace('/\D+/', '', $telephone), 0, 15);
}
private function compare_major_minor($v1, $v2) {
[$x1, $y1] = array_map('intval', explode('.', $v1));
[$x2, $y2] = array_map('intval', explode('.', $v2));
if ($x1 > $x2) return 1;
if ($x1 < $x2) return -1;
if ($y1 > $y2) return 1;
if ($y1 < $y2) return -1;
return 0;
}
function get_order_number_for_gateway( $order ) {
if ( ! $order instanceof WC_Order ) {
$order = wc_get_order( $order );
}
if ( ! $order ) {
return '';
}
if ( method_exists( $order, 'get_order_number' ) ) {
$order_number = $order->get_order_number();
} else {
$order_number = $order->get_id();
}
$order_number = trim( str_replace( '#', '', $order_number ) );
return $order_number . '_' . time();
}
function find_order_by_number( $order_number ) {
$order = null;
if ( function_exists( 'wc_sequential_order_numbers' ) ) {
$order_id = wc_sequential_order_numbers()->find_order_by_order_number( $order_number );
if ( $order_id ) {
$order = wc_get_order( $order_id );
}
}
if ( ! $order ) {
$orders = wc_get_orders( [
'meta_key'   => '_order_number',
'meta_value' => $order_number,
'return'     => 'ids',
'limit'      => 1,
] );
if ( $orders ) {
$order = wc_get_order( $orders[0] );
}
}
if ( ! $order && is_numeric( $order_number ) ) {
$order = wc_get_order( intval( $order_number ) );
}
return $order;
}
function create_woocommerce_order($user_id, $payment_method_id, $product_array, $mark_paid = false, $billingPayerData = '') {
if (empty($product_array)) {
return new WP_Error('empty_cart', 'Product list is empty');
}
$user = get_user_by('ID', $user_id);
if (!$user) {
return new WP_Error('invalid_user', 'User not found');
}
$order = wc_create_order([
'customer_id' => $user_id,
]);
foreach ($product_array as $item) {
if (!is_array($item) || count($item) != 2) {
return new WP_Error('invalid_product_array', 'Invalid product_array format');
}
list($product_id, $quantity) = $item;
$product = wc_get_product($product_id);
if (!$product) {
return new WP_Error('invalid_product', "Product with ID $product_id not found");
}
$order->add_product($product, $quantity);
}
$payment_gateways = WC()->payment_gateways->payment_gateways();
if (isset($payment_gateways[$payment_method_id])) {
$gateway = $payment_gateways[$payment_method_id];
$order->set_payment_method($gateway);
} else {
return new WP_Error('invalid_gateway', 'Payment method not found');
}
$billing = [
'first_name' => get_user_meta($user_id, 'billing_first_name', true) ?: '',
'last_name'  => get_user_meta($user_id, 'billing_last_name', true) ?: '',
'email'      => $user->user_email,
'phone'      => get_user_meta($user_id, 'billing_phone', true) ?: '0000000000',
'address_1'  => get_user_meta($user_id, 'billing_address_1', true) ?: '-',
'address_2'  => get_user_meta($user_id, 'billing_address_2', true) ?: '',
'city'       => get_user_meta($user_id, 'billing_city', true) ?: '-',
'state'      => get_user_meta($user_id, 'billing_state', true) ?: '',
'postcode'   => get_user_meta($user_id, 'billing_postcode', true) ?: '000000',
'country'    => get_user_meta($user_id, 'billing_country', true) ?: '',
];
$order->set_address($billing, 'billing');
$order->calculate_totals();
if ($mark_paid) {
$order->payment_complete();
}
$order->save();
return $order->get_id();
}
}
if (!function_exists('wpbo_get_woo_version_number')) {
function wpbo_get_woo_version_number()
{
if (!function_exists('get_plugins'))
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
$plugin_folder = get_plugins('/' . 'woocommerce');
$plugin_file = 'woocommerce.php';
if (isset($plugin_folder[$plugin_file]['Version'])) {
return $plugin_folder[$plugin_file]['Version'];
} else {
return "Unknown";
}
}
}