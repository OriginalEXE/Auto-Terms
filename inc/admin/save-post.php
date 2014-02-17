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

			$post_taxonomies = array_filter( $post_taxonomies, 'is_taxonomy_hierarchical' ); // we only want hierarchical taxonomies!

			$post_terms = wp_get_object_terms( $post_id, $post_taxonomies, array( 'fields' => 'ids' ) );

			$overwrite_terms = get_option( 'aterms_overwrite_terms' );

			$clean_terms = false; // whether plugin should clear all non-hierarchical taxonomies. Will be set to true if $overwrite_terms === true and there are no taxonomies

			if ( empty( $post_terms ) ) {

				if ( (bool) $overwrite_terms ) {

					$clean_terms = true;

				} else {

					return; // No terms = not interesting to us

				}

			}

			$option_targets = get_option( 'aterms_terms_relationship_targets' );
			$option_terms = get_option( 'aterms_terms_relationship_tax_input' );

			$i = 0; // iterration that counts which term are we checking
			$j = 0; // this one counts how many successful iterations there were (but is also 0 indexed)

			foreach ( $option_taxonomies[ $post->post_type ] as $term_id ) {

				if ( in_array( $term_id, $post_terms ) || $clean_terms ) {

					if ( ! $clean_terms ) {

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

						if ( $j ) { // $j is bigger than 0, means we already had one successful match

							$overwrite_terms = false; // this will be turned to true in actual function call

							$post_terms = array_merge( $post_terms, $terms_to_set );

						}

						wp_set_object_terms( $post_id, $terms_to_set, $option_targets[ $post->post_type ][ $i ], ! $overwrite_terms );

						$j++;

					} else { // just remove all terms

						wp_set_object_terms( $post_id, '', $option_targets[ $post->post_type ][ $i ], false );

					}

				}

				$i++;

			}

		}

	}

endif; // End "if ! class exists" check

new AtermsAdminSavePost();