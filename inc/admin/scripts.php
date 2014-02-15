<?php

// Exit if accessed directly
! defined( 'ABSPATH' ) && exit;

// Check if class apready exists
if ( ! class_exists( 'AtermsAdminScripts' ) ) :

	class AtermsAdminScripts {

		public function __construct() {

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		}

		public function enqueue_scripts( $hook ) {

		    if( 'settings_page_auto-terms' !== $hook ) return; // we only want this script on Settings -> Writing page

		    wp_enqueue_script( 'aterms_settings_script', ATERMS_JS_URL . 'settings.js', array( 'jquery' ), '0.0.1' );

			wp_localize_script(
				'aterms_settings_script',
				'aterms',
				array(
					'nonce' => wp_create_nonce( 'aterms_clear_cache_nonce' ),
				)
			);

		}

	}

endif; // End "if ! class exists" check

new AtermsAdminScripts();