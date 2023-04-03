<?php
class OPA_Model_Artist {

	/**
	 * Get Artwork for Artist
	 * @param $user_id
	 *
	 * @return array|object|null
	 */
	static function get_artwork( $user_id ) {

		global $wpdb;

		$artwork = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				art.*, 
				sh.post_title as `show_title`, 
				reg_st.meta_value as `registration_start_date`,
				reg_end.meta_value as `registration_end_date`
				FROM {$wpdb->prefix}opa_art art
				LEFT JOIN {$wpdb->prefix}posts sh ON sh.ID = art.show_id
				LEFT JOIN {$wpdb->prefix}postmeta as reg_st ON ( sh.ID = reg_st.post_id and reg_st.meta_key = \"opa_start_registration_date\" )
				LEFT JOIN {$wpdb->prefix}postmeta as reg_end ON ( sh.ID = reg_end.post_id and reg_end.meta_key = \"opa_end_registration_date\" )
				WHERE artist_id = %d
				ORDER BY stripe_payment_date DESC",
				$user_id
			),
			ARRAY_A
		);

		return $artwork;
	}

	/**
	 * Update Artwork
	 * @param $painting_id
	 * @param $painting_name
	 * @param $painting_description
	 * @param $painting_price
	 * @param $painting_width
	 * @param $painting_height
	 * @param $painting_file
	 */
	static function update_artwork( $painting_id, $painting_name, $painting_description, $painting_price, $painting_width, $painting_height, $painting_file,$painting_file_original ) {
		global $wpdb;

		$update = array(
			'painting_name' => $painting_name,
			'painting_description' => $painting_description,
			'painting_price' => $painting_price,
			'painting_width' => $painting_width,
			'painting_height' => $painting_height,
		);

		if ($painting_file_original) {
			$update['painting_file_original'] = $painting_file_original;
		}

		if ( $painting_file ) {
			$update['painting_file'] = $painting_file;
		}

		$wpdb->update(
			$wpdb->prefix . 'opa_art',
			$update,
			array(
				'id' => $painting_id
			)
		);
	}

}
