<?php
	if ( is_user_logged_in() ) {

		$juror                  = wp_get_current_user();
		$show_id               = is_array( $atts ) && array_key_exists( 'show', $atts ) ? $atts['show'] : get_the_ID();
		$current_jury_round_id = OPA_Model_Show::get_current_jury_round_id( $show_id );
		$juror_has_access       = OPA_Model_Jurors::has_round_access( $current_jury_round_id, $juror->ID );
		$judge_absolute_class  = is_array( $atts ) && array_key_exists( 'absolute', $atts ) ? 'opa-judge-button--absolute' : '';

		if ( $juror_has_access ) { ?>
			<div class="opa-judge-button <?php echo $judge_absolute_class ?>">
				<a href="<?php echo get_permalink( $show_id ) ?>?judge=true" class="opa-judge-button__link"><?php _e( 'Judge this Show', OPA_DOMAIN ) ?></a>
			</div><?php
		}
	}
?>
