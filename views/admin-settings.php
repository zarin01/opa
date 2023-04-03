<?php
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
?>
<div class="wrap">

    <h1>
        <?php _e( 'OPA Settings', OPA_DOMAIN ) ?>
    </h1>
    <hr>
    <br>

	<?php settings_errors() ?>

    <form method="post" action="options.php">

		<?php settings_fields( 'opa-settings-group' ); ?>
		<?php do_settings_sections( 'opa-settings-group' ); ?>

        <p><?php _e( 'Enter the following settings to configure the OPA Plugin.', OPA_DOMAIN ) ?></p>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e( 'Registration Fee', OPA_DOMAIN ) ?></th>
                <td><input type="text" name="opa_registration_fee"
                           value="<?php echo OPA_REGISTRATION_FEE; ?>"/></td>
            </tr>
            <!--
            <tr valign="top">
                <th scope="row"><?php _e( 'Profile Page ID (Unused)', OPA_DOMAIN ) ?></th>
                <td><input type="text" name="opa_profile_page_id"
                           value="<?php echo intval( get_option( 'opa_profile_page_id' ) ); ?>"/></td>
            </tr>
            -->
            <tr valign="top">
                <th scope="row"><?php _e( 'Show Terms and Conditions', OPA_DOMAIN ) ?></th>
                <td>
                    <?php
                    $content = get_option('opa_show_terms');
                    wp_editor( $content, 'opa_show_terms', $settings = array('textarea_rows'=> '10') );
                    ?>
                </td>
            </tr>
        </table>

		<?php submit_button(); ?>
    </form>
</div>
