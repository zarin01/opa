'use strict';

class StripePaymentForm {

    /**
     * @param $wrapper
     * @param globalEventDispatcher
     * @param buildingBlockName
     * @param useProductionKey
     */
    constructor($wrapper, globalEventDispatcher, buildingBlockName, useProductionKey = false) {
        this.$wrapper = $wrapper;
        this.globalEventDispatcher = globalEventDispatcher;
        this.stripeKey = this.$wrapper.attr('data-stripe-publishable-key');
        this.instantiate()
            .instantiateElements()
            .unbindEvents()
            .bindEvents();
    }

    /**
     * Call like this.selectors
     */
    static get _selectors() {
        return {
            form: '#payment-form'
        }
    }

    bindEvents() {
        this.$wrapper.on('submit', StripePaymentForm._selectors.form, this.handleFormSubmit.bind(this));
        return this;
    }

    unbindEvents() {
        this.$wrapper.off('submit', StripePaymentForm._selectors.form);
        return this;
    }

    /**
     * @return {StripePaymentForm}
     */
    instantiate() {
        this.stripe = Stripe(this.stripeKey);
        this.elements = this.stripe.elements();
        return this;
    }

    static cardStyles() {
        return {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };
    }

    /**
     * @return {StripePaymentForm}
     */
    instantiateElements() {
        let card = this.card = this.elements.create('card', {style: StripePaymentForm.cardStyles()});
        card.mount('#card');
        return this;
    }

    /**
     * @param e
     */
    handleFormSubmit(e) {
        
        e.preventDefault();
        let $form = $(e.currentTarget);
        let tokenData = {
            address_line1: $form.find('input[name="address_line_1"]').val(),
            address_city: $form.find('input[name="address_city"]').val(),
            address_state: $form.find('input[name="address_state"]').val(),
            address_zip: $form.find('input[name="address_zip"]').val()
        };
        this.stripe.createToken(this.card, tokenData).then((result) => {
            if (result.error) {
                // Inform the customer that there was an error.
                let errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
            } else {
                // Send the token to your server.
                this.stripeTokenHandler(result.token);
            }
        });
    }

    stripeTokenHandler(token) {

        const self = this;

        // Insert the token ID into the form so it gets submitted to the server
        let stripeToken = document.getElementById('stripeToken');
        stripeToken.value = token.id;

        // Collect Form info
        const $form = $('#payment-form');

        const formData = new FormData($form.get(0));

        // Submit the form to the server
        $.ajax({
            url: localized.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false
        }).then((data, textStatus, jqXHR) => {
            if ( data.success === true ) {
                self.paymentSuccessful( data );
            } else if ( data.success === false ) {
                self.paymentFailed( data );
            }
        }).catch((jqXHR) => {
            self.paymentFailed( null, "Server error.  Please try again later." );
        });

    }

    paymentSuccessful( response, message = null ) {
        console.log('success', response);
        window.location.href= '/thankyou/?orderId='+response.registration;
        //location.reload();
    }

    paymentFailed( response, message = null ) {
        console.log('failed', response);
    }
}

export default StripePaymentForm;
