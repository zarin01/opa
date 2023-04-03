<?php
class OPA_Model_Art
{

	/**
	 * Create a new User Registration
	 * @param WP_User $user
	 * @param $show_id
	 * @param $painting_name
	 * @param $painting_description
	 * @param $painting_file_base_64
	 * @param $stripe_charge_id
	 * @param $stripe_payment_amount
	 * @param $stripe_processed_date
	 *
	 * @return int
	 */
	static function create_user_registration(WP_User $user, $show_id, $painting_name, $painting_description, $painting_price, $painting_width, $painting_height, $painting_file_base_64,$painting_file_original,$painting_file_name, $stripe_charge_id, $stripe_payment_amount, $stripe_processed_date, $not_for_sale)
	{
		global $wpdb;

		$painting = OPA_Profile::upload_image_to_media($painting_name,$painting_file_original,$painting_file_name);

		$wpdb->insert(
			$wpdb->prefix . 'opa_art',
			array(
				'show_id' => $show_id,
				'artist_id' => $user->ID,
				'painting_name' => $painting_name,
				'painting_description' => $painting_description,
				'painting_price' => $painting_price,
				'painting_width' => $painting_width,
				'painting_height' => $painting_height,
				'painting_file' => $painting,
				'painting_file_original' => $painting,
				'stripe_charge_id' => $stripe_charge_id,
				'stripe_payment_amount' => $stripe_payment_amount,
				'stripe_payment_date' => $stripe_processed_date,
				'stripe_refunded_amount' => 0,
				'painting_not_for_sale' => $not_for_sale
			),
			array('%d', '%d', '%s', '%s', '%f', '%d', '%d', '%s', '%s', '%s', '%f', '%s', '%d', '%d')
		);

		return $wpdb->insert_id;
	}

	/**
	 * Get User Registrations from the Database
	 * @param WP_User $user
	 * @param int $show_id
	 *
	 * @return int
	 */
	static function get_user_registrations(WP_User $user, $show_id)
	{
		global $wpdb;

		$registrations = $wpdb->get_var(
			// 
			// $wpdb->prepare(
			// 	"SELECT COUNT(*) FROM {$wpdb->prefix}opa_art WHERE artist_id =".$user->ID." and show_id=".$show_id
			// )
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}opa_art WHERE artist_id = %d and show_id = %d",
				$user->ID,
				$show_id
			)
		);

		return intval($registrations);
	}

	/**
	 * Get Registrations by a specific column, comparison, value
	 * @param $column
	 * @param $value
	 * @param string $comparison
	 *
	 * @return array|object|null
	 */
	static function get_registrations_by($id, $column, $value, $comparison = '=')
	{
		global $wpdb;
		if ($column == 'stripe_charge_id') {
			$query = "SELECT *
FROM {$wpdb->prefix}opa_art
WHERE {$column} {$comparison} \"{$value}\" and id=" . $id;
		} else {
			$query = "
SELECT *
FROM {$wpdb->prefix}opa_art
WHERE {$column} {$comparison} \"{$value}\"";
		}

		$registrations = $wpdb->get_results($query);

		return $registrations;
	}

	/**
	 * Get User
	 * @param WP_User $user
	 * @param $show_id
	 *
	 * @return int
	 */
	static function get_user_registrations_remaining(WP_User $user, $show_id)
	{
		return count(OPA_SHOW_REGISTRATION_FEE) - self::get_user_registrations($user, $show_id);
	}

	/**
	 * Determines Price for User Registration (Tiered)
	 * @param WP_User $user
	 * @param $show_id
	 *
	 * @return mixed
	 */
	static function get_user_price_for_registration(WP_User $user, $show_id)
	{
		$registrations = self::get_user_registrations($user, $show_id);
		return array_key_exists($registrations, OPA_SHOW_REGISTRATION_FEE) ? OPA_SHOW_REGISTRATION_FEE[$registrations] : OPA_SHOW_REGISTRATION_FEE[0];
	}

	/**
	 * Updates the refunded amount for a Stripe Charge
	 * @param $stripe_charge_id
	 * @param $amount_refunded_in_dollars
	 */
	static function update_refunded_amount($art_id, $stripe_charge_id, $amount_refunded_in_dollars)
	{

		global $wpdb;

		$wpdb->update(
			$wpdb->prefix . 'opa_art',
			array(
				'stripe_refunded_amount' => $amount_refunded_in_dollars
			),
			array(
				'stripe_charge_id' => $stripe_charge_id,
				'id' => $art_id
			)
		);
	}

	/**
	 * Updates the image in the Database
	 * @param $art_id
	 * @param $blob
	 */
	static function update_image($art_id, $blob)
	{
		global $wpdb;

		$wpdb->update(
			$wpdb->prefix . 'opa_art',
			array(
				'painting_file' => $blob
			),
			array(
				'id' => $art_id
			)
		);
	}

	/**
	 * Update Artwork Acceptance
	 * @param $art_id
	 * @param $active
	 */
	static function update_acceptance($art_id, $active)
	{
		global $wpdb;

		$wpdb->update(
			$wpdb->prefix . 'opa_art',
			array(
				'accepted' => $active
			),
			array(
				'id' => $art_id
			)
		);
	}


	static function link_award($award_id, $art_id, $div_id)
	{
		global $wpdb;

		$wpdb->update(
			$wpdb->prefix . 'opa_art',
			array(
				'award_id' => $award_id,
				'division_id' => $div_id
			),
			array(
				'id' => $art_id
			)
		);

		return $award_id;
	}
}
