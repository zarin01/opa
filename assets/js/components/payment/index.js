import StripePaymentForm from "./StripePaymentForm";

(function($) {
    window.$ = $;
    $(document).ready(function() {
        if(document.getElementById('js-payment-form-widget')) {
            new StripePaymentForm($('#js-payment-form-widget'));
            //soctt shubham
        }
    });
})(jQuery);
