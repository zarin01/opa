<?php
if ( is_user_logged_in() ) { ?>
	<p>You are already registered.</p><?php
} else { ?>
	<div class="opa-registration">
		<div id="js-payment-form-widget" data-stripe-publishable-key="<?php echo OPA_STRIPE_PUBLISHABLE_KEY; ?>">
			<form id="payment-form" class="payment-form" method="POST">

                <div class="opa-registration__group">
                    <div class="opa-registration__firstname">
                        <label><?php _e( 'First Name', OPA_DOMAIN ) ?></label>
                        <input type="text" name="first_name" id="first_name">
                    </div>

                    <div class="opa-registration__lastname">
                        <label><?php _e( 'Last Name', OPA_DOMAIN ) ?></label>
                        <input type="text" name="last_name" id="last_name">
                    </div>
                </div>

                <div class="opa-registration__email">
                    <label><?php _e( 'Email', OPA_DOMAIN ) ?></label>
                    <input type="text" name="email" id="email">
                </div>

                <div class="opa-registration__subscription">
                    <label><?php _e( 'Payment Information - Yearly Subscription', OPA_DOMAIN ) ?></label>
                    <div class="opa-registration__subscription-price">$<?php echo number_format( OPA_REGISTRATION_FEE, 2 ); ?></div>
                </div>

				<div class="opa-registration__payment">
					<label for="card"><?php _e( 'Card Information', OPA_DOMAIN ) ?></label>
					<div id="card"></div>
		            <div class="opa-registration__address">
		                <label><?php _e( 'Address', OPA_DOMAIN ) ?></label>
		                <input type="text" name="address_line_1" id="address_line_1">
		            </div>

                    <div class="opa-registration__group">
                        <div class="opa-registration__city">
                            <label><?php _e( 'City', OPA_DOMAIN ) ?></label>
                            <input type="text" name="address_city" id="address_city">
                        </div>
                        <div class="opa-registration__state">
                            <label><?php _e( 'State', OPA_DOMAIN ) ?></label>
                            <input type="text" name="address_state" id="address_state">
                        </div>
                        <div class="opa-registration__zip">
                            <label><?php _e( 'Zip', OPA_DOMAIN ) ?></label>
                            <input type="text" name="address_zip" id="address_zip">
                        </div>
                    </div>
					<!-- Used to display Element errors. -->
					<div id="card-errors" role="alert"></div>
				</div>
				<input type="hidden" name="stripeToken" id="stripeToken">
				<input type="hidden" name="action" value="opa_registration">
				<?php echo wp_nonce_field( 'opa_registration', 'opa_registration_nonce', true, false ); ?>

				<button class="opa-registration__submit">Submit</button>
			</form>
		</div>
	</div><?php
}
