<?php
class OPA_Model_Jury_Rounds {

	/**
	 * Adds a Round
	 * @param $show_id
	 * @param $round_name
	 *
	 * @return int
	 */
	static function add_round( $show_id, $round_name ) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'opa_jury_rounds',
			array(
				'show_id' => $show_id,
				'jury_round_name' => $round_name,
				'created_at' => current_time( 'mysql' )
			),
			array('%d', '%s', '%s')
		);

		return $wpdb->insert_id;
	}

	/**
	 * Gets a Round
	 * @param $round_id
	 *
	 * @return array|object|null
	 */
	static function get_round( $round_id ) {
		global $wpdb;

		$artwork = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				* 
				FROM {$wpdb->prefix}opa_jury_rounds
				WHERE id = %d",
				$round_id
			),
			ARRAY_A
		);

		return $artwork;
	}

	/**
	 * Close a Round
	 * @param $round_id
	 *
	 * @return false|int
	 */
	static function close_round( $round_id ) {
		global $wpdb;

		return $wpdb->update(
			$wpdb->prefix . 'opa_jury_rounds',
			array(
				'jury_round_active' => 0
			),
			array(
				'id' => $round_id
			),
			array('%d')
		);
	}

}
