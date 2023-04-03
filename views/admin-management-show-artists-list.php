<?php
$show_id = intval( $_GET[ 'show_id' ] );
$artists = OPA_Model_Show::get_artists( $show_id );

usort($artists, function($a, $b) {
    $a_name = $a["Last Name"] . " " . $a["Last Name"];
    $b_name = $b["Last Name"] . " " . $b["Last Name"];
    return strcmp($a_name, $b_name);
});

for ($i = 0; $i < count($artists); $i++) {
    $membertype = get_user_meta($artists[$i]["ID"], 'membership', true);
    $designation = '';
    if ($membertype == 'signature-membership' || $membertype == 'Signature Member') $designation = 'OPA';
    if ($membertype == 'master-signature-membership' || $membertype == 'Master Signature Member') $designation = 'OPAM';

    $front_elements = array_slice($artists[$i], 0, 3, true);
    $back_elements = array_slice($artists[$i], 3, null, true);
    
    $artists[$i] = $front_elements + ["Designation" => $designation] + $back_elements;
}

?><h3><?php echo __( 'Registered Users ', OPA_DOMAIN ); ?></h3><?php

echo OPA_Exports::export_button( __( 'Export CSV' ), array(
	'opa_export_type' => 'export-user-csv',
	'show_id' => $show_id
) );

echo OPA_Functions::build_table( $artists, 'artist' );

function opa_expand_to_artist_payment() { ?>
	<script type="text/javascript" >
        jQuery(document).ready(function($) {
            $(document).on('click', '.opa-table__expand--artist', function() {
                var user_id = $(this).closest('tr').find('td:first-child').text();
                window.location.href = window.location.href + "&more=payment&user_id=" + user_id;
            });
        });
	</script> <?php
}
add_action( 'admin_footer', 'opa_expand_to_artist_payment' );
