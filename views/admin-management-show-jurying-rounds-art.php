<?php
$page_number = intval( $_GET[ 'page_number' ] );
$show_id = intval( $_GET[ 'show_id' ] );
$round_id = intval( $_GET[ 'round_id' ] );

?><h3><?php echo __( 'Artwork in this Round', OPA_DOMAIN ); ?></h3><?php

echo OPA_Exports::export_button( __( 'Export CSV' ), array(
	'opa_export_type' => 'export-artwork-round-csv',
	'show_id' => $show_id,
	'round_id' => $round_id
) );
echo OPA_Exports::export_button( __( 'Export Art Images' ), array(
	'opa_export_type' => 'export-artwork-round-zip',
	'show_id' => $show_id,
	'round_id' => $round_id
) );

$round_artwork = OPA_Model_Jury_Round_Art::get_artwork( $round_id, null );
$round_artwork_ids = wp_list_pluck( $round_artwork, 'id' );
$formatted_round_artwork = [];
foreach ( $round_artwork as $artwork ) {
	$formatted_round_artwork[] = array(
		'Painting Details' => '<strong>'.$artwork['painting_name'].'</strong><p>'.esc_html( $artwork['painting_description'] ).'</p>',
		'Painting' => '<img loading="lazy" src="' . wp_get_attachment_image_url($artwork['painting_file_original'], 'thumbnail', 'loading="lazy"') . '" style="width: 250px;" />',
        'Status' => (
            $artwork['art_active'] ? (
                __( 'Enabled', OPA_DOMAIN ) . '<br /><a href="#" style="color: #a00;" data-deactivate-art="' . $artwork['id'] . '">' . __( 'Deactivate', OPA_DOMAIN ) . '</a>'
            ) : (
                __( 'Disabled', OPA_DOMAIN ) . '<br /><a href="#" data-activate-art="' . $artwork['id'] . '">' . __( 'Activate', OPA_DOMAIN ) . '</a>'
            )
        ),
        'Remove from Round' => '<button class="button button-large" data-remove-art="' . $artwork['id'] . '">Remove from Round</button>'
	);
}

echo OPA_Functions::build_table( $formatted_round_artwork, 'hide' ); ?>

<br /><br />
<h3><?php echo __( 'Add Artwork to this Round', OPA_DOMAIN ); ?></h3>
<a href="#" class="opa-add-all-art-to-round"><?php echo __( 'Add All', OPA_DOMAIN ); ?></a>
<br />
<a href="#" class="opa-select-all-art-to-add"><?php echo __( 'Select All', OPA_DOMAIN ); ?></a>
<br /><br />
<?php

// For pagination
$filter = '';
$all_artwork = OPA_Model_Show::get_artwork_without_image_code($show_id,$filter);
$all_available_artwork = array();

foreach ( $all_artwork as $artwork ) {
    if (! in_array( $artwork['id'], $round_artwork_ids )) {
        array_push($all_available_artwork, $artwork);
    }
}
$filter = '';
$all_show_artwork = OPA_Model_Show::get_artwork_pagination( $all_available_artwork, $page_number, OPA_SHOW_ART_PAGE_LENGTH);
$formatted_available_artwork = [];

foreach ( $all_show_artwork as $artwork ) {
    if ( ! in_array( $artwork['id'], $round_artwork_ids ) ) {
	    $formatted_available_artwork[] = array(
		    'Add' => '<input type="checkbox" name="art_to_add[]" value="' . $artwork['id'] . '" />',
		    'Painting Details' => '<strong>'.$artwork['painting_name'].'</strong><p>'.esc_html( $artwork['painting_description'] ).'</p>',
		    'Painting' => '<img src="' . wp_get_attachment_image_url($artwork['painting_file_original'], 'thumbnail', 'loading="lazy"') . '" style="width: 250px;" />',
	    );
    }
} 
?>

