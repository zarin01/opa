<?php
if ( ! is_user_logged_in() ) {
	echo '<p>' . __( 'You must be logged in to manage your profile.', OPA_DOMAIN ) . '</p>';
} else {

	$user_id = is_array( $atts ) && array_key_exists( 'user_id', $atts ) ? intval( $atts['user_id'] ) : get_current_user_id();
	$user = get_user_by( 'ID', $user_id );

	?>
	<div class="opa-profile">

        <div class="opa-profile__shows"><?php _e( 'My Artwork', OPA_DOMAIN ) ?></div>

        <div class="opa-accordion">
        <?php

            // My Shows - listed by recent date
            $shows = OPA_Model_Artist::get_artwork( $user->ID );

            foreach ( $shows as $k => $show ) {
	            $now = new DateTime('now');
	            $registration_closed = new DateTime( $show['registration_end_date'] ); ?>

                <div class="opa-accordion__item <?php echo $k == 0 ? 'opa-accordion__item--active' : '' ?>">
                    <div class="opa-accordion__title">
                        <?php echo esc_html( $show['painting_name'] ) ?>
                    </div>
                    <div class="opa-accordion__content">
                        <?php if ( $now < $registration_closed ) { ?>
                            <div>
                                <div class="opa-profile__painting-name">
                                    <input name="painting_name" value="<?php echo esc_html( $show['painting_name'] ) ?>" />
                                    <input type="hidden" name="painting_id" value="<?php echo intval( $show['id'] ) ?>" />
                                </div>
                                <div class="opa-profile__painting-description">
                                    <textarea name="painting_description"><?php echo esc_html( $show['painting_description'] ) ?></textarea>
                                </div>
                                <div class="opa-profile__painting-price">
                                    <label><?php _e( 'Price', OPA_DOMAIN ) ?></label><br />
                                    <input name="painting_price" value="<?php echo esc_html( $show['painting_price'] ) ?>" />
                                </div>
                            </div>
                            <div>
                                <div class="opa-profile__painting-file">
                                    <img src="<?php echo esc_html( wp_get_attachment_url($show['painting_file_original']) ) ?>" style="width: 230px;" /><br />
                                    <input type="file" name="painting_file" />
                                </div>
                                <div class="opa-profile__group">
                                    <div class="opa-profile__painting-width">
                                        <label><?php _e( 'Width', OPA_DOMAIN ) ?></label><br />
                                        <input type="text" name="painting_width" id="painting_width" value="<?php echo esc_html( $show['painting_width'] ) ?>" />
                                    </div>
                                    <div class="opa-profile__painting-height">
                                        <label><?php _e( 'Height', OPA_DOMAIN ) ?></label><br />
                                        <input type="text" name="painting_height" id="painting_height" value="<?php echo esc_html( $show['painting_height'] ) ?>" />
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="opa-profile__painting-show">
                                    <strong><?php _e( 'Show: ', OPA_DOMAIN ) ?></strong><br />
                                    <a href="<?php echo esc_url( get_permalink( $show['show_id'] ) ) ?>"><?php echo esc_html( $show['show_title'] ) ?></a>
                                </div>
                                <div class="opa-profile__painting-status">
                                    <strong><?php _e( 'Status: ', OPA_DOMAIN ) ?></strong><br /><?php echo ( intval( $show['acceptance'] ) === 1 ? __( 'Accepted', OPA_DOMAIN ) : __( 'Not Yet Accepted', OPA_DOMAIN ) ) ?>
                                </div>
                                <div class="opa-profile__update">
                                    <button class="opa-profile__update-button"><?php _e( 'Update', OPA_DOMAIN ) ?></button><br />
                                    <span class="opa-profile__note"><?php echo __( 'Editable until ', OPA_DOMAIN ) . ' ' . $show['registration_end_date'] ?></span>
                                </div>
                            </div>
                            <?php
                        } else { ?>
                            <div>
                                <div class="opa-profile__painting-name">
	                                <?php echo esc_html( $show['painting_name'] ) ?>
                                </div>
                                <div class="opa-profile__painting-description">
	                                <?php echo esc_html( $show['painting_description'] ) ?>
                                </div>
                                <br />
                                <div class="opa-profile__painting-price">
	                                <?php echo __( 'Price', OPA_DOMAIN ) . ': ' .  esc_html( $show['painting_price'] ) ?>
                                </div>
                                <div class="opa-profile__painting-width">
	                                <?php echo __( 'Width', OPA_DOMAIN ) . ': ' .  esc_html( $show['painting_width'] ) ?>
                                </div>
                                <div class="opa-profile__painting-height">
	                                <?php echo __( 'Height', OPA_DOMAIN ) . ': ' .  esc_html( $show['painting_height'] ) ?>
                                </div>
                            </div>
                            <div>
                                <div class="opa-profile__painting-file">
                                    <img src="<?php echo esc_html( wp_get_attachment_url($show['painting_file_original']) ) ?>" style="width: 300px;" />
                                </div>
                            </div>
                            <div>
                                <div class="opa-profile__painting-show">
                                    <a href="<?php echo esc_url( get_permalink( $show['show_id'] ) ) ?>"><?php echo esc_html( $show['show_title'] ) ?></a>
                                </div>
                                <div class="opa-profile__painting-status">
                                    <strong><?php _e( 'Status: ', OPA_DOMAIN ) ?></strong><br /><?php echo ( intval( $show['acceptance'] ) === 1 ? __( 'Accepted', OPA_DOMAIN ) : __( 'Not Yet Accepted', OPA_DOMAIN ) ) ?>
                                </div>
                            </div><?php
                        } ?>
                    </div>
                </div><?php

            }

        ?>
        </div>
	</div>
    <form id="artist-update-art-form" class="opa-artist-update-art-form" method="POST" style="display: none;">
        <input type="hidden" name="painting_id" value="" />
        <input type="hidden" name="painting_name" value="" />
        <input type="hidden" name="painting_description" value="" />
        <input type="hidden" name="painting_width" value="" />
        <input type="hidden" name="painting_height" value="" />
        <input type="hidden" name="painting_price" value="" />
        <input type="hidden" name="painting_file" value="" />
        <input type="hidden" name="action" value="opa_artist_update_art" />
		<?php echo wp_nonce_field( 'opa_artist_update_art', 'opa_artist_update_art_nonce', true, false ); ?>
        <input type="submit" class="button button-primary button-large" value="<?php _e( 'Submit', OPA_DOMAIN ) ?>">
    </form>
    <?php
}

