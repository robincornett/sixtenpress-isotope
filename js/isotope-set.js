/**
 * Set up the isotope script and filters.
 */
(function ( document, $, undefined ) {
	'use strict';
	var SixTen = {};

	SixTen.init = function () {

		$( window ).on( 'resize.stp', _doIsotope ).triggerHandler( 'resize.stp' );

		/**
		 * Filter using an unordered list.
		 */
		$( '.filter a' ).on( 'click.stp', function () {
			_doFilter( $( this ) );
		} );

		/**
		 * Filter using dropdown(s).
		 */
		$( '.filter' ).on( 'change.stpselect', function() {
			_doSelect( $( this ) );
		} );
	};

	function _doIsotope() {
		var $container = $( '.masonry' );
		$container.isotope( {
			itemSelector: 'article',
			percentPosition: true,
			masonry: {
				isAnimated: true,
				gutter: parseInt( SixTenPress.gutter )
			}
		} );
	}

	/**
	 * Filter using an unordered list (buttons)
	 * @param $select
	 * @returns {boolean}
	 * @private
	 */
	function _doFilter( $select ) {
		var selector = $select.attr( 'data-filter' );
		$( '.masonry' ).isotope( { filter: selector } );
		$select.parents( 'ul' ).find( 'a' ).removeClass( 'active' );
		$select.addClass( 'active' );
		return false;
	}

	/**
	 * Filter using a dropdown/select
	 * @param $select
	 * @returns {boolean}
	 * @private
	 */
	function _doSelect( $select ) {
		var filters      = {},
		    group        = $select.attr( 'data-filter-group' );
		filters[ group ] = $select.find( 'option:selected' ).attr( 'value' );

		var selector = _combineFilters( filters );
		$( '.masonry' ).isotope( {
			filter: selector
		} );

		return false;
	}

	/**
	 * Combine two select filters
	 * @param obj
	 * @returns {string}
	 * @private
	 */
	function _combineFilters( obj ) {
		var value = '';
		for ( var prop in obj ) {
			value += obj[ prop ];
		}
		return value;
	}

	$( document ).ready( function () {
		SixTen.params = typeof SixTenPress === 'undefined' ? '' : SixTenPress;

		if ( typeof SixTen.params !== 'undefined' ) {
			SixTen.init();
		}
	} );
})( document, jQuery );
