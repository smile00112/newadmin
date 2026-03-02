jQuery(function($){



    function resetRBSCardSelection() {
        $.post(alfabankby_plugin_ajax.ajax_url, {
            action: 'alfabankby_set_card',
            card_id: 0
        });
        $('#alfabankby_saved_card_field').val('');
    }

    function loadRBSPaymentAlfabankBYDescription() {

        var selectedClassic = $('input[name="payment_method"]:checked').val() || '';
        var selectedBlock = $('input[name="radio-control-wc-payment-method-options"]:checked').val() || '';
        var selected = selectedClassic || selectedBlock;

        if (selected === 'alfabankby') {

            $.ajax({
                url: alfabankby_plugin_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'alfabankby_show_saved_cards',
                    client_id: alfabankby_plugin_ajax.client_id
                },
                success: function(html){

                    if (!html || !html.trim()) {
                        return;
                    }

                    var selectedPayment = $('input[name="payment_method"]:checked').closest('li');
                    var classicBox = selectedPayment.find('.payment_box');

                    if (!classicBox.length) {
                        selectedPayment.append('<div class="payment_box payment_method_alfabankby" style="display:block;"></div>');
                        classicBox = selectedPayment.find('.payment_box');
                    }

                    classicBox.find('.alfabankby-saved-cards').remove();
                    classicBox.append('<div class="alfabankby-saved-cards">' + html + '</div>');

                    var blockBox = $('input[name="radio-control-wc-payment-method-options"]:checked')
                        .closest('.wc-block-components-radio-control-accordion-option')
                        .find('.wc-block-components-radio-control-accordion-content');

                    if (blockBox.length) {
                        blockBox.find('.alfabankby-saved-cards').remove();
                        blockBox.append('<div class="alfabankby-saved-cards">' + html + '</div>');
                    }

                }
            });
        } else {
            resetRBSCardSelection();
        }
    }

    $('form.checkout, form#order_review').on('change', 'input[name="payment_method"]', loadRBSPaymentAlfabankBYDescription);
    $('body').on('change', 'input[name="radio-control-wc-payment-method-options"]', loadRBSPaymentAlfabankBYDescription);
    $('body').on('updated_checkout', loadRBSPaymentAlfabankBYDescription);

    function tryLoadRBSDescription() {
        if ($('input[name="payment_method"]:checked').length || $('input[name="radio-control-wc-payment-method-options"]:checked').length) {
            loadRBSPaymentAlfabankBYDescription();
        } else {
            resetRBSCardSelection();
            setTimeout(tryLoadRBSDescription, 100);
        }
    }
    tryLoadRBSDescription();


    $('body').on('change', '.saved-card input[type=radio]', function(){
        var cardId = $(this).val();

        $('#alfabankby_saved_card_field').val(cardId);

        $.post(alfabankby_plugin_ajax.ajax_url, {
            action: 'alfabankby_set_card',
            card_id: cardId
        });
    });

});
