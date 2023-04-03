<?php
    $artwork_id = intval( $_GET['artwork_id'] );
    $artwork = (array) OPA_Model_Art::get_registrations_by('', 'id', $artwork_id )[0];

    if ( $artwork ) { ?>

            <div class="opa-artwork-edit">
                <form id="artist-update-art-form" class="opa-artist-update-art-form" method="POST">
                    <div class="opa-artwork-edit__painting-name">
                        <label for="painting_name"><?php _e( 'Painting Name', OPA_DOMAIN ) ?></label>
                        <input name="painting_name" type="text" id="painting_name" value="<?php echo esc_html( $artwork['painting_name'] ) ?>" />
                        <input type="hidden" name="painting_id" value="<?php echo intval( $artwork['id'] ) ?>" />
                    </div>
                    <div class="opa-artwork-edit__painting-description">
                        <label for="painting_description"><?php _e('Painting Substrate', OPA_DOMAIN) ?></label>
                        <textarea name="painting_description" id="painting_description"><?php echo esc_html( $artwork['painting_description'] ) ?></textarea>
                    </div>
                    <div class="opa-artwork-edit__painting-price">
                        <label for="painting_price"><?php _e( 'Price', OPA_DOMAIN ) ?></label>
                        <input name="painting_price" type="text" id="painting_price" value="<?php echo esc_html( $artwork['painting_price'] ) ?>" />
                    </div>
                    <div class="opa-artwork-edite__painting-file">
                        <label for="painting_file"><?php _e( 'Painting', OPA_DOMAIN ) ?></label>
                        <img src="<?php echo esc_html( wp_get_attachment_image_url($artwork['painting_file_original'], 'thumbnail', 'loading="lazy"') ) ?>" style="width: 230px;" /><br />
                        <input type="file" name="painting_file" id="painting_file" />
                    </div>
                    <div class="opa-artwork-edit__group">
                        <div class="opa-profile__painting-height">
                            <label><?php _e( 'Height', OPA_DOMAIN ) ?></label>
                            <input type="text" name="painting_height" id="painting_height" value="<?php echo esc_html( $artwork['painting_height'] ) ?>" />
                        </div>
                        <div class="opa-profile__painting-width">
                            <label><?php _e( 'Width', OPA_DOMAIN ) ?></label>
                            <input type="text" name="painting_width" id="painting_width" value="<?php echo esc_html( $artwork['painting_width'] ) ?>" />
                        </div>
                    </div>
                    <input type="hidden" name="painting_id" value="<?php echo intval( $artwork['id'] ) ?>" />
                    <input type="hidden" name="action" value="opa_artist_update_art" />
	                <?php echo wp_nonce_field( 'opa_artist_update_art', 'opa_artist_update_art_nonce', true, false ); ?>
                    <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
                </form>
            </div>
        <?php
    }

function opa_edit_artwork_admin() { ?>
    <script type="text/javascript" >
        jQuery(document).ready(function($) {

            // Update Painting Information
            $(document).on('submit', '.opa-artist-update-art-form', function(e) {
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
                        location.reload(true);
                    } else if ( result.success === false ) {
                        alert( result.data.message );
                    }
                }).fail((jqXHR) => {
                    alert( "Server error.  Please try again later." );
                });
            });
        });
    </script> <?php
}

add_action( 'admin_footer', 'opa_edit_artwork_admin' );

?>
