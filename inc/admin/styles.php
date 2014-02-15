<?php

// Exit if accessed directly
! defined( 'ABSPATH' ) && exit;

// Check if class apready exists
if ( ! class_exists( 'AtermsAdminStyles' ) ) :

	class AtermsAdminStyles {

		public function __construct() {

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		}

		public function enqueue_styles( $hook ) {

		    if( 'settings_page_auto-terms' !== $hook ) return; // we only want this script on Settings -> Writing page

		    wp_enqueue_style( 'aterms_settings_style', ATERMS_CSS_URL . 'settings.css', '0.0.1' );

		}

	}

endif; // End "if ! class exists" check

new AtermsAdminStyles();