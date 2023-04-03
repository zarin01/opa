<?php
class OPA_Model_Jury_Scores {

	/**
	 * Submit a Rating for a Piece of Art
	 * @param $juror_id
	 * @param $round_id
	 * @param $art_id
	 * @param $score
	 *
	 * @return false|int
	 */
	static function submit_rating( $juror_id, $round_id, $art_id, $score ) {
		global $wpdb;

		$rating_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}opa_jury_scores 
				WHERE jury_round_id = %d and juror_id = %d and art_id = %d",
				$round_id,
				$juror_id,
				$art_id
			)
		);

		if ( $rating_exists ) {
			return $wpdb->update(
				$wpdb->prefix . 'opa_jury_scores',
				array(
					'score' => $score
				),
				array(
					'jury_round_id' => $round_id,
					'juror_id' => $juror_id,
					'art_id' => $art_id,
				),
				array('%f')
			);
		} else {
			return $wpdb->insert(
				$wpdb->prefix . 'opa_jury_scores',
				array(
					'jury_round_id' => $round_id,
					'juror_id' => $juror_id,
					'art_id' => $art_id,
					'score' => $score,
					'count_scores_for_average' => 1
				),
				array('%d', '%d', '%d', '%f')
			);
		}
	}

	/**
	 * Get Artwork within a Round for a Juror
	 * @param $round_id
	 *
	 * @return array|object|null
	 */
	static function get_artwork( $round_id, $juror_id ) {
		global $wpdb;

		$artwork = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				jra.*, art.*, scores.score
				FROM {$wpdb->prefix}opa_jury_round_art jra
				LEFT JOIN {$wpdb->prefix}opa_art art ON art.id = jra.art_id
				LEFT JOIN {$wpdb->prefix}opa_jury_scores scores ON ( scores.juror_id = %d and scores.art_id = art.id and scores.jury_round_id = %d )
				WHERE jra.jury_round_id = %d",
				$juror_id,
				$round_id,
				$round_id
			),
			ARRAY_A
		);

		return $artwork;
	}

}
