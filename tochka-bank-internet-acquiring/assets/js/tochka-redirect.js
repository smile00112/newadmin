document.addEventListener("DOMContentLoaded", function() {
    var form = document.querySelector('form[name="tochka_payment_form_ajax"]');
    if (form) {
        form.submit();
    }
});