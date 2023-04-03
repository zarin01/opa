<?php
class OPA_Model_Jurors {

	/**
	 * Add a Juror to a Show
	 * @param $user_id
	 * @param $show_id
	 *
	 * @return int
	 */
	static function add_juror( $user_id, $show_id ) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'opa_jurors',
			array(
				'show_id' => $show_id,
				'juror_id' => $user_id,
			),
			array('%d', '%d')
		);

		return $wpdb->insert_id;
	}

	/**
	 * Get Juror
	 * @param $round_id
	 *
	 * @return array|object|null
	 */
	static function get_juror( $user_id ) {
		global $wpdb;

		$juror = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				u.ID,
				first_name.meta_value as \"First Name\",
				last_name.meta_value as \"Last Name\",
				u.user_email as \"Email\", 
				address.meta_value as \"Address\",
				city.meta_value as \"City\",
				state.meta_value as \"State\",
				zip.meta_value as \"Zip\"
				FROM {$wpdb->prefix}opa_jurors jurors
				LEFT JOIN {$wpdb->prefix}users u ON u.ID = jurors.juror_id
				LEFT JOIN {$wpdb->prefix}usermeta as first_name ON ( u.ID = first_name.user_id and first_name.meta_key = \"first_name\" )
				LEFT JOIN {$wpdb->prefix}usermeta as last_name ON ( u.ID = last_name.user_id and last_name.meta_key = \"last_name\" )
				LEFT JOIN {$wpdb->prefix}usermeta as address ON ( u.ID = address.user_id and address.meta_key = \"address_line_1\" )
				LEFT JOIN {$wpdb->prefix}usermeta as city ON ( u.ID = city.user_id and city.meta_key = \"address_city\" )
				LEFT JOIN {$wpdb->prefix}usermeta as state ON ( u.ID = state.user_id and state.meta_key = \"address_state\" )
				LEFT JOIN {$wpdb->prefix}usermeta as zip ON ( u.ID = zip.user_id and zip.meta_key = \"address_zip\" )
				WHERE jurors.juror_id = %d",
				$user_id
			),
			ARRAY_A
		);

		return $juror;
	}

	/**
	 * Get Jurors Scores for a Round
	 * @param $round_id
	 *
	 * @return array|object|null
	 */
	static function get_round_scores( $user_id, $round_id ) {
		global $wpdb;

		$round_scoring = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				*
				FROM {$wpdb->prefix}opa_jury_round_art jra
				LEFT JOIN {$wpdb->prefix}opa_art art ON art.id = jra.art_id
				RIGHT JOIN {$wpdb->prefix}opa_jury_scores js ON ( js.jury_round_id = jra.jury_round_id and art.id = js.art_id and js.juror_id = %d )
				WHERE jra.jury_round_id = %d",
				$user_id,
				$round_id
			),
			ARRAY_A
		);

		return $round_scoring;
	}


	/**
	 * Check if Juror has access to a specific round ID
	 * @param $round_id
	 * @param $juror_id
	 *
	 * @return bool
	 */
	static function has_round_access( $round_id, $juror_id ) {
		global $wpdb;

		$round_scoring = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				*
				FROM {$wpdb->prefix}opa_jury_round_jurors jrj
				WHERE jrj.jury_round_id = %d and jrj.juror_id = %d",
				$round_id,
				$juror_id
			),
			ARRAY_A
		);

		return ( !empty( $round_scoring ) && intval( $round_scoring[0]['juror_active'] ) === 1 );
	}

}
