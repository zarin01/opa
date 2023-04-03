<?php
class OPA_Model_Show {

	/**
	 * Get Shows
	 * @return array
	 */
	static function get_shows() {

		$args = array(
			'post_type' => 'opa_show',
			'posts_per_page' => '-1'
		);
		$opa_shows = new WP_Query($args);

		return $opa_shows->posts;
	}

	/**
	 * Get Artists
	 * @param $show_id
	 *
	 * @return array|object|null
	 */
	static function get_artists( $show_id ) {

		global $wpdb;

		$artists = $wpdb->get_results(
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
				FROM {$wpdb->prefix}opa_art art
				LEFT JOIN {$wpdb->prefix}users u ON u.ID = art.artist_id
				LEFT JOIN {$wpdb->prefix}usermeta as first_name ON ( u.ID = first_name.user_id and first_name.meta_key = \"first_name\" )
				LEFT JOIN {$wpdb->prefix}usermeta as last_name ON ( u.ID = last_name.user_id and last_name.meta_key = \"last_name\" )
				LEFT JOIN {$wpdb->prefix}usermeta as address ON ( u.ID = address.user_id and address.meta_key = \"billing_address_1\" )
				LEFT JOIN {$wpdb->prefix}usermeta as city ON ( u.ID = city.user_id and city.meta_key = \"billing_city\" )
				LEFT JOIN {$wpdb->prefix}usermeta as state ON ( u.ID = state.user_id and state.meta_key = \"billing_state\" )
				LEFT JOIN {$wpdb->prefix}usermeta as zip ON ( u.ID = zip.user_id and zip.meta_key = \"billing_postcode\" )
				WHERE art.show_id = %d
				GROUP BY u.ID",
				$show_id
			),
			ARRAY_A
		);

		return $artists;

	}

	/**
	 * Get Jurors
	 * @param $show_id
	 */
	static function get_jurors( $show_id ) {

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
				zip.meta_value as \"Zip\"
				FROM {$wpdb->prefix}opa_jurors jurors
				LEFT JOIN {$wpdb->prefix}users u ON u.ID = jurors.juror_id
				LEFT JOIN {$wpdb->prefix}usermeta as first_name ON ( u.ID = first_name.user_id and first_name.meta_key = \"first_name\" )
				LEFT JOIN {$wpdb->prefix}usermeta as last_name ON ( u.ID = last_name.user_id and last_name.meta_key = \"last_name\" )
				LEFT JOIN {$wpdb->prefix}usermeta as address ON ( u.ID = address.user_id and address.meta_key = \"address_line_1\" )
				LEFT JOIN {$wpdb->prefix}usermeta as city ON ( u.ID = city.user_id and city.meta_key = \"address_city\" )
				LEFT JOIN {$wpdb->prefix}usermeta as state ON ( u.ID = state.user_id and state.meta_key = \"address_state\" )
				LEFT JOIN {$wpdb->prefix}usermeta as zip ON ( u.ID = zip.user_id and zip.meta_key = \"address_zip\" )
				
				WHERE jurors.show_id = %d 
				GROUP BY u.ID",
				$show_id
			),
			ARRAY_A
		);

		return $jurors;
	}

	//created a redundant query function to import only those jurors in the CSV file who have show_in_list status as 1
	static function get_jurors_for_csv( $show_id ) {

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
				zip.meta_value as \"Zip\"
				FROM {$wpdb->prefix}opa_jurors jurors
				LEFT JOIN {$wpdb->prefix}users u ON u.ID = jurors.juror_id
				LEFT JOIN {$wpdb->prefix}usermeta as first_name ON ( u.ID = first_name.user_id and first_name.meta_key = \"first_name\" )
				LEFT JOIN {$wpdb->prefix}usermeta as last_name ON ( u.ID = last_name.user_id and last_name.meta_key = \"last_name\" )
				LEFT JOIN {$wpdb->prefix}usermeta as address ON ( u.ID = address.user_id and address.meta_key = \"address_line_1\" )
				LEFT JOIN {$wpdb->prefix}usermeta as city ON ( u.ID = city.user_id and city.meta_key = \"address_city\" )
				LEFT JOIN {$wpdb->prefix}usermeta as state ON ( u.ID = state.user_id and state.meta_key = \"address_state\" )
				LEFT JOIN {$wpdb->prefix}usermeta as zip ON ( u.ID = zip.user_id and zip.meta_key = \"address_zip\" )
				
				WHERE jurors.show_id = %d  AND jurors.show_in_list=1
				GROUP BY u.ID",
				$show_id
			),
			ARRAY_A
		);

		return $jurors;
	}

	// static function get_record($show_id,$user_id)
	// {
	// 	global $wpdb;
	// 	$results = $wpdb->get_results("SELECT * FROM wp_opa_jurors WHERE show_id=$show_id AND juror_id = $user_id");
	// }

	static function get_show_in_list_status($show_id,$user_id)
	{
		global $wpdb;
		$results = $wpdb->get_results("SELECT show_in_list FROM wp_opa_jurors WHERE show_id=$show_id AND juror_id = $user_id");
		return $results;
	}

	/**
	 * Get Jury Rounds
	 * @param $show_id
	 *
	 * @return array|object|null
	 */
	static function get_jury_rounds( $show_id ) {

		global $wpdb;

		$jury_rounds = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				*
				FROM {$wpdb->prefix}opa_jury_rounds round
				WHERE round.show_id = %d",
				$show_id
			),
			ARRAY_A
		);

		return $jury_rounds;

	}

	/**
	 * Get Artists Art
	 * @param $user_id
	 * @param $show_id
	 *
	 * @return array|object|null
	 */
	static function get_artists_art( $user_id, $show_id ) {

		global $wpdb;

		$art_submissions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}opa_art WHERE artist_id = %d AND show_id = %d",
				$user_id,
				$show_id
			),
			ARRAY_A
		);

		return $art_submissions;

	}



	/**
	 * Get Artwork
	 * @param $show_id
	 *
	 * @return array|object|null
	 */
	static function get_artwork( $show_id ) {

		global $wpdb;

		$artwork = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}opa_art WHERE show_id = %d",
				$show_id
			),
			ARRAY_A
		);



		return $artwork;

	}

	static function get_duplicate_entires($show_id){
		global $wpdb;

		$q = "select artist_id from {$wpdb->prefix}opa_art where show_id = $show_id";

		$art_data = $wpdb->get_results($q);

		sort($art_data);

		$data = array();

		for($i=0; $i<count($art_data); $i++) {
			array_push($data,$art_data[$i]->artist_id);
		}

		$duplicate_entries = [];

		foreach (array_count_values($data) as $key => $value) {
			if($value > 1){
				array_push($duplicate_entries, $key);
			}
		}

		return $duplicate_entries;
	}

	static function get_artwork_pagination( $all_show_artwork, $page_number, $page_length) {

		global $wpdb;
if($filter){
	
}
		$art_min = $page_number * $page_length;
		$art_max = ($page_number + 1) * $page_length;

		$page_art = array();

		for ($i = 0; $i < count($all_show_artwork); $i++) {
			if ($i >= $art_min && $i < $art_max) {
				array_push($page_art, $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM {$wpdb->prefix}opa_art WHERE id = %d",
						intval($all_show_artwork[$i]['id'])
					),
					ARRAY_A
				)[0]);
			}
		}

		return $page_art;

	}

    /**
     * Get Artwork
     * @param $show_id
     *
     * @return array|object|null
     */
    static function get_artwork_without_image_code( $show_id,$filter ) {

        global $wpdb;
if($filter){
	if(is_numeric($filter)){
		$filter_string = 'and artist_id='.$filter.' or id='.$filter;
	}else{
        $filter_string = 'and painting_name like "%'.$filter.'%"';
	}
	
}else{
	$filter_string = '';	
}

        $artwork = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT `id`,`show_id`,`artist_id`,`award_id`,`painting_name`,`painting_description`,`painting_price`,`painting_width`,`painting_height`,`accepted`,`stripe_charge_id`,`stripe_payment_amount`,`stripe_payment_date`,`stripe_refunded_amount` FROM {$wpdb->prefix}opa_art WHERE show_id = %d ".$filter_string,
                $show_id
            ),
            ARRAY_A
        );

        return $artwork;

    }

    static function get_artwork_artists( $show_id,$round_id ) {

        global $wpdb;
        //  SELECT wp_users.`user_nicename`,wp_users.`user_email`,wp_opa_art.`id`,wp_opa_art.`show_id`,wp_opa_art.`artist_id`,wp_opa_art.`award_id`,wp_opa_art.`painting_name`,wp_opa_art.`painting_description`,wp_opa_art.`painting_price`,wp_opa_art.`painting_width`,wp_opa_art.`painting_height`,wp_opa_art.`accepted`,wp_opa_art.`stripe_charge_id`,wp_opa_art.`stripe_payment_amount`,wp_opa_art.`stripe_payment_date`,wp_opa_art.`stripe_refunded_amount`,wp_opa_jury_round_art.jury_round_id FROM wp_opa_art left join wp_opa_jury_round_art on wp_opa_jury_round_art.art_id= wp_opa_art.`id` LEFT JOIN wp_users ON wp_users.id = wp_opa_art.artist_id WHERE show_id = 14122 and wp_opa_jury_round_art.move_to_next_round = 1

		//LEFT JOIN {$wpdb->prefix}opa_jury_round_art on {$wpdb->prefix}opa_jury_round_art.art_id= {$wpdb->prefix}opa_art.`id`
		//SELECT wp_users.`user_nicename`,wp_users.`user_email`,wp_opa_art.`id`,wp_opa_art.`show_id`,wp_opa_art.`artist_id`,wp_opa_art.`award_id`,wp_opa_art.`painting_name`,wp_opa_art.`painting_description`,wp_opa_art.`painting_price`,wp_opa_art.`painting_width`,wp_opa_art.`painting_height`,wp_opa_art.`accepted`,wp_opa_art.`stripe_charge_id`,wp_opa_art.`stripe_payment_amount`,wp_opa_art.`stripe_payment_date`,wp_opa_art.`stripe_refunded_amount` FROM `wp_opa_jury_round_art` LEFT JOIN wp_opa_art on wp_opa_art.id= wp_opa_jury_round_art.art_id LEFT JOIN wp_users ON wp_users.id = wp_opa_art.artist_id WHERE wp_opa_art.show_id = 14122 and `jury_round_id` = 16 and move_to_next_round = 1
//		 echo   "SELECT {$wpdb->prefix}users.`user_nicename`,{$wpdb->prefix}users.`user_email`,{$wpdb->prefix}opa_art.`id`,{$wpdb->prefix}opa_art.`show_id`,{$wpdb->prefix}opa_art.`artist_id`,{$wpdb->prefix}opa_art.`award_id`,{$wpdb->prefix}opa_art.`painting_name`,{$wpdb->prefix}opa_art.`painting_description`,{$wpdb->prefix}opa_art.`painting_price`,{$wpdb->prefix}opa_art.`painting_width`,{$wpdb->prefix}opa_art.`painting_height`,{$wpdb->prefix}opa_art.`accepted`,{$wpdb->prefix}opa_art.`stripe_charge_id`,{$wpdb->prefix}opa_art.`stripe_payment_amount`,{$wpdb->prefix}opa_art.`stripe_payment_date`,{$wpdb->prefix}opa_art.`stripe_refunded_amount`, score_data.average FROM `{$wpdb->prefix}opa_jury_round_art` LEFT JOIN {$wpdb->prefix}opa_art on {$wpdb->prefix}opa_art.id= {$wpdb->prefix}opa_jury_round_art.art_id LEFT JOIN {$wpdb->prefix}users ON {$wpdb->prefix}users.id = {$wpdb->prefix}opa_art.artist_id  RIGHT JOIN (select art_id, ROUND(SUM(score)/7,2) as average from  {$wpdb->prefix}opa_jury_scores where jury_round_id = {$round_id} GROUP BY art_id) as score_data on score_data.art_id ={$wpdb->prefix}opa_art.id where {$wpdb->prefix}opa_art.show_id = {$show_id} and `jury_round_id` = {$round_id} and move_to_next_round = 1";
//		 die;
        $artwork = $wpdb->get_results(
		     $wpdb->prepare(
                 "SELECT {$wpdb->prefix}users.`user_nicename`,{$wpdb->prefix}users.`user_email`,{$wpdb->prefix}opa_art.`id`,{$wpdb->prefix}opa_art.`show_id`,{$wpdb->prefix}opa_art.`artist_id`,{$wpdb->prefix}opa_art.`award_id`,{$wpdb->prefix}opa_art.`painting_name`,{$wpdb->prefix}opa_art.`painting_description`,{$wpdb->prefix}opa_art.`painting_price`,{$wpdb->prefix}opa_art.`painting_width`,{$wpdb->prefix}opa_art.`painting_height`,score_data.average_score, {$wpdb->prefix}opa_art.`accepted`,{$wpdb->prefix}opa_art.`stripe_charge_id`,{$wpdb->prefix}opa_art.`stripe_payment_amount`,{$wpdb->prefix}opa_art.`stripe_payment_date`,{$wpdb->prefix}opa_art.`stripe_refunded_amount` FROM `{$wpdb->prefix}opa_jury_round_art` LEFT JOIN {$wpdb->prefix}opa_art on {$wpdb->prefix}opa_art.id= {$wpdb->prefix}opa_jury_round_art.art_id LEFT JOIN {$wpdb->prefix}users ON {$wpdb->prefix}users.id = {$wpdb->prefix}opa_art.artist_id  RIGHT JOIN (select art_id, ROUND(SUM(score)/7,2) as average_score from  {$wpdb->prefix}opa_jury_scores where jury_round_id = {$round_id} GROUP BY art_id) as score_data on score_data.art_id ={$wpdb->prefix}opa_art.id where {$wpdb->prefix}opa_art.show_id = {$show_id} and `jury_round_id` = {$round_id} and move_to_next_round = 1",
                $show_id
            ),
            ARRAY_A
        );

        return $artwork;

    }
    /**
	 * Get Revenue Information
	 * @param $show_id
	 */
	static function get_revenue_info( $show_id ) {
		global $wpdb;

		$art_submissions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ROUND( SUM( stripe_payment_amount ), 2 ) payments, ROUND( SUM( stripe_refunded_amount ), 2 ) refunds
 				FROM {$wpdb->prefix}opa_art 
 				WHERE show_id = %d",
				$show_id
			),
			ARRAY_A
		);

		return $art_submissions;
	}

	/**
	 * Get Current Jury Round for a Show
	 * @param $show_id
	 *
	 * @return int
	 */
	static function get_current_jury_round_id( $show_id ) {
		global $wpdb;

		$jury_rounds = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				*
				FROM {$wpdb->prefix}opa_jury_rounds round
				WHERE round.show_id = %d and round.jury_round_active = 1",
				$show_id
			),
			ARRAY_A
		);

		return !empty( $jury_rounds ) ? intval( $jury_rounds[0]['id'] ) : 0;
	}

	/**
	 * Get Awards for this Show
	 * @param $show_id
	 *
	 * @return array|object|null
	 */
	static function get_awards( $show_id ) {
		global $wpdb;

		$awards = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				*
				FROM {$wpdb->prefix}opa_awards award
				WHERE award.show_id = %d",
				$show_id
			),
			ARRAY_A
		);

		return $awards;
	}

	static function get_accepted_art( $show_id ) {
		global $wpdb;

		$awards = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
				*
				FROM {$wpdb->prefix}opa_art art
				WHERE art.show_id = %d
				AND art.accepted = 1",
				$show_id
			),
			ARRAY_A
		);

		return $awards;
	}

}
