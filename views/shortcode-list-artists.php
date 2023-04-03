<?php
if ( $atts && array_key_exists( 'show', $atts ) ) {
	$show_id = intval( $atts['show'] );
	$artists = OPA_Model_Show::get_artists( $show_id );

	if ( !empty( $artists ) ) { ?>
		<div class="opa-list-artists">
			<div class="opa-list-artists__title">
				<?php _e( 'Artists', OPA_DOMAIN ) ?>
			</div><?php
			foreach ( $artists as $artist ) { ?>
				<div class="opa-list-artists__art">
					<div class="opa-list-artists__art-name">
						<?php echo esc_html( $artist['First Name'] . ' ' . $artist['Last Name'] ) ?>
					</div>
				</div><?php
			}
			?>
		</div><?php
	}
}
