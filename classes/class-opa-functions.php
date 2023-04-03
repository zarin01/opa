<?php
class OPA_Functions {
	static function build_table( $array, $more_class, $more_text = null ) {

		if ( !is_array( $array ) || empty( $array ) ) {
			return "<p>No results.</p>";
		}
		global $wpdb;
		$table = $wpdb->prefix.'opa_art';
		$html = '<div class="opa-table-wrapper">';
			$html .= '<table class="opa-table">';
				$html .= '<thead class="opa-table__thead">';
					$html .= '<tr class="opa-table__tr">';
						foreach ( $array[0] as $key => $value ) {
							$html .= '<th class="opa-table__th">' . htmlspecialchars( $key ) . '</th>';
						}
						if ( $more_class !== 'hide' ) {
							$html .= '<th class="opa-table__th">' . __( '...', OPA_DOMAIN ) . '</th>';
						}
					$html .= '</tr>';
				$html .= '</thead>';

				$html .= '<tbody class="opa-table__tbody">';
					foreach ( $array as $key => $value ) {
						$html .= '<tr class="opa-table__tr opa-title_'.$key.'">';
							foreach ( $value as $key2 => $value2 ) {
								if($key2 == 'ID')
								{
									$artID = $value2;
								}
								//echo $key2."		".$value2,"<br>";
								 if($key2 == 'Painting Details')
								 {
				
								 		$artistID = $wpdb->get_results("SELECT artist_id from $table WHERE id = $artID");
										$schoolName = get_user_meta($artistID[0]->artist_id,'_billing_school_name',true);
										$age = get_user_meta($artistID[0]->artist_id,'_billing_age',true);
										if($schoolName && $age)
										{
								 			$html .= '<td class="opa-table__td opa-title_'.$key2.'">'.$value2."School Name: ".$schoolName."<br><br>Age: ".$age.'</td>';
										}
										else
										{
											$html .= '<td class="opa-table__td opa-title_'.$key2.'">' . $value2 .'</td>';
										}
								 }
								 else
								 {
									$html .= '<td class="opa-table__td opa-title_'.$key2.'">' . $value2 .'</td>';
								}
							}
							if ( $more_class !== 'hide' ) {
								$html .= '<td class="opa-table__td "><span class="opa-table__expand opa-table__expand--' . $more_class . '">' . ( $more_text !== null ? $more_text : __( 'More', OPA_DOMAIN ) ) . '</span></td>';
							}
						$html .= '</tr>';
					}
				$html .= '</tbody>';
			$html .= '</table>';
		$html .= '</div>';

		return $html;
	}



	static function build_show_pagination( $all_show_artwork, $url, $current_page_number, $page_length) {

		$html = '<div class="opa-show-pagination" style="margin-top: 20px;">';
/*
		global $wpdb;

		$artwork_ids = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT `id` FROM {$wpdb->prefix}opa_art WHERE show_id = %d",
				$show_id
			),
			ARRAY_A
		);*/

		if (strpos($url, '&page_number=') !== false) {
			$page_number_from_url = explode("&", explode("&page_number=", $url)[1])[0];
			$safe_url = str_replace("&page_number=" . $page_number_from_url, "", $url); 
		}else{
			$safe_url = $url;
		}

		for ($i = 0; $i < count($all_show_artwork) / $page_length; $i++) {
			$active_class = '';
			if ($current_page_number == $i) $active_class = ' active';

			$html .= '<a href="' . $safe_url . '&page_number=' . $i . '" class="opa-show-pagination-button' . $active_class . '" style="margin-right: 20px; ' . ($current_page_number == $i ? 'color: black;' : '') . '">' . ($i + 1) . '</a>';
		}

		$html .= '</div>';

		return $html;

	}

	static function clean_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	static function confirmation_email($response,$origin){
    //  if($origin=='show_registration'){
	// 	 global $wpdb;
    // //print_r($response);
	// $art_id = $response['data']['registration'];
	// $result = $wpdb->get_results('select * from {$wpdb->prefix}opa_art where id='.$art_id);
	// print_r($result);

	//  }
	}

}
