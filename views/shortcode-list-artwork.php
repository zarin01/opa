<?php
if ( $atts && array_key_exists( 'show', $atts ) ) {
	$show_id = intval( $atts['show'] );
	$artwork = OPA_Model_Show::get_artwork( $show_id );

	if ( !empty( $artwork ) ) { ?>
		<div class="opa-list-artwork">
			<div class="opa-list-artwork__title">
				<?php _e( 'Artwork', OPA_DOMAIN ) ?>
			</div>
			<div class="opa-list-artwork__arts"><?php
				foreach ( $artwork as $art ) { ?>
					<div class="opa-list-artwork__art">
						<div class="opa-list-artwork__art-photo">
							<img src="<?php echo wp_get_attachment_url($art['painting_file_original']) ?>" style="width: 250px;" />
						</div>
						<div class="opa-list-artwork__art-info">
							<div class="opa-list-artwork__art-info-name">
								<?php echo esc_html( $art['painting_name'] ) ?>
							</div>
							<div class="opa-list-artwork__art-info-description">
								<?php echo esc_html( $art['painting_description'] ) ?>
							</div>
						</div>
					</div><?php
				} ?>
			</div>
		</div><?php
	}
}
