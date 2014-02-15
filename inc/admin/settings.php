<?php

// Exit if accessed directly
! defined( 'ABSPATH' ) && exit;

// Check if class apready exists
if ( ! class_exists( 'AtermsAdminSettings' ) ) :

	class AtermsAdminSettings {

		public function __construct() {

			add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );

		}

		public function register_plugin_settings() { // register our settings section and place it Settings -> Writing

			add_settings_section(
				'aterms_auto_terms_section',
				__( 'Auto Terms', 'aterms' ),
				array( $this, 'auto_terms_section_callback' ),
				'writing'
			);

			add_settings_field(	
				'aterms_overwrite_terms',
				__( 'Overwrite terms', 'aterms' ),
				array( $this, 'overwrite_terms_callback' ),
				'writing',
				'aterms_auto_terms_section',
				array(
					__( 'If checked, plugin will overwrite terms based on relationship, instead of appending.', 'aterms' ),
				)
			);

			add_settings_field(	
				'aterms_clear_cache',
				__( 'Clear cache', 'aterms' ),
				array( $this, 'clear_cache_callback' ),
				'writing',
				'aterms_auto_terms_section',
				array(
					__( 'Plugin caches the list of terms/taxonomies. If you don\'t see your term/taxonomy in the list below, click this button, then refresh after confirmation.', 'aterms' ),
				)
			);

			add_settings_field(	
				'aterms_terms_relationship',
				__( 'Term relationship', 'aterms' ),
				array( $this, 'terms_relationship_callback' ),
				'writing',
				'aterms_auto_terms_section',
				array(
					__( 'Set up a terms relationship.', 'aterms' ),
				)
			);

			register_setting(
				'writing',
				'aterms_overwrite_terms',
				'intval'
			);

			register_setting(
				'writing',
				'aterms_terms_relationship_taxonomies'
			);

			register_setting(
				'writing',
				'aterms_terms_relationship_targets'
			);

			register_setting(
				'writing',
				'aterms_terms_relationship_tax_input'
			);

		}

		//
		// Section callbacks
		//

		public function auto_terms_section_callback() {

			_e( 'Auto Terms configuration', 'aterms' );

		}

		//
		// Field callbacks
		//

		public function overwrite_terms_callback( $args ) {

			$html = '<label for="aterms_overwrite_terms">';

			$html .= '<input type="checkbox" id="aterms_overwrite_terms" name="aterms_overwrite_terms" value="1" ' . checked( 1, get_option('aterms_overwrite_terms'), false ) . '>';

			$html .= ' ' . $args[0] . '</label>';

			echo $html;

		}

		public function clear_cache_callback( $args ) {

			$html = '<label for="aterms_clear_cache">';

			$html .= '<button id="aterms_clear_cache" class="button" data-completed="' . __( 'Cleared!', 'aterms' ) . '" data-failed="' . __( 'Failed!', 'aterms' ) . '">' . __( 'Clear cache', 'aterms' ) . '</button><br>';

			$html .= '<p class="description">' . $args[0] . '</p></label>';

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

			$option_taxonomies = get_option( 'aterms_terms_relationship_taxonomies' );
			$option_targets = get_option( 'aterms_terms_relationship_targets' );
			$option_terms = get_option( 'aterms_terms_relationship_tax_input' );

			foreach ( $query as $post_type => $taxonomies ) {

				echo '<div class="aterms-relationship-wrap-' . $post_type . '" data-posttype="' . $post_type . '">';

				echo '<h4>' . $post_type . '</h4>';

				$output_taxonomies = '<select class="aterms-terms-relationship-taxonomies" name="aterms_terms_relationship_taxonomies[' . $post_type . '][]">';
				$output_targets    = '<select class="aterms-terms-relationship-targets" name="aterms_terms_relationship_targets[' . $post_type . '][]">';

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

				$output_tax_input = '<input class="aterms-terms-relationship-tax-input" name="aterms_terms_relationship_tax_input[' . $post_type . '][]" type="text" placeholder="' . __( 'Terms separated by comma.', 'aterms' ) . '">';

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