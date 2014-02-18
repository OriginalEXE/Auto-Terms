<?php

// Exit if accessed directly
! defined( 'ABSPATH' ) && exit;

// Check if class apready exists
if ( ! class_exists( 'AtermsAdminSettings' ) ) :

	class AtermsAdminSettings {

		private static $prefix;

		public function __construct() {

			add_action( 'admin_menu', array( $this, 'register_plugin_menu' ) );
			add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );

		}

		public function register_plugin_menu() { // Register new Auto Terms page under "Settings" menu

			add_options_page( __( 'Auto Terms', 'aterms' ), __( 'Auto Terms', 'aterms' ), 'manage_options', 'auto-terms', array( $this, 'register_plugin_menu_callback' ) );

		}

		public function register_plugin_menu_callback() {

			?>

			<div class="wrap">
				<h2><?php _e( 'Auto Terms Plugin', 'aterms' ); ?></h2>

				<form method="post" action="options.php"> 

					<?php settings_fields( 'auto-terms' ); ?>
					<?php do_settings_sections( 'auto-terms' ); ?>

					<?php submit_button(); ?>

				</form>
			</div>			

			<?php

		}

		public function register_plugin_settings() { // register our settings section and place it Settings -> Auto Terms

			self::$prefix = AutoTerms::$prefix;

			add_settings_section(
				'aterms_auto_terms_section',
				'',
				array( $this, 'auto_terms_section_callback' ),
				'auto-terms'
			);

			add_settings_field(	
				'aterms_overwrite_terms',
				__( 'Overwrite terms', 'aterms' ),
				array( $this, 'overwrite_terms_callback' ),
				'auto-terms',
				'aterms_auto_terms_section',
				array(
					__( 'If checked, plugin will overwrite terms based on relationship, instead of appending.', 'aterms' ),
				)
			);

			add_settings_field(	
				'aterms_clear_cache',
				__( 'Clear cache', 'aterms' ),
				array( $this, 'clear_cache_callback' ),
				'auto-terms',
				'aterms_auto_terms_section',
				array(
					__( 'Plugin caches the list of terms/taxonomies. If you don\'t see your term/taxonomy in the list below, click this button. NOTE: After completion, window will refresh, make sure you save your settings first.', 'aterms' ),
				)
			);

			add_settings_field(	
				'aterms_terms_relationship',
				__( 'Term relationship', 'aterms' ),
				array( $this, 'terms_relationship_callback' ),
				'auto-terms',
				'aterms_auto_terms_section',
				array(
					__( 'Set up a terms relationship.', 'aterms' ),
				)
			);

			register_setting(
				'auto-terms',
				self::$prefix . 'aterms_overwrite_terms',
				'intval'
			);

			register_setting(
				'auto-terms',
				self::$prefix . 'aterms_terms_relationship_taxonomies'
			);

			register_setting(
				'auto-terms',
				self::$prefix . 'aterms_terms_relationship_targets'
			);

			register_setting(
				'auto-terms',
				self::$prefix . 'aterms_terms_relationship_tax_input'
			);

		}

		//
		// Section callbacks
		//

		public function auto_terms_section_callback() {

			_e( 'Connect and auto assign (non hierarchical) terms to posts in specified (hierarchical) terms', 'aterms' );

		}

		//
		// Field callbacks
		//

		public function overwrite_terms_callback( $args ) {

			$html = '<label for="aterms_overwrite_terms">';

			$html .= '<input type="checkbox" id="aterms_overwrite_terms" name="' . self::$prefix . 'aterms_overwrite_terms" value="1" ' . checked( 1, get_option( self::$prefix . 'aterms_overwrite_terms' ), false ) . '>';

			$html .= ' ' . $args[0] . '</label>';

			echo $html;

		}

		public function clear_cache_callback( $args ) {

			$html = '<label for="aterms_clear_cache">';

			$html .= '<button id="aterms_clear_cache" class="button" data-completed="' . __( 'Cleared!', 'aterms' ) . '" data-failed="' . __( 'Failed!', 'aterms' ) . '">' . __( 'Clear cache', 'aterms' ) . '</button><br>';

			$html .= '</label><p class="description">' . $args[0] . '</p>';

			echo $html;

		}

		public function terms_relationship_callback( $args ) {

			$query = AtermsAdminQuery::generate_terms_object();

			if ( empty( $query ) || ! is_array( $query ) ) {

				printf( '<strong>%s</strong><br>', __( 'Query is empty! Ups :S', 'aterms' ) );
				printf( '%s<br>', __( 'That means that you either have no post types that have both hierarchical and non-hierarchical taxonomy registered (weird), or something went wrong.', 'aterms' ) );
				printf( '%s<br>', __( 'If you think this is wrong, contact the developer!', 'aterms' ) );

			} else {

				echo '<div class="aterms-relationship-wrap">';

				$this->relationship_output( $query );

				echo '</div>';

			}

		}

		public function relationship_output( $query ) {

			$option_taxonomies = get_option( self::$prefix . 'aterms_terms_relationship_taxonomies' );
			$option_targets = get_option( self::$prefix . 'aterms_terms_relationship_targets' );
			$option_terms = get_option( self::$prefix . 'aterms_terms_relationship_tax_input' );

			foreach ( $query as $post_type => $taxonomies ) {

				echo '<div class="aterms-relationship-wrap-' . $post_type . '" data-posttype="' . $post_type . '">';

				$post_type_object = get_post_type_object( $post_type );

				echo '<h4>' . $post_type_object->label . '</h4>';

				$output_taxonomies = '<select class="aterms-terms-relationship-taxonomies" name="' . self::$prefix . 'aterms_terms_relationship_taxonomies[' . $post_type . '][]">';
				$output_targets    = '<select class="aterms-terms-relationship-targets" name="' . self::$prefix . 'aterms_terms_relationship_targets[' . $post_type . '][]">';

				foreach ( $taxonomies['hierarchical'] as $taxonomy => $terms ) {

					$output_taxonomies .= '<optgroup label="' . esc_attr( $taxonomy ) . '">';

					foreach ( $terms as $term_id => $term ) {

						$output_taxonomies .= '<option value="' . $term_id . '">' . $term . '</option>';

					}

					$output_taxonomies .= '</optgroup>';

				}

				foreach ( $taxonomies['nonhierarchical'] as $taxonomy => $label ) {


					$output_targets .= '<option value="' . $taxonomy . '">' . $label . '</option>';

				}

				$output_taxonomies .= '</select>';

				$output_tax_input = '<input class="aterms-terms-relationship-tax-input" name="' . self::$prefix . 'aterms_terms_relationship_tax_input[' . $post_type . '][]" type="text" placeholder="' . __( 'Terms separated by comma.', 'aterms' ) . '">';

				echo '<div class="aterms-terms-relationship-rule">'
				. $output_taxonomies . '<span> &rarr; </span>'
				. $output_targets . $output_tax_input
				. '<button class="button aterms-rule-add"> + </button>'
				. '<button class="button aterms-rule-remove" disabled="disabled"> - </button>'
				. '<button class="button aterms-rule-move-up"> &uarr; </button>'
				. '<button class="button aterms-rule-move-down"> &darr; </button>'
				. '</div>';

				if (
					! empty( $option_taxonomies[ $post_type ] )
					&& ! empty( $option_targets[ $post_type ] )
					&& ! empty( $option_terms[ $post_type ] )
				) {

					echo '<div class="aterms-option" data-posttype="'
					. $post_type
					. '" data-option-taxonomies="'
					. esc_attr( json_encode( $option_taxonomies[ $post_type ] ) )
					. '" data-option-targets="'
					. esc_attr( json_encode( $option_targets[ $post_type ] ) )
					. '" data-option-terms="'
					. esc_attr( json_encode( $option_terms[ $post_type ] ) )
					. '"></div>';

				}

				echo '</div>';

			}

		}

	}

endif; // End "if ! class exists" check

new AtermsAdminSettings();