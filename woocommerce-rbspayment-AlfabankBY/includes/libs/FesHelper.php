<?php
namespace WoocommerceRBSPaymentAlfabankBY\Includes\Libs;
class FesHelper
{
public function __construct()
{
add_action('woocommerce_product_options_general_product_data', array($this, 'fes_field'), 14);
add_action('woocommerce_process_product_meta', array($this, 'save_fes_field'), 14);
}
public function fes_field()
{
global $post;
$fes_truCode = get_post_meta($post->ID, '_fes_truCode', true);
?>
<script>
document.addEventListener("DOMContentLoaded", function () {
if (!document.getElementById('_fes_truCode')) {
console.log("The _fes_truCode field is missing, adding...");
let container = document.createElement("div");
container.classList.add("options_group");
let paragraphElement = document.createElement("p");
paragraphElement.classList.add("form-field");
let label = document.createElement("label");
label.setAttribute("for", "_fes_truCode");
label.textContent = "fes_truCode";
let input = document.createElement("input");
input.type = "text";
input.id = "_fes_truCode";
input.name = "_fes_truCode";
input.placeholder = "fes_truCode";
input.value = "<?php echo esc_attr($fes_truCode); ?>";
paragraphElement.appendChild(label);
paragraphElement.appendChild(input);
container.appendChild(paragraphElement);
let target = document.querySelector(".options_group");
if (target) {
target.parentNode.appendChild(container);
}
} else {
}
});
</script>
<?php
}
public function save_fes_field($post_id)
{
$custom_field_value = isset($_POST['_fes_truCode']) ? sanitize_text_field($_POST['_fes_truCode']) : '';
update_post_meta($post_id, '_fes_truCode', $custom_field_value);
}
public function get_fes_cashbox_id_field()
{
return array(
'title' => __("fes_cashboxId", 'wc-alfabankby-text-domain'),
'type' => 'text',
'description' => __('fes_cashboxId', 'wc-alfabankby-text-domain'),
);
}
}
add_action('woocommerce_loaded', function () {
new FesHelper();
});