<form id="artwork-to-round-form" class="opa-artwork-to-round-form" method="POST">
    <?php echo OPA_Functions::build_table( $formatted_available_artwork, 'hide' ); ?>
    <?php 
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        echo OPA_Functions::build_show_pagination( $formatted_available_artwork, $actual_link, $page_number, OPA_SHOW_ART_PAGE_LENGTH);
    ?>
    <br />
    <input type="hidden" name="round_id" value="<?php echo $round_id ?>" />
    <input type="hidden" name="action" value="opa_artwork_to_round" />
    <?php echo wp_nonce_field( 'opa_artwork_to_round', 'opa_artwork_to_round_nonce', true, false ); ?>
    <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
</form>
<form id="artwork-activate-deactivate-form" class="opa-artwork-activate-deactivate-form" method="POST" style="display: none;">
    <input type="hidden" name="active" value="" />
    <input type="hidden" name="art_id" value="" />
    <input type="hidden" name="round_id" value="<?php echo $round_id ?>" />
    <input type="hidden" name="action" value="opa_artwork_activate_deactivate" />
    <?php echo wp_nonce_field( 'opa_artwork_activate_deactivate', 'opa_artwork_activate_deactivate_nonce', true, false ); ?>
    <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
</form>
    <form id="artwork-remove-from-round" class="opa-artwork-remove-from-round" method="POST" style="display: none;">
        <input type="hidden" name="art_id" value="" />
        <input type="hidden" name="round_id" value="<?php echo $round_id ?>" />
        <input type="hidden" name="action" value="opa_artwork_remove_from_round" />
		<?php echo wp_nonce_field( 'opa_artwork_remove_from_round', 'opa_artwork_remove_from_round_nonce', true, false ); ?>
        <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
    </form>

<?php

function opa_show_jury_round_art_scripts() { ?>
    <script type="text/javascript" >
        jQuery(document).ready(function($) {

            // Deactivate Art
            $(document).on('click', '[data-deactivate-art]', function(e) {
                e.preventDefault();
                var art_id = parseInt( $(this).attr('data-deactivate-art') );
                $('.opa-artwork-activate-deactivate-form [name="art_id"]').val( art_id );
                $('.opa-artwork-activate-deactivate-form [name="active"]').val( 0 );
                $('.opa-artwork-activate-deactivate-form').submit();
            });

            // Activate Art
            $(document).on('click', '[data-activate-art]', function(e) {
                e.preventDefault();
                var art_id = parseInt( $(this).attr('data-activate-art') );
                $('.opa-artwork-activate-deactivate-form [name="art_id"]').val( art_id );
                $('.opa-artwork-activate-deactivate-form [name="active"]').val( 1 );
                $('.opa-artwork-activate-deactivate-form').submit();
            });

            // Select all Artwork
            $(document).on('click', '.opa-select-all-art-to-add', function(e) {
                e.preventDefault();
                $('.opa-artwork-to-round-form [name*=art_to_add]').attr('checked', 'checked');
            });

            // Remove Artwork from Round
            $(document).on('click', '[data-remove-art]', function(e) {
                e.preventDefault();
                var art_id = $(this).attr('data-remove-art');
                $('.opa-artwork-remove-from-round [name="art_id"]').val( art_id );
                $('.opa-artwork-remove-from-round').submit();
            });

            // Add Artwork to this Round
            $(document).on('submit', '.opa-artwork-to-round-form, .opa-artwork-activate-deactivate-form, .opa-artwork-remove-from-round', function(e) {
                e.preventDefault();

                var formData = new FormData($(this).get(0));

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

            $('.opa-add-all-art-to-round').click(function() {
                jQuery.ajax({
                    url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ) ?>',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {
                        action: 'add_all_artwork_to_round',

                        show_id: parseInt('<?php echo intval($_GET[ 'show_id' ]); ?>'),
                        round_id: parseInt('<?php echo intval($_GET[ 'round_id' ]); ?>'),
                    },
                    success: function (resp) {
                        if (resp.success) {
                            location.reload();
                        }else{
                            alert( result.data.message );
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alert ('Request failed: ' + thrownError.message);
                    },
                });
            });
        });
    </script> <?php
}
add_action( 'admin_footer', 'opa_show_jury_round_art_scripts' );
