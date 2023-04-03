<?php
class OPA_Exports {

	static function init() {
		add_action( 'plugins_loaded', __CLASS__ . '::export' );
	}

	/**
	 * Export a Zip File with Artwork
	 * @param $artwork
	 * @param string $file_name
	 */
	static function export_zip_of_photos( $artwork, $file_name = 'photos.zip'  ) {

		$zip     = new ZipArchive();

		if ($zip->open($file_name, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) !== TRUE) {
			die ("An error occurred creating your ZIP file.");
		}
		$zip->addEmptyDir( "photos" );
    
		for ( $i = 0; $i < count( $artwork ); $i ++ ) {
			$data = $artwork[ $i ];
			$user_id = $data["artist_id"];
			$painting_id = $data["painting_file_original"];
			$painting_title = $data["painting_name"];
			$painting_price = explode(".", $data["painting_price"])[0];
			$painting_width = $data["painting_width"];
			$painting_height = $data["painting_height"];

			$first_name = get_user_meta($user_id, "billing_first_name", true);
			$last_name = get_user_meta($user_id, "billing_last_name", true);

			$membertype = get_user_meta($user_id, 'membership', true);
			$designation = '';
			if ($membertype == 'signature-membership' || $membertype == 'Signature Member') $designation = 'OPA-';
			if ($membertype == 'master-signature-membership' || $membertype == 'Master Signature Member' || $membertype == 'Master') $designation = 'OPAM-';

			
			$file = wp_get_attachment_url($painting_id);

			
			$filetype = end(explode('.',end(explode("/", $file))));
			
			if (empty($file)) {
				error_log("Could not export image because file path is empty for attachment id " .  $painting_id . " from submission id " . $data["id"]);
				continue;
			}
			if (empty($filetype)) {
				error_log("Could not export image because filetype is empty for file " . $file . " from submission id " . $data["id"]);
				continue;
			}

			
			// Lastname-Firstname-Designation-Title-Dimensions-Price
			$filename = Clean_Image_Filename($last_name . "-" . $first_name . "-" . $designation . $painting_title . "-" . $painting_height . "x" . $painting_width . "-" . $painting_price) . "." . $filetype;

		    $data = file_get_contents($file);
	     
			$zip->addFromString("photos/" . $filename, $data );

		}

		$zip->close();

		header( "Content-type: application/zip" );
		header( "Content-Disposition: attachment; filename={$file_name}" );
		header( "Content-Transfer-Encoding: binary" );
      
		readfile( $file_name );
		unlink( $file_name );

		die();
	}

	static function Clean_Image_Filename($file_name) {
		$string = str_replace(' ', '-', $file_name); // Replaces all spaces with hyphens.
	 
		$pieces = explode(".", $string);
		$file_type = array_pop($pieces);
		
		if (count($pieces) > 0) {
			return preg_replace('/[^A-Za-z0-9\-]/', '', implode(".", $pieces)) . "." . $file_type;
		}
	
		return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
	}

	/**
	 * Exports a file in a Header Stream
	 * @param $data_to_export
	 * @param $file_name
	 */
	static function export_csv( $data_to_export, $file_name = 'data.csv' ) {
    
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename={$file_name}" );
		header("Content-Transfer-Encoding: binary");

		$fh = @fopen( 'php://output', 'w' );
		$headerDisplayed = false;

		if ( !empty( $data_to_export ) ) {
			foreach ( $data_to_export as $data ) {
				if ( !$headerDisplayed ) {
					fputcsv($fh, array_keys($data));
					$headerDisplayed = true;
				}
				fputcsv($fh, $data);
			}
		}
		fclose($fh);
		exit();
	}

	static function display_duplicates($data){
		echo "<h2 style='margin-left:10em'> Duplicate Entries </h2>";
		echo "<table style='margin:0 5em'>";
		echo "<tr>";
		
			foreach ($data as $value) {
				echo "<td>";
				echo "Artist ID: $value";
				echo "</td>";
			}
		
		echo "</tr>";
		echo "</table>";
	}

