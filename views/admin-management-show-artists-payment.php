<?php
$show_id = intval( $_GET['show_id'] );
$user_id = intval( $_GET['user_id'] );
$user = new WP_User( $user_id );
$registrations = OPA_Model_Show::get_artists_art( $user_id, $show_id );
$formatted_registrations = [];

?><h3><?php echo __( 'Payments for ', OPA_DOMAIN ) . $user->first_name . ' ' . $user->last_name; ?></h3><?php

foreach ( $registrations as $registration ) {
	$formatted_registrations[] = array(
		'ID' => $registration['id'],
		'Painting' => '<img src="' .wp_get_attachment_image_src($registration['painting_file_original'],'thumbnail')[0] . '" style="width: 80px;" />',
		'Stripe Charge ID' => $registration['stripe_charge_id'],
		'Stripe Payment Date' => $registration['stripe_payment_date'],
		'Total Collected' => $registration['stripe_payment_amount'],
		'Total Refunded' => $registration['stripe_refunded_amount'],
        'Remove Art' => '<button style="color:red" onclick="removeArt('.$registration['id'].')">Remove Art</button><input type="hidden" id="removeaction" value="'.wp_create_nonce('removeArt').'">',
	);
}

echo OPA_Exports::export_button( __( 'Export Payments' ), array(
	'opa_export_type' => 'export-user-payments-csv',
	'show_id' => $show_id,
    'user_id' => $user_id
) );

echo OPA_Functions::build_table( $formatted_registrations, 'artist-refund', '<form class="opa-artist-trigger-refund"><input type="text" name="refund_amount" /><button type="submit">' . __( 'Refund', OPA_DOMAIN ) . '</button></form>' );
printf(
    '<form class="opa-refund-form">
        <input type="hidden" name="refund_amount" value="0" />
        <input type="hidden" name="stripe_charge_id" value="0" />
        <input type="hidden" name="art_id" value="0" />
        <input type="hidden" name="painting_amount" value="0" />
        <input type="hidden" name="show_id" value="'.$show_id.'" />
        <input type="hidden" name="user_id" value="'.$user_id.'" />
        <input type="hidden" name="action" value="opa_refund" />
        %s
    </form>',
	wp_nonce_field( 'opa_refund', 'opa_refund_nonce', true, false )
);

function opa_refund_artist_payment() { ?>
	<script type="text/javascript" >
        jQuery(document).ready(function($) {

            var $refund_form = $('.opa-refund-form');

            $(document).on('submit', '.opa-artist-trigger-refund', function(e) {
                e.preventDefault();

                // Collect Form info
                var $form = $(this);
                var art_id = $form.closest('tr').find('td:nth-child(1)').text();
                var charge_id = $form.closest('tr').find('td:nth-child(3)').text();
                var painting_amount = $form.closest('tr').find('td:nth-child(5)').text();
                var refund_amount = $form.find('[name="refund_amount"]').val();
                $refund_form.find('[name="art_id"]').val( art_id );
                $refund_form.find('[name="stripe_charge_id"]').val( charge_id );
                $refund_form.find('[name="painting_amount"]').val( painting_amount );
                $refund_form.find('[name="refund_amount"]').val( refund_amount );
               


                var formData = new FormData($refund_form.get(0));

                // Submit the form to the server
                $.ajax({
                    url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ) ?>',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                }).then((result, textStatus, jqXHR) => {
                    if ( result.success === true ) {
                        location.reload();
                    } else if ( result.success === false ) {
                        alert( result.data.message );
                    }
                }).fail((jqXHR) => {
                    alert( "Server error.  Please try again later." );
                });
            });

            // remove art work
            removeArt = (id)=>{
                var nonce = $("#removeaction").val();
                var r = confirm("Note: Once the art is removed you can not refund. press OK to continue removing.");
                if (r != true) {
                return;
                } 
                
                   $.ajax({
                    url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ) ?>',
                    method: 'POST',
                    data: {
                        id:id,
                        nonce: nonce,
                        action:"removeArt"
                    },
                  
                    
                    dataType: 'json'
                }).then((result, textStatus, jqXHR) => {
                   if(result.status=='success'){
                       location.reload();
                   }else{
                       alert(result.message);
                   }
                }).fail((jqXHR) => {
                  
                });
    }
        });
	</script> <?php
}
add_action( 'admin_footer', 'opa_refund_artist_payment' );

?>
