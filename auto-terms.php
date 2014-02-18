<?php

/**
 * Plugin Name: Auto Terms
 * Plugin URI: http://profiles.wordpress.org/originalexe
 * Description: Connect and auto assign terms to posts in specified taxonomies
 * Author: OriginalEXE
 * Author URI: http://profiles.wordpress.org/originalexe
 * Version: 0.0.1
 */

// Exit if accessed directly
! defined( 'ABSPATH' ) && exit;

// Check if class apready exists
if ( ! class_exists( 'AutoTerms' ) ) :

final class AutoTerms {

	// Will hold the only instance of our main plugin class
	private static $instance;

	// Holds prefix, used for wpml compatibility
	public static $prefix;

	// Instantiate the class and set up stuff
	public static function instantiate() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AutoTerms ) ) {

			self::$instance = new AutoTerms();
			self::$instance->define_constants();
			self::$instance->include_files();

			// load textdomain
			add_action( 'plugins_loaded', array( 'AutoTerms', 'load_textdomain' ) );

			// set prefix (WPML compatibility)
			add_action( 'plugins_loaded', array( 'AutoTerms', 'set_prefix' ) );

		}

		return self::$instance;

	}

	// Defines plugin constants
	public function define_constants() {

		// Plugin version
		if ( ! defined( 'ATERMS_VERSION' ) )
			define( 'ATERMS_VERSION', '0.0.1' );

		// Plugin Folder Path
		if ( ! defined( 'ATERMS_PLUGIN_DIR' ) )
			define( 'ATERMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

		// Plugin Include Path
		if ( ! defined( 'ATERMS_PLUGIN_DIR_INC' ) )
			define( 'ATERMS_PLUGIN_DIR_INC', ATERMS_PLUGIN_DIR . 'inc/' );

		// Plugin Folder URL
		if ( ! defined( 'ATERMS_PLUGIN_URL' ) )
			define( 'ATERMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		// Plugin JS Folder URL
		if ( ! defined( 'ATERMS_JS_URL' ) )
			define( 'ATERMS_JS_URL', ATERMS_PLUGIN_URL . 'js/' );

		// Plugin CSS Folder URL
		if ( ! defined( 'ATERMS_CSS_URL' ) )
			define( 'ATERMS_CSS_URL', ATERMS_PLUGIN_URL . 'css/' );

		// Plugin Root File
		if ( ! defined( 'ATERMS_PLUGIN_FILE' ) )
			define( 'ATERMS_PLUGIN_FILE', __FILE__ );

	}

	// Includes necessary files
	public function include_files() {

		if ( is_admin() ) {

			if ( ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {

				require_once ATERMS_PLUGIN_DIR_INC . 'admin/settings.php';
				require_once ATERMS_PLUGIN_DIR_INC . 'admin/scripts.php';
				require_once ATERMS_PLUGIN_DIR_INC . 'admin/styles.php';
				require_once ATERMS_PLUGIN_DIR_INC . 'admin/query.php';

			}

			require_once ATERMS_PLUGIN_DIR_INC . 'admin/ajax.php';

		}

		require_once ATERMS_PLUGIN_DIR_INC . 'admin/save-post.php';

	}

	public static function load_textdomain() {

		$lang_dir = dirname( plugin_basename( ATERMS_PLUGIN_FILE ) ) . '/languages/';

		$lang_dir = apply_filters( 'aterms_textdomain_location', $lang_dir );

		load_plugin_textdomain( 'aterms', false, $lang_dir );	

	}

	public function set_prefix() {

		self::$prefix = ( defined( 'ICL_LANGUAGE_CODE' ) ) ? esc_attr( ICL_LANGUAGE_CODE ) . '_' : '';

	}

}

endif; // End "if ! class exists" check

AutoTerms::instantiate();