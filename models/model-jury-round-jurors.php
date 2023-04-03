<?php
class OPA_Model_Jury_Round_Jurors {

	/**
	 * Add a Juror to a Round
	 * @param $juror_id
	 * @param $round_id
	 *
	 * @return false|int
	 */
	static function add_juror( $juror_id, $round_id ) {
		global $wpdb;

		return $wpdb->insert(
			$wpdb->prefix . 'opa_jury_round_jurors',
			array(
				'jury_round_id' => $round_id,
				'juror_id' => $juror_id,
				'juror_active' => 1
			),
			array('%d', '%d', '%d')
		);
	}

	/**
	 * Get Jurors for a Specific Round
	 * @param $round_id
	 *
	 * @return array|object|null
	 */
	static function get_jurors( $round_id ) {
		global $wpdb;

		$jurors = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				u.ID,
				first_name.meta_value as \"First Name\",
				last_name.meta_value as \"Last Name\",
				u.user_email as \"Email\", 
				address.meta_value as \"Address\",
				city.meta_value as \"City\",
				state.meta_value as \"State\",
				zip.meta_value as \"Zip\",
				jrj.juror_active as \"Status\",
				scores.completions as \"Completions\", 
				scores.average as \"Average Score\"
				FROM {$wpdb->prefix}opa_jurors jurors
				LEFT JOIN {$wpdb->prefix}users u ON u.ID = jurors.juror_id
				LEFT JOIN {$wpdb->prefix}usermeta as first_name ON ( u.ID = first_name.user_id and first_name.meta_key = \"first_name\" )
				LEFT JOIN {$wpdb->prefix}usermeta as last_name ON ( u.ID = last_name.user_id and last_name.meta_key = \"last_name\" )
				LEFT JOIN {$wpdb->prefix}usermeta as address ON ( u.ID = address.user_id and address.meta_key = \"address_line_1\" )
				LEFT JOIN {$wpdb->prefix}usermeta as city ON ( u.ID = city.user_id and city.meta_key = \"address_city\" )
				LEFT JOIN {$wpdb->prefix}usermeta as state ON ( u.ID = state.user_id and state.meta_key = \"address_state\" )
				LEFT JOIN {$wpdb->prefix}usermeta as zip ON ( u.ID = zip.user_id and zip.meta_key = \"address_zip\" )
				LEFT JOIN (
					SELECT juror_id, jury_round_id, count(id) completions, ROUND( AVG(score), 2 ) average
					FROM {$wpdb->prefix}opa_jury_scores
					WHERE jury_round_id = %d and score!=0.00
					GROUP BY juror_id
				) as scores ON ( scores.juror_id = jurors.juror_id )
				RIGHT JOIN {$wpdb->prefix}opa_jury_round_jurors as jrj ON ( jurors.juror_id = jrj.juror_id )
				WHERE jrj.jury_round_id = %d and jrj.show_in_list=1
				GROUP BY u.ID",
				$round_id,
				$round_id
			),
			ARRAY_A
		);

		return $jurors;
	}

	static function get_show_in_list_status($juror_id,$round_id)
	{
		global $wpdb;
        $table = $wpdb->prefix.'opa_jury_round_jurors';
		$results = $wpdb->get_results("SELECT show_in_list FROM $table WHERE jury_round_id=$round_id AND juror_id = $juror_id");
		return $results;
	}

	/**
	 * Activates or Deactivates a Juror within the round
	 * @param $round_id
	 * @param $juror_id
	 * @param $active
	 *
	 * @return false|int
	 */
	static function activate_or_deactivate_juror( $round_id, $juror_id, $active ) {
		global $wpdb;

		return $wpdb->update(
			$wpdb->prefix . 'opa_jury_round_jurors',
			array(
				'juror_active' => $active
			),
			array(
				'jury_round_id' => $round_id,
				'juror_id' => $juror_id,
			),
			array('%d', '%d', '%d')
		);
	}

}
