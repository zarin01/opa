<?php
class OPA_Model_Jury_Round_Art {

    /**
     * Gets the Artwork from the previous round that is good enough for the next round of judging
     * @param $show_id
     *
     * @return array|object|null
     */
    static function get_artwork_for_next_round( $show_id ) {
        global $wpdb;

        $artwork = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *
				FROM {$wpdb->prefix}opa_jury_round_art jra
				RIGHT JOIN (
					SELECT jr.* FROM {$wpdb->prefix}opa_jury_rounds as jr WHERE jr.show_id = %d ORDER BY jr.created_at DESC LIMIT 1
				) as tmp ON jra.jury_round_id = tmp.id
				WHERE jra.move_to_next_round = 1",
                $show_id
            ),
            ARRAY_A
        );

        return $artwork;
    }

    /**
     * Add Artwork to a Round
     * @param $round_id
     * @param $art_id
     *
     * @return false|int
     */
    static function add_artwork( $round_id, $art_id ) {
        global $wpdb;

        return $wpdb->insert(
            $wpdb->prefix . 'opa_jury_round_art',
            array(
                'jury_round_id' => $round_id,
                'art_id' => $art_id,
                'art_active' => 1
            ),
            array('%d', '%d', '%d')
        );
    }

    /**
     * Get Artwork within a Round
     * @param $round_id
     *
     * @return array|object|null
     */
    static function get_artwork( $round_id,$score_val,$last_name="" ,$filterField='', $filterOrder='') {

        global $wpdb;

        $score_raw_value = $score_val == null ? '' : $score_val;
        $score = explode('-',$score_raw_value);
        $score_value = $score[0];
        $score_operator = $score[1];
        if(!$score_operator){
            $score_operator = '<';
        }
        if($score_value){
            $add_string = 'and scores.average '.$score_operator.' '.$score_value . ' ';
        }else{
            $add_string = '';
        }

        $orderByField = 'average';
            if($filterField){
            $orderByField = ($filterField=='lastname')?'m1.meta_value':$filterField;
        }


        $orderBy = 'ASC';
        if($filterOrder){
            $orderBy = $filterOrder;
        }
        $search_last_name = $last_name ? true : false;
         $jurorsCount = count(OPA_Model_Jury_Round_Jurors::get_jurors($round_id ))??1;


        $artwork = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
				jra.*, art.*" . ($score_val == null ? ' ' : ", scores.average, scores.amount ") .
                "FROM {$wpdb->prefix}opa_jury_round_art jra
				LEFT JOIN {$wpdb->prefix}opa_art art ON art.id = jra.art_id 
				" . ($search_last_name || $filterField=='lastname'? "JOIN {$wpdb->prefix}usermeta m1 ON m1.user_id = art.artist_id " : "") .
                ($score_val == null ? '' : "RIGHT JOIN (
					SELECT art_id, COUNT(score) as amount, ROUND(SUM(score)/{$jurorsCount},2) as average
					FROM {$wpdb->prefix}opa_jury_scores
					WHERE count_scores_for_average = 1 AND jury_round_id = %d " . "
					GROUP BY art_id
				) as scores ON scores.art_id = jra.art_id ") .
                "WHERE jury_round_id = %d $acceptedOrder" . $add_string .
                ($search_last_name ?
                    "AND m1.meta_key = 'billing_last_name'
				AND m1.meta_value LIKE '" . $last_name . "%'"
                    : "") .
                ($filterField=='lastname' ?
                    "AND m1.meta_key = 'billing_last_name'":""
                    ) .
                ($score_val == null ? '' : "ORDER BY $orderByField $orderBy"),
                $round_id,
                $round_id
            ),
            ARRAY_A
        );

        return $artwork;
    }


    static function get_dulicate_artwork( $round_id,$score_val ) {

        $score_raw_value = $score_val == null ? '' : $score_val;
        $score = explode('-',$score_raw_value);
        $score_value = $score[0];
        $score_operator = $score[1];
        if(!$score_operator){
            $score_operator = '<';
        }
        if($score_value){
            $add_string = 'and scores.average '.$score_operator.' '.$score_value . ' ';
        }else{
            $add_string = '';
        }
        $jurorsCount = count(OPA_Model_Jury_Round_Jurors::get_jurors($round_id ))??1;
        global $wpdb;
// 		$score = explode('-',$score_val);
// 		$score_val = $score[0];
// 		$score_operator = $score[1];
// 		if(!$score_operator){
// 			$score_operator = '<';
// 		}
// if($score_val){
// 	$add_string = 'and `score` '.$score_operator.' '.$score_val;
// }else{
// 	$add_string = '';
// }

        $artwork = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                jra.*, art.*, scores.average, scores.amount 
                FROM {$wpdb->prefix}opa_jury_round_art jra
                LEFT JOIN {$wpdb->prefix}opa_art art ON art.id = jra.art_id
                RIGHT JOIN (
                    SELECT art_id, COUNT(score) as amount, ROUND(SUM(score)/{$jurorsCount},2) as average
                    FROM {$wpdb->prefix}opa_jury_scores
                    WHERE jury_round_id = %d 
                    GROUP BY art_id
                ) as scores ON scores.art_id = jra.art_id
                WHERE count_scores_for_average = 1 AND jury_round_id = %d ".$add_string."
                group BY scores.art_id",
                $round_id,
                $round_id
            ),
            ARRAY_A
        );
        $Mfr = array_column($artwork,'artist_id');

        $dupes = array_diff(array_count_values($Mfr), [1]);

        foreach($dupes as $key => $val){
            $res[] = max(array_intersect_key(array_column($artwork, 'artist_id'), array_intersect($Mfr, [$key])));
        }
        $duplicates = array();
        foreach($artwork as $key=>$art){
            if(in_array($art['artist_id'],$res)){
                $duplicates[] =  $art;

            }

        }
        return $duplicates;
        //return $artwork;

        $duplicate_score = array();
        foreach($artwork as $key=>$art){
            if(in_array($art['score_id'],$res)){
                $duplicate_score[] =  $art;

            }
        }
        return $duplicate_score;
        //return artwork with duplicate scores;

    }
    /**
     * Get Artwork within a Round
     * @param $round_id
     *
     * @return array|object|null
     */
    static function get_artwork_without_image_code( $round_id ) {
        global $wpdb;

        $artwork = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
				jra.*, art.id,art.show_id,art.artist_id,art.award_id,art.painting_name,art.painting_description,art.painting_price,art.painting_width,art.painting_height,art.accepted,art.stripe_charge_id,art.stripe_payment_amount,art.stripe_payment_date,art.stripe_refunded_amount, scores.average, scores.amount 
				FROM {$wpdb->prefix}opa_jury_round_art jra
				LEFT JOIN {$wpdb->prefix}opa_art art ON art.id = jra.art_id
				LEFT JOIN (
					SELECT art_id, COUNT(score) as amount, ROUND( AVG(score), 2 ) average
					FROM {$wpdb->prefix}opa_jury_scores
					WHERE jury_round_id = %d
					GROUP BY art_id
				) as scores ON scores.art_id = jra.art_id
				WHERE jury_round_id = %d
				ORDER BY average DESC",
                $round_id,
                $round_id
            ),
            ARRAY_A
        );

        return $artwork;
    }

    static function get_artwork_accepted( $round_id ) {
        global $wpdb;

        $artwork = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
				jra.*, art.*, scores.average, scores.amount 
				FROM {$wpdb->prefix}opa_jury_round_art jra
				LEFT JOIN {$wpdb->prefix}opa_art art ON art.id = jra.art_id
				LEFT JOIN (
					SELECT art_id, COUNT(score) as amount, ROUND( AVG(score), 2 ) average
					FROM {$wpdb->prefix}opa_jury_scores
					WHERE jury_round_id = %d
					GROUP BY art_id
				) as scores ON scores.art_id = jra.art_id
				WHERE jury_round_id = %d AND art.accepted = 1
				ORDER BY average DESC",
                $round_id,
                $round_id
            ),
            ARRAY_A
        );

        return $artwork;
    }


    /**
     * Remove Artwork from a Round
     * @param $round_id
     * @param $art_id
     *
     * @return false|int
     */
    static function remove_artwork( $round_id, $art_id ) {
        global $wpdb;

        return $wpdb->delete(
            $wpdb->prefix . 'opa_jury_round_art',
            array(
                'jury_round_id' => $round_id,
                'art_id' => $art_id
            ),
            array('%d', '%d')
        );
    }

    /**
     * Activates or Deactivates a piece of art within the round
     * @param $round_id
     * @param $art_id
     * @param $active
     *
     * @return false|int
     */
    static function activate_or_deactivate_art( $round_id, $art_id, $active ) {
        global $wpdb;

        return $wpdb->update(
            $wpdb->prefix . 'opa_jury_round_art',
            array(
                'art_active' => $active
            ),
            array(
                'jury_round_id' => $round_id,
                'art_id' => $art_id,
            ),
            array('%d', '%d', '%d')
        );
    }

    /**
     * Activates or Deactivates a piece of art prep for next round
     * @param $round_id
     * @param $art_id
     * @param $active
     *
     * @return false|int
     */
    static function activate_or_deactivate_art_next_round( $round_id, $art_id, $active ) {
        global $wpdb;

        return $wpdb->update(
            $wpdb->prefix . 'opa_jury_round_art',
            array(
                'move_to_next_round' => $active
            ),
            array(
                'jury_round_id' => $round_id,
                'art_id' => $art_id,
            ),
            array('%d', '%d', '%d')
        );
    }

}
