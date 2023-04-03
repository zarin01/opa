<?php
// Vendor
require_once( OPA_PATH . '/vendor/autoload.php' );

// Classes
require_once( OPA_PATH . '/classes/class-opa-activation.php' );
require_once( OPA_PATH . '/classes/class-opa-exports.php' );
require_once( OPA_PATH . '/classes/class-opa-functions.php' );
require_once( OPA_PATH . '/classes/class-opa-image-manipulation.php' );
require_once( OPA_PATH . '/classes/class-opa-menu.php' );
require_once( OPA_PATH . '/classes/class-opa-payment.php' );
require_once( OPA_PATH . '/classes/class-opa-permissions.php' );
require_once( OPA_PATH . '/classes/class-opa-profile.php' );
require_once( OPA_PATH . '/classes/class-opa-refunds.php' );
require_once( OPA_PATH . '/classes/class-opa-registration.php' );
require_once( OPA_PATH . '/classes/class-opa-shows.php' );

// Models
require_once( OPA_PATH . '/models/model-art.php' );
require_once( OPA_PATH . '/models/model-award.php' );
require_once( OPA_PATH . '/models/model-artist.php' );
require_once( OPA_PATH . '/models/model-jurors.php' );
require_once( OPA_PATH . '/models/model-jury-rounds.php' );
require_once( OPA_PATH . '/models/model-jury-round-art.php' );
require_once( OPA_PATH . '/models/model-jury-round-jurors.php' );
require_once( OPA_PATH . '/models/model-jury-scores.php' );
require_once( OPA_PATH . '/models/model-show.php' );

class OPA {

	/**
	 * OPA constructor.
	 */
	public function __construct() {

		// load translations
		add_action( 'plugins_loaded', array( $this, 'load_translations' ) );

		// scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts_and_styles' ) );
		add_action( 'admin_init', array( $this, 'admin_scripts_and_styles' ) );

		// ACF functionality
		add_filter( 'acf/settings/load_json', array( $this, 'acf_local_json' ));

		// Activation
		OPA_Activation::init();

		// Admin Menu
		OPA_Menu::init();

		// User Permissions
		//OPA_Permissions::init();

		// User Registration
		OPA_Registration::init();

		// Image Manipulation
		OPA_Image_Manipulation::init();

		// Shows
		OPA_Shows::init();

		// Refunds
		OPA_Refunds::init();

		// Exports
		OPA_Exports::init();

		// Profile
		OPA_Profile::init();

	}

	/**
	 * Loads the translations for the plugin
	 */
	static public function load_translations() {
		$path    = str_replace( '\\', '/', OPA_PATH );
		$mu_path = str_replace( '\\', '/', WPMU_PLUGIN_DIR );

		if ( false !== stripos( $path, $mu_path ) ) {
			load_muplugin_textdomain( OPA_DOMAIN,
				dirname( OPA_BASENAME ) . '/languages/' );
		} else {
			load_plugin_textdomain( OPA_DOMAIN,
				false,
				dirname( OPA_BASENAME ) . '/languages/' );
		}
	}

	public function scripts_and_styles() {
		wp_enqueue_style( 'rateyo', get_bloginfo('wpurl') . OPA_RELATIVE_WP_PATH . 'assets/css/vendor/rateyo.css', array(), '2.3.2', false );
		wp_enqueue_style( 'opa-plugin-css', get_bloginfo('wpurl') . OPA_RELATIVE_WP_PATH . 'assets/dist/app.min.css', array(), OPA_VERSION, false );
		wp_enqueue_script( 'rateyo', get_bloginfo('wpurl') . OPA_RELATIVE_WP_PATH . 'assets/js/vendor/jquery.rateyo.js', array('jquery'), '2.3.2', false );
		wp_enqueue_script( 'okzoom', get_bloginfo('wpurl') . OPA_RELATIVE_WP_PATH . 'assets/js/vendor/jquery.okzoom.js', array('jquery'), '1.0', false );
		wp_enqueue_script( 'opa-plugin-js', get_bloginfo('wpurl') . OPA_RELATIVE_WP_PATH . 'assets/dist/app.min.js', array('jquery'), OPA_VERSION, true );
		wp_enqueue_script( 'heic2any', get_bloginfo('wpurl') . OPA_RELATIVE_WP_PATH . 'assets/dist/heic2any.js', array('jquery'), OPA_VERSION, true );

		wp_localize_script( 'opa-plugin-js', 'localized', array(
			'ajax_url' => admin_url( 'admin-ajax.php' )
		) );
	}

	public function admin_scripts_and_styles() {
		wp_enqueue_style( 'croppie', get_bloginfo('wpurl') . OPA_RELATIVE_WP_PATH . 'assets/css/vendor/croppie.css', array(), '2.6.4', false );
		wp_enqueue_style( 'opa-plugin-admin', get_bloginfo('wpurl') . OPA_RELATIVE_WP_PATH . 'admin/assets/dist/admin.min.css', array(), OPA_VERSION, false );
		wp_enqueue_script( 'croppie', get_bloginfo('wpurl') . OPA_RELATIVE_WP_PATH . 'assets/js/vendor/jquery.croppie.js', array('jquery'), '2.6.4', false );
	}

	static public function acf_local_json( $paths ) {
		$paths[] = OPA_PATH . '/acf-json';
		return $paths;
	}

}

$plugin = new OPA();
