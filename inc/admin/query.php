<?php

// Exit if accessed directly
! defined( 'ABSPATH' ) && exit;

// Check if class apready exists
if ( ! class_exists( 'AtermsAdminQuery' ) ) :

	class AtermsAdminQuery {

		public static function generate_terms_object() {

				$cached = get_transient( 'aterms_terms_query' );

				if ( $cached ) return $cached; // cache got it covered, pheeew

				$post_types = get_post_types();

				$result = array();

				foreach ( $post_types as $post_type ) {

					$taxonomies = get_object_taxonomies( $post_type, 'objects' );

					if ( 1 >= count( $taxonomies ) ) continue; // we are only interested in post types that have two or more taxonomies registered

					foreach ( $taxonomies as $taxonomy => $taxonomy_obj ) {

						if ( 'post_format' === $taxonomy ) continue; // we don't want post formats here

						if ( $taxonomy_obj->hierarchical ) {

							$taxonomy_obj->label = esc_attr( $taxonomy_obj->label );

							$result[ $post_type ]['hierarchical'][ $taxonomy_obj->label ] = array();

						} else {

							$result[ $post_type ]['nonhierarchical'][ $taxonomy ] = $taxonomy_obj->label;

						}	

						$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );

						if ( array() === $terms ) continue;

						if ( $taxonomy_obj->hierarchical ) {

							foreach ( $terms as $term ) {

								$result[ $post_type ]['hierarchical'][ $taxonomy_obj->label ][ $term->term_id ] = $term->name;

							}

						}

					}

					if (
						   ! isset ( $result[ $post_type ]['hierarchical'] )
						|| ! isset ( $result[ $post_type ]['nonhierarchical'] )
					) {

						unset( $result[ $post_type ] ); // we don't want it! We need both hierarchical and non-hierarchical taxonomies

					}

				}

				set_transient( 'aterms_terms_query', $result, DAY_IN_SECONDS );

				return $result;

			}

	}

endif; // End "if ! class exists" check