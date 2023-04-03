<?php
class OPA_Registration {

	static function init() {
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::scripts' );
		add_action( 'wp_ajax_nopriv_opa_registration', __CLASS__ . '::register' );
		add_shortcode( 'opa-registration', __CLASS__ . '::registration_form' );
	}

	static function registration_form() {
		require_once( OPA_PATH . 'views/shortcode-registration.php' );
	}

	static function register() {

		// This is a secure process to validate if this request comes from a valid source.
		check_ajax_referer( 'opa_registration', 'opa_registration_nonce' );

		$first_name = OPA_Functions::clean_input($_POST['first_name']);
		$last_name = OPA_Functions::clean_input($_POST['last_name']);
		$email = OPA_Functions::clean_input($_POST['email']);
		$stripeToken = OPA_Functions::clean_input( $_POST["stripeToken"] );
		$payment_time = current_time( 'timestamp' );

		try {
			// Insert User and Set Address
			add_filter( 'insert_user_meta', __CLASS__ . '::add_custom_user_meta', 10, 3 );
			$new_user_id = wp_insert_user(array(
				'user_login' =>  $email,
				'user_pass'  =>  wp_generate_password(),
				'user_email' => $email,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'show_admin_bar_front' => false,
				'role' => 'member'
			));
			remove_filter( 'insert_user_meta', __CLASS__ . '::add_custom_user_meta', 10 );

			// Process Payment
			$new_user = new WP_User( $new_user_id );
			$payment_response = OPA_Payment::process_payment( $new_user, $stripeToken, intval( OPA_REGISTRATION_FEE * 100 ) );

			// Send response
			if ( $payment_response['success'] === true ) {
				update_option( 'opa_registration_stripe_customer_id', $payment_response['stripe_customer_id'] );
				update_option( 'opa_registration_stripe_charge_id', $payment_response['stripe_charge_id'] );
				update_option( 'opa_registration_stripe_charge_time', $payment_time );
				wp_send_json_success( array(
					'message' => 'Payment Successful'
				));
			} else {
				wp_delete_user( $new_user_id );
				wp_send_json_error( array(
					'message' => $payment_response['error']
				));
			}
		} catch( Exception $e ) {
			wp_send_json_error( array(
				'message' => 'Server Error!'
			));
		}
		die();

	}

	static function scripts() {
		wp_enqueue_script( 'stripe', 'https://js.stripe.com/v3/', array(), '3.0', false );
	}

	/**
	 * Adds Custom User Meta on AJAX Registration
	 * @param $meta
	 * @param $user
	 *
	 * @return mixed
	 */
	static function add_custom_user_meta ($meta, $user) {

		$meta['address_line_1'] = OPA_Functions::clean_input($_POST["address_line_1"]);
		$meta['address_city']  = OPA_Functions::clean_input($_POST["address_city"]);
		$meta['address_state']  = OPA_Functions::clean_input($_POST["address_state"]);
		$meta['address_zip']  = OPA_Functions::clean_input($_POST["address_zip"]);

		return $meta;
	}

	static function user_is_paying_member() {
		// TODO: Add functionality
		return true;
	}

}
