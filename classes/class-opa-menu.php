<?php
class OPA_Menu {

	static function init() {
		add_action( 'admin_init', __CLASS__ . '::register_settings' );
		add_action( 'admin_menu', __CLASS__ . '::admin_menu' );
	}

	/**
	 * Admin Menu
	 */
	static function admin_menu() {
		add_menu_page(
			__( 'OPA', OPA_DOMAIN ),
			__( 'OPA', OPA_DOMAIN ),
			'manage_options',
			'opa',
			__CLASS__ . '::opa_management_page'
		);

		add_submenu_page(
			'opa',
			__( 'Management', OPA_DOMAIN ),
			__( 'Management', OPA_DOMAIN ),
			'manage_options',
			'opa',
			__CLASS__ . '::opa_management_page'
		);

		add_submenu_page(
			'opa',
			__( 'Settings', OPA_DOMAIN ),
			__( 'Settings', OPA_DOMAIN ),
			'manage_options',
			'opa_settings',
			__CLASS__ . '::opa_settings_page'
		);
	}

	/**
	 * Admin Menu - Main
	 */
	static function opa_management_page() {
		require_once( OPA_PATH . 'views/admin-management.php' );
	}

	/**
	 * Admin Menu - Settings
	 */
	static function opa_settings_page() {
		require_once( OPA_PATH . 'views/admin-settings.php' );
	}

	/**
	 * Pass Parameters and this will build the URL to get us to the correct plugin page
	 * @param $params
	 *
	 * @return string
	 */
	static function helper_url( $params = array() ) {

		$url = admin_url('admin.php?page=opa');

		if ( !empty( $params ) ) {
			foreach ( $params as $key => $value ) {
				$url .= "&" . $key . "=" . $value;
			}
		}

		return esc_url( $url );
	}

	/**
	 * Generate Breadcrumb for Plugin Admin
	 */
	static function admin_breadcrumb() {

		$breadcrumb =
		'<div class="opa-admin-breadcrumb">
			<ul>
				<li>
					<a href="' . OPA_Menu::helper_url( array() ) . '">' . __( 'Shows', OPA_DOMAIN ) . '</a>
				</li>';

			if ( array_key_exists( 'show_id', $_GET ) ) {
				$breadcrumb .=  sprintf(
					'<li>
						<a href="%s">%s</a>
					</li>',
					self::helper_url( array( 'show_id' => $_GET['show_id'] ) ),
					esc_html( get_the_title( $_GET['show_id'] ) )
				);
			}

			if ( array_key_exists( 'section', $_GET ) ) {
				$breadcrumb .=  sprintf(
					'<li>
						<a href="%s">%s</a>
					</li>',
					self::helper_url( array( 'show_id' => $_GET['show_id'], 'section' => $_GET['section'] ) ),
					esc_html( ucwords( str_replace( '-', ' ', $_GET['section'] ) ) )
				);
			}

			if ( array_key_exists( 'more', $_GET ) ) {
				$breadcrumb .=  sprintf(
					'<li>
						<span>%s</span>
					</li>',
					esc_html( ucwords( str_replace( '-', ' ', $_GET['more'] ) ) )
				);
			}

		$breadcrumb .= '</ul>
		</div>';

		echo $breadcrumb;

	}

	static function register_settings() {
		register_setting( 'opa-settings-group', 'opa_registration_fee' );
		register_setting( 'opa-settings-group', 'opa_profile_page_id' );
		register_setting( 'opa-settings-group', 'opa_show_terms' );
	}

}
