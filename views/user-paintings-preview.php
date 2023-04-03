<?php
	// Get user paintings for show - Requires $user and $show_id in scope before required
	$user_paintings = OPA_Model_Show::get_artists_art( $user->ID, $show_id );
?>
<div class="user-paintings">
	<?php
		foreach ( $user_paintings as $painting ) {
			printf(
				'<div class="user-painting">
					<div class="user-painting__image" style="background-image: url(%s )"></div>
				</div>',
				wp_get_attachment_url($painting['painting_file_original'])
			);
		}
	?>
</div>
