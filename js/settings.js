jQuery( function( $ ) {

	"use strict";

	var $wpbody = $( '#wpbody' ),
		$relationshipWrap = $( '.aterms-relationship-wrap' );

	// Send AJAX request to clear plugin cache
	$wpbody
		.on( 'click', '#aterms_clear_cache', function( e ) {

			e.preventDefault();

			var $this = $( this );

			$.post(
				ajaxurl,
				{
					action: 'aterms_clear_cache',
					nonce : aterms.nonce
				},
				function( response ) {

					if ( '0' === response ) {

						$this.text( $this.data( 'failed' ) ); // Request failed

					} else {

						$this.addClass( 'button-primary' ).text( $this.data( 'completed' ) ); // Request completed

						setTimeout( function() {

							window.location.reload( true );

						}, 200 );

					}

				}
			);

		});

	// + = add new row of rules, - = remove that set of rules
	$relationshipWrap
		.on( 'click', '.aterms-rule-add:not([disabled])', function( e ) {

			e.preventDefault();

			var $this = $( this ),
				$container = $this.parent(),
				$containerWrap = $container.parent();

			var $clone = $container.clone();

			$clone
				.find( 'select, input' )
				.val( '' );


			$container.after( $clone );

			$containerWrap.find( '.aterms-rule-remove' ).removeAttr( 'disabled' );

		}).on( 'click', '.aterms-rule-remove:not([disabled])', function( e ) {

			e.preventDefault();

			var $this = $( this ),
				$container = $this.parent(),
				$containerWrap = $container.parent();

			$container.remove();

			if ( $containerWrap.children( '.aterms-terms-relationship-rule' ).length === 1 ) {

				$containerWrap.find( '.aterms-rule-remove' ).attr( 'disabled', 'disabled' );

			}

		}).on( 'click', '.aterms-rule-move-up', function( e ) {

			e.preventDefault();

			var $this = $( this ),
				$container = $this.parent(),
				$containerPrev = $container.prev( '.aterms-terms-relationship-rule' );

			if ( ! $containerPrev.length ) {

				return; // we can't move you up, sorry

			}

			$containerPrev.before( $container );

		}).on( 'click', '.aterms-rule-move-down', function( e ) {

			e.preventDefault();

			var $this = $( this ),
				$container = $this.parent(),
				$containerNext = $container.next( '.aterms-terms-relationship-rule' );

			if ( ! $containerNext.length ) {

				return; // we can't move you up, sorry

			}

			$containerNext.after( $container );

		});

	// set - to disabled if only one row of rules
	$relationshipWrap.children().each( function() {

		var $this = $( this );

		if ( $this.children( 'div' ).length !== 1 ) {

			$this.find( '.aterms-rule-remove' ).removeAttr( 'disabled', 'disabled' );

		}

	});

	// generate options interface based on saved data
	$relationshipWrap.children( 'div' ).each( function() {

		var $this = $( this ),
			postType = $this.data( 'posttype' ),
			$rule = $this.find( '.aterms-terms-relationship-rule' ),
			$options = $this.find( '.aterms-option' );

		if ( ! $options.length ) return; // nothing is saved

		var option = {};

		option['taxonomies'] = $options.data( 'option-taxonomies' );
		option['targets'] = $options.data( 'option-targets' );
		option['terms'] = $options.data( 'option-terms' );

		$.each( option['taxonomies'], function( i, id ) {

			var $template = $rule.clone();

			$template
				.find( '.aterms-terms-relationship-taxonomies option[value="' + id + '"]' )
					.prop( 'selected', 'selected' )
					.end()
				.find( '.aterms-terms-relationship-targets option[value="' + option['targets'][ i ] + '"]' )
					.prop( 'selected', 'selected' )
					.end()
				.children( '.aterms-terms-relationship-tax-input' )
					.val( option['terms'][ i ] );

			$this.append( $template );

		});

		$rule.remove();

	});

})