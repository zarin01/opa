<?php
class OPA_Refunds {

	static function init() {
		add_action( 'wp_ajax_opa_refund', __CLASS__ . '::refund' );
		add_action( 'wp_ajax_removeArt', __CLASS__ . '::removeArt' );
	}

	static function refund() {
      global $wpdb;
		// This is a secure process to validate if this request comes from a valid source.
		check_ajax_referer( 'opa_refund', 'opa_refund_nonce' );
		$art_id = OPA_Functions::clean_input( $_POST["art_id"] );
		$show_id = OPA_Functions::clean_input( $_POST["show_id"] );
		$user_id = OPA_Functions::clean_input( $_POST["user_id"] );
		$stripe_charge_id = OPA_Functions::clean_input( $_POST["stripe_charge_id"] );
		$refund_amount = floatval( $_POST["refund_amount"] );
		if(!$refund_amount){
		
		$result = $wpdb->get_results('select stripe_payment_amount from '.$wpdb->prefix.'opa_art where artist_id="'.$user_id.'" and show_id="'.$show_id.'" and stripe_refunded_amount="0.00" and stripe_payment_amount!="0.00" ORDER BY stripe_payment_amount ASC');
   
		$refund_amount = $result[0]->stripe_payment_amount;
	   if(!$refund_amount){
		$refund_amount = $_POST["painting_amount"]; 
	   }
		} 

		
	
		$registration = OPA_Model_Art::get_registrations_by( $art_id,'stripe_charge_id', $stripe_charge_id, '=' );
		$refund_amount_in_dollars = number_format( $refund_amount, 2 );
		$refund_amount_in_cents = intval( number_format( $refund_amount, 2 ) * 100 );

		if ( empty( $registration ) ) {
			wp_send_json_error( array(
				'message' => 'Invalid charge id'
			));
		}

		try {

			// Process Refund
		
			$refund_response = OPA_Payment::process_refund( $stripe_charge_id, $refund_amount_in_cents );

			// Send response
			
			if ( $refund_response['success'] === true ) {
				OPA_Model_Art::update_refunded_amount($art_id, $stripe_charge_id, $registration[0]->stripe_refunded_amount + $refund_amount_in_dollars );
				wp_send_json_success( array(
					'message' => 'Refund Successful',
					'stripe_refund' => $refund_response['stripe_refund'],
					'stripe_refund_amount' => $refund_response['stripe_refund_amount']
				));
			} else {
				wp_send_json_error( array(
					'message' => $refund_response['error']
				));
			}

		} catch( Exception $e ) {
			wp_send_json_error( array(
				'message' => 'Server Error!'
			));
		}
		die();
	}


	static function removeArt() {
	   $id =  $_REQUEST['id'];
	   $nonce = $_REQUEST['nonce'];
	   global $wpdb;
	  if(wp_verify_nonce($nonce,'removeArt')){
		  $art_id =   $query = $wpdb->get_results("select painting_file_original from wp_opa_art where id=".$id);
		   wp_delete_attachment($art_id[0]->painting_file_original,1);
		 
        $query = $wpdb->query("delete from wp_opa_art where id=".$id);
		if($query){
		    $res = array("status"=>"success","message"=>"Art has been removed");
		}
	   }else{
		   $res = array("status"=>"failed","message"=>"unable to remove the painting");
	   }
     echo json_encode($res);
		
		die;
    }

}
