jQuery(function($){
function resetRBSCardSelection() {
$.post(jccgateway_plugin_ajax.ajax_url, {
action: 'jccgateway_set_card',
card_id: 0
});
$('#jccgateway_saved_card_field').val('');
}
function loadJCCGateway_Description() {
var selectedClassic = $('input[name="payment_method"]:checked').val() || '';
var selectedBlock = $('input[name="radio-control-wc-payment-method-options"]:checked').val() || '';
var selected = selectedClassic || selectedBlock;
if (!selected) {
return;
}
if (selected === 'jccgateway') {
$.ajax({
url: jccgateway_plugin_ajax.ajax_url,
method: 'POST',
data: {
action: 'jccgateway_show_saved_cards',
client_id: jccgateway_plugin_ajax.client_id
},
success: function(html){
if (!html || !html.trim()) {
return;
}
var selectedPayment = $('input[name="payment_method"]:checked').closest('li');
var classicBox = selectedPayment.find('.payment_box');
if (!classicBox.length) {
selectedPayment.append('<div class="payment_box payment_method_jccgateway" style="display:block;"></div>');
classicBox = selectedPayment.find('.payment_box');
}
classicBox.find('.jccgateway-saved-cards').remove();
classicBox.append('<div class="jccgateway-saved-cards">' + html + '</div>');
var blockBox = $('input[name="radio-control-wc-payment-method-options"]:checked')
.closest('.wc-block-components-radio-control-accordion-option')
.find('.wc-block-components-radio-control-accordion-content');
if (blockBox.length) {
blockBox.find('.jccgateway-saved-cards').remove();
blockBox.append('<div class="jccgateway-saved-cards">' + html + '</div>');
}
}
});
} else {
resetRBSCardSelection();
}
}
$('form.checkout, form#order_review').on('change', 'input[name="payment_method"]', loadJCCGateway_Description);
$('body').on('change', 'input[name="radio-control-wc-payment-method-options"]', loadJCCGateway_Description);
$('body').on('updated_checkout', loadJCCGateway_Description);
function tryLoadRBSDescription() {
if ($('input[name="payment_method"]:checked').length || $('input[name="radio-control-wc-payment-method-options"]:checked').length) {
loadJCCGateway_Description();
} else {
resetRBSCardSelection();
setTimeout(tryLoadRBSDescription, 100);
}
}
tryLoadRBSDescription();
$('body').on('change', '.saved-card input[type=radio]', function(){
var cardId = $(this).val();
$('#jccgateway_saved_card_field').val(cardId);
$.post(jccgateway_plugin_ajax.ajax_url, {
action: 'jccgateway_set_card',
card_id: cardId
});
});
});
