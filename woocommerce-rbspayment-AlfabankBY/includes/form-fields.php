<?php
namespace WoocommerceRBSPaymentAlfabankBY\Includes;
class FormFieldsGenerator
{
public static function generate($id)
{
$form_fields = self::getBaseFields($id);
if (defined('RBSPAYMENT_ALFABANKBY_API_VERSION') && RBSPAYMENT_ALFABANKBY_API_VERSION >= 2) {
$settings = get_option('woocommerce_alfabankby_settings');
$merchant = isset($settings['merchant']) ? $settings['merchant'] : '';
$password = isset($settings['password']) ? $settings['password'] : '';
$token_default = '';
if (!empty($merchant) && !empty($password)) {
$token_default = base64_encode($merchant . ":" . $password);
}
$token_field = array(
'title' => __('Token', 'wc-' . $id . '-text-domain'),
'type' => 'password',
'default' => $token_default,
'css' => 'width:80%;',
);
$merchant_key = array_search('merchant', array_keys($form_fields), true);
$form_fields = array_merge(
array_slice($form_fields, 0, $merchant_key),
array('token' => $token_field),
array_slice($form_fields, $merchant_key)
);
unset($form_fields['merchant']);
unset($form_fields['password']);
}
$form_fields_ext1 = array(
'description' => array(
'title' => __('Description', 'wc-' . $id . '-text-domain'),
'type' => 'textarea',
'description' => __('Payment description displayed to your customer.', 'wc-' . $id . '-text-domain'),
'css' => 'width:80%;',
),
'order_status_paid' => array(
'title' => __('Paid order status', 'wc-' . $id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => 'wc-completed',
'options' => array(
'wc-processing' => _x('Processing', 'Order status', 'woocommerce'),
'wc-completed' => _x('Completed', 'Order status', 'woocommerce'),
),
),
'success_url' => array(
'title' => __('Success URL', 'wc-' . $id . '-text-domain'),
'type' => 'text',
'description' => __('Page your customer will be redirected to after a <b>successful payment</b>.<br/>Leave this field blank, if you want to use default settings.', 'wc-' . $id . '-text-domain'),
),
'fail_url' => array(
'title' => __('Fail URL', 'wc-' . $id . '-text-domain'),
'type' => 'text',
'description' => __('Page your customer will be redirected to after an <b>unsuccessful payment</b>.<br/>Leave this field blank, if you want to use default settings.', 'wc-' . $id . '-text-domain'),
),
);
$form_fields = array_merge($form_fields, $form_fields_ext1);
if (defined('RBSPAYMENT_ALFABANKBY_ENABLE_CART_OPTIONS') && RBSPAYMENT_ALFABANKBY_ENABLE_CART_OPTIONS == true) {
$form_fields_cartOptions = array(
'header_cart_options' => array(
'type' => 'title',
'css' => 'font-size: 1.4em;',
'title' => "Send Cart Data settings",
),
'send_order' => array(
'title' => __("Send cart data<br />(including customer info)", 'wc-' . $id . '-text-domain'),
'type' => 'checkbox',
'label' => __('Enable', 'woocommerce'),
'description' => __('If this option is enabled order receipts will be created and sent to your customer and to the revenue service.', 'wc-' . $id . '-text-domain'),
'default' => 'no'
),
'tax_system' => array(
'title' => __('Tax system', 'wc-' . $id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => '0',
'options' => array(
'0' => __('General', 'wc-' . $id . '-text-domain'),
'1' => __('Simplified, income', 'wc-' . $id . '-text-domain'),
'2' => __('Simplified, income minus expences', 'wc-' . $id . '-text-domain'),
'3' => __('Unified tax on imputed income', 'wc-' . $id . '-text-domain'),
'4' => __('Unified agricultural tax', 'wc-' . $id . '-text-domain'),
'5' => __('Patent taxation system', 'wc-' . $id . '-text-domain'),
),
),
'tax_type' => array(
'title' => __('Default VAT', 'wc-' . $id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => '0',
'options' => array(
'0' => __('No VAT', 'wc-' . $id . '-text-domain'),
'1' => __('VAT 0%', 'wc-' . $id . '-text-domain'),
'10' => __('VAT 5%', 'wc-' . $id . '-text-domain'),
'12' => __('VAT 7%', 'wc-' . $id . '-text-domain'),
'2' => __('VAT 10%', 'wc-' . $id . '-text-domain'),
'3' => __('VAT 18%', 'wc-' . $id . '-text-domain'),
'6' => __('VAT 20%', 'wc-' . $id . '-text-domain'),
'14' => __('VAT 22%', 'wc-' . $id . '-text-domain'),
'11' => __('VAT applicable rate 5/105', 'wc-' . $id . '-text-domain'),
'13' => __('VAT applicable rate 7/107', 'wc-' . $id . '-text-domain'),
'4' => __('VAT applicable rate 10/110', 'wc-' . $id . '-text-domain'),
'5' => __('VAT applicable rate 18/118', 'wc-' . $id . '-text-domain'),
'7' => __('VAT applicable rate 20/120', 'wc-' . $id . '-text-domain'),
'15' => __('VAT applicable rate 22/122', 'wc-' . $id . '-text-domain'),
),
),
'versionFfd' => array(
'title' => __('Fiscal document format', 'wc-' . $id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => 'v1_05',
'options' => array(
'v1_05' => __('v1.05', 'wc-' . $id . '-text-domain'),
'v1_2' => __('v1.2', 'wc-' . $id . '-text-domain'),
),
'description' => __('Also specify the version in your bank web account and in your fiscal service web account.', 'wc-' . $id . '-text-domain'),
),
'paymentMethodType' => array(
'title' => __('Payment type', 'wc-' . $id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => '1',
'options' => array(
'1' => __('Full prepayment', 'wc-' . $id . '-text-domain'),
'2' => __('Partial prepayment', 'wc-' . $id . '-text-domain'),
'3' => __('Advance payment', 'wc-' . $id . '-text-domain'),
'4' => __('Full payment', 'wc-' . $id . '-text-domain'),
'5' => __('Partial payment with further credit', 'wc-' . $id . '-text-domain'),
'6' => __('No payment with further credit', 'wc-' . $id . '-text-domain'),
'7' => __('Payment on credit', 'wc-' . $id . '-text-domain'),
),
),
'paymentMethodType_delivery' => array(
'title' => __('Payment type for delivery', 'wc-' . $id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => '1',
'options' => array(
'1' => __('Full prepayment', 'wc-' . $id . '-text-domain'),
'2' => __('Partial prepayment', 'wc-' . $id . '-text-domain'),
'3' => __('Advance payment', 'wc-' . $id . '-text-domain'),
'4' => __('Full payment', 'wc-' . $id . '-text-domain'),
'5' => __('Partial payment with further credit', 'wc-' . $id . '-text-domain'),
'6' => __('No payment with further credit', 'wc-' . $id . '-text-domain'),
'7' => __('Payment on credit', 'wc-' . $id . '-text-domain'),
),
),
'paymentObjectType' => array(
'title' => __('Type of goods and services', 'wc-' . $id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => '1',
'options' => array(
'1' => __('Goods', 'wc-' . $id . '-text-domain'),
'2' => __('Excised goods', 'wc-' . $id . '-text-domain'),
'3' => __('Job', 'wc-' . $id . '-text-domain'),
'4' => __('Service', 'wc-' . $id . '-text-domain'),
'5' => __('Stake in gambling', 'wc-' . $id . '-text-domain'),
'7' => __('Lottery ticket', 'wc-' . $id . '-text-domain'),
'9' => __('Intellectual property provision', 'wc-' . $id . '-text-domain'),
'10' => __('Payment', 'wc-' . $id . '-text-domain'),
'11' => __("Agent's commission", 'wc-' . $id . '-text-domain'),
'12' => __('Combined', 'wc-' . $id . '-text-domain'),
'13' => __('Other', 'wc-' . $id . '-text-domain'),
),
),
);
$form_fields = array_merge($form_fields, $form_fields_cartOptions);
}
if (class_exists('WoocommerceRBSPaymentAlfabankBY\\Includes\\Libs\\PaymentWidgetHelper')
&& defined('RBSPAYMENT_ALFABANKBY_ENABLE_PAYMENT_WIDGET')
&& RBSPAYMENT_ALFABANKBY_ENABLE_PAYMENT_WIDGET === true
) {
$form_fields = array_merge($form_fields, \WoocommerceRBSPaymentAlfabankBY\Includes\Libs\PaymentWidgetHelper::getPaymentWidgetFields($id));
}
if (class_exists('WoocommerceRBSPaymentAlfabankBY\\Includes\\Libs\\FesHelper')
&& defined('RBSPAYMENT_ALFABANKBY_ENABLE_FES_CODES')
&& RBSPAYMENT_ALFABANKBY_ENABLE_FES_CODES === true
) {
$fesHelper = new \WoocommerceRBSPaymentAlfabankBY\Includes\Libs\FesHelper();
$form_fields['fes_cashboxId'] = $fesHelper->get_fes_cashbox_id_field();
}
$form_fields_miscellaneous = array(
'header_miscellaneous_options' => array(
'type' => 'title',
'css' => 'font-size: 1.4em;',
'title' => __('Miscellaneous', 'wc-' . $id . '-text-domain'),
),
'min_order_total' => array(
'title' => __('Minimum order total', 'wc-' . $id . '-text-domain'),
'type' => 'number',
'default' => '',
'description' => __('Minimum allowed order amount for payment method to be available.', 'wc-' . $id . '-text-domain'),
'desc_tip' => true,
'custom_attributes' => array(
'step' => '0.01',
'min' => '0',
),
),
'max_order_total' => array(
'title' => __('Maximum order total', 'wc-' . $id . '-text-domain'),
'type' => 'number',
'default' => '',
'description' => __('Maximum allowed order amount for payment method to be available.', 'wc-' . $id . '-text-domain'),
'desc_tip' => true,
'custom_attributes' => array(
'step' => '0.01',
'min' => '0',
),
),
'allowed_category' => array(
'title' => __('Allowed categories', 'wc-' . $id . '-text-domain'),
'type' => 'multiselect',
'class' => 'wc-enhanced-select',
'css' => 'width: 400px;',
'options' => self::get_product_categories(),
'description' => __('Select categories where this payment method will be available.', 'wc-' . $id . '-text-domain'),
'desc_tip' => true,
),
'disallowed_category' => array(
'title' => __('Disallowed categories', 'wc-' . $id . '-text-domain'),
'type' => 'multiselect',
'class' => 'wc-enhanced-select',
'css' => 'width: 400px;',
'options' => self::get_product_categories(),
'description' => __('Select categories where this payment method will NOT be available.', 'wc-' . $id . '-text-domain'),
'desc_tip' => true,
),
);
$form_fields = array_merge($form_fields, $form_fields_miscellaneous);
if (defined('RBSPAYMENT_ALFABANKBY_ENABLE_BACK_URL_SETTINGS') && RBSPAYMENT_ALFABANKBY_ENABLE_BACK_URL_SETTINGS === true) {
$form_fields_back_url = array(
'backToShopUrl' => array(
'title' => __('Back to shop URL', 'wc-' . $id . '-text-domain'),
'type' => 'text',
'default' => '',
'description' => __('Adds URL for checkout page button that will take a cardholder back to the assigned merchant web-site URL.', 'wc-' . $id . '-text-domain'),
'desc_tip' => true,
),
);
$form_fields = array_merge($form_fields, $form_fields_back_url);
}
if (defined('RBSPAYMENT_ALFABANKBY_ENABLE_SAVED_CARDS_PAYMENT') && RBSPAYMENT_ALFABANKBY_ENABLE_SAVED_CARDS_PAYMENT === true) {
$form_fields = array_merge($form_fields, self::getSavedCardsPaymentFields($id));
}
return $form_fields;
}
private static function getBaseFields($id) {
$idd_html = "<!-- RBSPAYMENT_ALFABANKBY_MODULE_VERSION: 5.5.1(1770560622) -->\n";
$logo_path = plugin_dir_path(__FILE__) . '../assets/images/logo.png';
if (file_exists($logo_path)) {
$idd_html .= '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../assets/images/logo.png') . '" alt="iD:RBSPAYMENT_ALFABANKBY_MODULE_VERSION: 5.5.1" style="max-width: 100%; max-height: 42px; margin-bottom: 15px;" />';
}
return array(
'custom_logo' => array(
'type' => 'title',
'title' => $idd_html,
),
'header_base' => array(
'type' => 'title',
'css' => 'font-size: 1.4em;',
'title' => "Base settings",
'description' => __('Configure the gateway connection settings, including mode and credentials.', 'wc-' . $id . '-text-domain'),
),
'enabled' => array(
'title' => __('Enable/Disable', 'wc-' . $id . '-text-domain'),
'type' => 'checkbox',
'label' => __('Enable', 'woocommerce') . " " . RBSPAYMENT_ALFABANKBY_PAYMENT_NAME,
'default' => 'yes'
),
'title' => array(
'title' => __('Title', 'wc-' . $id . '-text-domain'),
'type' => 'text',
'default' => __('Pay by debit/credit card', 'woocommerce') . " (" . RBSPAYMENT_ALFABANKBY_PAYMENT_NAME . ")",
'description' => __('Title displayed to your customer when they make their order.', 'wc-' . $id . '-text-domain'),
),
'merchant' => array(
'title' => __('Login-API', 'wc-' . $id . '-text-domain'),
'type' => 'text',
'default' => '',
),
'password' => array(
'title' => __('Password', 'wc-' . $id . '-text-domain'),
'type' => 'password',
'default' => '',
),
'test_mode' => array(
'title' => __('Test mode', 'wc-' . $id . '-text-domain'),
'type' => 'checkbox',
'label' => __('Enable', 'woocommerce'),
'description' => __('In this mode no actual payments are processed.', 'wc-' . $id . '-text-domain'),
'default' => 'no'
),
'stage_mode' => array(
'title' => __('Payments type', 'wc-' . $id . '-text-domain'),
'type' => 'select',
'class' => 'wc-enhanced-select',
'default' => 'one-stage',
'options' => array(
'one-stage' => __('One-phase payments', 'wc-' . $id . '-text-domain'),
'two-stage' => __('Two-phase payments', 'wc-' . $id . '-text-domain'),
),
),
);
}
private static function get_product_categories() {
$categories = get_terms( array(
'taxonomy'   => 'product_cat',
'hide_empty' => false,
) );
$options = array();
if ( ! is_wp_error( $categories ) ) {
foreach ( $categories as $cat ) {
$options[$cat->term_id] = $cat->name;
}
}
return $options;
}
private static function getSavedCardsPaymentFields($id) {
return array(
'header_saved_cards_options' => array(
'type' => 'title',
'css' => 'font-size: 1.4em;',
'title' => __('Payment with saved cards', 'wc-' . $id . '-text-domain'),
),
'saved_cards_payment_enable' => array(
'title' => __('Enable payment with saved cards', 'wc-' . $id . '-text-domain'),
'type' => 'checkbox',
'label' => __('Enable', 'woocommerce'),
'default' => 'no'
),
);
}
}