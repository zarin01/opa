<?php
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
?>

<h3><?php esc_html_e( 'Address Information', OPA_DOMAIN ); ?></h3>
<table class="form-table">
	<tr>
		<th><label for="address_line_1"><?php esc_html_e( 'Street Address', OPA_DOMAIN ); ?></label></th>
		<td><input type="text" name="address_line_1" value="<?php echo esc_html( get_the_author_meta( 'address_line_1', $user->ID ) ); ?>"></td>
	</tr>
	<tr>
		<th><label for="address_city"><?php esc_html_e( 'City', OPA_DOMAIN ); ?></label></th>
		<td><input type="text" name="address_city" value="<?php echo esc_html( get_the_author_meta( 'address_city', $user->ID ) ); ?>"></td>
	</tr>
	<tr>
		<th><label for="address_state"><?php esc_html_e( 'State', OPA_DOMAIN ); ?></label></th>
		<td><input type="text" name="address_state" value="<?php echo esc_html( get_the_author_meta( 'address_state', $user->ID ) ); ?>"></td>
	</tr>
	<tr>
		<th><label for="address_zip"><?php esc_html_e( 'Zip', OPA_DOMAIN ); ?></label></th>
		<td><input type="text" name="address_zip" value="<?php echo esc_html( get_the_author_meta( 'address_zip', $user->ID ) ); ?>"></td>
	</tr>
</table>