	/**
	 * Export Stream
	 */
	static function export() {
		if ( array_key_exists( 'opa_export_type', $_GET ) ) {
			switch ( $_GET['opa_export_type'] ) {
				case "export-artwork-csv":
					$show_id = intval( $_GET[ 'show_id' ] );
					$filter = $_GET['filter'];
					
					$data = OPA_Model_Show::get_artwork_without_image_code( $show_id,$filter );

					$new_data = [];
					foreach ($data as $line) {
						
						$full_name = "";
						$user_id = $line['artist_id'];
	
						$prefix = get_user_meta($user_id, "billing_salutation", true);
						$first_name = get_user_meta($user_id, "billing_first_name", true);
						$middle_name = get_user_meta($user_id, "billing_middle_name", true);
						$last_name = get_user_meta($user_id, "billing_last_name", true);
	
						if (!empty($prefix)) $full_name .= $prefix . ' ';
						$full_name .= $first_name;
						if (!empty($middle_name)) $full_name .= ' ' . $middle_name;
						$full_name .= ' ' . $last_name;
	
						$membertype = get_user_meta($user_id, 'membership', true);
						$designation = '';
						if ($membertype == 'signature-membership' || $membertype == 'Signature Member') $designation = 'OPA';
						if ($membertype == 'master-signature-membership' || $membertype == 'Master Signature Member' || $membertype == 'Master') $designation = 'OPAM';
	
						if (!empty($designation)) $full_name .= ' ' . $designation;
						
						$new_line = array_slice($line, 0, 3, true) +
										array("artist_name" => $full_name) +
										array_slice($line, 3, count($line) - 1, true);
					
						array_push($new_data, $new_line);
					}

					self::export_csv( $new_data, 'artwork.csv' );
					break;
				case "export-artwork-zip":
					$show_id = intval( $_GET[ 'show_id' ] );
					$data = OPA_Model_Show::get_artwork( $show_id );
					self::export_zip_of_photos( $data, 'artwork.zip' );
					break;
				case "export-duplicate-entries":
					$show_id = intval( $_GET[ 'show_id' ] );

					$data = OPA_Model_Show::get_duplicate_entires( $show_id );

					echo json_encode($data);
					exit;
					
					break;
				case "export-user-csv":
					$show_id = intval( $_GET['show_id'] );
					$artists = OPA_Model_Show::get_artists( $show_id );
					self::export_csv( $artists, 'artists.csv' );
					break;
				case "export-user-payments-csv":
					$show_id = intval( $_GET['show_id'] );
					$user_id = intval( $_GET['user_id'] );
					$registrations = OPA_Model_Show::get_artists_art( $user_id, $show_id );
					self::export_csv( $registrations, 'artists-payments.csv' );
					break;
				case "export-jurors-in-show-csv":
					$show_id = intval( $_GET['show_id'] );
					$jurors = OPA_Model_Show::get_jurors_for_csv( $show_id );
					self::export_csv( $jurors, 'jurors-in-show.csv' );
					break;
				case "export-artwork-round-csv":
					$round_id = intval( $_GET[ 'round_id' ] );
					$data = OPA_Model_Jury_Round_Art::get_artwork( $round_id,'' );
					self::export_csv( $data, 'artwork.csv' );
					break;
				case "export-artwork-round-zip":
					$round_id = intval( $_GET[ 'round_id' ] );
					$data = OPA_Model_Jury_Round_Art::get_artwork( $round_id,'' );
					self::export_zip_of_photos( $data, 'artwork.zip' );
					break;

                case "export-images-round-judging":
                    $round_id = intval( $_GET[ 'round_id' ] );
					  $data = OPA_Model_Jury_Round_Art::get_artwork_accepted( $round_id );
				
                    self::export_zip_of_photos( $data, 'images-round-judging.zip' );
                    break;
                case "export-csv-round-judging":

                    $show_id = intval( $_GET[ 'show_id' ] );
					$round_id = intval( $_GET[ 'round_id' ] );
                    $data = OPA_Model_Show::get_artwork_artists( $show_id,$round_id );

                    $count = 0;
                    foreach($data as $data_id){
                        $id = $data_id['artist_id'];
						$membertype = get_user_meta($id, 'membership', true);
						
						$first_name = get_user_meta($id,'billing_first_name',true);
                        $last_name = get_user_meta($id,'billing_last_name',true);
						$middle_name = get_user_meta($id,'billing_middle_name',true);
						$billing_address_1 = get_user_meta($id,'billing_middle_name',true);
                      
                        $billing_address_1 = get_user_meta($id,'billing_address_1',true);
                        $billing_address_2 = get_user_meta($id,'billing_address_2',true);

                        $billing_phone = get_user_meta($id,'billing_phone',true);
                        $billing_city = get_user_meta($id,'billing_city',true);
                        $billing_postcode = get_user_meta($id,'billing_postcode',true);
						$data[$count]['first_name'] = $first_name;
						$data[$count]['last_name'] = $last_name;
						$data[$count]['middle_name'] = $middle_name;
						$data[$count]['member_type'] = $membertype;
                        $data[$count]['billing_address_1'] = $billing_address_1;
                        $data[$count]['billing_address_2'] = $billing_address_2;
                        $data[$count]['billing_city'] = $billing_city;
                        $data[$count]['billing_phone'] = $billing_phone;
                        $data[$count]['billing_postcode'] = $billing_postcode;

                        $count++;
                    }
					
                    self::export_csv( $data, 'csv-round-judging.csv' );
                    break;

				case "export-accepted-artwork-csv":

					$show_id = intval( $_GET[ 'show_id' ] );
                    $data = OPA_Model_Show::get_accepted_art( $show_id );
					
					$export_data = [];

                    $count = 0;
                    foreach($data as $art){
                        $id = $art['artist_id'];
						$first_name = get_user_meta($id,'billing_first_name',true);
                        $last_name = get_user_meta($id,'billing_last_name',true);
						$middle_name = get_user_meta($id,'billing_middle_name',true);

						$membertype = get_user_meta($id, 'membership', true);
						$designation = '';
						if ($membertype == 'signature-membership' || $membertype == 'Signature Member') $designation = 'OPA';
						if ($membertype == 'master-signature-membership' || $membertype == 'Master Signature Member' || $membertype == 'Master') $designation = 'OPAM';

                        $billing_address_1 = get_user_meta($id,'billing_address_1',true);
                        $billing_address_2 = get_user_meta($id,'billing_address_2',true);

                        $billing_phone = get_user_meta($id,'billing_phone',true);
                        $billing_city = get_user_meta($id,'billing_city',true);
						$billing_state = get_user_meta($id,'billing_state',true);
						$billing_country = get_user_meta($id,'billing_country',true);
                        $billing_postcode = get_user_meta($id,'billing_postcode',true);

						$email = get_userdata( $id )->user_email;

						$export_data[$count]['first_name'] = $first_name;
						$export_data[$count]['middle_name'] = $middle_name;
						$export_data[$count]['last_name'] = $last_name;
						$export_data[$count]['designation'] = $designation;
                        $export_data[$count]['billing_address_1'] = $billing_address_1;
                        $export_data[$count]['billing_address_2'] = $billing_address_2;
                        $export_data[$count]['billing_city'] = $billing_city;
						$export_data[$count]['billing_state'] = $billing_state;
						$export_data[$count]['billing_country'] = $billing_country;
                        $export_data[$count]['billing_postcode'] = $billing_postcode;
                        $export_data[$count]['billing_phone'] = $billing_phone;
						$export_data[$count]['email'] = $email;
						$export_data[$count]['title'] = $art["painting_name"];
						$export_data[$count]['height'] = $art["painting_height"];
						$export_data[$count]['width'] = $art["painting_width"];
						$export_data[$count]['price'] = $art["painting_price"];
						$export_data[$count]['substrate'] = $art["painting_description"];
						
                        $count++;
                    }

					self::export_csv( $export_data, 'accepted-artwork.csv' );

					break;

				case "export-accepted-images":
					$show_id = intval( $_GET[ 'show_id' ] );
					$data = OPA_Model_Show::get_accepted_art( $show_id );
				
					self::export_zip_of_photos( $data, 'accepted-images.zip' );
					break;
				default:
					return '';
			}
		}
	}

	/**
	 * Export Button
	 */
	static function export_button( $button_text, $params = array() ) {

		$form = '<form class="opa-inline-form" target="_blank" method="GET" action="' . OPA_Menu::helper_url() . '">';
			foreach ( $params as $k => $v ) {
				$form .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
			}
			$form .= '<input type="hidden" name="page" value="opa" />';
			$form .= '<button class="button button-large" type="submit">' . $button_text . '</button>';
		$form .= '</form>';

		return $form;
	}

}
