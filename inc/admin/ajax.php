<?php

// Exit if accessed directly
! defined( 'ABSPATH' ) && exit;

// Check if class apready exists
if ( ! class_exists( 'AtermsAdminAjax' ) ) :

	class AtermsAdminAjax {

		private static $prefix;

		public function __construct() {

			add_action( 'wp_ajax_aterms_clear_cache', array( $this, 'clear_cache' ) );

		}

		public function clear_cache() {

			self::$prefix = AutoTerms::$prefix;

			check_ajax_referer( 'aterms_clear_cache_nonce', 'nonce', false ); // no nonce, no fun!

			delete_transient( self::$prefix . 'aterms_terms_query' );

			echo '1';

			die(); // Now, young Skywalker… you will die.
		}

	}

endif; // End "if ! class exists" check

new AtermsAdminAjax();