<?php
if ( $atts && array_key_exists( 'show', $atts ) ) {
	$show_id = intval( $atts['show'] );
	$jurors = OPA_Model_Show::get_jurors( $show_id );
	if ( !empty( $jurors ) ) { ?>
		<div class="opa-list-jurors">
			<div class="opa-list-jurors__title">
				<?php _e( 'Jurors', OPA_DOMAIN ) ?>
			</div><?php
			foreach ( $jurors as $juror ) { ?>
				<div class="opa-list-jurors__juror">
					<div class="opa-list-jurors__juror-name">
						<?php echo esc_html( $juror['First Name'] . ' ' . $juror['Last Name'] ) ?>
					</div>
				</div><?php
			}
			?>
		</div><?php
	}
}
