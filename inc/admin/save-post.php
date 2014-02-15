<?php

// Exit if accessed directly
! defined( 'ABSPATH' ) && exit;

// Check if class apready exists
if ( ! class_exists( 'AtermsAdminSavePost' ) ) :

	class AtermsAdminSavePost {

		public function __construct() {

			add_action( 'save_post', array( $this, 'save_post_hook' ), 10, 2 );

		}

		public function save_post_hook( $post_id, $post ) {

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {

				return; // we are not interested in autosave

			}

			/* Get the post type object. */
			$post_type = get_post_type_object( $post->post_type );

			if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {

				return;

			}

			$option_taxonomies = get_option( 'aterms_terms_relationship_taxonomies' );

			if ( empty( $option_taxonomies[ $post->post_type ] ) ) {

				return;

			}

			$post_taxonomies = get_object_taxonomies( $post->post_type );

			if ( empty( $post_taxonomies ) ) {

				return; // Gotcha!!! No taxonomies defined! You sneaky bastard!

			}

			$post_terms = wp_get_object_terms( $post_id, $post_taxonomies, array( 'fields' => 'ids' ) );

			if ( empty( $post_terms ) ) {

				return; // No terms = not interesting to us

			}

			$option_targets = get_option( 'aterms_terms_relationship_targets' );
			$option_terms = get_option( 'aterms_terms_relationship_tax_input' );
			$overwrite_terms = get_option( 'aterms_overwrite_terms' );

			$i = 0;

			foreach ( $option_taxonomies[ $post->post_type ] as $term_id ) {

				if ( in_array( $term_id, $post_terms ) ) {

					$terms_to_set = $option_terms[ $post->post_type ][ $i ];

					if ( empty( $terms_to_set ) ) {

						continue; // Empty input filed, do nothing with it

					}

					$terms_to_set = str_replace( ', ', ',', $terms_to_set ); // we of course support space after comma for separating tags
					$terms_to_set = str_replace( ' ,', ',', $terms_to_set ); // yeah we even support space before comma, I'm looking at you 00

					if ( ',' === $terms_to_set ) {

						continue; // You only had comma in the input field??? What is wrong with you man...

					}

					$terms_to_set = explode( ',', $terms_to_set );

					wp_set_object_terms( $post_id, $terms_to_set, $option_targets[ $post->post_type ][ $i ], ! $overwrite_terms );

				}

				$i++;

			}

		}

	}

endif; // End "if ! class exists" check

new AtermsAdminSavePost();