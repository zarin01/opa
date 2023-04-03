<?php
class OPA_Model_Award {

	/**
	 * Add Award to a Show
	 * @param $show_id
	 * @param $award_title
	 * @param $award_description
	 * @param $award_value
	 *
	 * @return int
	 */
	static function add_award( $show_id, $award_title, $award_description, $award_value ) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'opa_awards',
			array(
				'show_id' => $show_id,
				'title' => $award_title,
				'description' => $award_description,
				'value' => $award_value,
			),
			array('%d', '%s', '%s', '%s')
		);

		return $wpdb->insert_id;
	}

}
