<?php

if ( array_key_exists( 'judge', $_GET ) ) {
	require_once( OPA_PATH . 'views/single-shows-judge.php' );
} else {
	$post_id = get_the_ID();
	$opa_show_featured_image = get_the_post_thumbnail_url( $post_id, 'full' );
	$opa_show_short_description = get_field( 'opa_show_short_description' );
	$opa_show_location = get_field( 'opa_show_location' );
	$opa_show_event_time = get_field( 'opa_show_event_time' );
	$opa_show_region = get_field( 'opa_show_region' );
	$opa_show_address_line_1 = get_field( 'opa_show_address_line_1' );
	$opa_show_address_city = get_field( 'opa_show_address_city' );
	$opa_show_address_state = get_field( 'opa_show_address_state' );
	$opa_show_address_zip = get_field( 'opa_show_address_zip' );
	$opa_start_registration_date = parse_acf_date(get_field( 'opa_start_registration_date' ));
	$opa_end_registration_date = parse_acf_date(get_field( 'opa_end_registration_date' ));

	?>
    <div class="opa-show-detail">

    <div class="opa-show-detail__main">

		<?php if ( $opa_show_featured_image ) : ?>
            <div class="opa-show-detail__image">
            <img src="<?php echo $opa_show_featured_image ?>" />
            </div><?php
		endif; ?>

        <div class="opa-show-detail__info">
            <div class="opa-show-detail__info-title">
				<?php esc_html_e( get_the_title() ) ?>
            </div>
            <div class="opa-show-detail__info-description">
				<?php esc_html_e( $opa_show_short_description ) ?>
            </div>
            <div class="opa-show-detail__info-location">
				<?php _e( 'Location', OPA_DOMAIN ) ?>: <?php esc_html_e( $opa_show_location ) ?>
            </div>
            <div class="opa-show-detail__info-days">
				<?php
				foreach( $opa_show_event_time as $day ) {
					printf(
						'<div class="opa-show-detail__info-day">
                                                %s - %s
                                            </div>',
						$day['start_time'],
						$day['end_time']
					);
				}
				?>
            </div>
        </div>
    </div>

    <div class="opa-show-detail__main-content">
		<?php
		remove_filter( 'the_content', 'OPA_Shows::show_detail_content' );
		the_content();
		add_filter( 'the_content', 'OPA_Shows::show_detail_content', 10, 2 );
		?>
    </div>

<!--    Content moved to single-opa_show.php  -->
    </div><?php
}

