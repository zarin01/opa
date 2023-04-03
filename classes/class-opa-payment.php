<?php
use Stripe\Charge;

class OPA_Payment {

	/**
	 * Process a Payment to Stripe API
	 * @param WP_user $user
	 * @param string $stripe_token
	 * @param int $charge_amount_in_cents
	 * @param string $statement_description
	 *
	 * @return array
	 */
	static function process_payment( WP_user $user, $stripe_token, $charge_amount_in_cents, $statement_description = 'OPA Payment' ) {

		$error = false;
		$stripeCustomer = false;
		$stripeCharge = false;

		$stripeToken = OPA_Functions::clean_input( $stripe_token );

		if (empty($stripeToken)) {
			$error = 'Stripe token is invalid.';
		}

		\Stripe\Stripe::setApiKey( OPA_STRIPE_SECRET_KEY );

		try {
			$stripeCustomer = self::retrieveStripeCustomer( $user ) ?: self::createStripeCustomer( $user, $stripeToken );
			$stripeCharge = self::createStripeCustomerCharge( $user, $stripeCustomer, $charge_amount_in_cents, $statement_description );
		} catch(\Stripe\Exception\CardException $e) {
			$error = 'There was a problem charging your card: '.$e->getError()->message;
		} catch (\Stripe\Exception\RateLimitException $e) {
			$error = 'Too many requests made to the API too quickly: '.$e->getError()->message;
		} catch (\Stripe\Exception\InvalidRequestException $e) {
			$error = 'Invalid parameters were supplied to Stripe\'s API: '.$e->getError()->message;
		} catch (\Stripe\Exception\AuthenticationException $e) {
			$error = 'Authentication with Stripe\'s API failed: '.$e->getError()->message;
		} catch (\Stripe\Exception\ApiConnectionException $e) {
			$error = 'Network communication with Stripe failed: '.$e->getError()->message;
		} catch (\Stripe\Exception\ApiErrorException $e) {
			$error = 'Error processing payment. Contact customer support: '.$e->getError()->message;
		} catch (\Exception $e) {
			$error = 'System error: '.$e->getMessage();
		}

		if( !$error && $stripeCustomer && $stripeCharge ) {
			return array(
				'success' => true,
				'stripe_customer_id' => $stripeCustomer->id,
				'stripe_charge_id' => $stripeCharge->id
			);
		} else {
			return array(
				'success' => false,
				'error' => $error
			);
		}
	}

	/**
	 * Refunds a partially or full refund (full by default)
	 * @param $stripe_charge_id
	 * @param int $amount_to_refund_in_cents
	 *
	 * @return array
	 */
	static function process_refund( $stripe_charge_id, $amount_to_refund_in_cents = 0 ) {

		\Stripe\Stripe::setApiKey( OPA_STRIPE_SECRET_KEY );

		try {
			$stripeRefund = self::createStripeRefund( $stripe_charge_id, $amount_to_refund_in_cents );
		} catch(\Stripe\Exception\CardException $e) {
			$error = 'There was a problem charging your card: '.$e->getError()->message;
		} catch (\Stripe\Exception\RateLimitException $e) {
			$error = 'Too many requests made to the API too quickly: '.$e->getError()->message;
		} catch (\Stripe\Exception\InvalidRequestException $e) {
			$error = 'Invalid parameters were supplied to Stripe\'s API: '.$e->getError()->message;
		} catch (\Stripe\Exception\AuthenticationException $e) {
			$error = 'Authentication with Stripe\'s API failed: '.$e->getError()->message;
		} catch (\Stripe\Exception\ApiConnectionException $e) {
			$error = 'Network communication with Stripe failed: '.$e->getError()->message;
		} catch (\Stripe\Exception\ApiErrorException $e) {
			$error = 'Error processing payment. Contact customer support: '.$e->getError()->message;
		} catch (\Exception $e) {
			$error = 'System error: '.$e->getMessage();
		}

		if( !$error && $stripeRefund ) {
			return array(
				'success' => true,
				'stripe_refund' => $stripeRefund->id,
				'stripe_refund_amount' => $amount_to_refund_in_cents
			);
		} else {
			return array(
				'success' => false,
				'error' => $error
			);
		}
	}

	/**
	 * Create a Stripe Refund
	 * @param $stripe_charge_id
	 * @param $amount_to_refund
	 *
	 * @return \Stripe\Refund
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	static function createStripeRefund( $stripe_charge_id, $amount_to_refund ) {
		$params = [
			'charge' => $stripe_charge_id
		];

		if ( $amount_to_refund > 0 ) {
			$params['amount'] = $amount_to_refund;
		}

		return \Stripe\Refund::create($params);
	}


	/**
	 * Retrieve an Existing Stripe Customer
	 * @param WP_user $user
	 *
	 * @return bool|\Stripe\Customer
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	static function retrieveStripeCustomer( WP_User $user ) {

		if ( ! get_user_meta( $user->ID, 'stripe_customer_id', true ) ) {
			return false;
		}

		return \Stripe\Customer::retrieve( get_user_meta( $user->ID, 'stripe_customer_id', true ) );
	}


	/**
	 * @param WP_User $user
	 * @param $token
	 * @return \Stripe\Customer
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	static function createStripeCustomer(WP_User $user, $token) {
		$customer = \Stripe\Customer::create([
			'name' => $user->first_name . ' ' . $user->last_name,
			'email' => $user->user_email,
			'source' => $token,
			'address' => array(
				'line1' => get_user_meta( $user->ID, 'address_line_1', true ),
				'city' => get_user_meta( $user->ID, 'address_city', true  ),
				'state' => get_user_meta( $user->ID, 'address_state', true  ),
				'postal_code' => get_user_meta( $user->ID, 'address_zip', true  ),
			)
		]);
		return $customer;
	}

	/**
	 * Creates a charge on a customer object
	 * (Perfect for reoccurring charges, charges on customer that already exists, and up-sells)
	 * @param WP_User $user
	 * @param \Stripe\Customer $customer
	 * @param int $amount Amount to charge the customer card
	 * @param string $description
	 * @return Charge
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	static function createStripeCustomerCharge(WP_User $user, \Stripe\Customer $customer, $amount = 0, $description = 'OPA Payment')
	{

		$charge = \Stripe\Charge::create([
			'amount' => $amount,
			'currency' => 'usd',
			'description' => $description,
			'receipt_email' => $user->user_email,
			'customer' => $customer->id,
			'metadata[Line Item 1]' => 'Show Entry ' . $description . ' | Quantity: 1 | Item total: '. number_format( $amount / 100, 2 )
		]);
		return $charge;
	}

}