function opa_frontend_profile() { ?>
    <script type="text/javascript" >
        jQuery(document).ready(function($) {

            $(document).on('click', '.opa-accordion__title', function(e){
                $('.opa-accordion__title').not($(this)).closest('.opa-accordion__item').removeClass('opa-accordion__item--active');
                $(this).closest('.opa-accordion__item').toggleClass('opa-accordion__item--active');
            });

            // Row Update
            $(document).on('click', '.opa-profile__update', function(e){
                var $row = $(this).closest('.opa-accordion__content');
                var $form = $('.opa-artist-update-art-form' );
                $form.find('[name="painting_id"]').val( $row.find('[name="painting_id"]').val() );
                $form.find('[name="painting_name"]').val( $row.find('[name="painting_name"]').val() );
                $form.find('[name="painting_description"]').val( $row.find('[name="painting_description"]').val() );
                $form.find('[name="painting_price"]').val( $row.find('[name="painting_price"]').val() );
                $form.find('[name="painting_width"]').val( $row.find('[name="painting_width"]').val() );
                $form.find('[name="painting_height"]').val( $row.find('[name="painting_height"]').val() );

                // Clone Upload
                $form.find('[name="painting_file"]').remove();
                var $upload = $row.find('[name="painting_file"]');
                var $cloneUpload = $upload.clone();
                $upload.after($cloneUpload).appendTo($form);

                // Submit
                $form.submit();
            });

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
                        location.reload();
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
add_action( 'wp_footer', 'opa_frontend_profile' );
