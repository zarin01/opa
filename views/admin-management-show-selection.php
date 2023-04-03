<?php
$shows = OPA_Model_Show::get_shows();
?>
<form action="<?php echo OPA_Menu::helper_url( array() ) ?>" method="GET">
	<select onchange="this.form.submit()" name="show_id">
		<option value="">Select a Show</option>
		<?php
		foreach ( $shows as $show ) {
			printf(
				'<option value="%s">%s</option>',
				intval( $show->ID ),
				esc_html( $show->post_title )
			);
		}
		?>
	</select>
	<input type="hidden" name="page" value="opa" />
</form>
